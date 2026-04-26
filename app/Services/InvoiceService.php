<?php
/**
 * Invoice Service
 * Business logic layer for invoice operations
 */

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Repositories\InvoiceRepository;
use Core\Database\Database;
use App\DTOs\InvoiceData;
use Exception;

class InvoiceService
{
    private InvoiceRepository $repository;
    private Database $db;
    
    public function __construct()
    {
        $this->repository = new InvoiceRepository();
        $config = config('database');
        $this->db = Database::getInstance($config);
    }
    
    /**
     * Create invoice with line items
     */
    public function createInvoice(InvoiceData $data): int
    {
        return $this->db->transaction(function($db) use ($data) {
            // Validate client exists and belongs to workspace
            $client = $this->repository->findClient($data->clientId, $data->workspaceId);
            
            if (!$client) {
                throw new Exception('Client not found in this workspace');
            }
            
            // Generate invoice number if not provided
            $invoiceNumber = $data->invoiceNumber ?? $this->repository->generateInvoiceNumber($data->workspaceId);
            
            // Calculate totals
            $totals = $this->calculateTotals($data->items, $data->discountType, $data->discountValue);
            
            // Create invoice
            $invoiceId = $this->repository->create([
                'workspace_id' => $data->workspaceId,
                'client_id' => $data->clientId,
                'invoice_number' => $invoiceNumber,
                'reference' => $data->reference,
                'type' => $data->type,
                'status' => $data->status,
                'currency' => $data->currency,
                'exchange_rate' => $data->exchangeRate ?? 1.0,
                'language' => $data->language,
                'issue_date' => $data->issueDate,
                'due_date' => $data->dueDate,
                'subtotal' => $totals['subtotal'],
                'discount_type' => $data->discountType,
                'discount_value' => $data->discountValue,
                'tax_total' => $totals['taxTotal'],
                'total' => $totals['total'],
                'notes' => $data->notes,
                'footer' => $data->footer,
                'terms' => $data->terms,
                'created_by' => $data->createdBy
            ]);
            
            // Create line items
            foreach ($data->items as $index => $item) {
                $this->repository->createItem([
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'sku' => $item['sku'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_type' => $item['discount_type'] ?? 'percentage',
                    'discount_value' => $item['discount_value'] ?? 0,
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'line_total' => $item['line_total'],
                    'sort_order' => $index
                ]);
            }
            
            // Log activity
            $this->logActivity($invoiceId, $data->workspaceId, $data->createdBy, 'created');
            
            return $invoiceId;
        });
    }
    
    /**
     * Update invoice with line items
     */
    public function updateInvoice(int $id, int $workspaceId, InvoiceData $data, int $userId): bool
    {
        return $this->db->transaction(function($db) use ($id, $workspaceId, $data, $userId) {
            $invoice = $this->repository->findById($id, $workspaceId);
            
            if (!$invoice) {
                throw new Exception('Invoice not found');
            }
            
            // Cannot update cancelled or paid invoices
            if (in_array($invoice['status'], ['cancelled', 'paid'])) {
                throw new Exception('Cannot modify ' . $invoice['status'] . ' invoice');
            }
            
            // Calculate new totals
            $totals = $this->calculateTotals($data->items, $data->discountType, $data->discountValue);
            
            // Update invoice
            $this->repository->update($id, $workspaceId, [
                'client_id' => $data->clientId,
                'reference' => $data->reference,
                'type' => $data->type,
                'currency' => $data->currency,
                'exchange_rate' => $data->exchangeRate,
                'language' => $data->language,
                'issue_date' => $data->issueDate,
                'due_date' => $data->dueDate,
                'subtotal' => $totals['subtotal'],
                'discount_type' => $data->discountType,
                'discount_value' => $data->discountValue,
                'tax_total' => $totals['taxTotal'],
                'total' => $totals['total'],
                'notes' => $data->notes,
                'footer' => $data->footer,
                'terms' => $data->terms
            ]);
            
            // Delete existing items and recreate
            $this->repository->deleteItems($id);
            
            foreach ($data->items as $index => $item) {
                $this->repository->createItem([
                    'invoice_id' => $id,
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'sku' => $item['sku'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_type' => $item['discount_type'] ?? 'percentage',
                    'discount_value' => $item['discount_value'] ?? 0,
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'line_total' => $item['line_total'],
                    'sort_order' => $index
                ]);
            }
            
            // Log activity
            $this->logActivity($id, $workspaceId, $userId, 'updated');
            
            return true;
        });
    }
    
    /**
     * Send invoice (mark as sent)
     */
    public function sendInvoice(int $id, int $workspaceId, int $userId): bool
    {
        $invoice = $this->repository->findById($id, $workspaceId);
        
        if (!$invoice || $invoice['status'] === 'cancelled') {
            return false;
        }
        
        $this->repository->updateStatus($id, $workspaceId, 'sent');
        $this->logActivity($id, $workspaceId, $userId, 'sent');
        
        return true;
    }
    
    /**
     * Mark invoice as viewed
     */
    public function markAsViewed(int $id, int $workspaceId): bool
    {
        $invoice = $this->repository->findById($id, $workspaceId);
        
        if (!$invoice || $invoice['viewed_at']) {
            return false;
        }
        
        $this->repository->updateStatus($id, $workspaceId, 'viewed');
        return true;
    }
    
    /**
     * Cancel invoice
     */
    public function cancelInvoice(int $id, int $workspaceId, int $userId, string $reason = null): bool
    {
        $invoice = $this->repository->findById($id, $workspaceId);
        
        if (!$invoice || in_array($invoice['status'], ['paid', 'cancelled'])) {
            return false;
        }
        
        $this->repository->updateStatus($id, $workspaceId, 'cancelled');
        $this->logActivity($id, $workspaceId, $userId, 'cancelled', $reason);
        
        return true;
    }
    
    /**
     * Duplicate invoice
     */
    public function duplicateInvoice(int $id, int $workspaceId, int $userId): ?int
    {
        return $this->repository->duplicate($id, $workspaceId, $userId);
    }
    
    /**
     * Calculate invoice totals
     */
    private function calculateTotals(array $items, string $discountType, float $discountValue): array
    {
        $subtotal = 0;
        $taxTotal = 0;
        
        foreach ($items as $item) {
            // Calculate line total before discount
            $lineSubtotal = $item['quantity'] * $item['unit_price'];
            
            // Apply item-level discount
            if ($item['discount_type'] ?? '' === 'percentage') {
                $lineSubtotal -= ($lineSubtotal * $item['discount_value'] / 100);
            } else {
                $lineSubtotal -= ($item['discount_value'] ?? 0);
            }
            
            // Calculate tax
            $taxAmount = $lineSubtotal * ($item['tax_rate'] ?? 0) / 100;
            
            $subtotal += $lineSubtotal;
            $taxTotal += $taxAmount;
            
            // Update item tax amount
            $item['tax_amount'] = $taxAmount;
            $item['line_total'] = $lineSubtotal + $taxAmount;
        }
        
        // Apply invoice-level discount
        if ($discountType === 'percentage') {
            $discountAmount = $subtotal * ($discountValue / 100);
        } else {
            $discountAmount = $discountValue;
        }
        
        $subtotal -= $discountAmount;
        $total = $subtotal + $taxTotal;
        
        return [
            'subtotal' => round($subtotal, 2),
            'taxTotal' => round($taxTotal, 2),
            'total' => round($total, 2)
        ];
    }
    
    /**
     * Get invoice statistics for dashboard
     */
    public function getDashboardStats(int $workspaceId): array
    {
        return $this->repository->getDashboardStats($workspaceId);
    }
    
    /**
     * Get revenue vs expenses data for chart
     */
    public function getRevenueVsExpenses(int $workspaceId, int $months = 12): array
    {
        return $this->repository->getRevenueVsExpenses($workspaceId, $months);
    }
    
    /**
     * Get top clients by revenue
     */
    public function getTopClients(int $workspaceId, int $limit = 5): array
    {
        return $this->repository->getTopClients($workspaceId, $limit);
    }
    
    /**
     * Log activity
     */
    private function logActivity(int $invoiceId, int $workspaceId, int $userId, string $action, string $notes = null): void
    {
        try {
            $this->db->insert('eb_activity_logs', [
                'workspace_id' => $workspaceId,
                'user_id' => $userId,
                'entity_type' => 'invoice',
                'entity_id' => $invoiceId,
                'action' => $action,
                'notes' => $notes,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            // Don't fail the main operation if logging fails
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }
}

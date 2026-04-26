<?php
/**
 * Invoice Model
 * Represents an invoice entity with all business logic
 */

namespace App\Models;

use Core\Database\Database;
use DateTime;

class Invoice
{
    private Database $db;
    private string $table = 'eb_invoices';
    
    public function __construct()
    {
        $config = config('database');
        $this->db = Database::getInstance($config);
    }
    
    /**
     * Find invoice by ID with workspace isolation
     */
    public function findById(int $id, int $workspaceId): ?array
    {
        $sql = "SELECT i.*, c.company_name, c.contact_name, c.email as client_email, 
                c.vat_number as client_vat, c.address_line1 as client_address1,
                c.address_line2 as client_address2, c.city as client_city,
                c.postal_code as client_postal, c.country_code as client_country
                FROM {$this->table} i
                JOIN eb_clients c ON i.client_id = c.id
                WHERE i.id = :id AND i.workspace_id = :workspace_id AND i.deleted_at IS NULL";
        
        return $this->db->fetchOne($sql, ['id' => $id, 'workspace_id' => $workspaceId]);
    }
    
    /**
     * Get all invoices for workspace with filtering
     */
    public function findAll(int $workspaceId, array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT i.*, c.company_name, c.contact_name 
                FROM {$this->table} i
                JOIN eb_clients c ON i.client_id = c.id
                WHERE i.workspace_id = :workspace_id AND i.deleted_at IS NULL";
        
        $params = ['workspace_id' => $workspaceId];
        
        if (!empty($filters['status'])) {
            $sql .= " AND i.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['type'])) {
            $sql .= " AND i.type = :type";
            $params['type'] = $filters['type'];
        }
        
        if (!empty($filters['client_id'])) {
            $sql .= " AND i.client_id = :client_id";
            $params['client_id'] = $filters['client_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND i.issue_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND i.issue_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (i.invoice_number LIKE :search OR c.company_name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        $sql .= " ORDER BY i.created_at DESC LIMIT :limit OFFSET :offset";
        
        // Replace placeholders for LIMIT/OFFSET
        $sql = str_replace(':limit', $limit, $sql);
        $sql = str_replace(':offset', $offset, $sql);
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Create new invoice
     */
    public function create(array $data): int
    {
        $invoiceData = [
            'workspace_id' => $data['workspace_id'],
            'client_id' => $data['client_id'],
            'invoice_number' => $data['invoice_number'],
            'reference' => $data['reference'] ?? null,
            'type' => $data['type'] ?? 'invoice',
            'status' => $data['status'] ?? 'draft',
            'currency' => $data['currency'] ?? 'EUR',
            'exchange_rate' => $data['exchange_rate'] ?? 1.0,
            'language' => $data['language'] ?? 'en',
            'issue_date' => $data['issue_date'],
            'due_date' => $data['due_date'],
            'subtotal' => $data['subtotal'] ?? 0,
            'discount_type' => $data['discount_type'] ?? 'percentage',
            'discount_value' => $data['discount_value'] ?? 0,
            'tax_total' => $data['tax_total'] ?? 0,
            'total' => $data['total'] ?? 0,
            'paid_amount' => 0,
            'balance_due' => $data['total'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'footer' => $data['footer'] ?? null,
            'terms' => $data['terms'] ?? null,
            'payment_method' => $data['payment_method'] ?? null,
            'structured_reference' => $data['structured_reference'] ?? null,
            'created_by' => $data['created_by'] ?? null
        ];
        
        return $this->db->insert($this->table, $invoiceData);
    }
    
    /**
     * Update invoice
     */
    public function update(int $id, int $workspaceId, array $data): bool
    {
        $updateData = [];
        $allowedFields = [
            'client_id', 'reference', 'type', 'status', 'currency', 'exchange_rate',
            'language', 'issue_date', 'due_date', 'subtotal', 'discount_type',
            'discount_value', 'tax_total', 'total', 'notes', 'footer', 'terms',
            'payment_method', 'structured_reference'
        ];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        $updated = $this->db->update(
            $this->table,
            $updateData,
            'id = :id AND workspace_id = :workspace_id AND deleted_at IS NULL',
            ['id' => $id, 'workspace_id' => $workspaceId]
        );
        
        return $updated > 0;
    }
    
    /**
     * Soft delete invoice
     */
    public function delete(int $id, int $workspaceId): bool
    {
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() 
                WHERE id = :id AND workspace_id = :workspace_id AND deleted_at IS NULL";
        
        $result = $this->db->getConnection()->prepare($sql);
        return $result->execute(['id' => $id, 'workspace_id' => $workspaceId]);
    }
    
    /**
     * Update invoice status
     */
    public function updateStatus(int $id, int $workspaceId, string $status): bool
    {
        $validStatuses = ['draft', 'sent', 'viewed', 'partial', 'paid', 'overdue', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $updateData = ['status' => $status];
        
        if ($status === 'sent') {
            $updateData['sent_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'viewed') {
            $updateData['viewed_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'paid') {
            $updateData['paid_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->update($id, $workspaceId, $updateData);
    }
    
    /**
     * Record payment
     */
    public function recordPayment(int $id, int $workspaceId, float $amount, string $paymentMethod = null): bool
    {
        return $this->db->transaction(function($db) use ($id, $workspaceId, $amount, $paymentMethod) {
            // Get current invoice
            $invoice = $this->findById($id, $workspaceId);
            
            if (!$invoice) {
                throw new \Exception('Invoice not found');
            }
            
            $newPaidAmount = $invoice['paid_amount'] + $amount;
            $newBalance = $invoice['balance_due'] - $amount;
            
            // Determine new status
            if ($newBalance <= 0) {
                $newStatus = 'paid';
                $paidAt = date('Y-m-d H:i:s');
            } elseif ($newPaidAmount > 0) {
                $newStatus = 'partial';
                $paidAt = null;
            } else {
                $newStatus = $invoice['status'];
                $paidAt = null;
            }
            
            $sql = "UPDATE {$this->table} SET 
                    paid_amount = :paid_amount,
                    balance_due = :balance_due,
                    status = :status,
                    payment_method = COALESCE(:payment_method, payment_method),
                    paid_at = COALESCE(:paid_at, paid_at)
                    WHERE id = :id AND workspace_id = :workspace_id";
            
            $stmt = $db->getConnection()->prepare($sql);
            return $stmt->execute([
                'paid_amount' => $newPaidAmount,
                'balance_due' => max(0, $newBalance),
                'status' => $newStatus,
                'payment_method' => $paymentMethod,
                'paid_at' => $paidAt,
                'id' => $id,
                'workspace_id' => $workspaceId
            ]);
        });
    }
    
    /**
     * Get overdue invoices
     */
    public function getOverdue(int $workspaceId): array
    {
        $sql = "SELECT i.*, c.company_name 
                FROM {$this->table} i
                JOIN eb_clients c ON i.client_id = c.id
                WHERE i.workspace_id = :workspace_id 
                AND i.status IN ('sent', 'partial') 
                AND i.due_date < CURRENT_DATE 
                AND i.deleted_at IS NULL
                ORDER BY i.due_date ASC";
        
        return $this->db->query($sql, ['workspace_id' => $workspaceId]);
    }
    
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(int $workspaceId, string $currency = 'EUR'): array
    {
        $currentMonthStart = date('Y-m-01');
        $currentMonthEnd = date('Y-m-t');
        
        // Revenue this month (paid invoices)
        $revenueSql = "SELECT COALESCE(SUM(paid_amount), 0) as total 
                       FROM {$this->table} 
                       WHERE workspace_id = :workspace_id 
                       AND status IN ('paid', 'partial')
                       AND paid_at >= :month_start 
                       AND paid_at <= :month_end
                       AND deleted_at IS NULL";
        
        $revenueResult = $this->db->fetchOne($revenueSql, [
            'workspace_id' => $workspaceId,
            'month_start' => $currentMonthStart,
            'month_end' => $currentMonthEnd
        ]);
        
        // Outstanding invoices (unpaid balance)
        $outstandingSql = "SELECT COALESCE(SUM(balance_due), 0) as total 
                          FROM {$this->table} 
                          WHERE workspace_id = :workspace_id 
                          AND status IN ('sent', 'partial', 'overdue')
                          AND deleted_at IS NULL";
        
        $outstandingResult = $this->db->fetchOne($outstandingSql, [
            'workspace_id' => $workspaceId
        ]);
        
        // Overdue count
        $overdueSql = "SELECT COUNT(*) as count 
                      FROM {$this->table} 
                      WHERE workspace_id = :workspace_id 
                      AND status IN ('sent', 'partial')
                      AND due_date < CURRENT_DATE 
                      AND deleted_at IS NULL";
        
        $overdueResult = $this->db->fetchOne($overdueSql, [
            'workspace_id' => $workspaceId
        ]);
        
        // Total invoices count
        $totalSql = "SELECT COUNT(*) as count 
                    FROM {$this->table} 
                    WHERE workspace_id = :workspace_id 
                    AND deleted_at IS NULL";
        
        $totalResult = $this->db->fetchOne($totalSql, [
            'workspace_id' => $workspaceId
        ]);
        
        return [
            'revenue_this_month' => (float) ($revenueResult['total'] ?? 0),
            'outstanding_invoices' => (float) ($outstandingResult['total'] ?? 0),
            'overdue_count' => (int) ($overdueResult['count'] ?? 0),
            'total_invoices' => (int) ($totalResult['count'] ?? 0)
        ];
    }
    
    /**
     * Generate next invoice number
     */
    public function generateInvoiceNumber(int $workspaceId, string $prefix = 'INV'): string
    {
        $year = date('Y');
        $pattern = "{$prefix}-{$year}-%";
        
        $sql = "SELECT invoice_number FROM {$this->table} 
                WHERE workspace_id = :workspace_id 
                AND invoice_number LIKE :pattern 
                AND deleted_at IS NULL
                ORDER BY invoice_number DESC 
                LIMIT 1";
        
        $lastInvoice = $this->db->fetchOne($sql, [
            'workspace_id' => $workspaceId,
            'pattern' => $pattern
        ]);
        
        if ($lastInvoice) {
            // Extract sequence number
            preg_match('/-(\d+)$/', $lastInvoice['invoice_number'], $matches);
            $nextNum = isset($matches[1]) ? (int) $matches[1] + 1 : 1;
        } else {
            $nextNum = 1;
        }
        
        return sprintf("%s-%s-%04d", $prefix, $year, $nextNum);
    }
    
    /**
     * Duplicate invoice
     */
    public function duplicate(int $id, int $workspaceId, int $userId): ?int
    {
        return $this->db->transaction(function($db) use ($id, $workspaceId, $userId) {
            $original = $this->findById($id, $workspaceId);
            
            if (!$original) {
                return null;
            }
            
            // Create new invoice
            $newInvoiceNumber = $this->generateInvoiceNumber($workspaceId);
            
            $newId = $this->create([
                'workspace_id' => $workspaceId,
                'client_id' => $original['client_id'],
                'invoice_number' => $newInvoiceNumber,
                'reference' => $original['reference'],
                'type' => $original['type'],
                'status' => 'draft',
                'currency' => $original['currency'],
                'exchange_rate' => $original['exchange_rate'],
                'language' => $original['language'],
                'issue_date' => date('Y-m-d'),
                'due_date' => date('Y-m-d', strtotime('+30 days')),
                'subtotal' => $original['subtotal'],
                'discount_type' => $original['discount_type'],
                'discount_value' => $original['discount_value'],
                'tax_total' => $original['tax_total'],
                'total' => $original['total'],
                'notes' => $original['notes'],
                'footer' => $original['footer'],
                'terms' => $original['terms'],
                'created_by' => $userId
            ]);
            
            // Copy line items
            $itemsSql = "SELECT * FROM eb_invoice_items WHERE invoice_id = :invoice_id";
            $items = $this->db->query($itemsSql, ['invoice_id' => $id]);
            
            foreach ($items as $item) {
                $itemData = [
                    'invoice_id' => $newId,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'],
                    'sku' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_type' => $item['discount_type'],
                    'discount_value' => $item['discount_value'],
                    'tax_rate' => $item['tax_rate'],
                    'tax_amount' => $item['tax_amount'],
                    'line_total' => $item['line_total'],
                    'sort_order' => $item['sort_order']
                ];
                
                $db->insert('eb_invoice_items', $itemData);
            }
            
            return $newId;
        });
    }
}

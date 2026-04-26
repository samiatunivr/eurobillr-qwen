<?php
/**
 * Invoice Repository
 * Data access layer for invoices
 */

namespace App\Repositories;

use Core\Database\Database;

class InvoiceRepository
{
    private Database $db;
    
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
                FROM eb_invoices i
                JOIN eb_clients c ON i.client_id = c.id
                WHERE i.id = :id AND i.workspace_id = :workspace_id AND i.deleted_at IS NULL";
        
        return $this->db->fetchOne($sql, ['id' => $id, 'workspace_id' => $workspaceId]);
    }
    
    /**
     * Find client within workspace
     */
    public function findClient(int $clientId, int $workspaceId): ?array
    {
        $sql = "SELECT * FROM eb_clients 
                WHERE id = :id AND workspace_id = :workspace_id AND deleted_at IS NULL";
        
        return $this->db->fetchOne($sql, ['id' => $clientId, 'workspace_id' => $workspaceId]);
    }
    
    /**
     * Create invoice record
     */
    public function create(array $data): int
    {
        return $this->db->insert('eb_invoices', $data);
    }
    
    /**
     * Update invoice record
     */
    public function update(int $id, int $workspaceId, array $data): bool
    {
        return $this->db->update(
            'eb_invoices',
            $data,
            'id = :id AND workspace_id = :workspace_id AND deleted_at IS NULL',
            ['id' => $id, 'workspace_id' => $workspaceId]
        ) > 0;
    }
    
    /**
     * Update invoice status
     */
    public function updateStatus(int $id, int $workspaceId, string $status): bool
    {
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
     * Create invoice item
     */
    public function createItem(array $data): int
    {
        return $this->db->insert('eb_invoice_items', $data);
    }
    
    /**
     * Delete all items for an invoice
     */
    public function deleteItems(int $invoiceId): bool
    {
        $sql = "DELETE FROM eb_invoice_items WHERE invoice_id = :invoice_id";
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute(['invoice_id' => $invoiceId]);
    }
    
    /**
     * Get invoice items
     */
    public function getItems(int $invoiceId): array
    {
        $sql = "SELECT * FROM eb_invoice_items WHERE invoice_id = :invoice_id ORDER BY sort_order ASC";
        return $this->db->query($sql, ['invoice_id' => $invoiceId]);
    }
    
    /**
     * Generate next invoice number
     */
    public function generateInvoiceNumber(int $workspaceId, string $prefix = 'INV'): string
    {
        $year = date('Y');
        $pattern = "{$prefix}-{$year}-%";
        
        $sql = "SELECT invoice_number FROM eb_invoices 
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
            preg_match('/-(\d+)$/', $lastInvoice['invoice_number'], $matches);
            $nextNum = isset($matches[1]) ? (int) $matches[1] + 1 : 1;
        } else {
            $nextNum = 1;
        }
        
        return sprintf("%s-%s-%04d", $prefix, $year, $nextNum);
    }
    
    /**
     * Duplicate invoice with items
     */
    public function duplicate(int $id, int $workspaceId, int $userId): ?int
    {
        return $this->db->transaction(function($db) use ($id, $workspaceId, $userId) {
            $original = $this->findById($id, $workspaceId);
            
            if (!$original) {
                return null;
            }
            
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
            $items = $this->getItems($id);
            
            foreach ($items as $item) {
                $this->createItem([
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
                ]);
            }
            
            return $newId;
        });
    }
    
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(int $workspaceId): array
    {
        $currentMonthStart = date('Y-m-01');
        $currentMonthEnd = date('Y-m-t');
        
        // Revenue this month
        $revenueSql = "SELECT COALESCE(SUM(paid_amount), 0) as total 
                       FROM eb_invoices 
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
        
        // Outstanding
        $outstandingSql = "SELECT COALESCE(SUM(balance_due), 0) as total 
                          FROM eb_invoices 
                          WHERE workspace_id = :workspace_id 
                          AND status IN ('sent', 'partial', 'overdue')
                          AND deleted_at IS NULL";
        
        $outstandingResult = $this->db->fetchOne($outstandingSql, [
            'workspace_id' => $workspaceId
        ]);
        
        // Overdue count
        $overdueSql = "SELECT COUNT(*) as count 
                      FROM eb_invoices 
                      WHERE workspace_id = :workspace_id 
                      AND status IN ('sent', 'partial')
                      AND due_date < CURRENT_DATE 
                      AND deleted_at IS NULL";
        
        $overdueResult = $this->db->fetchOne($overdueSql, [
            'workspace_id' => $workspaceId
        ]);
        
        // Total count
        $totalSql = "SELECT COUNT(*) as count 
                    FROM eb_invoices 
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
     * Get revenue vs expenses for chart
     */
    public function getRevenueVsExpenses(int $workspaceId, int $months = 12): array
    {
        $data = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $monthStart = date('Y-m-01', strtotime("-{$i} months"));
            $monthEnd = date('Y-m-t', strtotime("-{$i} months"));
            $monthLabel = date('M Y', strtotime("-{$i} months"));
            
            // Revenue for month
            $revenueSql = "SELECT COALESCE(SUM(paid_amount), 0) as total 
                          FROM eb_invoices 
                          WHERE workspace_id = :workspace_id 
                          AND paid_at >= :start AND paid_at <= :end
                          AND deleted_at IS NULL";
            
            $revenueResult = $this->db->fetchOne($revenueSql, [
                'workspace_id' => $workspaceId,
                'start' => $monthStart,
                'end' => $monthEnd
            ]);
            
            // Expenses for month
            $expenseSql = "SELECT COALESCE(SUM(total), 0) as total 
                          FROM eb_expenses 
                          WHERE workspace_id = :workspace_id 
                          AND expense_date >= :start AND expense_date <= :end
                          AND deleted_at IS NULL";
            
            $expenseResult = $this->db->fetchOne($expenseSql, [
                'workspace_id' => $workspaceId,
                'start' => $monthStart,
                'end' => $monthEnd
            ]);
            
            $data[] = [
                'label' => $monthLabel,
                'revenue' => (float) ($revenueResult['total'] ?? 0),
                'expenses' => (float) ($expenseResult['total'] ?? 0)
            ];
        }
        
        return $data;
    }
    
    /**
     * Get top clients by revenue
     */
    public function getTopClients(int $workspaceId, int $limit = 5): array
    {
        $sql = "SELECT c.id, c.company_name, c.contact_name,
                COALESCE(SUM(i.paid_amount), 0) as total_revenue,
                COUNT(DISTINCT i.id) as invoice_count
                FROM eb_clients c
                LEFT JOIN eb_invoices i ON c.id = i.client_id 
                    AND i.status IN ('paid', 'partial')
                    AND i.deleted_at IS NULL
                WHERE c.workspace_id = :workspace_id AND c.deleted_at IS NULL
                GROUP BY c.id, c.company_name, c.contact_name
                ORDER BY total_revenue DESC
                LIMIT :limit";
        
        $sql = str_replace(':limit', $limit, $sql);
        
        return $this->db->query($sql, ['workspace_id' => $workspaceId]);
    }
    
    /**
     * Get all invoices with filters
     */
    public function findAll(int $workspaceId, array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT i.*, c.company_name, c.contact_name 
                FROM eb_invoices i
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
        
        $sql .= " ORDER BY i.created_at DESC LIMIT {$limit} OFFSET {$offset}";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Count invoices
     */
    public function count(int $workspaceId, array $filters = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM eb_invoices 
                WHERE workspace_id = :workspace_id AND deleted_at IS NULL";
        
        $params = ['workspace_id' => $workspaceId];
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return (int) ($result['count'] ?? 0);
    }
}

<?php

namespace App\Models;

use App\Core\Database;

/**
 * Expense Model
 * Manages expenses with receipt tracking, OCR data, and multi-currency support
 */
class Expense
{
    private Database $db;
    private string $table = 'expenses';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all expenses for a workspace with optional filters
     */
    public function getWorkspaceExpenses(
        string $workspaceId,
        array $filters = [],
        int $limit = 50,
        int $offset = 0
    ): array {
        $sql = "SELECT e.*, ec.name as category_name, ec.color_code as category_color,
                       c.company_name as billable_client_name
                FROM {$this->table} e
                LEFT JOIN expense_categories ec ON e.category_id = ec.id
                LEFT JOIN clients c ON e.billable_to_client_id = c.id
                WHERE e.workspace_id = :workspace_id";
        
        $params = ['workspace_id' => $workspaceId];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND e.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND e.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND e.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND e.expense_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND e.expense_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        if (isset($filters['is_billable'])) {
            $sql .= " AND e.is_billable = :is_billable";
            $params['is_billable'] = $filters['is_billable'] ? 1 : 0;
        }
        
        $sql .= " ORDER BY e.expense_date DESC, e.created_at DESC";
        $sql .= " LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Find expense by ID with workspace isolation
     */
    public function findById(string $id, string $workspaceId): ?array
    {
        $sql = "SELECT e.*, ec.name as category_name, ec.color_code as category_color,
                       c.company_name as billable_client_name
                FROM {$this->table} e
                LEFT JOIN expense_categories ec ON e.category_id = ec.id
                LEFT JOIN clients c ON e.billable_to_client_id = c.id
                WHERE e.id = :id AND e.workspace_id = :workspace_id";
        
        $expense = $this->db->fetch($sql, [
            'id' => $id,
            'workspace_id' => $workspaceId
        ]);
        
        return $expense ?: null;
    }

    /**
     * Search expenses
     */
    public function search(string $workspaceId, string $query): array
    {
        $searchTerm = "%{$query}%";
        
        $sql = "SELECT e.*, ec.name as category_name
                FROM {$this->table} e
                LEFT JOIN expense_categories ec ON e.category_id = ec.id
                WHERE e.workspace_id = :workspace_id 
                AND (e.vendor_name LIKE :search 
                     OR e.reference_number LIKE :search 
                     OR e.notes LIKE :search)
                ORDER BY e.expense_date DESC
                LIMIT 50";
        
        return $this->db->fetchAll($sql, [
            'workspace_id' => $workspaceId,
            'search' => $searchTerm
        ]);
    }

    /**
     * Create new expense
     */
    public function create(array $data): string
    {
        $id = $this->generateUuid();
        
        // Calculate base currency amount if different currency
        $baseAmount = $data['total_amount'];
        if ($data['currency_code'] !== $data['base_currency_code']) {
            $baseAmount = $data['total_amount'] * ($data['exchange_rate'] ?? 1.0);
        }
        
        $sql = "INSERT INTO {$this->table} (
                    id, workspace_id, user_id,
                    vendor_name, vendor_vat_number, vendor_address,
                    amount, tax_amount, total_amount, currency_code, exchange_rate, base_currency_amount,
                    category_id, billable_to_client_id, is_billable,
                    expense_date, due_date, paid_date, payment_method,
                    receipt_path, receipt_mime_type, ocr_processed, ocr_confidence_score, ocr_raw_data,
                    status, reference_number, notes, internal_notes
                ) VALUES (
                    :id, :workspace_id, :user_id,
                    :vendor_name, :vendor_vat_number, :vendor_address,
                    :amount, :tax_amount, :total_amount, :currency_code, :exchange_rate, :base_currency_amount,
                    :category_id, :billable_to_client_id, :is_billable,
                    :expense_date, :due_date, :paid_date, :payment_method,
                    :receipt_path, :receipt_mime_type, :ocr_processed, :ocr_confidence_score, :ocr_raw_data,
                    :status, :reference_number, :notes, :internal_notes
                )";
        
        $params = [
            'id' => $id,
            'workspace_id' => $data['workspace_id'],
            'user_id' => $data['user_id'],
            'vendor_name' => $data['vendor_name'],
            'amount' => $data['amount'],
            'tax_amount' => $data['tax_amount'] ?? 0,
            'total_amount' => $data['total_amount'],
            'currency_code' => $data['currency_code'] ?? 'EUR',
            'exchange_rate' => $data['exchange_rate'] ?? 1.0,
            'base_currency_amount' => $baseAmount,
            'expense_date' => $data['expense_date'],
            'status' => $data['status'] ?? 'pending',
        ];
        
        // Optional fields
        $optionalFields = [
            'vendor_vat_number', 'vendor_address', 'category_id', 'billable_to_client_id',
            'is_billable', 'due_date', 'paid_date', 'payment_method',
            'receipt_path', 'receipt_mime_type', 'ocr_processed', 'ocr_confidence_score',
            'reference_number', 'notes', 'internal_notes'
        ];
        
        foreach ($optionalFields as $field) {
            if (isset($data[$field])) {
                $params[$field] = $data[$field];
            }
        }
        
        // Handle JSON field
        if (isset($data['ocr_raw_data']) && is_array($data['ocr_raw_data'])) {
            $params['ocr_raw_data'] = json_encode($data['ocr_raw_data']);
        }
        
        $this->db->execute($sql, $params);
        
        return $id;
    }

    /**
     * Update expense
     */
    public function update(string $id, string $workspaceId, array $data): bool
    {
        $existing = $this->findById($id, $workspaceId);
        if (!$existing) {
            return false;
        }
        
        $setClause = [];
        $params = ['id' => $id, 'workspace_id' => $workspaceId];
        
        $allowedFields = [
            'vendor_name', 'vendor_vat_number', 'vendor_address',
            'amount', 'tax_amount', 'total_amount', 'currency_code', 'exchange_rate',
            'category_id', 'billable_to_client_id', 'is_billable',
            'expense_date', 'due_date', 'paid_date', 'payment_method',
            'status', 'reference_number', 'notes', 'internal_notes',
            'ocr_processed', 'ocr_confidence_score', 'ocr_raw_data'
        ];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $setClause[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }
        
        // Recalculate base currency amount if currency or amount changed
        if (isset($data['total_amount']) || isset($data['exchange_rate']) || isset($data['currency_code'])) {
            $currency = $data['currency_code'] ?? $existing['currency_code'];
            $totalAmount = $data['total_amount'] ?? $existing['total_amount'];
            $exchangeRate = $data['exchange_rate'] ?? $existing['exchange_rate'];
            
            // Assuming we have access to workspace base currency
            $baseAmount = $totalAmount * $exchangeRate;
            $setClause[] = "base_currency_amount = :base_currency_amount";
            $params['base_currency_amount'] = $baseAmount;
        }
        
        if (empty($setClause)) {
            return true;
        }
        
        $sql = "UPDATE {$this->table} 
                SET " . implode(', ', $setClause) . ", updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND workspace_id = :workspace_id";
        
        return $this->db->execute($sql, $params);
    }

    /**
     * Update expense status
     */
    public function updateStatus(string $id, string $workspaceId, string $status): bool
    {
        $validStatuses = ['pending', 'approved', 'rejected', 'paid', 'reimbursed'];
        
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} 
                SET status = :status, updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND workspace_id = :workspace_id";
        
        return $this->db->execute($sql, [
            'id' => $id,
            'workspace_id' => $workspaceId,
            'status' => $status
        ]);
    }

    /**
     * Mark expense as paid
     */
    public function markAsPaid(string $id, string $workspaceId, ?string $paidDate = null): bool
    {
        $sql = "UPDATE {$this->table} 
                SET status = 'paid', paid_date = :paid_date, updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND workspace_id = :workspace_id";
        
        return $this->db->execute($sql, [
            'id' => $id,
            'workspace_id' => $workspaceId,
            'paid_date' => $paidDate ?? date('Y-m-d')
        ]);
    }

    /**
     * Delete expense
     */
    public function delete(string $id, string $workspaceId): bool
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE id = :id AND workspace_id = :workspace_id";
        
        return $this->db->execute($sql, [
            'id' => $id,
            'workspace_id' => $workspaceId
        ]);
    }

    /**
     * Get expenses statistics for dashboard
     */
    public function getStatistics(string $workspaceId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $dateFilter = '';
        $params = ['workspace_id' => $workspaceId];
        
        if ($dateFrom && $dateTo) {
            $dateFilter = "AND expense_date BETWEEN :date_from AND :date_to";
            $params['date_from'] = $dateFrom;
            $params['date_to'] = $dateTo;
        } elseif ($dateFrom) {
            $dateFilter = "AND expense_date >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_expenses,
                    SUM(base_currency_amount) as total_amount,
                    SUM(tax_amount) as total_tax,
                    SUM(CASE WHEN status = 'pending' THEN base_currency_amount ELSE 0 END) as pending_amount,
                    SUM(CASE WHEN status = 'approved' THEN base_currency_amount ELSE 0 END) as approved_amount,
                    SUM(CASE WHEN status = 'paid' THEN base_currency_amount ELSE 0 END) as paid_amount,
                    SUM(CASE WHEN is_billable = 1 THEN base_currency_amount ELSE 0 END) as billable_amount
                FROM {$this->table}
                WHERE workspace_id = :workspace_id {$dateFilter}";
        
        $stats = $this->db->fetch($sql, $params);
        
        return [
            'total_expenses' => (int) ($stats['total_expenses'] ?? 0),
            'total_amount' => (float) ($stats['total_amount'] ?? 0),
            'total_tax' => (float) ($stats['total_tax'] ?? 0),
            'pending_amount' => (float) ($stats['pending_amount'] ?? 0),
            'approved_amount' => (float) ($stats['approved_amount'] ?? 0),
            'paid_amount' => (float) ($stats['paid_amount'] ?? 0),
            'billable_amount' => (float) ($stats['billable_amount'] ?? 0),
        ];
    }

    /**
     * Get monthly expense totals for charts
     */
    public function getMonthlyTotals(string $workspaceId, int $months = 12): array
    {
        $sql = "SELECT 
                    DATE_FORMAT(expense_date, '%Y-%m') as month,
                    SUM(base_currency_amount) as total_amount,
                    COUNT(*) as count
                FROM {$this->table}
                WHERE workspace_id = :workspace_id
                AND expense_date >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                GROUP BY DATE_FORMAT(expense_date, '%Y-%m')
                ORDER BY month ASC";
        
        return $this->db->fetchAll($sql, [
            'workspace_id' => $workspaceId,
            'months' => $months
        ]);
    }

    /**
     * Get expenses by category
     */
    public function getByCategory(string $workspaceId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $dateFilter = '';
        $params = ['workspace_id' => $workspaceId];
        
        if ($dateFrom && $dateTo) {
            $dateFilter = "AND e.expense_date BETWEEN :date_from AND :date_to";
            $params['date_from'] = $dateFrom;
            $params['date_to'] = $dateTo;
        }
        
        $sql = "SELECT 
                    ec.id as category_id,
                    ec.name as category_name,
                    ec.color_code,
                    SUM(e.base_currency_amount) as total_amount,
                    COUNT(e.id) as count
                FROM {$this->table} e
                LEFT JOIN expense_categories ec ON e.category_id = ec.id
                WHERE e.workspace_id = :workspace_id {$dateFilter}
                GROUP BY ec.id, ec.name, ec.color_code
                ORDER BY total_amount DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Store OCR data from receipt
     */
    public function storeOcrData(string $id, string $workspaceId, array $ocrData, float $confidenceScore): bool
    {
        $sql = "UPDATE {$this->table} 
                SET ocr_processed = 1, 
                    ocr_confidence_score = :confidence,
                    ocr_raw_data = :ocr_data,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND workspace_id = :workspace_id";
        
        return $this->db->execute($sql, [
            'id' => $id,
            'workspace_id' => $workspaceId,
            'confidence' => $confidenceScore,
            'ocr_data' => json_encode($ocrData)
        ]);
    }

    /**
     * Get expenses needing review (low OCR confidence)
     */
    public function getNeedsReview(string $workspaceId, float $minConfidence = 0.80): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE workspace_id = :workspace_id
                AND ocr_processed = 1
                AND ocr_confidence_score < :min_confidence
                AND status = 'pending'
                ORDER BY ocr_confidence_score ASC";
        
        return $this->db->fetchAll($sql, [
            'workspace_id' => $workspaceId,
            'min_confidence' => $minConfidence
        ]);
    }

    /**
     * Generate UUID v4
     */
    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

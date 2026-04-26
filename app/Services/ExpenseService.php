<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Core\Database;
use Exception;

/**
 * Expense Service
 * Business logic layer for expense management with OCR support
 */
class ExpenseService
{
    private Expense $expenseModel;
    private Database $db;

    public function __construct()
    {
        $this->expenseModel = new Expense();
        $this->db = Database::getInstance();
    }

    /**
     * Get all expenses for workspace with filters
     */
    public function getExpenses(
        string $workspaceId,
        array $filters = [],
        int $limit = 50,
        int $offset = 0
    ): array {
        return $this->expenseModel->getWorkspaceExpenses($workspaceId, $filters, $limit, $offset);
    }

    /**
     * Get single expense
     */
    public function getExpense(string $id, string $workspaceId): ?array
    {
        return $this->expenseModel->findById($id, $workspaceId);
    }

    /**
     * Search expenses
     */
    public function searchExpenses(string $workspaceId, string $query): array
    {
        if (strlen($query) < 2) {
            return [];
        }
        
        return $this->expenseModel->search($workspaceId, $query);
    }

    /**
     * Create expense with validation
     */
    public function createExpense(array $data): array
    {
        $errors = $this->validateExpenseData($data);
        
        if (!empty($errors)) {
            throw new Exception(json_encode($errors));
        }
        
        // Ensure amounts are calculated correctly
        if (!isset($data['total_amount'])) {
            $data['total_amount'] = ($data['amount'] ?? 0) + ($data['tax_amount'] ?? 0);
        }
        
        // Set base currency code from workspace
        $data['base_currency_code'] = $this->getWorkspaceBaseCurrency($data['workspace_id']);
        
        $id = $this->expenseModel->create($data);
        
        $this->logActivity($data['workspace_id'], 'expense_created', $id);
        
        return [
            'success' => true,
            'id' => $id,
            'message' => 'Expense created successfully'
        ];
    }

    /**
     * Update expense
     */
    public function updateExpense(string $id, string $workspaceId, array $data): array
    {
        $existing = $this->expenseModel->findById($id, $workspaceId);
        if (!$existing) {
            throw new Exception('Expense not found');
        }
        
        $errors = $this->validateExpenseData($data, true);
        
        if (!empty($errors)) {
            throw new Exception(json_encode($errors));
        }
        
        // Recalculate total if amount or tax changed
        if (isset($data['amount']) || isset($data['tax_amount'])) {
            $amount = $data['amount'] ?? $existing['amount'];
            $taxAmount = $data['tax_amount'] ?? $existing['tax_amount'];
            $data['total_amount'] = $amount + $taxAmount;
        }
        
        $success = $this->expenseModel->update($id, $workspaceId, $data);
        
        if ($success) {
            $this->logActivity($workspaceId, 'expense_updated', $id);
        }
        
        return [
            'success' => $success,
            'message' => $success ? 'Expense updated successfully' : 'Failed to update expense'
        ];
    }

    /**
     * Update expense status
     */
    public function updateStatus(string $id, string $workspaceId, string $status): array
    {
        $validStatuses = ['pending', 'approved', 'rejected', 'paid', 'reimbursed'];
        
        if (!in_array($status, $validStatuses)) {
            throw new Exception('Invalid status');
        }
        
        $success = $this->expenseModel->updateStatus($id, $workspaceId, $status);
        
        if ($success) {
            $this->logActivity($workspaceId, 'expense_status_changed', $id, ['status' => $status]);
        }
        
        return [
            'success' => $success,
            'message' => $success ? 'Status updated successfully' : 'Failed to update status'
        ];
    }

    /**
     * Mark expense as paid
     */
    public function markAsPaid(string $id, string $workspaceId, ?string $paidDate = null): array
    {
        $success = $this->expenseModel->markAsPaid($id, $workspaceId, $paidDate);
        
        if ($success) {
            $this->logActivity($workspaceId, 'expense_paid', $id);
        }
        
        return [
            'success' => $success,
            'message' => $success ? 'Expense marked as paid' : 'Failed to mark as paid'
        ];
    }

    /**
     * Delete expense
     */
    public function deleteExpense(string $id, string $workspaceId): array
    {
        $existing = $this->expenseModel->findById($id, $workspaceId);
        if (!$existing) {
            throw new Exception('Expense not found');
        }
        
        // Check if expense is already paid or reimbursed
        if (in_array($existing['status'], ['paid', 'reimbursed'])) {
            throw new Exception('Cannot delete paid or reimbursed expenses');
        }
        
        $success = $this->expenseModel->delete($id, $workspaceId);
        
        if ($success) {
            $this->logActivity($workspaceId, 'expense_deleted', $id);
        }
        
        return [
            'success' => $success,
            'message' => $success ? 'Expense deleted successfully' : 'Failed to delete expense'
        ];
    }

    /**
     * Process OCR data from receipt
     */
    public function processOcrData(string $id, string $workspaceId, array $ocrData): array
    {
        $existing = $this->expenseModel->findById($id, $workspaceId);
        if (!$existing) {
            throw new Exception('Expense not found');
        }
        
        // Extract data from OCR response (Amazon Textract format example)
        $extractedData = $this->extractFromOcr($ocrData);
        
        // Calculate confidence score
        $confidenceScore = $this->calculateConfidence($ocrData);
        
        // Store OCR data
        $this->expenseModel->storeOcrData($id, $workspaceId, $ocrData, $confidenceScore);
        
        // Auto-fill fields if confidence is high
        if ($confidenceScore >= 0.85) {
            $updateData = [];
            
            if (!empty($extractedData['vendor_name'])) {
                $updateData['vendor_name'] = $extractedData['vendor_name'];
            }
            
            if (!empty($extractedData['amount'])) {
                $updateData['amount'] = $extractedData['amount'];
                $updateData['total_amount'] = $extractedData['amount'] + ($extractedData['tax_amount'] ?? 0);
            }
            
            if (!empty($extractedData['date'])) {
                $updateData['expense_date'] = $extractedData['date'];
            }
            
            if (!empty($extractedData['reference_number'])) {
                $updateData['reference_number'] = $extractedData['reference_number'];
            }
            
            if (!empty($updateData)) {
                $this->expenseModel->update($id, $workspaceId, $updateData);
            }
        }
        
        return [
            'success' => true,
            'confidence' => $confidenceScore,
            'extracted_data' => $extractedData,
            'needs_review' => $confidenceScore < 0.85
        ];
    }

    /**
     * Get expense statistics
     */
    public function getStatistics(string $workspaceId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        return $this->expenseModel->getStatistics($workspaceId, $dateFrom, $dateTo);
    }

    /**
     * Get monthly expense totals for charts
     */
    public function getMonthlyTotals(string $workspaceId, int $months = 12): array
    {
        return $this->expenseModel->getMonthlyTotals($workspaceId, $months);
    }

    /**
     * Get expenses by category
     */
    public function getByCategory(string $workspaceId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        return $this->expenseModel->getByCategory($workspaceId, $dateFrom, $dateTo);
    }

    /**
     * Get expenses needing review (low OCR confidence)
     */
    public function getNeedsReview(string $workspaceId): array
    {
        return $this->expenseModel->getNeedsReview($workspaceId);
    }

    /**
     * Get all expense categories for workspace
     */
    public function getCategories(string $workspaceId): array
    {
        $sql = "SELECT * FROM expense_categories 
                WHERE workspace_id = :workspace_id OR is_system = 1
                ORDER BY name ASC";
        
        return $this->db->fetchAll($sql, ['workspace_id' => $workspaceId]);
    }

    /**
     * Create custom expense category
     */
    public function createCategory(string $workspaceId, string $name, string $colorCode = '#6B7280'): array
    {
        // Check for duplicate
        $sql = "SELECT id FROM expense_categories 
                WHERE name = :name AND workspace_id = :workspace_id";
        
        $existing = $this->db->fetch($sql, [
            'name' => $name,
            'workspace_id' => $workspaceId
        ]);
        
        if ($existing) {
            throw new Exception('Category already exists');
        }
        
        $id = $this->generateUuid();
        
        $sql = "INSERT INTO expense_categories (id, workspace_id, name, color_code) 
                VALUES (:id, :workspace_id, :name, :color_code)";
        
        $this->db->execute($sql, [
            'id' => $id,
            'workspace_id' => $workspaceId,
            'name' => $name,
            'color_code' => $colorCode
        ]);
        
        return [
            'success' => true,
            'id' => $id,
            'message' => 'Category created successfully'
        ];
    }

    /**
     * Validate expense data
     */
    private function validateExpenseData(array $data, bool $isUpdate = false): array
    {
        $errors = [];
        
        // Required fields
        if (!$isUpdate || !isset($data['vendor_name'])) {
            if (empty($data['vendor_name'])) {
                $errors['vendor_name'] = 'Vendor name is required';
            }
        }
        
        if (!$isUpdate || !isset($data['expense_date'])) {
            if (empty($data['expense_date'])) {
                $errors['expense_date'] = 'Expense date is required';
            }
        }
        
        if (!$isUpdate || !isset($data['amount'])) {
            if (!isset($data['amount']) || $data['amount'] <= 0) {
                $errors['amount'] = 'Amount must be greater than zero';
            }
        }
        
        // Amount validation
        if (isset($data['amount']) && $data['amount'] < 0) {
            $errors['amount'] = 'Amount cannot be negative';
        }
        
        if (isset($data['tax_amount']) && $data['tax_amount'] < 0) {
            $errors['tax_amount'] = 'Tax amount cannot be negative';
        }
        
        // Date validation
        if (isset($data['expense_date'])) {
            $date = \DateTime::createFromFormat('Y-m-d', $data['expense_date']);
            if (!$date) {
                $errors['expense_date'] = 'Invalid date format';
            }
        }
        
        if (isset($data['due_date']) && isset($data['expense_date'])) {
            if ($data['due_date'] < $data['expense_date']) {
                $errors['due_date'] = 'Due date cannot be before expense date';
            }
        }
        
        return $errors;
    }

    /**
     * Extract data from OCR response
     */
    private function extractFromOcr(array $ocrData): array
    {
        $extracted = [];
        
        // Handle Amazon Textract response format
        if (isset($ocrData['Blocks'])) {
            foreach ($ocrData['Blocks'] as $block) {
                if ($block['BlockType'] === 'LINE') {
                    $text = $block['Text'] ?? '';
                    
                    // Simple keyword matching for demo
                    if (preg_match('/Total|Amount|Sum/i', $text)) {
                        // Would need more sophisticated parsing in production
                    }
                }
            }
        }
        
        // For now, return raw data - production would use proper parsing
        return $extracted;
    }

    /**
     * Calculate OCR confidence score
     */
    private function calculateConfidence(array $ocrData): float
    {
        // Handle Amazon Textract confidence scores
        if (isset($ocrData['Blocks'])) {
            $confidences = [];
            foreach ($ocrData['Blocks'] as $block) {
                if (isset($block['Confidence'])) {
                    $confidences[] = $block['Confidence'];
                }
            }
            
            if (!empty($confidences)) {
                return array_sum($confidences) / count($confidences) / 100;
            }
        }
        
        return 0.50; // Default moderate confidence
    }

    /**
     * Get workspace base currency
     */
    private function getWorkspaceBaseCurrency(string $workspaceId): string
    {
        $sql = "SELECT default_currency FROM workspaces WHERE id = :id";
        $result = $this->db->fetch($sql, ['id' => $workspaceId]);
        
        return $result['default_currency'] ?? 'EUR';
    }

    /**
     * Log activity
     */
    private function logActivity(string $workspaceId, string $type, string $referenceId, array $metadata = []): void
    {
        // Implementation would use ActivityLog model
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

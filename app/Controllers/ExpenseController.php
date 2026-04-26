<?php

namespace App\Controllers;

use App\Services\ExpenseService;
use Exception;

/**
 * Expense Controller
 * Handles HTTP requests for expense management
 */
class ExpenseController extends BaseController
{
    private ExpenseService $expenseService;

    public function __construct()
    {
        parent::__construct();
        $this->expenseService = new ExpenseService();
    }

    /**
     * List all expenses
     */
    public function index(): void
    {
        $this->requireAuth();
        
        $workspaceId = $this->getCurrentWorkspaceId();
        
        // Get filters from query string
        $filters = [
            'status' => $_GET['status'] ?? '',
            'category_id' => $_GET['category'] ?? '',
            'date_from' => $_GET['from'] ?? '',
            'date_to' => $_GET['to'] ?? '',
        ];
        
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 25;
        $offset = ($page - 1) * $limit;
        
        try {
            $expenses = $this->expenseService->getExpenses($workspaceId, array_filter($filters), $limit, $offset);
            $categories = $this->expenseService->getCategories($workspaceId);
            
            // Get statistics for the period
            $stats = $this->expenseService->getStatistics(
                $workspaceId, 
                $filters['date_from'] ?: null, 
                $filters['date_to'] ?: null
            );
            
            $this->render('expenses/index', [
                'expenses' => $expenses,
                'categories' => $categories,
                'filters' => $filters,
                'stats' => $stats,
                'current_page' => $page,
                'page_title' => 'Expenses'
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load expenses: ' . $e->getMessage());
            $this->redirect('/expenses');
        }
    }

    /**
     * Show create expense form
     */
    public function create(): void
    {
        $this->requireAuth();
        
        $workspaceId = $this->getCurrentWorkspaceId();
        
        try {
            $categories = $this->expenseService->getCategories($workspaceId);
            $clients = $this->getActiveClients($workspaceId);
            
            $this->render('expenses/create', [
                'categories' => $categories,
                'clients' => $clients,
                'currencies' => $this->getCurrencies(),
                'payment_methods' => $this->getPaymentMethods(),
                'page_title' => 'Add New Expense'
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load form: ' . $e->getMessage());
            $this->redirect('/expenses');
        }
    }

    /**
     * Store new expense
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->checkCsrf();
        
        $workspaceId = $this->getCurrentWorkspaceId();
        $user = $this->getCurrentUser();
        
        $data = [
            'workspace_id' => $workspaceId,
            'user_id' => $user['id'],
            'vendor_name' => $_POST['vendor_name'] ?? '',
            'vendor_vat_number' => $_POST['vendor_vat_number'] ?? '',
            'vendor_address' => $_POST['vendor_address'] ?? '',
            'amount' => $this->parseAmount($_POST['amount'] ?? '0'),
            'tax_amount' => $this->parseAmount($_POST['tax_amount'] ?? '0'),
            'currency_code' => $_POST['currency_code'] ?? 'EUR',
            'exchange_rate' => $this->parseAmount($_POST['exchange_rate'] ?? '1'),
            'category_id' => $_POST['category_id'] ?: null,
            'billable_to_client_id' => $_POST['billable_to_client_id'] ?: null,
            'is_billable' => isset($_POST['is_billable']),
            'expense_date' => $_POST['expense_date'] ?? date('Y-m-d'),
            'due_date' => $_POST['due_date'] ?: null,
            'paid_date' => $_POST['paid_date'] ?: null,
            'payment_method' => $_POST['payment_method'] ?? 'bank_transfer',
            'status' => $_POST['status'] ?? 'pending',
            'reference_number' => $_POST['reference_number'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'internal_notes' => $_POST['internal_notes'] ?? ''
        ];
        
        // Handle file upload
        if (!empty($_FILES['receipt']['name'])) {
            $uploadResult = $this->handleReceiptUpload($_FILES['receipt']);
            if ($uploadResult['success']) {
                $data['receipt_path'] = $uploadResult['path'];
                $data['receipt_mime_type'] = $uploadResult['mime_type'];
            }
        }
        
        try {
            $result = $this->expenseService->createExpense($data);
            
            $this->setFlash('success', $result['message']);
            $this->redirect('/expenses/' . $result['id']);
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/expenses/create');
        }
    }

    /**
     * Show single expense
     */
    public function show(string $id): void
    {
        $this->requireAuth();
        
        $workspaceId = $this->getCurrentWorkspaceId();
        
        try {
            $expense = $this->expenseService->getExpense($id, $workspaceId);
            
            if (!$expense) {
                http_response_code(404);
                $this->render('errors/404', ['page_title' => 'Expense Not Found']);
                return;
            }
            
            $categories = $this->expenseService->getCategories($workspaceId);
            
            $this->render('expenses/show', [
                'expense' => $expense,
                'categories' => $categories,
                'page_title' => $expense['vendor_name']
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load expense: ' . $e->getMessage());
            $this->redirect('/expenses');
        }
    }

    /**
     * Show edit expense form
     */
    public function edit(string $id): void
    {
        $this->requireAuth();
        
        $workspaceId = $this->getCurrentWorkspaceId();
        
        try {
            $expense = $this->expenseService->getExpense($id, $workspaceId);
            
            if (!$expense) {
                http_response_code(404);
                $this->render('errors/404', ['page_title' => 'Expense Not Found']);
                return;
            }
            
            $categories = $this->expenseService->getCategories($workspaceId);
            $clients = $this->getActiveClients($workspaceId);
            
            $this->render('expenses/edit', [
                'expense' => $expense,
                'categories' => $categories,
                'clients' => $clients,
                'currencies' => $this->getCurrencies(),
                'payment_methods' => $this->getPaymentMethods(),
                'page_title' => 'Edit Expense'
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load expense: ' . $e->getMessage());
            $this->redirect('/expenses');
        }
    }

    /**
     * Update expense
     */
    public function update(string $id): void
    {
        $this->requireAuth();
        $this->checkCsrf();
        
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $data = [
            'vendor_name' => $_POST['vendor_name'] ?? '',
            'vendor_vat_number' => $_POST['vendor_vat_number'] ?? '',
            'vendor_address' => $_POST['vendor_address'] ?? '',
            'amount' => $this->parseAmount($_POST['amount'] ?? '0'),
            'tax_amount' => $this->parseAmount($_POST['tax_amount'] ?? '0'),
            'currency_code' => $_POST['currency_code'] ?? 'EUR',
            'exchange_rate' => $this->parseAmount($_POST['exchange_rate'] ?? '1'),
            'category_id' => $_POST['category_id'] ?: null,
            'billable_to_client_id' => $_POST['billable_to_client_id'] ?: null,
            'is_billable' => isset($_POST['is_billable']),
            'expense_date' => $_POST['expense_date'] ?? date('Y-m-d'),
            'due_date' => $_POST['due_date'] ?: null,
            'paid_date' => $_POST['paid_date'] ?: null,
            'payment_method' => $_POST['payment_method'] ?? 'bank_transfer',
            'status' => $_POST['status'] ?? 'pending',
            'reference_number' => $_POST['reference_number'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'internal_notes' => $_POST['internal_notes'] ?? ''
        ];
        
        // Handle file upload (new receipt)
        if (!empty($_FILES['receipt']['name'])) {
            $uploadResult = $this->handleReceiptUpload($_FILES['receipt']);
            if ($uploadResult['success']) {
                $data['receipt_path'] = $uploadResult['path'];
                $data['receipt_mime_type'] = $uploadResult['mime_type'];
            }
        }
        
        try {
            $result = $this->expenseService->updateExpense($id, $workspaceId, $data);
            
            $this->setFlash('success', $result['message']);
            $this->redirect('/expenses/' . $id);
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/expenses/' . $id . '/edit');
        }
    }

    /**
     * Update expense status
     */
    public function updateStatus(string $id): void
    {
        $this->requireAuth();
        $this->checkCsrf();
        
        $workspaceId = $this->getCurrentWorkspaceId();
        $status = $_POST['status'] ?? 'pending';
        
        try {
            $result = $this->expenseService->updateStatus($id, $workspaceId, $status);
            
            $this->setFlash('success', $result['message']);
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        
        $this->redirect('/expenses/' . $id);
    }

    /**
     * Mark expense as paid
     */
    public function markPaid(string $id): void
    {
        $this->requireAuth();
        $this->checkCsrf();
        
        $workspaceId = $this->getCurrentWorkspaceId();
        $paidDate = $_POST['paid_date'] ?? date('Y-m-d');
        
        try {
            $result = $this->expenseService->markAsPaid($id, $workspaceId, $paidDate);
            
            $this->setFlash('success', $result['message']);
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        
        $this->redirect('/expenses/' . $id);
    }

    /**
     * Delete expense
     */
    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->checkCsrf();
        
        $workspaceId = $this->getCurrentWorkspaceId();
        
        try {
            $result = $this->expenseService->deleteExpense($id, $workspaceId);
            
            $this->setFlash('success', $result['message']);
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        
        $this->redirect('/expenses');
    }

    /**
     * Upload receipt for OCR processing
     */
    public function uploadReceipt(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');
        
        if (empty($_FILES['receipt']['name'])) {
            echo json_encode(['success' => false, 'error' => 'No file uploaded']);
            return;
        }
        
        $uploadResult = $this->handleReceiptUpload($_FILES['receipt']);
        
        if (!$uploadResult['success']) {
            echo json_encode(['success' => false, 'error' => $uploadResult['error']]);
            return;
        }
        
        // In production, would queue OCR job here
        echo json_encode([
            'success' => true,
            'path' => $uploadResult['path'],
            'message' => 'Receipt uploaded. OCR processing will begin shortly.'
        ]);
    }

    /**
     * Get expenses needing review (low OCR confidence)
     */
    public function needsReview(): void
    {
        $this->requireAuth();
        
        $workspaceId = $this->getCurrentWorkspaceId();
        
        try {
            $expenses = $this->expenseService->getNeedsReview($workspaceId);
            
            $this->render('expenses/review', [
                'expenses' => $expenses,
                'page_title' => 'Expenses Needing Review'
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load expenses: ' . $e->getMessage());
            $this->redirect('/expenses');
        }
    }

    /**
     * Parse amount from string
     */
    private function parseAmount(string $amount): float
    {
        // Remove currency symbols and whitespace
        $amount = preg_replace('/[^\d.,-]/', '', $amount);
        
        // Handle European format (comma as decimal separator)
        if (substr_count($amount, ',') === 1 && substr_count($amount, '.') === 0) {
            $amount = str_replace(',', '.', $amount);
        } elseif (substr_count($amount, ',') > 1) {
            // Multiple commas = thousand separators
            $amount = str_replace(',', '', $amount);
        }
        
        return (float) $amount;
    }

    /**
     * Handle receipt file upload
     */
    private function handleReceiptUpload(array $file): array
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type. Allowed: JPG, PNG, GIF, PDF'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'File too large. Maximum size: 10MB'];
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        
        // Create upload directory if not exists
        $uploadDir = UPLOADS_DIR . '/receipts/' . date('Y/m/') ;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $destination = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'error' => 'Failed to save file'];
        }
        
        return [
            'success' => true,
            'path' => '/uploads/receipts/' . date('Y/m/') . $filename,
            'mime_type' => $file['type']
        ];
    }

    /**
     * Get active clients for billing dropdown
     */
    private function getActiveClients(string $workspaceId): array
    {
        $sql = "SELECT id, company_name, email 
                FROM clients 
                WHERE workspace_id = :workspace_id AND is_active = 1 
                ORDER BY company_name ASC";
        
        return $this->db->fetchAll($sql, ['workspace_id' => $workspaceId]);
    }

    /**
     * Get payment methods list
     */
    private function getPaymentMethods(): array
    {
        return [
            ['value' => 'cash', 'label' => 'Cash'],
            ['value' => 'credit_card', 'label' => 'Credit Card'],
            ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
            ['value' => 'paypal', 'label' => 'PayPal'],
            ['value' => 'other', 'label' => 'Other']
        ];
    }

    /**
     * Get currencies list
     */
    private function getCurrencies(): array
    {
        return [
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$'],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£'],
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF'],
            ['code' => 'PLN', 'name' => 'Polish Złoty', 'symbol' => 'zł'],
            ['code' => 'CZK', 'name' => 'Czech Koruna', 'symbol' => 'Kč'],
            ['code' => 'SEK', 'name' => 'Swedish Krona', 'symbol' => 'kr'],
            ['code' => 'DKK', 'name' => 'Danish Krone', 'symbol' => 'kr'],
        ];
    }
}

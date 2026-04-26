<?php
/**
 * Invoice Controller
 * Handles invoice CRUD operations and actions
 */

namespace App\Controllers;

use App\Services\InvoiceService;
use App\DTOs\InvoiceData;
use Core\Session\SessionManager;

class InvoiceController extends Controller
{
    private InvoiceService $service;
    private SessionManager $session;
    
    public function __construct()
    {
        parent::__construct();
        $this->service = new InvoiceService();
        $this->session = SessionManager::getInstance();
        
        // Require authentication
        $this->middleware('auth');
    }
    
    /**
     * List all invoices
     */
    public function index(): void
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $filters = [
            'status' => $_GET['status'] ?? null,
            'type' => $_GET['type'] ?? null,
            'client_id' => $_GET['client_id'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'search' => $_GET['search'] ?? null
        ];
        
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $invoices = $this->service->getRepository()->findAll($workspaceId, $filters, $limit, $offset);
        $total = $this->service->getRepository()->count($workspaceId, array_filter($filters));
        $totalPages = ceil($total / $limit);
        
        // Get clients for filter dropdown
        $clients = $this->getClients($workspaceId);
        
        $this->render('invoices/index', [
            'invoices' => $invoices,
            'clients' => $clients,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $total
            ]
        ]);
    }
    
    /**
     * Show invoice details
     */
    public function show(int $id): void
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $invoice = $this->service->getRepository()->findById($id, $workspaceId);
        
        if (!$invoice) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }
        
        $items = $this->service->getRepository()->getItems($id);
        
        $this->render('invoices/show', [
            'invoice' => $invoice,
            'items' => $items
        ]);
    }
    
    /**
     * Show create form
     */
    public function create(): void
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $clients = $this->getClients($workspaceId);
        $products = $this->getProducts($workspaceId);
        $nextNumber = $this->service->getRepository()->generateInvoiceNumber($workspaceId);
        
        $this->render('invoices/create', [
            'clients' => $clients,
            'products' => $products,
            'next_invoice_number' => $nextNumber,
            'default_currency' => $this->getWorkspaceDefaultCurrency(),
            'default_language' => $this->getWorkspaceDefaultLanguage(),
            'default_payment_terms' => $this->getWorkspacePaymentTerms()
        ]);
    }
    
    /**
     * Store new invoice
     */
    public function store(): void
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        $userId = $this->getCurrentUserId();
        
        // Validate CSRF
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('error', 'Invalid security token');
            redirect('/invoices/create');
        }
        
        // Build items array
        $items = [];
        $descriptions = $_POST['description'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $unitPrices = $_POST['unit_price'] ?? [];
        $taxRates = $_POST['tax_rate'] ?? [];
        $discountTypes = $_POST['item_discount_type'] ?? [];
        $discountValues = $_POST['item_discount_value'] ?? [];
        
        foreach ($descriptions as $index => $description) {
            if (!empty(trim($description))) {
                $items[] = [
                    'description' => trim($description),
                    'quantity' => (float) ($quantities[$index] ?? 1),
                    'unit_price' => (float) ($unitPrices[$index] ?? 0),
                    'tax_rate' => (float) ($taxRates[$index] ?? 0),
                    'discount_type' => $discountTypes[$index] ?? 'percentage',
                    'discount_value' => (float) ($discountValues[$index] ?? 0)
                ];
            }
        }
        
        // Create DTO
        $data = InvoiceData::fromArray([
            'workspace_id' => $workspaceId,
            'client_id' => (int) ($_POST['client_id'] ?? 0),
            'type' => $_POST['type'] ?? 'invoice',
            'status' => $_POST['status'] ?? 'draft',
            'invoice_number' => trim($_POST['invoice_number'] ?? ''),
            'reference' => trim($_POST['reference'] ?? ''),
            'currency' => $_POST['currency'] ?? 'EUR',
            'language' => $_POST['language'] ?? 'en',
            'issue_date' => $_POST['issue_date'] ?? date('Y-m-d'),
            'due_date' => $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
            'items' => $items,
            'discount_type' => $_POST['discount_type'] ?? 'percentage',
            'discount_value' => (float) ($_POST['discount_value'] ?? 0),
            'notes' => trim($_POST['notes'] ?? ''),
            'footer' => trim($_POST['footer'] ?? ''),
            'terms' => trim($_POST['terms'] ?? ''),
            'created_by' => $userId
        ]);
        
        // Validate
        $errors = $data->validate();
        
        if (!empty($errors)) {
            $this->session->setFlash('error', implode('<br>', $errors));
            redirect('/invoices/create');
        }
        
        try {
            $invoiceId = $this->service->createInvoice($data);
            
            $this->session->setFlash('success', 'Invoice created successfully');
            
            // Redirect based on action
            if ($_POST['action'] === 'save_and_send') {
                $this->service->sendInvoice($invoiceId, $workspaceId, $userId);
                redirect('/invoices/' . $invoiceId . '/send');
            } else {
                redirect('/invoices/' . $invoiceId);
            }
        } catch (\Exception $e) {
            $this->session->setFlash('error', 'Failed to create invoice: ' . $e->getMessage());
            redirect('/invoices/create');
        }
    }
    
    /**
     * Show edit form
     */
    public function edit(int $id): void
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $invoice = $this->service->getRepository()->findById($id, $workspaceId);
        
        if (!$invoice) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }
        
        // Cannot edit paid or cancelled invoices
        if (in_array($invoice['status'], ['paid', 'cancelled'])) {
            $this->session->setFlash('error', 'Cannot edit ' . $invoice['status'] . ' invoice');
            redirect('/invoices/' . $id);
        }
        
        $items = $this->service->getRepository()->getItems($id);
        $clients = $this->getClients($workspaceId);
        $products = $this->getProducts($workspaceId);
        
        $this->render('invoices/edit', [
            'invoice' => $invoice,
            'items' => $items,
            'clients' => $clients,
            'products' => $products
        ]);
    }
    
    /**
     * Update invoice
     */
    public function update(int $id): void
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        $userId = $this->getCurrentUserId();
        
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('error', 'Invalid security token');
            redirect('/invoices/' . $id . '/edit');
        }
        
        // Build items (same as store)
        $items = [];
        $descriptions = $_POST['description'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $unitPrices = $_POST['unit_price'] ?? [];
        $taxRates = $_POST['tax_rate'] ?? [];
        
        foreach ($descriptions as $index => $description) {
            if (!empty(trim($description))) {
                $items[] = [
                    'description' => trim($description),
                    'quantity' => (float) ($quantities[$index] ?? 1),
                    'unit_price' => (float) ($unitPrices[$index] ?? 0),
                    'tax_rate' => (float) ($taxRates[$index] ?? 0)
                ];
            }
        }
        
        $data = InvoiceData::fromArray([
            'workspace_id' => $workspaceId,
            'client_id' => (int) ($_POST['client_id'] ?? 0),
            'type' => $_POST['type'] ?? 'invoice',
            'currency' => $_POST['currency'] ?? 'EUR',
            'language' => $_POST['language'] ?? 'en',
            'issue_date' => $_POST['issue_date'] ?? date('Y-m-d'),
            'due_date' => $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
            'items' => $items,
            'discount_type' => $_POST['discount_type'] ?? 'percentage',
            'discount_value' => (float) ($_POST['discount_value'] ?? 0),
            'notes' => trim($_POST['notes'] ?? ''),
            'footer' => trim($_POST['footer'] ?? ''),
            'terms' => trim($_POST['terms'] ?? '')
        ]);
        
        $errors = $data->validate();
        
        if (!empty($errors)) {
            $this->session->setFlash('error', implode('<br>', $errors));
            redirect('/invoices/' . $id . '/edit');
        }
        
        try {
            $this->service->updateInvoice($id, $workspaceId, $data, $userId);
            $this->session->setFlash('success', 'Invoice updated successfully');
            redirect('/invoices/' . $id);
        } catch (\Exception $e) {
            $this->session->setFlash('error', 'Failed to update invoice: ' . $e->getMessage());
            redirect('/invoices/' . $id . '/edit');
        }
    }
    
    /**
     * Delete invoice
     */
    public function destroy(int $id): void
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        $userId = $this->getCurrentUserId();
        
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('error', 'Invalid security token');
            redirect('/invoices');
        }
        
        try {
            $this->service->getRepository()->delete($id, $workspaceId);
            $this->session->setFlash('success', 'Invoice deleted successfully');
        } catch (\Exception $e) {
            $this->session->setFlash('error', 'Failed to delete invoice: ' . $e->getMessage());
        }
        
        redirect('/invoices');
    }
    
    /**
     * Send invoice
     */
    public function send(int $id): void
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        $userId = $this->getCurrentUserId();
        
        try {
            $this->service->sendInvoice($id, $workspaceId, $userId);
            $this->session->setFlash('success', 'Invoice marked as sent');
        } catch (\Exception $e) {
            $this->session->setFlash('error', 'Failed to send invoice: ' . $e->getMessage());
        }
        
        redirect('/invoices/' . $id);
    }
    
    /**
     * Cancel invoice
     */
    public function cancel(int $id): void
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        $userId = $this->getCurrentUserId();
        
        $reason = trim($_POST['reason'] ?? '');
        
        try {
            $this->service->cancelInvoice($id, $workspaceId, $userId, $reason);
            $this->session->setFlash('success', 'Invoice cancelled');
        } catch (\Exception $e) {
            $this->session->setFlash('error', 'Failed to cancel invoice: ' . $e->getMessage());
        }
        
        redirect('/invoices/' . $id);
    }
    
    /**
     * Duplicate invoice
     */
    public function duplicate(int $id): void
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        $userId = $this->getCurrentUserId();
        
        try {
            $newId = $this->service->duplicateInvoice($id, $workspaceId, $userId);
            
            if ($newId) {
                $this->session->setFlash('success', 'Invoice duplicated successfully');
                redirect('/invoices/' . $newId . '/edit');
            } else {
                $this->session->setFlash('error', 'Failed to duplicate invoice');
                redirect('/invoices/' . $id);
            }
        } catch (\Exception $e) {
            $this->session->setFlash('error', 'Failed to duplicate invoice: ' . $e->getMessage());
            redirect('/invoices/' . $id);
        }
    }
    
    /**
     * Record payment
     */
    public function recordPayment(int $id): void
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $amount = (float) ($_POST['amount'] ?? 0);
        $method = $_POST['payment_method'] ?? null;
        $date = $_POST['paid_at'] ?? date('Y-m-d');
        
        if ($amount <= 0) {
            $this->session->setFlash('error', 'Invalid payment amount');
            redirect('/invoices/' . $id);
            return;
        }
        
        try {
            $this->service->getRepository()->recordPayment($id, $workspaceId, $amount, $method);
            $this->session->setFlash('success', 'Payment recorded successfully');
        } catch (\Exception $e) {
            $this->session->setFlash('error', 'Failed to record payment: ' . $e->getMessage());
        }
        
        redirect('/invoices/' . $id);
    }
    
    /**
     * Download PDF
     */
    public function downloadPdf(int $id): void
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $invoice = $this->service->getRepository()->findById($id, $workspaceId);
        
        if (!$invoice) {
            http_response_code(404);
            echo 'Invoice not found';
            return;
        }
        
        // Mark as viewed
        $this->service->markAsViewed($id, $workspaceId);
        
        // Generate PDF (to be implemented in PDF service)
        $pdfService = new \App\Services\PdfService();
        $pdfContent = $pdfService->generateInvoicePdf($invoice, $workspaceId);
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="invoice-' . $invoice['invoice_number'] . '.pdf"');
        echo $pdfContent;
        exit;
    }
    
    /**
     * Helper: Get clients for workspace
     */
    private function getClients(int $workspaceId): array
    {
        $sql = "SELECT id, company_name, contact_name, email 
                FROM eb_clients 
                WHERE workspace_id = :workspace_id 
                AND is_customer = 1 
                AND status = 'active' 
                AND deleted_at IS NULL
                ORDER BY company_name ASC";
        
        return \Core\Database\Database::getInstance(config('database'))->query($sql, [
            'workspace_id' => $workspaceId
        ]);
    }
    
    /**
     * Helper: Get products for workspace
     */
    private function getProducts(int $workspaceId): array
    {
        $sql = "SELECT id, name, sku, unit_price, tax_rate 
                FROM eb_products 
                WHERE workspace_id = :workspace_id 
                AND is_active = 1 
                AND deleted_at IS NULL
                ORDER BY name ASC";
        
        return \Core\Database\Database::getInstance(config('database'))->query($sql, [
            'workspace_id' => $workspaceId
        ]);
    }
    
    private function getWorkspaceDefaultCurrency(): string
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        $sql = "SELECT default_currency FROM eb_workspaces WHERE id = :id";
        $result = \Core\Database\Database::getInstance(config('database'))->fetchOne($sql, ['id' => $workspaceId]);
        return $result['default_currency'] ?? 'EUR';
    }
    
    private function getWorkspaceDefaultLanguage(): string
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        $sql = "SELECT default_language FROM eb_workspaces WHERE id = :id";
        $result = \Core\Database\Database::getInstance(config('database'))->fetchOne($sql, ['id' => $workspaceId]);
        return $result['default_language'] ?? 'en';
    }
    
    private function getWorkspacePaymentTerms(): int
    {
        $workspaceId = $this->getCurrentWorkspaceId();
        $sql = "SELECT default_payment_terms FROM eb_workspaces WHERE id = :id";
        $result = \Core\Database\Database::getInstance(config('database'))->fetchOne($sql, ['id' => $workspaceId]);
        return $result['default_payment_terms'] ?? 30;
    }
}

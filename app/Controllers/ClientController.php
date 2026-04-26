<?php

namespace App\Controllers;

use App\Services\ClientService;
use App\Core\SessionManager;
use Exception;

/**
 * Client Controller
 * Handles HTTP requests for client management
 */
class ClientController extends BaseController
{
    private ClientService $clientService;

    public function __construct()
    {
        parent::__construct();
        $this->clientService = new ClientService();
    }

    /**
     * List all clients
     */
    public function index(): void
    {
        $this->requireAuth();
        
        $workspaceId = $this->getCurrentWorkspaceId();
        $search = $_GET['q'] ?? '';
        
        try {
            if ($search) {
                $clients = $this->clientService->searchClients($workspaceId, $search);
            } else {
                $clients = $this->clientService->getClients($workspaceId);
            }
            
            $this->render('clients/index', [
                'clients' => $clients,
                'search' => $search,
                'page_title' => 'Clients'
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load clients: ' . $e->getMessage());
            $this->redirect('/clients');
        }
    }

    /**
     * Show create client form
     */
    public function create(): void
    {
        $this->requireAuth();
        
        $this->render('clients/create', [
            'page_title' => 'Add New Client',
            'countries' => $this->getEuropeanCountries(),
            'currencies' => $this->getCurrencies()
        ]);
    }

    /**
     * Store new client
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->checkCsrf();
        
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $data = [
            'workspace_id' => $workspaceId,
            'company_name' => $_POST['company_name'] ?? '',
            'contact_name' => $_POST['contact_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'website' => $_POST['website'] ?? '',
            'vat_number' => $_POST['vat_number'] ?? '',
            'country_code' => $_POST['country_code'] ?? 'NL',
            'address_line1' => $_POST['address_line1'] ?? '',
            'address_line2' => $_POST['address_line2'] ?? '',
            'city' => $_POST['city'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? '',
            'state_province' => $_POST['state_province'] ?? '',
            'currency_code' => $_POST['currency_code'] ?? 'EUR',
            'payment_terms_days' => (int) ($_POST['payment_terms_days'] ?? 30),
            'default_language' => $_POST['default_language'] ?? 'en',
            'notes' => $_POST['notes'] ?? ''
        ];
        
        try {
            $result = $this->clientService->createClient($data);
            
            $this->setFlash('success', $result['message']);
            $this->redirect('/clients/' . $result['id']);
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/clients/create');
        }
    }

    /**
     * Show single client
     */
    public function show(string $id): void
    {
        $this->requireAuth();
        
        $workspaceId = $this->getCurrentWorkspaceId();
        
        try {
            $client = $this->clientService->getClient($id, $workspaceId);
            
            if (!$client) {
                http_response_code(404);
                $this->render('errors/404', ['page_title' => 'Client Not Found']);
                return;
            }
            
            // Get related data
            $invoices = $this->getClientInvoices($id, $workspaceId);
            $expenses = $this->getClientExpenses($id, $workspaceId);
            
            $this->render('clients/show', [
                'client' => $client,
                'invoices' => $invoices,
                'expenses' => $expenses,
                'page_title' => $client['company_name']
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load client: ' . $e->getMessage());
            $this->redirect('/clients');
        }
    }

    /**
     * Show edit client form
     */
    public function edit(string $id): void
    {
        $this->requireAuth();
        
        $workspaceId = $this->getCurrentWorkspaceId();
        
        try {
            $client = $this->clientService->getClient($id, $workspaceId);
            
            if (!$client) {
                http_response_code(404);
                $this->render('errors/404', ['page_title' => 'Client Not Found']);
                return;
            }
            
            $this->render('clients/edit', [
                'client' => $client,
                'page_title' => 'Edit Client',
                'countries' => $this->getEuropeanCountries(),
                'currencies' => $this->getCurrencies()
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load client: ' . $e->getMessage());
            $this->redirect('/clients');
        }
    }

    /**
     * Update client
     */
    public function update(string $id): void
    {
        $this->requireAuth();
        $this->checkCsrf();
        
        $workspaceId = $this->getCurrentWorkspaceId();
        
        $data = [
            'company_name' => $_POST['company_name'] ?? '',
            'contact_name' => $_POST['contact_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'website' => $_POST['website'] ?? '',
            'vat_number' => $_POST['vat_number'] ?? '',
            'country_code' => $_POST['country_code'] ?? 'NL',
            'address_line1' => $_POST['address_line1'] ?? '',
            'address_line2' => $_POST['address_line2'] ?? '',
            'city' => $_POST['city'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? '',
            'state_province' => $_POST['state_province'] ?? '',
            'currency_code' => $_POST['currency_code'] ?? 'EUR',
            'payment_terms_days' => (int) ($_POST['payment_terms_days'] ?? 30),
            'default_language' => $_POST['default_language'] ?? 'en',
            'notes' => $_POST['notes'] ?? ''
        ];
        
        try {
            $result = $this->clientService->updateClient($id, $workspaceId, $data);
            
            $this->setFlash('success', $result['message']);
            $this->redirect('/clients/' . $id);
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/clients/' . $id . '/edit');
        }
    }

    /**
     * Archive client
     */
    public function archive(string $id): void
    {
        $this->requireAuth();
        $this->checkCsrf();
        
        $workspaceId = $this->getCurrentWorkspaceId();
        
        try {
            $result = $this->clientService->archiveClient($id, $workspaceId);
            
            $this->setFlash('success', $result['message']);
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        
        $this->redirect('/clients');
    }

    /**
     * Restore archived client
     */
    public function restore(string $id): void
    {
        $this->requireAuth();
        $this->checkCsrf();
        
        $workspaceId = $this->getCurrentWorkspaceId();
        
        try {
            $result = $this->clientService->restoreClient($id, $workspaceId);
            
            $this->setFlash('success', $result['message']);
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        
        $this->redirect('/clients');
    }

    /**
     * API: Search clients (for autocomplete)
     */
    public function apiSearch(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');
        
        $workspaceId = $this->getCurrentWorkspaceId();
        $query = $_GET['q'] ?? '';
        
        try {
            $clients = $this->clientService->searchClients($workspaceId, $query);
            
            echo json_encode([
                'success' => true,
                'clients' => array_map(function($client) {
                    return [
                        'id' => $client['id'],
                        'name' => $client['company_name'],
                        'email' => $client['email'],
                        'vat_number' => $client['vat_number']
                    ];
                }, $clients)
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Get client invoices (helper method)
     */
    private function getClientInvoices(string $clientId, string $workspaceId): array
    {
        $sql = "SELECT * FROM invoices 
                WHERE client_id = :client_id AND workspace_id = :workspace_id 
                ORDER BY issue_date DESC LIMIT 10";
        
        return $this->db->fetchAll($sql, [
            'client_id' => $clientId,
            'workspace_id' => $workspaceId
        ]);
    }

    /**
     * Get client expenses (helper method)
     */
    private function getClientExpenses(string $clientId, string $workspaceId): array
    {
        $sql = "SELECT e.*, ec.name as category_name 
                FROM expenses e
                LEFT JOIN expense_categories ec ON e.category_id = ec.id
                WHERE e.billable_to_client_id = :client_id AND e.workspace_id = :workspace_id 
                ORDER BY expense_date DESC LIMIT 10";
        
        return $this->db->fetchAll($sql, [
            'client_id' => $clientId,
            'workspace_id' => $workspaceId
        ]);
    }

    /**
     * Get European countries list
     */
    private function getEuropeanCountries(): array
    {
        return [
            ['code' => 'AT', 'name' => 'Austria'],
            ['code' => 'BE', 'name' => 'Belgium'],
            ['code' => 'BG', 'name' => 'Bulgaria'],
            ['code' => 'HR', 'name' => 'Croatia'],
            ['code' => 'CY', 'name' => 'Cyprus'],
            ['code' => 'CZ', 'name' => 'Czech Republic'],
            ['code' => 'DK', 'name' => 'Denmark'],
            ['code' => 'EE', 'name' => 'Estonia'],
            ['code' => 'FI', 'name' => 'Finland'],
            ['code' => 'FR', 'name' => 'France'],
            ['code' => 'DE', 'name' => 'Germany'],
            ['code' => 'GR', 'name' => 'Greece'],
            ['code' => 'HU', 'name' => 'Hungary'],
            ['code' => 'IE', 'name' => 'Ireland'],
            ['code' => 'IT', 'name' => 'Italy'],
            ['code' => 'LV', 'name' => 'Latvia'],
            ['code' => 'LT', 'name' => 'Lithuania'],
            ['code' => 'LU', 'name' => 'Luxembourg'],
            ['code' => 'MT', 'name' => 'Malta'],
            ['code' => 'NL', 'name' => 'Netherlands'],
            ['code' => 'PL', 'name' => 'Poland'],
            ['code' => 'PT', 'name' => 'Portugal'],
            ['code' => 'RO', 'name' => 'Romania'],
            ['code' => 'SK', 'name' => 'Slovakia'],
            ['code' => 'SI', 'name' => 'Slovenia'],
            ['code' => 'ES', 'name' => 'Spain'],
            ['code' => 'SE', 'name' => 'Sweden'],
            ['code' => 'GB', 'name' => 'United Kingdom'],
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
            ['code' => 'HUF', 'name' => 'Hungarian Forint', 'symbol' => 'Ft'],
            ['code' => 'SEK', 'name' => 'Swedish Krona', 'symbol' => 'kr'],
            ['code' => 'DKK', 'name' => 'Danish Krone', 'symbol' => 'kr'],
            ['code' => 'NOK', 'name' => 'Norwegian Krone', 'symbol' => 'kr'],
        ];
    }
}

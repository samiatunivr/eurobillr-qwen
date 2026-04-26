<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Core\Database;
use Exception;

/**
 * Client Service
 * Business logic layer for client management
 */
class ClientService
{
    private Client $clientModel;
    private Database $db;

    public function __construct()
    {
        $this->clientModel = new Client();
        $this->db = Database::getInstance();
    }

    /**
     * Get all clients for workspace
     */
    public function getClients(string $workspaceId, bool $activeOnly = true): array
    {
        return $this->clientModel->getWorkspaceClients($workspaceId, $activeOnly);
    }

    /**
     * Get single client
     */
    public function getClient(string $id, string $workspaceId): ?array
    {
        return $this->clientModel->findById($id, $workspaceId);
    }

    /**
     * Search clients
     */
    public function searchClients(string $workspaceId, string $query): array
    {
        if (strlen($query) < 2) {
            return [];
        }
        
        return $this->clientModel->search($workspaceId, $query);
    }

    /**
     * Create client with validation
     */
    public function createClient(array $data): array
    {
        // Validation
        $errors = $this->validateClientData($data);
        
        if (!empty($errors)) {
            throw new Exception(json_encode($errors));
        }
        
        // Check for duplicate email
        $existing = $this->clientModel->findByEmail($data['email'], $data['workspace_id']);
        if ($existing) {
            throw new Exception('A client with this email already exists');
        }
        
        // Validate VAT number if provided
        if (!empty($data['vat_number']) && !empty($data['country_code'])) {
            if (!$this->clientModel->validateVatNumber($data['vat_number'], $data['country_code'])) {
                throw new Exception('Invalid VAT number format for selected country');
            }
        }
        
        $id = $this->clientModel->create($data);
        
        // Log activity
        $this->logActivity($data['workspace_id'], 'client_created', $id);
        
        return [
            'success' => true,
            'id' => $id,
            'message' => 'Client created successfully'
        ];
    }

    /**
     * Update client
     */
    public function updateClient(string $id, string $workspaceId, array $data): array
    {
        $existing = $this->clientModel->findById($id, $workspaceId);
        if (!$existing) {
            throw new Exception('Client not found');
        }
        
        $errors = $this->validateClientData($data, true);
        
        if (!empty($errors)) {
            throw new Exception(json_encode($errors));
        }
        
        // Check for duplicate email (excluding current client)
        if (isset($data['email']) && $data['email'] !== $existing['email']) {
            $duplicate = $this->clientModel->findByEmail($data['email'], $workspaceId);
            if ($duplicate && $duplicate['id'] !== $id) {
                throw new Exception('A client with this email already exists');
            }
        }
        
        // Validate VAT number if changed
        if (isset($data['vat_number']) && isset($data['country_code'])) {
            if (!$this->clientModel->validateVatNumber($data['vat_number'], $data['country_code'])) {
                throw new Exception('Invalid VAT number format for selected country');
            }
        }
        
        $success = $this->clientModel->update($id, $workspaceId, $data);
        
        if ($success) {
            $this->logActivity($workspaceId, 'client_updated', $id);
        }
        
        return [
            'success' => $success,
            'message' => $success ? 'Client updated successfully' : 'Failed to update client'
        ];
    }

    /**
     * Archive client
     */
    public function archiveClient(string $id, string $workspaceId): array
    {
        $existing = $this->clientModel->findById($id, $workspaceId);
        if (!$existing) {
            throw new Exception('Client not found');
        }
        
        // Check if client has unpaid invoices
        $hasUnpaidInvoices = $this->checkUnpaidInvoices($id);
        if ($hasUnpaidInvoices) {
            throw new Exception('Cannot archive client with unpaid invoices');
        }
        
        $success = $this->clientModel->archive($id, $workspaceId);
        
        if ($success) {
            $this->logActivity($workspaceId, 'client_archived', $id);
        }
        
        return [
            'success' => $success,
            'message' => $success ? 'Client archived successfully' : 'Failed to archive client'
        ];
    }

    /**
     * Restore archived client
     */
    public function restoreClient(string $id, string $workspaceId): array
    {
        $success = $this->clientModel->restore($id, $workspaceId);
        
        if ($success) {
            $this->logActivity($workspaceId, 'client_restored', $id);
        }
        
        return [
            'success' => $success,
            'message' => $success ? 'Client restored successfully' : 'Failed to restore client'
        ];
    }

    /**
     * Get client statistics
     */
    public function getStatistics(string $workspaceId): array
    {
        return $this->clientModel->getStatistics($workspaceId);
    }

    /**
     * Validate client data
     */
    private function validateClientData(array $data, bool $isUpdate = false): array
    {
        $errors = [];
        
        // Required fields for create
        if (!$isUpdate) {
            if (empty($data['company_name'])) {
                $errors['company_name'] = 'Company name is required';
            }
            
            if (empty($data['email'])) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            }
            
            if (empty($data['country_code'])) {
                $errors['country_code'] = 'Country is required';
            }
            
            if (empty($data['address_line1'])) {
                $errors['address_line1'] = 'Address is required';
            }
            
            if (empty($data['city'])) {
                $errors['city'] = 'City is required';
            }
            
            if (empty($data['postal_code'])) {
                $errors['postal_code'] = 'Postal code is required';
            }
        } else {
            // Update validation
            if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            }
        }
        
        // Optional field validations
        if (!empty($data['phone']) && !preg_match('/^[\d\s\+\-\(\)]+$/', $data['phone'])) {
            $errors['phone'] = 'Invalid phone number format';
        }
        
        if (!empty($data['website']) && !filter_var($data['website'], FILTER_VALIDATE_URL)) {
            $errors['website'] = 'Invalid website URL';
        }
        
        return $errors;
    }

    /**
     * Check if client has unpaid invoices
     */
    private function checkUnpaidInvoices(string $clientId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM invoices 
                WHERE client_id = :client_id 
                AND status IN ('sent', 'partial', 'overdue')";
        
        $result = $this->db->fetch($sql, ['client_id' => $clientId]);
        
        return $result && $result['count'] > 0;
    }

    /**
     * Log activity
     */
    private function logActivity(string $workspaceId, string $type, string $referenceId): void
    {
        // Implementation would use ActivityLog model
        // Keeping simple for now
    }
}

<?php

namespace App\Models;

use App\Core\Database;

/**
 * Client Model
 * Manages client data with EU VAT validation and Peppol support
 */
class Client
{
    private Database $db;
    private string $table = 'clients';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all clients for a workspace
     */
    public function getWorkspaceClients(string $workspaceId, bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE workspace_id = :workspace_id";
        
        if ($activeOnly) {
            $sql .= " AND is_active = 1 AND archived_at IS NULL";
        }
        
        $sql .= " ORDER BY company_name ASC";
        
        return $this->db->fetchAll($sql, ['workspace_id' => $workspaceId]);
    }

    /**
     * Find client by ID with workspace isolation
     */
    public function findById(string $id, string $workspaceId): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE id = :id AND workspace_id = :workspace_id";
        
        $client = $this->db->fetch($sql, [
            'id' => $id,
            'workspace_id' => $workspaceId
        ]);
        
        return $client ?: null;
    }

    /**
     * Find client by email
     */
    public function findByEmail(string $email, string $workspaceId): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE email = :email AND workspace_id = :workspace_id";
        
        $client = $this->db->fetch($sql, [
            'email' => $email,
            'workspace_id' => $workspaceId
        ]);
        
        return $client ?: null;
    }

    /**
     * Search clients
     */
    public function search(string $workspaceId, string $query): array
    {
        $searchTerm = "%{$query}%";
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE workspace_id = :workspace_id 
                AND is_active = 1
                AND (company_name LIKE :search 
                     OR contact_name LIKE :search 
                     OR email LIKE :search 
                     OR vat_number LIKE :search)
                ORDER BY company_name ASC
                LIMIT 50";
        
        return $this->db->fetchAll($sql, [
            'workspace_id' => $workspaceId,
            'search' => $searchTerm
        ]);
    }

    /**
     * Create new client
     */
    public function create(array $data): string
    {
        $id = $this->generateUuid();
        
        $sql = "INSERT INTO {$this->table} (
                    id, workspace_id, company_name, contact_name, email, phone, website,
                    vat_number, tax_id, peppol_id, country_code,
                    address_line1, address_line2, city, postal_code, state_province,
                    currency_code, payment_terms_days, default_language, notes
                ) VALUES (
                    :id, :workspace_id, :company_name, :contact_name, :email, :phone, :website,
                    :vat_number, :tax_id, :peppol_id, :country_code,
                    :address_line1, :address_line2, :city, :postal_code, :state_province,
                    :currency_code, :payment_terms_days, :default_language, :notes
                )";
        
        $params = array_merge([
            'id' => $id,
            'workspace_id' => $data['workspace_id'],
            'company_name' => $data['company_name'],
            'email' => $data['email'],
            'country_code' => $data['country_code'] ?? 'NL',
            'address_line1' => $data['address_line1'] ?? '',
            'city' => $data['city'] ?? '',
            'postal_code' => $data['postal_code'] ?? '',
            'currency_code' => $data['currency_code'] ?? 'EUR',
            'payment_terms_days' => $data['payment_terms_days'] ?? 30,
            'default_language' => $data['default_language'] ?? 'en',
        ], array_filter([
            'contact_name' => $data['contact_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'website' => $data['website'] ?? null,
            'vat_number' => $data['vat_number'] ?? null,
            'tax_id' => $data['tax_id'] ?? null,
            'peppol_id' => $data['peppol_id'] ?? null,
            'address_line2' => $data['address_line2'] ?? null,
            'state_province' => $data['state_province'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]));
        
        $this->db->execute($sql, $params);
        
        return $id;
    }

    /**
     * Update client
     */
    public function update(string $id, string $workspaceId, array $data): bool
    {
        // First verify ownership
        $existing = $this->findById($id, $workspaceId);
        if (!$existing) {
            return false;
        }
        
        $setClause = [];
        $params = ['id' => $id, 'workspace_id' => $workspaceId];
        
        $allowedFields = [
            'company_name', 'contact_name', 'email', 'phone', 'website',
            'vat_number', 'tax_id', 'peppol_id', 'country_code',
            'address_line1', 'address_line2', 'city', 'postal_code', 'state_province',
            'currency_code', 'payment_terms_days', 'default_language', 'notes'
        ];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $setClause[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
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
     * Archive client (soft delete)
     */
    public function archive(string $id, string $workspaceId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET is_active = 0, archived_at = CURRENT_TIMESTAMP 
                WHERE id = :id AND workspace_id = :workspace_id";
        
        return $this->db->execute($sql, [
            'id' => $id,
            'workspace_id' => $workspaceId
        ]);
    }

    /**
     * Restore archived client
     */
    public function restore(string $id, string $workspaceId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET is_active = 1, archived_at = NULL 
                WHERE id = :id AND workspace_id = :workspace_id";
        
        return $this->db->execute($sql, [
            'id' => $id,
            'workspace_id' => $workspaceId
        ]);
    }

    /**
     * Validate EU VAT number format
     */
    public function validateVatNumber(string $vatNumber, string $countryCode): bool
    {
        $vatNumber = strtoupper(str_replace([' ', '-', '.'], '', $vatNumber));
        
        $patterns = [
            'AT' => '/^ATU\d{8}$/',
            'BE' => '/^BE0?\d{9}$/',
            'BG' => '/^BG\d{9,10}$/',
            'HR' => '/^HR\d{11}$/',
            'CY' => '/^CY\d{8}[A-Z]$/',
            'CZ' => '/^CZ\d{8,10}$/',
            'DK' => '/^DK\d{8}$/',
            'EE' => '/^EE\d{9}$/',
            'FI' => '/^FI\d{8}$/',
            'FR' => '/^FR[A-Z0-9]{2}\d{9}$/',
            'DE' => '/^DE\d{9}$/',
            'GR' => '/^GR\d{9}$/',
            'HU' => '/^HU\d{8}$/',
            'IE' => '/^IE\d{7}[A-Z]{1,2}$/',
            'IT' => '/^IT\d{11}$/',
            'LV' => '/^LV\d{11}$/',
            'LT' => '/^LT\d{9,12}$/',
            'LU' => '/^LU\d{8}$/',
            'MT' => '/^MT\d{8}$/',
            'NL' => '/^NL\d{9}B\d{2}$/',
            'PL' => '/^PL\d{10}$/',
            'PT' => '/^PT\d{9}$/',
            'RO' => '/^RO\d{2,10}$/',
            'SK' => '/^SK\d{10}$/',
            'SI' => '/^SI\d{8}$/',
            'ES' => '/^ES[A-Z0-9]\d{7}[A-Z0-9]$/',
            'SE' => '/^SE\d{12}$/',
            'GB' => '/^GB\d{9}$/',
        ];
        
        if (!isset($patterns[$countryCode])) {
            return true; // Allow unknown countries
        }
        
        return (bool) preg_match($patterns[$countryCode], $countryCode . $vatNumber);
    }

    /**
     * Get client statistics for dashboard
     */
    public function getStatistics(string $workspaceId): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_clients,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_clients,
                    SUM(CASE WHEN archived_at IS NOT NULL THEN 1 ELSE 0 END) as archived_clients
                FROM {$this->table}
                WHERE workspace_id = :workspace_id";
        
        return $this->db->fetch($sql, ['workspace_id' => $workspaceId]) ?: [
            'total_clients' => 0,
            'active_clients' => 0,
            'archived_clients' => 0
        ];
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

<?php
/**
 * InvoiceData DTO
 * Data Transfer Object for invoice operations
 */

namespace App\DTOs;

class InvoiceData
{
    public function __construct(
        public int $workspaceId,
        public int $clientId,
        public string $type = 'invoice',
        public string $status = 'draft',
        public ?string $invoiceNumber = null,
        public ?string $reference = null,
        public string $currency = 'EUR',
        public ?float $exchangeRate = null,
        public string $language = 'en',
        public string $issueDate,
        public string $dueDate,
        public array $items = [],
        public string $discountType = 'percentage',
        public float $discountValue = 0.0,
        public ?string $notes = null,
        public ?string $footer = null,
        public ?string $terms = null,
        public ?int $createdBy = null
    ) {}
    
    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            workspaceId: (int) ($data['workspace_id'] ?? 0),
            clientId: (int) ($data['client_id'] ?? 0),
            type: $data['type'] ?? 'invoice',
            status: $data['status'] ?? 'draft',
            invoiceNumber: $data['invoice_number'] ?? null,
            reference: $data['reference'] ?? null,
            currency: $data['currency'] ?? 'EUR',
            exchangeRate: isset($data['exchange_rate']) ? (float) $data['exchange_rate'] : null,
            language: $data['language'] ?? 'en',
            issueDate: $data['issue_date'],
            dueDate: $data['due_date'],
            items: $data['items'] ?? [],
            discountType: $data['discount_type'] ?? 'percentage',
            discountValue: (float) ($data['discount_value'] ?? 0),
            notes: $data['notes'] ?? null,
            footer: $data['footer'] ?? null,
            terms: $data['terms'] ?? null,
            createdBy: isset($data['created_by']) ? (int) $data['created_by'] : null
        );
    }
    
    /**
     * Validate the DTO
     */
    public function validate(): array
    {
        $errors = [];
        
        if ($this->workspaceId <= 0) {
            $errors[] = 'Workspace ID is required';
        }
        
        if ($this->clientId <= 0) {
            $errors[] = 'Client ID is required';
        }
        
        if (empty($this->issueDate)) {
            $errors[] = 'Issue date is required';
        }
        
        if (empty($this->dueDate)) {
            $errors[] = 'Due date is required';
        }
        
        if ($this->dueDate < $this->issueDate) {
            $errors[] = 'Due date cannot be before issue date';
        }
        
        if (empty($this->items)) {
            $errors[] = 'At least one line item is required';
        }
        
        foreach ($this->items as $index => $item) {
            if (empty($item['description'])) {
                $errors[] = "Item #" . ($index + 1) . " description is required";
            }
            
            if (!isset($item['quantity']) || $item['quantity'] <= 0) {
                $errors[] = "Item #" . ($index + 1) . " quantity must be positive";
            }
            
            if (!isset($item['unit_price']) || $item['unit_price'] < 0) {
                $errors[] = "Item #" . ($index + 1) . " unit price must be non-negative";
            }
        }
        
        return $errors;
    }
}

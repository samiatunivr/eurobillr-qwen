-- Clients Module Migration
-- Supports EU VAT validation, multi-currency, and Peppol IDs

CREATE TABLE IF NOT EXISTS clients (
    id CHAR(36) PRIMARY KEY,
    workspace_id CHAR(36) NOT NULL,
    
    -- Core Identity
    company_name VARCHAR(255) NOT NULL,
    contact_name VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    
    -- Tax & Legal
    vat_number VARCHAR(50) DEFAULT NULL, -- e.g., DE123456789
    tax_id VARCHAR(50) DEFAULT NULL, -- Local tax ID if different from VAT
    peppol_id VARCHAR(100) DEFAULT NULL, -- For e-invoicing
    country_code CHAR(2) NOT NULL DEFAULT 'NL',
    
    -- Address
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255) DEFAULT NULL,
    city VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    state_province VARCHAR(100) DEFAULT NULL,
    
    -- Financial Defaults
    currency_code CHAR(3) NOT NULL DEFAULT 'EUR',
    payment_terms_days INT DEFAULT 30,
    default_language CHAR(2) DEFAULT 'en',
    
    -- Metadata
    notes TEXT DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    archived_at TIMESTAMP NULL DEFAULT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_workspace (workspace_id),
    INDEX idx_vat (vat_number),
    INDEX idx_email (email),
    INDEX idx_active (is_active),
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Expenses Module Migration
-- Supports receipts, OCR data, and categorization

CREATE TABLE IF NOT EXISTS expense_categories (
    id CHAR(36) PRIMARY KEY,
    workspace_id CHAR(36) NOT NULL,
    name VARCHAR(100) NOT NULL,
    color_code CHAR(7) DEFAULT '#6B7280', -- Hex color for UI
    tax_rate_default DECIMAL(5,2) DEFAULT 0.00,
    is_system BOOLEAN DEFAULT FALSE, -- Cannot delete system categories
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_workspace (workspace_id),
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS expenses (
    id CHAR(36) PRIMARY KEY,
    workspace_id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL, -- Who created the expense
    
    -- Vendor Details (Snapshot at time of entry)
    vendor_name VARCHAR(255) NOT NULL,
    vendor_vat_number VARCHAR(50) DEFAULT NULL,
    vendor_address TEXT DEFAULT NULL,
    
    -- Financials
    amount DECIMAL(15,2) NOT NULL, -- Net amount
    tax_amount DECIMAL(15,2) DEFAULT 0.00,
    total_amount DECIMAL(15,2) NOT NULL, -- Gross amount
    currency_code CHAR(3) NOT NULL DEFAULT 'EUR',
    exchange_rate DECIMAL(15,6) DEFAULT 1.000000, -- If not base currency
    base_currency_amount DECIMAL(15,2) NOT NULL, -- Converted to workspace base
    
    -- Categorization
    category_id CHAR(36) DEFAULT NULL,
    billable_to_client_id CHAR(36) DEFAULT NULL, -- If pass-through expense
    is_billable BOOLEAN DEFAULT FALSE,
    
    -- Dates
    expense_date DATE NOT NULL,
    due_date DATE DEFAULT NULL,
    paid_date DATE DEFAULT NULL,
    payment_method ENUM('cash', 'credit_card', 'bank_transfer', 'paypal', 'other') DEFAULT 'bank_transfer',
    
    -- Receipt & OCR
    receipt_path VARCHAR(500) DEFAULT NULL,
    receipt_mime_type VARCHAR(100) DEFAULT NULL,
    ocr_processed BOOLEAN DEFAULT FALSE,
    ocr_confidence_score DECIMAL(3,2) DEFAULT 0.00,
    ocr_raw_data JSON DEFAULT NULL, -- Store raw OCR response
    
    -- Status & Workflow
    status ENUM('pending', 'approved', 'rejected', 'paid', 'reimbursed') DEFAULT 'pending',
    reference_number VARCHAR(100) DEFAULT NULL, -- Vendor invoice number
    notes TEXT DEFAULT NULL,
    internal_notes TEXT DEFAULT NULL, -- For team collaboration
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_workspace (workspace_id),
    INDEX idx_user (user_id),
    INDEX idx_date (expense_date),
    INDEX idx_status (status),
    INDEX idx_category (category_id),
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES expense_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (billable_to_client_id) REFERENCES clients(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default categories
INSERT INTO expense_categories (id, workspace_id, name, color_code, is_system) VALUES 
(UUID(), '00000000-0000-0000-0000-000000000000', 'Office Supplies', '#3B82F6', TRUE),
(UUID(), '00000000-0000-0000-0000-000000000000', 'Software & Subscriptions', '#8B5CF6', TRUE),
(UUID(), '00000000-0000-0000-0000-000000000000', 'Travel & Transport', '#10B981', TRUE),
(UUID(), '00000000-0000-0000-0000-000000000000', 'Meals & Entertainment', '#F59E0B', TRUE),
(UUID(), '00000000-0000-0000-0000-000000000000', 'Professional Services', '#EC4899', TRUE);

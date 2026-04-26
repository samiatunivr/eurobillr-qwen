-- Eurobillr Database Schema
-- Enterprise SaaS Invoicing Platform
-- Version 1.0.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================
-- USERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `country_code` CHAR(2) DEFAULT 'BE',
    `language` VARCHAR(10) DEFAULT 'en',
    `timezone` VARCHAR(50) DEFAULT 'Europe/Brussels',
    `avatar_path` VARCHAR(255) DEFAULT NULL,
    `email_verified_at` DATETIME DEFAULT NULL,
    `verification_token` VARCHAR(64) DEFAULT NULL,
    `two_factor_enabled` TINYINT(1) DEFAULT 0,
    `two_factor_secret` VARCHAR(255) DEFAULT NULL,
    `two_factor_recovery_codes` TEXT DEFAULT NULL,
    `remember_token` VARCHAR(64) DEFAULT NULL,
    `last_login_at` DATETIME DEFAULT NULL,
    `last_login_ip` VARCHAR(45) DEFAULT NULL,
    `login_attempts` INT DEFAULT 0,
    `locked_until` DATETIME DEFAULT NULL,
    `status` ENUM('active', 'inactive', 'suspended', 'deleted') DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_email` (`email`),
    KEY `idx_status` (`status`),
    KEY `idx_country` (`country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- WORKSPACES TABLE (Multi-tenant)
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_workspaces` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `owner_id` INT UNSIGNED NOT NULL,
    `logo_path` VARCHAR(255) DEFAULT NULL,
    `brand_color_primary` VARCHAR(7) DEFAULT '#2563eb',
    `brand_color_secondary` VARCHAR(7) DEFAULT '#1e40af',
    `address_line1` VARCHAR(255) DEFAULT NULL,
    `address_line2` VARCHAR(255) DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `postal_code` VARCHAR(20) DEFAULT NULL,
    `country_code` CHAR(2) DEFAULT 'BE',
    `vat_number` VARCHAR(50) DEFAULT NULL,
    `company_number` VARCHAR(50) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `website` VARCHAR(255) DEFAULT NULL,
    `default_currency` VARCHAR(3) DEFAULT 'EUR',
    `default_language` VARCHAR(10) DEFAULT 'en',
    `default_payment_terms` INT DEFAULT 30,
    `invoice_footer` TEXT DEFAULT NULL,
    `invoice_notes` TEXT DEFAULT NULL,
    `peppol_id` VARCHAR(100) DEFAULT NULL,
    `peppol_scheme` VARCHAR(50) DEFAULT NULL,
    `tax_system` ENUM('standard', 'margin', 'exempt') DEFAULT 'standard',
    `subscription_plan` VARCHAR(50) DEFAULT 'free',
    `subscription_status` ENUM('active', 'trial', 'cancelled', 'expired') DEFAULT 'trial',
    `trial_ends_at` DATETIME DEFAULT NULL,
    `subscription_expires_at` DATETIME DEFAULT NULL,
    `settings` JSON DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_slug` (`slug`),
    KEY `idx_owner` (`owner_id`),
    KEY `idx_country` (`country_code`),
    KEY `idx_subscription` (`subscription_status`),
    CONSTRAINT `fk_workspace_owner` FOREIGN KEY (`owner_id`) REFERENCES `eb_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- WORKSPACE MEMBERS (RBAC)
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_workspace_members` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `workspace_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `role` ENUM('owner', 'admin', 'accountant', 'staff', 'viewer') NOT NULL DEFAULT 'viewer',
    `permissions` JSON DEFAULT NULL,
    `invited_by` INT UNSIGNED DEFAULT NULL,
    `invitation_token` VARCHAR(64) DEFAULT NULL,
    `invitation_accepted_at` DATETIME DEFAULT NULL,
    `status` ENUM('pending', 'active', 'inactive') DEFAULT 'pending',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_workspace_user` (`workspace_id`, `user_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_role` (`role`),
    CONSTRAINT `fk_wm_workspace` FOREIGN KEY (`workspace_id`) REFERENCES `eb_workspaces` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_wm_user` FOREIGN KEY (`user_id`) REFERENCES `eb_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_wm_invited_by` FOREIGN KEY (`invited_by`) REFERENCES `eb_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CLIENTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_clients` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `workspace_id` INT UNSIGNED NOT NULL,
    `type` ENUM('company', 'individual') DEFAULT 'company',
    `company_name` VARCHAR(255) DEFAULT NULL,
    `contact_name` VARCHAR(255) DEFAULT NULL,
    `vat_number` VARCHAR(50) DEFAULT NULL,
    `company_number` VARCHAR(50) DEFAULT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `website` VARCHAR(255) DEFAULT NULL,
    `address_line1` VARCHAR(255) DEFAULT NULL,
    `address_line2` VARCHAR(255) DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `postal_code` VARCHAR(20) DEFAULT NULL,
    `country_code` CHAR(2) DEFAULT 'BE',
    `currency` VARCHAR(3) DEFAULT 'EUR',
    `language` VARCHAR(10) DEFAULT 'en',
    `payment_terms` INT DEFAULT 30,
    `notes` TEXT DEFAULT NULL,
    `peppol_id` VARCHAR(100) DEFAULT NULL,
    `peppol_scheme` VARCHAR(50) DEFAULT NULL,
    `is_vendor` TINYINT(1) DEFAULT 0,
    `is_customer` TINYINT(1) DEFAULT 1,
    `status` ENUM('active', 'archived') DEFAULT 'active',
    `last_invoice_date` DATE DEFAULT NULL,
    `total_invoiced` DECIMAL(15,2) DEFAULT 0.00,
    `total_paid` DECIMAL(15,2) DEFAULT 0.00,
    `balance_due` DECIMAL(15,2) DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_workspace` (`workspace_id`),
    KEY `idx_email` (`email`),
    KEY `idx_vat` (`vat_number`),
    KEY `idx_country` (`country_code`),
    KEY `idx_status` (`status`),
    CONSTRAINT `fk_client_workspace` FOREIGN KEY (`workspace_id`) REFERENCES `eb_workspaces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INVOICES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_invoices` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `workspace_id` INT UNSIGNED NOT NULL,
    `client_id` INT UNSIGNED NOT NULL,
    `invoice_number` VARCHAR(50) NOT NULL,
    `reference` VARCHAR(100) DEFAULT NULL,
    `type` ENUM('invoice', 'credit_note', 'proforma') DEFAULT 'invoice',
    `status` ENUM('draft', 'sent', 'viewed', 'partial', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
    `currency` VARCHAR(3) DEFAULT 'EUR',
    `exchange_rate` DECIMAL(20,10) DEFAULT 1.0000000000,
    `language` VARCHAR(10) DEFAULT 'en',
    `issue_date` DATE NOT NULL,
    `due_date` DATE NOT NULL,
    `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `discount_type` ENUM('percentage', 'fixed') DEFAULT 'percentage',
    `discount_value` DECIMAL(15,2) DEFAULT 0.00,
    `tax_total` DECIMAL(15,2) DEFAULT 0.00,
    `total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `paid_amount` DECIMAL(15,2) DEFAULT 0.00,
    `balance_due` DECIMAL(15,2) DEFAULT 0.00,
    `notes` TEXT DEFAULT NULL,
    `footer` TEXT DEFAULT NULL,
    `terms` TEXT DEFAULT NULL,
    `payment_method` VARCHAR(50) DEFAULT NULL,
    `structured_reference` VARCHAR(50) DEFAULT NULL,
    `peppol_sent` TINYINT(1) DEFAULT 0,
    `peppol_message_id` VARCHAR(100) DEFAULT NULL,
    `peppol_sent_at` DATETIME DEFAULT NULL,
    `peppol_status` VARCHAR(50) DEFAULT NULL,
    `pdf_path` VARCHAR(255) DEFAULT NULL,
    `ubl_xml_path` VARCHAR(255) DEFAULT NULL,
    `recurring_frequency` ENUM('weekly', 'biweekly', 'monthly', 'quarterly', 'yearly') DEFAULT NULL,
    `recurring_next_date` DATE DEFAULT NULL,
    `recurring_end_date` DATE DEFAULT NULL,
    `parent_invoice_id` INT UNSIGNED DEFAULT NULL,
    `original_invoice_id` INT UNSIGNED DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `sent_at` DATETIME DEFAULT NULL,
    `viewed_at` DATETIME DEFAULT NULL,
    `paid_at` DATETIME DEFAULT NULL,
    `reminder_count` INT DEFAULT 0,
    `last_reminder_sent` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_invoice_number` (`workspace_id`, `invoice_number`),
    KEY `idx_workspace` (`workspace_id`),
    KEY `idx_client` (`client_id`),
    KEY `idx_status` (`status`),
    KEY `idx_type` (`type`),
    KEY `idx_issue_date` (`issue_date`),
    KEY `idx_due_date` (`due_date`),
    KEY `idx_currency` (`currency`),
    KEY `idx_peppol` (`peppol_sent`),
    CONSTRAINT `fk_invoice_workspace` FOREIGN KEY (`workspace_id`) REFERENCES `eb_workspaces` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_invoice_client` FOREIGN KEY (`client_id`) REFERENCES `eb_clients` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_invoice_parent` FOREIGN KEY (`parent_invoice_id`) REFERENCES `eb_invoices` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_invoice_original` FOREIGN KEY (`original_invoice_id`) REFERENCES `eb_invoices` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_invoice_created_by` FOREIGN KEY (`created_by`) REFERENCES `eb_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INVOICE ITEMS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_invoice_items` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `invoice_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED DEFAULT NULL,
    `description` TEXT NOT NULL,
    `sku` VARCHAR(100) DEFAULT NULL,
    `quantity` DECIMAL(15,4) NOT NULL DEFAULT 1.0000,
    `unit_price` DECIMAL(15,2) NOT NULL,
    `discount_type` ENUM('percentage', 'fixed') DEFAULT 'percentage',
    `discount_value` DECIMAL(15,2) DEFAULT 0.00,
    `tax_rate` DECIMAL(5,2) DEFAULT 0.00,
    `tax_amount` DECIMAL(15,2) DEFAULT 0.00,
    `line_total` DECIMAL(15,2) NOT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_invoice` (`invoice_id`),
    KEY `idx_product` (`product_id`),
    CONSTRAINT `fk_item_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `eb_invoices` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_item_product` FOREIGN KEY (`product_id`) REFERENCES `eb_products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PRODUCTS/SERVICES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_products` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `workspace_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `sku` VARCHAR(100) DEFAULT NULL,
    `unit_price` DECIMAL(15,2) NOT NULL,
    `tax_rate` DECIMAL(5,2) DEFAULT 0.00,
    `category` VARCHAR(100) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_workspace` (`workspace_id`),
    KEY `idx_sku` (`sku`),
    KEY `idx_category` (`category`),
    CONSTRAINT `fk_product_workspace` FOREIGN KEY (`workspace_id`) REFERENCES `eb_workspaces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- EXPENSES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_expenses` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `workspace_id` INT UNSIGNED NOT NULL,
    `vendor_id` INT UNSIGNED DEFAULT NULL,
    `expense_date` DATE NOT NULL,
    `invoice_number` VARCHAR(100) DEFAULT NULL,
    `description` TEXT NOT NULL,
    `category_id` INT UNSIGNED DEFAULT NULL,
    `currency` VARCHAR(3) DEFAULT 'EUR',
    `exchange_rate` DECIMAL(20,10) DEFAULT 1.0000000000,
    `amount` DECIMAL(15,2) NOT NULL,
    `tax_rate` DECIMAL(5,2) DEFAULT 0.00,
    `tax_amount` DECIMAL(15,2) DEFAULT 0.00,
    `total` DECIMAL(15,2) NOT NULL,
    `payment_method` ENUM('bank_transfer', 'credit_card', 'cash', 'paypal', 'other') DEFAULT 'bank_transfer',
    `payment_status` ENUM('pending', 'paid', 'reimbursed') DEFAULT 'pending',
    `paid_at` DATE DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `receipt_path` VARCHAR(255) DEFAULT NULL,
    `ocr_data` JSON DEFAULT NULL,
    `peppol_received` TINYINT(1) DEFAULT 0,
    `peppol_message_id` VARCHAR(100) DEFAULT NULL,
    `billable` TINYINT(1) DEFAULT 0,
    `invoice_id` INT UNSIGNED DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_workspace` (`workspace_id`),
    KEY `idx_vendor` (`vendor_id`),
    KEY `idx_category` (`category_id`),
    KEY `idx_date` (`expense_date`),
    KEY `idx_payment_status` (`payment_status`),
    KEY `idx_billable` (`billable`),
    CONSTRAINT `fk_expense_workspace` FOREIGN KEY (`workspace_id`) REFERENCES `eb_workspaces` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_expense_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `eb_clients` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_expense_category` FOREIGN KEY (`category_id`) REFERENCES `eb_expense_categories` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_expense_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `eb_invoices` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_expense_created_by` FOREIGN KEY (`created_by`) REFERENCES `eb_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- EXPENSE CATEGORIES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_expense_categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `workspace_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(100) NOT NULL,
    `color` VARCHAR(7) DEFAULT '#6b7280',
    `is_system` TINYINT(1) DEFAULT 0,
    `tax_rate` DECIMAL(5,2) DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_workspace` (`workspace_id`),
    CONSTRAINT `fk_category_workspace` FOREIGN KEY (`workspace_id`) REFERENCES `eb_workspaces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PAYMENTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_payments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `workspace_id` INT UNSIGNED NOT NULL,
    `invoice_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `currency` VARCHAR(3) DEFAULT 'EUR',
    `exchange_rate` DECIMAL(20,10) DEFAULT 1.0000000000,
    `payment_method` VARCHAR(50) NOT NULL,
    `payment_gateway` VARCHAR(50) DEFAULT NULL,
    `gateway_transaction_id` VARCHAR(255) DEFAULT NULL,
    `reference` VARCHAR(100) DEFAULT NULL,
    `status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    `processed_at` DATETIME DEFAULT NULL,
    `metadata` JSON DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_workspace` (`workspace_id`),
    KEY `idx_invoice` (`invoice_id`),
    KEY `idx_status` (`status`),
    KEY `idx_gateway` (`payment_gateway`),
    KEY `idx_processed_at` (`processed_at`),
    CONSTRAINT `fk_payment_workspace` FOREIGN KEY (`workspace_id`) REFERENCES `eb_workspaces` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_payment_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `eb_invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SESSIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_sessions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id` VARCHAR(128) NOT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `data` TEXT NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_session_id` (`session_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_expires` (`expires_at`),
    CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `eb_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PASSWORD RESETS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_password_resets` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `used` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_email` (`email`),
    KEY `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- AUDIT LOGS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_audit_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `workspace_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(50) DEFAULT NULL,
    `entity_id` INT UNSIGNED DEFAULT NULL,
    `old_values` JSON DEFAULT NULL,
    `new_values` JSON DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_workspace` (`workspace_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_entity` (`entity_type`, `entity_id`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ACTIVITY LOGS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_activity_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `workspace_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `activity_type` VARCHAR(50) NOT NULL,
    `description` TEXT NOT NULL,
    `metadata` JSON DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_workspace` (`workspace_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_type` (`activity_type`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_activity_workspace` FOREIGN KEY (`workspace_id`) REFERENCES `eb_workspaces` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `eb_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- NOTIFICATIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_notifications` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `workspace_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `type` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `action_url` VARCHAR(255) DEFAULT NULL,
    `read` TINYINT(1) DEFAULT 0,
    `read_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_workspace` (`workspace_id`),
    KEY `idx_read` (`read`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `eb_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_notification_workspace` FOREIGN KEY (`workspace_id`) REFERENCES `eb_workspaces` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- EXCHANGE RATES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_exchange_rates` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `base_currency` VARCHAR(3) NOT NULL,
    `target_currency` VARCHAR(3) NOT NULL,
    `rate` DECIMAL(20,10) NOT NULL,
    `date` DATE NOT NULL,
    `source` VARCHAR(50) DEFAULT 'api',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_currency_date` (`base_currency`, `target_currency`, `date`),
    KEY `idx_base` (`base_currency`),
    KEY `idx_target` (`target_currency`),
    KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- WEBHOOKS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_webhooks` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `workspace_id` INT UNSIGNED NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    `events` JSON NOT NULL,
    `secret` VARCHAR(64) NOT NULL,
    `active` TINYINT(1) DEFAULT 1,
    `last_triggered_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_workspace` (`workspace_id`),
    KEY `idx_active` (`active`),
    CONSTRAINT `fk_webhook_workspace` FOREIGN KEY (`workspace_id`) REFERENCES `eb_workspaces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- QUEUE JOBS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_queue_jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `queue` VARCHAR(50) NOT NULL DEFAULT 'default',
    `job_class` VARCHAR(255) NOT NULL,
    `payload` JSON NOT NULL,
    `attempts` INT DEFAULT 0,
    `reserved_at` DATETIME DEFAULT NULL,
    `available_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `failed_at` DATETIME DEFAULT NULL,
    `error_message` TEXT DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_queue` (`queue`),
    KEY `idx_available` (`available_at`),
    KEY `idx_reserved` (`reserved_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- API TOKENS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_api_tokens` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `workspace_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(100) NOT NULL,
    `token_hash` VARCHAR(64) NOT NULL,
    `abilities` JSON DEFAULT NULL,
    `last_used_at` DATETIME DEFAULT NULL,
    `expires_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_token_hash` (`token_hash`),
    KEY `idx_user` (`user_id`),
    KEY `idx_workspace` (`workspace_id`),
    CONSTRAINT `fk_token_user` FOREIGN KEY (`user_id`) REFERENCES `eb_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_token_workspace` FOREIGN KEY (`workspace_id`) REFERENCES `eb_workspaces` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PEPPOL DOCUMENTS LOG
-- ============================================
CREATE TABLE IF NOT EXISTS `eb_peppol_documents` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `workspace_id` INT UNSIGNED NOT NULL,
    `document_type` ENUM('invoice', 'credit_note', 'order', 'catalogue') NOT NULL,
    `document_id` INT UNSIGNED NOT NULL,
    `message_id` VARCHAR(100) NOT NULL,
    `sender_id` VARCHAR(100) NOT NULL,
    `receiver_id` VARCHAR(100) NOT NULL,
    `direction` ENUM('sent', 'received') NOT NULL,
    `status` VARCHAR(50) DEFAULT 'pending',
    `response` JSON DEFAULT NULL,
    `error_message` TEXT DEFAULT NULL,
    `xml_content` LONGTEXT DEFAULT NULL,
    `sent_at` DATETIME DEFAULT NULL,
    `received_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_workspace` (`workspace_id`),
    KEY `idx_document` (`document_type`, `document_id`),
    KEY `idx_message_id` (`message_id`),
    KEY `idx_status` (`status`),
    CONSTRAINT `fk_peppol_workspace` FOREIGN KEY (`workspace_id`) REFERENCES `eb_workspaces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT DEFAULT DATA
-- ============================================

-- Default expense categories
INSERT INTO `eb_expense_categories` (`name`, `color`, `is_system`) VALUES
('Office Supplies', '#3b82f6', 1),
('Travel & Transport', '#10b981', 1),
('Meals & Entertainment', '#f59e0b', 1),
('Professional Services', '#8b5cf6', 1),
('Software & Subscriptions', '#ec4899', 1),
('Marketing & Advertising', '#ef4444', 1),
('Utilities', '#6366f1', 1),
('Rent & Lease', '#14b8a6', 1),
('Insurance', '#f97316', 1),
('Bank Fees', '#6b7280', 1),
('Other', '#9ca3af', 1);

COMMIT;

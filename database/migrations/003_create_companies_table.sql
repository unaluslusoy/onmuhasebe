-- ============================================
-- Migration: 003_create_companies_table
-- Description: Create companies table
-- Created: 2025-01-02
-- ============================================

CREATE TABLE IF NOT EXISTS companies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    trade_name VARCHAR(255),
    tax_office VARCHAR(255),
    tax_number VARCHAR(20),
    vkn VARCHAR(10),
    tckn VARCHAR(11),
    mersis_no VARCHAR(16),
    company_type ENUM('individual', 'limited', 'corporation', 'other') NOT NULL DEFAULT 'limited',
    address TEXT,
    district VARCHAR(100),
    city VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Türkiye',
    postal_code VARCHAR(10),
    phone VARCHAR(20),
    fax VARCHAR(20),
    email VARCHAR(255),
    website VARCHAR(255),
    logo_path VARCHAR(500),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_owner_id (owner_id),
    INDEX idx_tax_number (tax_number),
    INDEX idx_vkn (vkn),
    INDEX idx_is_active (is_active),
    INDEX idx_deleted_at (deleted_at),
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

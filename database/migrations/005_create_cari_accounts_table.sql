-- ============================================
-- Migration: 005_create_cari_accounts_table
-- Description: Create cari (customer/supplier) accounts table
-- Created: 2025-10-03
-- ============================================

CREATE TABLE IF NOT EXISTS cari_accounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    
    -- Tip ve Durum
    account_type ENUM('customer', 'supplier', 'both') NOT NULL DEFAULT 'customer',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    
    -- Temel Bilgiler
    code VARCHAR(50) NOT NULL,  -- Cari kodu (otomatik veya manuel)
    title VARCHAR(255) NOT NULL, -- Ünvan
    name VARCHAR(255) NOT NULL,  -- Ad
    surname VARCHAR(255),        -- Soyad (bireysel için)
    
    -- Vergi Bilgileri
    tax_office VARCHAR(255),
    tax_number VARCHAR(20),
    vkn VARCHAR(10),    -- Vergi Kimlik No (şirket için)
    tckn VARCHAR(11),   -- TC Kimlik No (bireysel için)
    
    -- İletişim Bilgileri
    email VARCHAR(255),
    phone VARCHAR(20),
    mobile VARCHAR(20),
    fax VARCHAR(20),
    website VARCHAR(255),
    
    -- Adres Bilgileri
    billing_address TEXT,
    billing_district VARCHAR(100),
    billing_city VARCHAR(100),
    billing_country VARCHAR(100) DEFAULT 'Türkiye',
    billing_postal_code VARCHAR(10),
    
    shipping_address TEXT,
    shipping_district VARCHAR(100),
    shipping_city VARCHAR(100),
    shipping_country VARCHAR(100) DEFAULT 'Türkiye',
    shipping_postal_code VARCHAR(10),
    
    -- Mali Bilgiler
    currency VARCHAR(3) DEFAULT 'TRY',
    payment_term INT DEFAULT 0,  -- Vade günü (0 = peşin, 30 = 30 gün vade)
    credit_limit DECIMAL(15,2) DEFAULT 0.00,
    current_balance DECIMAL(15,2) DEFAULT 0.00, -- Güncel bakiye (+ alacak, - borç)
    
    -- Banka Bilgileri
    bank_name VARCHAR(255),
    bank_branch VARCHAR(255),
    bank_account_no VARCHAR(50),
    iban VARCHAR(32),
    
    -- Notlar ve Etiketler
    notes TEXT,
    tags VARCHAR(500), -- Virgülle ayrılmış etiketler
    
    -- Risk ve Grup Bilgileri
    risk_group ENUM('low', 'medium', 'high') DEFAULT 'low',
    customer_group VARCHAR(100),
    
    -- E-Fatura Bilgileri
    efatura_enabled BOOLEAN DEFAULT FALSE,
    efatura_alias VARCHAR(255),  -- e-Fatura posta kutusu alias
    
    -- Timestamp
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    
    -- Indexes
    INDEX idx_company_id (company_id),
    INDEX idx_code (code),
    INDEX idx_account_type (account_type),
    INDEX idx_is_active (is_active),
    INDEX idx_tax_number (tax_number),
    INDEX idx_vkn (vkn),
    INDEX idx_tckn (tckn),
    INDEX idx_email (email),
    INDEX idx_deleted_at (deleted_at),
    UNIQUE KEY unique_company_code (company_id, code),
    
    -- Foreign Keys
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Fulltext Index for Search
-- ============================================
ALTER TABLE cari_accounts ADD FULLTEXT INDEX ft_search (title, name, surname, email);

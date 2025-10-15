-- ============================================
-- Migration: 006_create_cari_transactions_table
-- Description: Create cari account transactions table (hareketler)
-- Created: 2025-10-03
-- ============================================

CREATE TABLE IF NOT EXISTS cari_transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    cari_account_id INT UNSIGNED NOT NULL,
    
    -- İşlem Bilgileri
    transaction_type ENUM('invoice_sale', 'invoice_purchase', 'payment_received', 'payment_made', 'opening_balance', 'adjustment', 'other') NOT NULL,
    transaction_date DATE NOT NULL,
    due_date DATE,
    
    -- Referans Bilgileri
    reference_type VARCHAR(50), -- invoice, payment, receipt, etc.
    reference_id INT UNSIGNED,  -- İlgili kaydın ID'si
    document_no VARCHAR(100),   -- Belge no (fatura no, makbuz no)
    
    -- Tutar Bilgileri
    currency VARCHAR(3) DEFAULT 'TRY',
    amount DECIMAL(15,2) NOT NULL,
    exchange_rate DECIMAL(10,4) DEFAULT 1.0000,
    amount_try DECIMAL(15,2) NOT NULL,  -- TRY karşılığı
    
    -- Borç/Alacak
    debit DECIMAL(15,2) DEFAULT 0.00,   -- Borç (+)
    credit DECIMAL(15,2) DEFAULT 0.00,  -- Alacak (-)
    balance DECIMAL(15,2) DEFAULT 0.00, -- İşlem sonrası bakiye
    
    -- Açıklama
    description TEXT,
    notes TEXT,
    
    -- Durum
    is_reconciled BOOLEAN DEFAULT FALSE, -- Mutabakat yapıldı mı?
    reconciled_at TIMESTAMP NULL,
    reconciled_by INT UNSIGNED,
    
    -- Timestamp
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    
    -- Indexes
    INDEX idx_company_id (company_id),
    INDEX idx_cari_account_id (cari_account_id),
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_due_date (due_date),
    INDEX idx_reference (reference_type, reference_id),
    INDEX idx_document_no (document_no),
    INDEX idx_deleted_at (deleted_at),
    
    -- Foreign Keys
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (cari_account_id) REFERENCES cari_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Trigger: Update cari_accounts.current_balance on INSERT
-- ============================================
DELIMITER $$

CREATE TRIGGER update_cari_balance_after_insert
AFTER INSERT ON cari_transactions
FOR EACH ROW
BEGIN
    -- Bakiye güncelle: debit - credit
    UPDATE cari_accounts 
    SET current_balance = current_balance + (NEW.debit - NEW.credit),
        updated_at = CURRENT_TIMESTAMP
    WHERE id = NEW.cari_account_id;
END$$

DELIMITER ;

-- ============================================
-- Trigger: Update cari_accounts.current_balance on UPDATE
-- ============================================
DELIMITER $$

CREATE TRIGGER update_cari_balance_after_update
AFTER UPDATE ON cari_transactions
FOR EACH ROW
BEGIN
    -- Eski işlemi geri al
    UPDATE cari_accounts 
    SET current_balance = current_balance - (OLD.debit - OLD.credit)
    WHERE id = OLD.cari_account_id;
    
    -- Yeni işlemi ekle
    UPDATE cari_accounts 
    SET current_balance = current_balance + (NEW.debit - NEW.credit),
        updated_at = CURRENT_TIMESTAMP
    WHERE id = NEW.cari_account_id;
END$$

DELIMITER ;

-- ============================================
-- Trigger: Update cari_accounts.current_balance on DELETE
-- ============================================
DELIMITER $$

CREATE TRIGGER update_cari_balance_after_delete
AFTER DELETE ON cari_transactions
FOR EACH ROW
BEGIN
    -- İşlemi geri al
    UPDATE cari_accounts 
    SET current_balance = current_balance - (OLD.debit - OLD.credit),
        updated_at = CURRENT_TIMESTAMP
    WHERE id = OLD.cari_account_id;
END$$

DELIMITER ;

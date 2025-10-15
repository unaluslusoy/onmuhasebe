-- ============================================
-- Sprint 1.6 - Quotation Management Module
-- Migration: 009_create_quotation_tables.sql
-- Created: October 3, 2025
-- ============================================
-- Purpose: Complete quotation/offer management with workflow
-- Features: Multi-line items, tax calculation, discounts, status workflow, PDF export
-- ============================================

USE onmuhasebe;

-- ============================================
-- Table: quotations
-- Main quotation/offer table with workflow
-- ============================================
CREATE TABLE IF NOT EXISTS quotations (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id INT UNSIGNED NOT NULL,
    quotation_number VARCHAR(64) NOT NULL,
    quotation_date DATE NOT NULL,
    valid_until DATE NOT NULL,
    
    -- Customer information
    cari_id INT UNSIGNED NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NULL,
    customer_phone VARCHAR(20) NULL,
    customer_tax_number VARCHAR(20) NULL,
    customer_tax_office VARCHAR(100) NULL,
    customer_address TEXT NULL,
    customer_city VARCHAR(100) NULL,
    customer_district VARCHAR(100) NULL,
    customer_postal_code VARCHAR(10) NULL,
    
    -- Status workflow: draft -> sent -> accepted/rejected/expired
    status ENUM('draft', 'sent', 'accepted', 'rejected', 'expired', 'converted') NOT NULL DEFAULT 'draft',
    
    -- Financial fields (auto-calculated by triggers)
    subtotal DECIMAL(18,2) NOT NULL DEFAULT 0,
    discount_type ENUM('percentage', 'fixed') NULL,
    discount_value DECIMAL(18,2) NULL DEFAULT 0,
    discount_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    total DECIMAL(18,2) NOT NULL DEFAULT 0,
    
    -- Currency
    currency VARCHAR(3) NOT NULL DEFAULT 'TRY',
    exchange_rate DECIMAL(18,6) NOT NULL DEFAULT 1,
    
    -- Notes and terms
    notes TEXT NULL,
    terms_conditions TEXT NULL,
    internal_notes TEXT NULL,
    
    -- Workflow tracking
    sent_at DATETIME NULL,
    sent_by INT UNSIGNED NULL,
    accepted_at DATETIME NULL,
    rejected_at DATETIME NULL,
    rejection_reason TEXT NULL,
    converted_to_invoice_id INT UNSIGNED NULL,
    converted_at DATETIME NULL,
    
    -- User tracking
    created_by INT UNSIGNED NOT NULL,
    updated_by INT UNSIGNED NULL,
    
    -- Timestamps
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Indexes
    INDEX idx_company (company_id),
    INDEX idx_cari (cari_id),
    INDEX idx_status (status),
    INDEX idx_quotation_date (quotation_date),
    INDEX idx_valid_until (valid_until),
    INDEX idx_quotation_number (quotation_number),
    INDEX idx_created_by (created_by),
    INDEX idx_deleted (deleted_at),
    UNIQUE KEY uq_company_quotation_number (company_id, quotation_number, deleted_at),
    
    -- Foreign keys
    CONSTRAINT fk_quotation_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_quotation_cari FOREIGN KEY (cari_id) 
        REFERENCES cari_accounts(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_quotation_creator FOREIGN KEY (created_by) 
        REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_quotation_updater FOREIGN KEY (updated_by) 
        REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_quotation_sender FOREIGN KEY (sent_by) 
        REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL
        
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: quotation_items
-- Line items for quotations
-- ============================================
CREATE TABLE IF NOT EXISTS quotation_items (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    quotation_id INT UNSIGNED NOT NULL,
    
    -- Product reference (optional - can be free text item)
    product_id INT UNSIGNED NULL,
    variant_id INT UNSIGNED NULL,
    
    -- Item details
    item_order INT NOT NULL DEFAULT 0,
    item_type ENUM('product', 'service', 'text', 'subtotal') NOT NULL DEFAULT 'product',
    item_code VARCHAR(64) NULL,
    item_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    
    -- Quantities and pricing
    quantity DECIMAL(18,4) NOT NULL DEFAULT 1,
    unit VARCHAR(32) NOT NULL DEFAULT 'Adet',
    unit_price DECIMAL(18,2) NOT NULL DEFAULT 0,
    
    -- Discounts (item level)
    discount_type ENUM('percentage', 'fixed') NULL,
    discount_value DECIMAL(18,2) NULL DEFAULT 0,
    discount_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    
    -- Tax
    tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    
    -- Totals (auto-calculated)
    subtotal DECIMAL(18,2) NOT NULL DEFAULT 0,
    total DECIMAL(18,2) NOT NULL DEFAULT 0,
    
    -- Timestamps
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Indexes
    INDEX idx_quotation (quotation_id),
    INDEX idx_product (product_id),
    INDEX idx_variant (variant_id),
    INDEX idx_order (item_order),
    INDEX idx_deleted (deleted_at),
    
    -- Foreign keys
    CONSTRAINT fk_qitem_quotation FOREIGN KEY (quotation_id) 
        REFERENCES quotations(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_qitem_product FOREIGN KEY (product_id) 
        REFERENCES products(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_qitem_variant FOREIGN KEY (variant_id) 
        REFERENCES product_variants(id) ON UPDATE CASCADE ON DELETE SET NULL
        
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Trigger: Auto-calculate quotation item totals
-- ============================================
DELIMITER $$

CREATE TRIGGER trg_quotation_item_calculate_before_insert
BEFORE INSERT ON quotation_items
FOR EACH ROW
BEGIN
    -- Calculate item subtotal (quantity * unit_price)
    SET NEW.subtotal = NEW.quantity * NEW.unit_price;
    
    -- Calculate discount amount
    IF NEW.discount_type = 'percentage' THEN
        SET NEW.discount_amount = NEW.subtotal * (NEW.discount_value / 100);
    ELSEIF NEW.discount_type = 'fixed' THEN
        SET NEW.discount_amount = NEW.discount_value;
    ELSE
        SET NEW.discount_amount = 0;
    END IF;
    
    -- Calculate tax amount (after discount)
    SET NEW.tax_amount = (NEW.subtotal - NEW.discount_amount) * (NEW.tax_rate / 100);
    
    -- Calculate total
    SET NEW.total = NEW.subtotal - NEW.discount_amount + NEW.tax_amount;
END$$

CREATE TRIGGER trg_quotation_item_calculate_before_update
BEFORE UPDATE ON quotation_items
FOR EACH ROW
BEGIN
    -- Calculate item subtotal (quantity * unit_price)
    SET NEW.subtotal = NEW.quantity * NEW.unit_price;
    
    -- Calculate discount amount
    IF NEW.discount_type = 'percentage' THEN
        SET NEW.discount_amount = NEW.subtotal * (NEW.discount_value / 100);
    ELSEIF NEW.discount_type = 'fixed' THEN
        SET NEW.discount_amount = NEW.discount_value;
    ELSE
        SET NEW.discount_amount = 0;
    END IF;
    
    -- Calculate tax amount (after discount)
    SET NEW.tax_amount = (NEW.subtotal - NEW.discount_amount) * (NEW.tax_rate / 100);
    
    -- Calculate total
    SET NEW.total = NEW.subtotal - NEW.discount_amount + NEW.tax_amount;
END$$

-- ============================================
-- Trigger: Update quotation totals when items change
-- ============================================
CREATE TRIGGER trg_update_quotation_totals_after_item_insert
AFTER INSERT ON quotation_items
FOR EACH ROW
BEGIN
    DECLARE v_subtotal DECIMAL(18,2);
    DECLARE v_tax_amount DECIMAL(18,2);
    DECLARE v_discount_amount DECIMAL(18,2);
    DECLARE v_total DECIMAL(18,2);
    
    -- Calculate totals from all items
    SELECT 
        COALESCE(SUM(subtotal), 0),
        COALESCE(SUM(tax_amount), 0)
    INTO v_subtotal, v_tax_amount
    FROM quotation_items
    WHERE quotation_id = NEW.quotation_id AND deleted_at IS NULL;
    
    -- Get quotation-level discount
    SELECT 
        CASE 
            WHEN discount_type = 'percentage' THEN v_subtotal * (discount_value / 100)
            WHEN discount_type = 'fixed' THEN discount_value
            ELSE 0
        END
    INTO v_discount_amount
    FROM quotations
    WHERE id = NEW.quotation_id;
    
    -- Calculate final total
    SET v_total = v_subtotal - v_discount_amount + v_tax_amount;
    
    -- Update quotation
    UPDATE quotations
    SET 
        subtotal = v_subtotal,
        discount_amount = v_discount_amount,
        tax_amount = v_tax_amount,
        total = v_total
    WHERE id = NEW.quotation_id;
END$$

CREATE TRIGGER trg_update_quotation_totals_after_item_update
AFTER UPDATE ON quotation_items
FOR EACH ROW
BEGIN
    DECLARE v_subtotal DECIMAL(18,2);
    DECLARE v_tax_amount DECIMAL(18,2);
    DECLARE v_discount_amount DECIMAL(18,2);
    DECLARE v_total DECIMAL(18,2);
    
    -- Calculate totals from all items
    SELECT 
        COALESCE(SUM(subtotal), 0),
        COALESCE(SUM(tax_amount), 0)
    INTO v_subtotal, v_tax_amount
    FROM quotation_items
    WHERE quotation_id = NEW.quotation_id AND deleted_at IS NULL;
    
    -- Get quotation-level discount
    SELECT 
        CASE 
            WHEN discount_type = 'percentage' THEN v_subtotal * (discount_value / 100)
            WHEN discount_type = 'fixed' THEN discount_value
            ELSE 0
        END
    INTO v_discount_amount
    FROM quotations
    WHERE id = NEW.quotation_id;
    
    -- Calculate final total
    SET v_total = v_subtotal - v_discount_amount + v_tax_amount;
    
    -- Update quotation
    UPDATE quotations
    SET 
        subtotal = v_subtotal,
        discount_amount = v_discount_amount,
        tax_amount = v_tax_amount,
        total = v_total
    WHERE id = NEW.quotation_id;
END$$

CREATE TRIGGER trg_update_quotation_totals_after_item_delete
AFTER DELETE ON quotation_items
FOR EACH ROW
BEGIN
    DECLARE v_subtotal DECIMAL(18,2);
    DECLARE v_tax_amount DECIMAL(18,2);
    DECLARE v_discount_amount DECIMAL(18,2);
    DECLARE v_total DECIMAL(18,2);
    
    -- Calculate totals from remaining items
    SELECT 
        COALESCE(SUM(subtotal), 0),
        COALESCE(SUM(tax_amount), 0)
    INTO v_subtotal, v_tax_amount
    FROM quotation_items
    WHERE quotation_id = OLD.quotation_id AND deleted_at IS NULL;
    
    -- Get quotation-level discount
    SELECT 
        CASE 
            WHEN discount_type = 'percentage' THEN v_subtotal * (discount_value / 100)
            WHEN discount_type = 'fixed' THEN discount_value
            ELSE 0
        END
    INTO v_discount_amount
    FROM quotations
    WHERE id = OLD.quotation_id;
    
    -- Calculate final total
    SET v_total = v_subtotal - v_discount_amount + v_tax_amount;
    
    -- Update quotation
    UPDATE quotations
    SET 
        subtotal = v_subtotal,
        discount_amount = v_discount_amount,
        tax_amount = v_tax_amount,
        total = v_total
    WHERE id = OLD.quotation_id;
END$$

DELIMITER ;

-- ============================================
-- View: Quotation summary with customer info
-- ============================================
CREATE OR REPLACE VIEW view_quotation_summary AS
SELECT 
    q.id,
    q.company_id,
    q.quotation_number,
    q.quotation_date,
    q.valid_until,
    q.status,
    q.customer_name,
    q.customer_email,
    q.customer_phone,
    q.subtotal,
    q.discount_amount,
    q.tax_amount,
    q.total,
    q.currency,
    CASE 
        WHEN q.valid_until < CURDATE() AND q.status = 'sent' THEN 'expired'
        ELSE q.status
    END as effective_status,
    DATEDIFF(q.valid_until, CURDATE()) as days_until_expiry,
    c.code as customer_code,
    c.title as cari_name,
    u.full_name as created_by_name,
    COUNT(qi.id) as item_count,
    q.created_at,
    q.updated_at
FROM quotations q
LEFT JOIN cari_accounts c ON q.cari_id = c.id
LEFT JOIN users u ON q.created_by = u.id
LEFT JOIN quotation_items qi ON q.id = qi.quotation_id AND qi.deleted_at IS NULL
WHERE q.deleted_at IS NULL
GROUP BY q.id;

-- ============================================
-- Success message
-- ============================================
SELECT '✓ Quotation tables, triggers, and views created successfully!' as status;

-- ============================================
-- Invoice Management Tables
-- Sprint 1.7 - Fatura Yönetimi
-- ============================================

-- Drop existing tables if any
DROP TABLE IF EXISTS invoice_logs;
DROP TABLE IF EXISTS invoice_payments;
DROP TABLE IF EXISTS invoice_items;
DROP TABLE IF EXISTS invoices;

DROP VIEW IF EXISTS view_invoice_summary;
DROP VIEW IF EXISTS view_invoice_aging;

-- Drop existing triggers
DROP TRIGGER IF EXISTS trg_invoice_item_calculate_before_insert;
DROP TRIGGER IF EXISTS trg_invoice_item_calculate_before_update;
DROP TRIGGER IF EXISTS trg_update_invoice_totals_after_item_insert;
DROP TRIGGER IF EXISTS trg_update_invoice_totals_after_item_update;
DROP TRIGGER IF EXISTS trg_update_invoice_totals_after_item_delete;
DROP TRIGGER IF EXISTS trg_update_invoice_payment_status_after_payment;
DROP TRIGGER IF EXISTS trg_update_stock_after_invoice_item_insert;
DROP TRIGGER IF EXISTS trg_update_stock_after_invoice_item_delete;

-- ============================================
-- 1. Invoices Table (Ana Fatura Tablosu)
-- ============================================
CREATE TABLE invoices (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT(10) UNSIGNED NOT NULL,
    cari_id INT(10) UNSIGNED NULL,
    warehouse_id INT(10) UNSIGNED NULL,
    
    -- Invoice identification
    invoice_number VARCHAR(50) NOT NULL,
    invoice_uuid VARCHAR(36) NULL COMMENT 'e-Invoice UUID',
    invoice_type ENUM('sales', 'purchase', 'sales_return', 'purchase_return') NOT NULL,
    invoice_category ENUM('normal', 'proforma', 'efatura', 'earsiv', 'export') DEFAULT 'normal',
    
    -- Dates
    invoice_date DATE NOT NULL,
    due_date DATE NULL COMMENT 'Payment due date',
    
    -- Customer/Supplier info (snapshot for history)
    customer_name VARCHAR(200) NULL,
    customer_email VARCHAR(100) NULL,
    customer_phone VARCHAR(20) NULL,
    customer_tax_number VARCHAR(20) NULL,
    customer_tax_office VARCHAR(100) NULL,
    customer_address TEXT NULL,
    customer_city VARCHAR(100) NULL,
    customer_district VARCHAR(100) NULL,
    customer_postal_code VARCHAR(10) NULL,
    
    -- Financial amounts (will be calculated by triggers)
    subtotal DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Sum of item subtotals',
    discount_type ENUM('percentage', 'fixed') NULL,
    discount_value DECIMAL(18,2) DEFAULT 0.00,
    discount_amount DECIMAL(18,2) DEFAULT 0.00 COMMENT 'Invoice-level discount',
    tax_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Total tax (KDV)',
    withholding_amount DECIMAL(18,2) DEFAULT 0.00 COMMENT 'Stopaj/Tevkifat',
    total DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Grand total',
    
    -- Currency
    currency ENUM('TRY', 'USD', 'EUR', 'GBP') DEFAULT 'TRY',
    exchange_rate DECIMAL(18,6) DEFAULT 1.000000,
    
    -- Payment tracking
    payment_status ENUM('unpaid', 'partial', 'paid', 'overdue', 'cancelled') DEFAULT 'unpaid',
    paid_amount DECIMAL(18,2) DEFAULT 0.00,
    remaining_amount DECIMAL(18,2) GENERATED ALWAYS AS (total - paid_amount) STORED,
    
    -- e-Invoice integration
    efatura_status ENUM('pending', 'sent', 'delivered', 'accepted', 'rejected', 'timeout') NULL,
    efatura_sent_at DATETIME NULL,
    efatura_response_at DATETIME NULL,
    efatura_response_code VARCHAR(10) NULL,
    efatura_response_desc TEXT NULL,
    efatura_portal_id VARCHAR(100) NULL COMMENT 'GIB integrator ID',
    
    -- Waybill (İrsaliye)
    waybill_number VARCHAR(50) NULL,
    waybill_date DATE NULL,
    
    -- Order reference
    order_number VARCHAR(50) NULL,
    quotation_id INT(10) UNSIGNED NULL COMMENT 'Converted from quotation',
    
    -- Notes
    notes TEXT NULL COMMENT 'Customer will see',
    internal_notes TEXT NULL COMMENT 'Internal use only',
    terms_conditions TEXT NULL,
    
    -- Files
    pdf_path VARCHAR(255) NULL,
    xml_path VARCHAR(255) NULL COMMENT 'e-Invoice XML path',
    
    -- Recurring invoice
    is_recurring BOOLEAN DEFAULT FALSE,
    recurring_frequency ENUM('daily', 'weekly', 'monthly', 'yearly') NULL,
    recurring_interval INT DEFAULT 1 COMMENT 'Every X frequency',
    recurring_start_date DATE NULL,
    recurring_end_date DATE NULL,
    recurring_last_generated DATE NULL,
    parent_invoice_id INT(10) UNSIGNED NULL COMMENT 'Original recurring invoice',
    
    -- Cancellation/Return
    is_cancelled BOOLEAN DEFAULT FALSE,
    cancelled_at DATETIME NULL,
    cancelled_by INT(10) UNSIGNED NULL,
    cancellation_reason TEXT NULL,
    original_invoice_id INT(10) UNSIGNED NULL COMMENT 'For return invoices',
    
    -- Approval
    is_approved BOOLEAN DEFAULT FALSE,
    approved_by INT(10) UNSIGNED NULL,
    approved_at DATETIME NULL,
    
    -- Status flags
    is_draft BOOLEAN DEFAULT TRUE,
    is_locked BOOLEAN DEFAULT FALSE COMMENT 'Accounting lock',
    
    -- Audit
    created_by INT(10) UNSIGNED NOT NULL,
    updated_by INT(10) UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Foreign keys
    FOREIGN KEY (company_id) REFERENCES companies(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (cari_id) REFERENCES cari_accounts(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON UPDATE CASCADE ON DELETE SET NULL,
    FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON UPDATE CASCADE ON DELETE SET NULL,
    FOREIGN KEY (parent_invoice_id) REFERENCES invoices(id) ON UPDATE CASCADE ON DELETE SET NULL,
    FOREIGN KEY (original_invoice_id) REFERENCES invoices(id) ON UPDATE CASCADE ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
    FOREIGN KEY (cancelled_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_company_id (company_id),
    INDEX idx_cari_id (cari_id),
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_invoice_uuid (invoice_uuid),
    INDEX idx_invoice_date (invoice_date),
    INDEX idx_due_date (due_date),
    INDEX idx_payment_status (payment_status),
    INDEX idx_invoice_type (invoice_type),
    INDEX idx_efatura_status (efatura_status),
    INDEX idx_is_recurring (is_recurring),
    INDEX idx_created_by (created_by),
    INDEX idx_deleted_at (deleted_at),
    
    UNIQUE KEY uq_company_invoice_number (company_id, invoice_number, deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. Invoice Items Table (Fatura Kalemleri)
-- ============================================
CREATE TABLE invoice_items (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT(10) UNSIGNED NOT NULL,
    product_id INT(10) UNSIGNED NULL,
    variant_id INT(10) UNSIGNED NULL,
    
    -- Line ordering
    item_order INT DEFAULT 0,
    
    -- Item type
    item_type ENUM('product', 'service', 'text', 'subtotal') DEFAULT 'product',
    
    -- Product information
    item_code VARCHAR(50) NULL,
    item_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    barcode VARCHAR(100) NULL,
    
    -- Quantity and unit
    quantity DECIMAL(18,4) NOT NULL DEFAULT 1.0000,
    unit VARCHAR(20) DEFAULT 'Adet',
    
    -- Pricing
    unit_price DECIMAL(18,2) NOT NULL,
    discount_type ENUM('percentage', 'fixed') NULL,
    discount_value DECIMAL(18,2) DEFAULT 0.00,
    discount_amount DECIMAL(18,2) DEFAULT 0.00,
    
    -- Tax (KDV)
    tax_rate DECIMAL(5,2) DEFAULT 20.00,
    tax_amount DECIMAL(18,2) DEFAULT 0.00,
    tax_exception_code VARCHAR(10) NULL COMMENT 'Tax exemption code',
    tax_exception_reason VARCHAR(255) NULL,
    
    -- Withholding (Tevkifat/Stopaj)
    withholding_rate DECIMAL(5,2) DEFAULT 0.00,
    withholding_amount DECIMAL(18,2) DEFAULT 0.00,
    
    -- Calculated totals (by triggers)
    subtotal DECIMAL(18,2) DEFAULT 0.00 COMMENT 'quantity * unit_price',
    total DECIMAL(18,2) DEFAULT 0.00 COMMENT 'subtotal - discount + tax - withholding',
    
    -- Stock tracking
    stock_movement_id INT(10) UNSIGNED NULL COMMENT 'Related stock movement',
    affects_stock BOOLEAN DEFAULT TRUE,
    
    -- e-Invoice specific
    gtip_code VARCHAR(20) NULL COMMENT 'Customs tariff code',
    
    -- Notes
    notes TEXT NULL,
    
    -- Audit
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Foreign keys
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE SET NULL,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON UPDATE CASCADE ON DELETE SET NULL,
    FOREIGN KEY (stock_movement_id) REFERENCES stock_movements(id) ON UPDATE CASCADE ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_invoice_id (invoice_id),
    INDEX idx_product_id (product_id),
    INDEX idx_variant_id (variant_id),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. Invoice Payments Table (Fatura Ödemeleri)
-- ============================================
CREATE TABLE invoice_payments (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT(10) UNSIGNED NOT NULL,
    company_id INT(10) UNSIGNED NOT NULL,
    
    -- Payment details
    payment_date DATE NOT NULL,
    amount DECIMAL(18,2) NOT NULL,
    
    -- Payment method
    payment_method ENUM('cash', 'bank_transfer', 'credit_card', 'check', 'promissory_note', 'other') NOT NULL,
    
    -- Bank details (if applicable)
    bank_account_id INT(10) UNSIGNED NULL,
    transaction_reference VARCHAR(100) NULL,
    
    -- Check/Promissory note details
    check_number VARCHAR(50) NULL,
    check_date DATE NULL,
    check_bank VARCHAR(100) NULL,
    
    -- Notes
    notes TEXT NULL,
    
    -- Receipt
    receipt_number VARCHAR(50) NULL,
    receipt_path VARCHAR(255) NULL,
    
    -- Audit
    created_by INT(10) UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Foreign keys
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    
    -- Indexes
    INDEX idx_invoice_id (invoice_id),
    INDEX idx_company_id (company_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. Invoice Logs Table (Fatura İşlem Logları)
-- ============================================
CREATE TABLE invoice_logs (
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT(10) UNSIGNED NOT NULL,
    user_id INT(10) UNSIGNED NOT NULL,
    
    -- Action type
    action_type ENUM('created', 'updated', 'sent', 'approved', 'paid', 'cancelled', 'locked', 'unlocked', 'emailed', 'efatura_sent', 'efatura_accepted', 'efatura_rejected') NOT NULL,
    
    -- Details
    description TEXT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    
    -- Metadata
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    
    -- Timestamp
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign keys
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    
    -- Indexes
    INDEX idx_invoice_id (invoice_id),
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TRIGGERS
-- ============================================

-- Trigger 1: Calculate invoice item totals before insert
DELIMITER $$
CREATE TRIGGER trg_invoice_item_calculate_before_insert
BEFORE INSERT ON invoice_items
FOR EACH ROW
BEGIN
    -- Calculate subtotal
    SET NEW.subtotal = NEW.quantity * NEW.unit_price;
    
    -- Calculate discount amount
    IF NEW.discount_type = 'percentage' THEN
        SET NEW.discount_amount = NEW.subtotal * (NEW.discount_value / 100);
    ELSEIF NEW.discount_type = 'fixed' THEN
        SET NEW.discount_amount = NEW.discount_value;
    ELSE
        SET NEW.discount_amount = 0;
    END IF;
    
    -- Calculate tax amount
    SET NEW.tax_amount = (NEW.subtotal - NEW.discount_amount) * (NEW.tax_rate / 100);
    
    -- Calculate withholding amount
    SET NEW.withholding_amount = (NEW.subtotal - NEW.discount_amount) * (NEW.withholding_rate / 100);
    
    -- Calculate total
    SET NEW.total = NEW.subtotal - NEW.discount_amount + NEW.tax_amount - NEW.withholding_amount;
END$$
DELIMITER ;

-- Trigger 2: Calculate invoice item totals before update
DELIMITER $$
CREATE TRIGGER trg_invoice_item_calculate_before_update
BEFORE UPDATE ON invoice_items
FOR EACH ROW
BEGIN
    -- Calculate subtotal
    SET NEW.subtotal = NEW.quantity * NEW.unit_price;
    
    -- Calculate discount amount
    IF NEW.discount_type = 'percentage' THEN
        SET NEW.discount_amount = NEW.subtotal * (NEW.discount_value / 100);
    ELSEIF NEW.discount_type = 'fixed' THEN
        SET NEW.discount_amount = NEW.discount_value;
    ELSE
        SET NEW.discount_amount = 0;
    END IF;
    
    -- Calculate tax amount
    SET NEW.tax_amount = (NEW.subtotal - NEW.discount_amount) * (NEW.tax_rate / 100);
    
    -- Calculate withholding amount
    SET NEW.withholding_amount = (NEW.subtotal - NEW.discount_amount) * (NEW.withholding_rate / 100);
    
    -- Calculate total
    SET NEW.total = NEW.subtotal - NEW.discount_amount + NEW.tax_amount - NEW.withholding_amount;
END$$
DELIMITER ;

-- Trigger 3: Update invoice totals after item insert
DELIMITER $$
CREATE TRIGGER trg_update_invoice_totals_after_item_insert
AFTER INSERT ON invoice_items
FOR EACH ROW
BEGIN
    DECLARE v_subtotal DECIMAL(18,2);
    DECLARE v_tax_amount DECIMAL(18,2);
    DECLARE v_withholding_amount DECIMAL(18,2);
    DECLARE v_invoice_discount DECIMAL(18,2);
    DECLARE v_total DECIMAL(18,2);
    
    -- Get sum of all items
    SELECT 
        COALESCE(SUM(subtotal), 0),
        COALESCE(SUM(tax_amount), 0),
        COALESCE(SUM(withholding_amount), 0)
    INTO v_subtotal, v_tax_amount, v_withholding_amount
    FROM invoice_items
    WHERE invoice_id = NEW.invoice_id AND deleted_at IS NULL;
    
    -- Get invoice-level discount
    SELECT 
        CASE 
            WHEN discount_type = 'percentage' THEN v_subtotal * (discount_value / 100)
            WHEN discount_type = 'fixed' THEN discount_value
            ELSE 0
        END
    INTO v_invoice_discount
    FROM invoices
    WHERE id = NEW.invoice_id;
    
    -- Calculate total
    SET v_total = v_subtotal - v_invoice_discount + v_tax_amount - v_withholding_amount;
    
    -- Update invoice
    UPDATE invoices
    SET 
        subtotal = v_subtotal,
        discount_amount = v_invoice_discount,
        tax_amount = v_tax_amount,
        withholding_amount = v_withholding_amount,
        total = v_total
    WHERE id = NEW.invoice_id;
END$$
DELIMITER ;

-- Trigger 4: Update invoice totals after item update
DELIMITER $$
CREATE TRIGGER trg_update_invoice_totals_after_item_update
AFTER UPDATE ON invoice_items
FOR EACH ROW
BEGIN
    DECLARE v_subtotal DECIMAL(18,2);
    DECLARE v_tax_amount DECIMAL(18,2);
    DECLARE v_withholding_amount DECIMAL(18,2);
    DECLARE v_invoice_discount DECIMAL(18,2);
    DECLARE v_total DECIMAL(18,2);
    
    -- Get sum of all items
    SELECT 
        COALESCE(SUM(subtotal), 0),
        COALESCE(SUM(tax_amount), 0),
        COALESCE(SUM(withholding_amount), 0)
    INTO v_subtotal, v_tax_amount, v_withholding_amount
    FROM invoice_items
    WHERE invoice_id = NEW.invoice_id AND deleted_at IS NULL;
    
    -- Get invoice-level discount
    SELECT 
        CASE 
            WHEN discount_type = 'percentage' THEN v_subtotal * (discount_value / 100)
            WHEN discount_type = 'fixed' THEN discount_value
            ELSE 0
        END
    INTO v_invoice_discount
    FROM invoices
    WHERE id = NEW.invoice_id;
    
    -- Calculate total
    SET v_total = v_subtotal - v_invoice_discount + v_tax_amount - v_withholding_amount;
    
    -- Update invoice
    UPDATE invoices
    SET 
        subtotal = v_subtotal,
        discount_amount = v_invoice_discount,
        tax_amount = v_tax_amount,
        withholding_amount = v_withholding_amount,
        total = v_total
    WHERE id = NEW.invoice_id;
END$$
DELIMITER ;

-- Trigger 5: Update invoice totals after item delete
DELIMITER $$
CREATE TRIGGER trg_update_invoice_totals_after_item_delete
AFTER UPDATE ON invoice_items
FOR EACH ROW
BEGIN
    DECLARE v_subtotal DECIMAL(18,2);
    DECLARE v_tax_amount DECIMAL(18,2);
    DECLARE v_withholding_amount DECIMAL(18,2);
    DECLARE v_invoice_discount DECIMAL(18,2);
    DECLARE v_total DECIMAL(18,2);
    
    -- Only run if item was soft deleted
    IF NEW.deleted_at IS NOT NULL AND OLD.deleted_at IS NULL THEN
        -- Get sum of all items
        SELECT 
            COALESCE(SUM(subtotal), 0),
            COALESCE(SUM(tax_amount), 0),
            COALESCE(SUM(withholding_amount), 0)
        INTO v_subtotal, v_tax_amount, v_withholding_amount
        FROM invoice_items
        WHERE invoice_id = NEW.invoice_id AND deleted_at IS NULL;
        
        -- Get invoice-level discount
        SELECT 
            CASE 
                WHEN discount_type = 'percentage' THEN v_subtotal * (discount_value / 100)
                WHEN discount_type = 'fixed' THEN discount_value
                ELSE 0
            END
        INTO v_invoice_discount
        FROM invoices
        WHERE id = NEW.invoice_id;
        
        -- Calculate total
        SET v_total = v_subtotal - v_invoice_discount + v_tax_amount - v_withholding_amount;
        
        -- Update invoice
        UPDATE invoices
        SET 
            subtotal = v_subtotal,
            discount_amount = v_invoice_discount,
            tax_amount = v_tax_amount,
            withholding_amount = v_withholding_amount,
            total = v_total
        WHERE id = NEW.invoice_id;
    END IF;
END$$
DELIMITER ;

-- Trigger 6: Update invoice payment status after payment
DELIMITER $$
CREATE TRIGGER trg_update_invoice_payment_status_after_payment
AFTER INSERT ON invoice_payments
FOR EACH ROW
BEGIN
    DECLARE v_total DECIMAL(18,2);
    DECLARE v_paid DECIMAL(18,2);
    DECLARE v_new_status VARCHAR(20);
    
    -- Get invoice total
    SELECT total INTO v_total
    FROM invoices
    WHERE id = NEW.invoice_id;
    
    -- Get total paid amount
    SELECT COALESCE(SUM(amount), 0) INTO v_paid
    FROM invoice_payments
    WHERE invoice_id = NEW.invoice_id AND deleted_at IS NULL;
    
    -- Determine payment status
    IF v_paid >= v_total THEN
        SET v_new_status = 'paid';
    ELSEIF v_paid > 0 THEN
        SET v_new_status = 'partial';
    ELSE
        SET v_new_status = 'unpaid';
    END IF;
    
    -- Update invoice
    UPDATE invoices
    SET 
        paid_amount = v_paid,
        payment_status = v_new_status
    WHERE id = NEW.invoice_id;
END$$
DELIMITER ;

-- ============================================
-- VIEWS
-- ============================================

-- View 1: Invoice Summary
CREATE VIEW view_invoice_summary AS
SELECT 
    i.id,
    i.company_id,
    i.invoice_number,
    i.invoice_type,
    i.invoice_category,
    i.invoice_date,
    i.due_date,
    DATEDIFF(CURDATE(), i.due_date) as days_overdue,
    CASE 
        WHEN i.payment_status = 'paid' THEN 'paid'
        WHEN i.is_cancelled = 1 THEN 'cancelled'
        WHEN i.payment_status = 'unpaid' AND i.due_date < CURDATE() THEN 'overdue'
        WHEN i.payment_status = 'partial' AND i.due_date < CURDATE() THEN 'overdue'
        ELSE i.payment_status
    END as effective_status,
    i.cari_id,
    c.title as cari_name,
    c.code as cari_code,
    i.customer_name,
    i.subtotal,
    i.discount_amount,
    i.tax_amount,
    i.withholding_amount,
    i.total,
    i.paid_amount,
    i.remaining_amount,
    i.payment_status,
    i.currency,
    i.is_draft,
    i.is_approved,
    i.is_locked,
    i.is_cancelled,
    i.efatura_status,
    (SELECT COUNT(*) FROM invoice_items WHERE invoice_id = i.id AND deleted_at IS NULL) as item_count,
    u.full_name as created_by_name,
    i.created_at,
    i.updated_at
FROM invoices i
LEFT JOIN cari_accounts c ON i.cari_id = c.id
LEFT JOIN users u ON i.created_by = u.id
WHERE i.deleted_at IS NULL;

-- View 2: Invoice Aging Report
CREATE VIEW view_invoice_aging AS
SELECT 
    i.id,
    i.company_id,
    i.invoice_number,
    i.invoice_type,
    i.invoice_date,
    i.due_date,
    i.cari_id,
    c.title as cari_name,
    i.total,
    i.paid_amount,
    i.remaining_amount,
    DATEDIFF(CURDATE(), i.due_date) as days_overdue,
    CASE 
        WHEN i.payment_status = 'paid' THEN 'paid'
        WHEN DATEDIFF(CURDATE(), i.due_date) <= 0 THEN 'current'
        WHEN DATEDIFF(CURDATE(), i.due_date) BETWEEN 1 AND 30 THEN '1-30_days'
        WHEN DATEDIFF(CURDATE(), i.due_date) BETWEEN 31 AND 60 THEN '31-60_days'
        WHEN DATEDIFF(CURDATE(), i.due_date) BETWEEN 61 AND 90 THEN '61-90_days'
        ELSE '90+_days'
    END as aging_bucket
FROM invoices i
LEFT JOIN cari_accounts c ON i.cari_id = c.id
WHERE i.deleted_at IS NULL 
  AND i.is_cancelled = FALSE
  AND i.payment_status != 'paid';

-- ============================================
-- Success Message
-- ============================================
SELECT '✓ Invoice tables, triggers, and views created successfully!' as Status;

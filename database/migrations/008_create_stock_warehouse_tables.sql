-- ============================================
-- Migration: Stock & Warehouse Management
-- Version: 008
-- Date: 2025-10-03
-- Description: Warehouses, locations, stock movements, transfers, counts, lot/serial tracking
-- ============================================

USE onmuhasebe;

-- ============================================
-- 1. Warehouses (Depolar)
-- ============================================
CREATE TABLE IF NOT EXISTS warehouses (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id INT UNSIGNED NOT NULL,
    warehouse_code VARCHAR(50) NOT NULL,
    warehouse_name VARCHAR(255) NOT NULL,
    warehouse_type ENUM('main', 'branch', 'virtual', 'transit') DEFAULT 'main',
    
    -- Address Information
    address TEXT NULL,
    city VARCHAR(100) NULL,
    district VARCHAR(100) NULL,
    postal_code VARCHAR(20) NULL,
    country VARCHAR(100) DEFAULT 'Turkey',
    
    -- Contact Information
    phone VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    manager_name VARCHAR(255) NULL,
    
    -- Warehouse Settings
    is_active BOOLEAN DEFAULT TRUE,
    is_default BOOLEAN DEFAULT FALSE,
    allow_negative_stock BOOLEAN DEFAULT FALSE,
    auto_allocate BOOLEAN DEFAULT TRUE,
    
    -- Capacity Information
    total_capacity DECIMAL(18,2) NULL COMMENT 'Total capacity in m3',
    used_capacity DECIMAL(18,2) DEFAULT 0 COMMENT 'Used capacity in m3',
    
    -- Additional Info
    description TEXT NULL,
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Indexes
    UNIQUE KEY uk_warehouse_code (company_id, warehouse_code),
    INDEX idx_company (company_id),
    INDEX idx_active (is_active),
    INDEX idx_type (warehouse_type),
    
    -- Foreign Keys
    CONSTRAINT fk_warehouse_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. Warehouse Locations (Depo Lokasyonları)
-- ============================================
CREATE TABLE IF NOT EXISTS warehouse_locations (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    warehouse_id INT UNSIGNED NOT NULL,
    location_code VARCHAR(50) NOT NULL,
    location_name VARCHAR(255) NOT NULL,
    location_type ENUM('aisle', 'shelf', 'bin', 'floor', 'zone') DEFAULT 'bin',
    
    -- Hierarchical Structure
    parent_location_id INT UNSIGNED NULL,
    location_path VARCHAR(500) NULL COMMENT 'Full path: Aisle-Shelf-Bin',
    level INT DEFAULT 1,
    
    -- Location Details
    capacity DECIMAL(18,2) NULL COMMENT 'Capacity in m3',
    used_capacity DECIMAL(18,2) DEFAULT 0,
    max_weight DECIMAL(18,2) NULL COMMENT 'Max weight in kg',
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    is_pickable BOOLEAN DEFAULT TRUE,
    is_receivable BOOLEAN DEFAULT TRUE,
    
    -- Additional Info
    barcode VARCHAR(128) NULL,
    description TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Indexes
    UNIQUE KEY uk_location_code (warehouse_id, location_code),
    INDEX idx_warehouse (warehouse_id),
    INDEX idx_parent (parent_location_id),
    INDEX idx_barcode (barcode),
    INDEX idx_active (is_active),
    
    -- Foreign Keys
    CONSTRAINT fk_location_warehouse FOREIGN KEY (warehouse_id) 
        REFERENCES warehouses(id) ON DELETE CASCADE,
    CONSTRAINT fk_location_parent FOREIGN KEY (parent_location_id) 
        REFERENCES warehouse_locations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. Stock Movements (Stok Hareketleri)
-- ============================================
CREATE TABLE IF NOT EXISTS stock_movements (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id INT UNSIGNED NOT NULL,
    warehouse_id INT UNSIGNED NOT NULL,
    location_id INT UNSIGNED NULL,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED NULL,
    
    -- Movement Details
    movement_type ENUM(
        'purchase_in',      -- Satın alma girişi
        'sale_out',         -- Satış çıkışı
        'transfer_in',      -- Transfer girişi
        'transfer_out',     -- Transfer çıkışı
        'production_in',    -- Üretim girişi
        'production_out',   -- Üretim çıkışı
        'adjustment_in',    -- Sayım fazlası
        'adjustment_out',   -- Sayım eksiği
        'return_in',        -- İade girişi
        'return_out',       -- İade çıkışı
        'damage_out',       -- Fire/Hasarlı çıkış
        'sample_out',       -- Numune çıkışı
        'initial'           -- Açılış stoku
    ) NOT NULL,
    
    movement_date DATE NOT NULL,
    movement_time TIME DEFAULT CURRENT_TIME,
    
    -- Quantity & Cost
    quantity DECIMAL(18,4) NOT NULL,
    unit VARCHAR(50) NOT NULL DEFAULT 'Adet',
    unit_cost DECIMAL(18,4) DEFAULT 0 COMMENT 'Cost per unit',
    total_cost DECIMAL(18,2) DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'TRY',
    
    -- FIFO/LIFO Support
    cost_method ENUM('fifo', 'lifo', 'average', 'specific') DEFAULT 'fifo',
    batch_number VARCHAR(100) NULL,
    production_date DATE NULL,
    expiry_date DATE NULL,
    
    -- Lot/Serial Tracking
    lot_number VARCHAR(100) NULL,
    serial_number VARCHAR(100) NULL,
    
    -- Reference Documents
    reference_type VARCHAR(50) NULL COMMENT 'invoice, order, transfer, count, etc.',
    reference_id INT UNSIGNED NULL,
    reference_number VARCHAR(100) NULL,
    
    -- Transaction Partner (Cari)
    cari_account_id INT UNSIGNED NULL,
    
    -- Additional Info
    description TEXT NULL,
    notes TEXT NULL,
    created_by INT UNSIGNED NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Indexes
    INDEX idx_company (company_id),
    INDEX idx_warehouse (warehouse_id),
    INDEX idx_location (location_id),
    INDEX idx_product (product_id),
    INDEX idx_variant (variant_id),
    INDEX idx_movement_type (movement_type),
    INDEX idx_movement_date (movement_date),
    INDEX idx_reference (reference_type, reference_id),
    INDEX idx_lot (lot_number),
    INDEX idx_serial (serial_number),
    INDEX idx_cari (cari_account_id),
    
    -- Foreign Keys
    CONSTRAINT fk_movement_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_movement_warehouse FOREIGN KEY (warehouse_id) 
        REFERENCES warehouses(id) ON DELETE RESTRICT,
    CONSTRAINT fk_movement_location FOREIGN KEY (location_id) 
        REFERENCES warehouse_locations(id) ON DELETE SET NULL,
    CONSTRAINT fk_movement_product FOREIGN KEY (product_id) 
        REFERENCES products(id) ON DELETE RESTRICT,
    CONSTRAINT fk_movement_variant FOREIGN KEY (variant_id) 
        REFERENCES product_variants(id) ON DELETE RESTRICT,
    CONSTRAINT fk_movement_cari FOREIGN KEY (cari_account_id) 
        REFERENCES cari_accounts(id) ON DELETE SET NULL,
    CONSTRAINT fk_movement_user FOREIGN KEY (created_by) 
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. Stock Transfers (Depo Transferleri)
-- ============================================
CREATE TABLE IF NOT EXISTS stock_transfers (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id INT UNSIGNED NOT NULL,
    transfer_number VARCHAR(50) NOT NULL,
    
    -- Source & Destination
    from_warehouse_id INT UNSIGNED NOT NULL,
    to_warehouse_id INT UNSIGNED NOT NULL,
    from_location_id INT UNSIGNED NULL,
    to_location_id INT UNSIGNED NULL,
    
    -- Transfer Details
    transfer_date DATE NOT NULL,
    expected_arrival_date DATE NULL,
    actual_arrival_date DATE NULL,
    
    -- Status
    status ENUM(
        'draft',        -- Taslak
        'pending',      -- Beklemede
        'in_transit',   -- Yolda
        'completed',    -- Tamamlandı
        'cancelled'     -- İptal
    ) DEFAULT 'draft',
    
    -- Totals
    total_items INT DEFAULT 0,
    total_quantity DECIMAL(18,4) DEFAULT 0,
    total_value DECIMAL(18,2) DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'TRY',
    
    -- Responsible Users
    requested_by INT UNSIGNED NULL,
    approved_by INT UNSIGNED NULL,
    shipped_by INT UNSIGNED NULL,
    received_by INT UNSIGNED NULL,
    
    -- Timestamps
    requested_at TIMESTAMP NULL,
    approved_at TIMESTAMP NULL,
    shipped_at TIMESTAMP NULL,
    received_at TIMESTAMP NULL,
    
    -- Additional Info
    reason TEXT NULL,
    notes TEXT NULL,
    shipping_method VARCHAR(100) NULL,
    tracking_number VARCHAR(100) NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Indexes
    UNIQUE KEY uk_transfer_number (company_id, transfer_number),
    INDEX idx_company (company_id),
    INDEX idx_from_warehouse (from_warehouse_id),
    INDEX idx_to_warehouse (to_warehouse_id),
    INDEX idx_status (status),
    INDEX idx_transfer_date (transfer_date),
    
    -- Foreign Keys
    CONSTRAINT fk_transfer_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_transfer_from_warehouse FOREIGN KEY (from_warehouse_id) 
        REFERENCES warehouses(id) ON DELETE RESTRICT,
    CONSTRAINT fk_transfer_to_warehouse FOREIGN KEY (to_warehouse_id) 
        REFERENCES warehouses(id) ON DELETE RESTRICT,
    CONSTRAINT fk_transfer_from_location FOREIGN KEY (from_location_id) 
        REFERENCES warehouse_locations(id) ON DELETE SET NULL,
    CONSTRAINT fk_transfer_to_location FOREIGN KEY (to_location_id) 
        REFERENCES warehouse_locations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. Stock Transfer Items (Transfer Detayları)
-- ============================================
CREATE TABLE IF NOT EXISTS stock_transfer_items (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    transfer_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED NULL,
    
    -- Quantities
    requested_quantity DECIMAL(18,4) NOT NULL,
    shipped_quantity DECIMAL(18,4) DEFAULT 0,
    received_quantity DECIMAL(18,4) DEFAULT 0,
    unit VARCHAR(50) NOT NULL DEFAULT 'Adet',
    
    -- Cost Information
    unit_cost DECIMAL(18,4) DEFAULT 0,
    total_cost DECIMAL(18,2) DEFAULT 0,
    
    -- Lot/Serial
    lot_number VARCHAR(100) NULL,
    serial_number VARCHAR(100) NULL,
    
    -- Status per item
    status ENUM('pending', 'shipped', 'received', 'partial') DEFAULT 'pending',
    
    -- Notes
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_transfer (transfer_id),
    INDEX idx_product (product_id),
    INDEX idx_variant (variant_id),
    
    -- Foreign Keys
    CONSTRAINT fk_transfer_item_transfer FOREIGN KEY (transfer_id) 
        REFERENCES stock_transfers(id) ON DELETE CASCADE,
    CONSTRAINT fk_transfer_item_product FOREIGN KEY (product_id) 
        REFERENCES products(id) ON DELETE RESTRICT,
    CONSTRAINT fk_transfer_item_variant FOREIGN KEY (variant_id) 
        REFERENCES product_variants(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. Stock Counts (Stok Sayımları)
-- ============================================
CREATE TABLE IF NOT EXISTS stock_counts (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id INT UNSIGNED NOT NULL,
    count_number VARCHAR(50) NOT NULL,
    warehouse_id INT UNSIGNED NOT NULL,
    
    -- Count Details
    count_date DATE NOT NULL,
    count_type ENUM('full', 'partial', 'cycle', 'spot') DEFAULT 'full',
    
    -- Status
    status ENUM(
        'planned',      -- Planlandı
        'in_progress',  -- Devam ediyor
        'completed',    -- Tamamlandı
        'approved',     -- Onaylandı
        'cancelled'     -- İptal
    ) DEFAULT 'planned',
    
    -- Statistics
    total_items INT DEFAULT 0,
    counted_items INT DEFAULT 0,
    variance_items INT DEFAULT 0 COMMENT 'Items with differences',
    
    total_variance_value DECIMAL(18,2) DEFAULT 0,
    positive_variance_value DECIMAL(18,2) DEFAULT 0,
    negative_variance_value DECIMAL(18,2) DEFAULT 0,
    
    -- Responsible Users
    created_by INT UNSIGNED NULL,
    counted_by INT UNSIGNED NULL,
    approved_by INT UNSIGNED NULL,
    
    -- Timestamps
    planned_at TIMESTAMP NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    approved_at TIMESTAMP NULL,
    
    -- Additional Info
    reason TEXT NULL,
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Indexes
    UNIQUE KEY uk_count_number (company_id, count_number),
    INDEX idx_company (company_id),
    INDEX idx_warehouse (warehouse_id),
    INDEX idx_status (status),
    INDEX idx_count_date (count_date),
    
    -- Foreign Keys
    CONSTRAINT fk_count_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_count_warehouse FOREIGN KEY (warehouse_id) 
        REFERENCES warehouses(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. Stock Count Items (Sayım Detayları)
-- ============================================
CREATE TABLE IF NOT EXISTS stock_count_items (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    count_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED NULL,
    location_id INT UNSIGNED NULL,
    
    -- Quantities
    system_quantity DECIMAL(18,4) DEFAULT 0 COMMENT 'System stock',
    counted_quantity DECIMAL(18,4) DEFAULT 0 COMMENT 'Physical count',
    variance_quantity DECIMAL(18,4) DEFAULT 0 COMMENT 'Difference',
    unit VARCHAR(50) NOT NULL DEFAULT 'Adet',
    
    -- Cost Impact
    unit_cost DECIMAL(18,4) DEFAULT 0,
    variance_value DECIMAL(18,2) DEFAULT 0,
    
    -- Lot/Serial
    lot_number VARCHAR(100) NULL,
    serial_number VARCHAR(100) NULL,
    
    -- Status
    is_counted BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT FALSE,
    
    -- Counter Info
    counted_by INT UNSIGNED NULL,
    counted_at TIMESTAMP NULL,
    
    -- Notes
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_count (count_id),
    INDEX idx_product (product_id),
    INDEX idx_variant (variant_id),
    INDEX idx_location (location_id),
    INDEX idx_variance (variance_quantity),
    
    -- Foreign Keys
    CONSTRAINT fk_count_item_count FOREIGN KEY (count_id) 
        REFERENCES stock_counts(id) ON DELETE CASCADE,
    CONSTRAINT fk_count_item_product FOREIGN KEY (product_id) 
        REFERENCES products(id) ON DELETE RESTRICT,
    CONSTRAINT fk_count_item_variant FOREIGN KEY (variant_id) 
        REFERENCES product_variants(id) ON DELETE RESTRICT,
    CONSTRAINT fk_count_item_location FOREIGN KEY (location_id) 
        REFERENCES warehouse_locations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. Lot/Serial Numbers (Lot/Seri Numarası Takibi)
-- ============================================
CREATE TABLE IF NOT EXISTS lot_serial_numbers (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED NULL,
    warehouse_id INT UNSIGNED NOT NULL,
    location_id INT UNSIGNED NULL,
    
    -- Lot/Serial Info
    tracking_type ENUM('lot', 'serial') NOT NULL,
    lot_number VARCHAR(100) NULL,
    serial_number VARCHAR(100) NULL,
    
    -- Dates
    manufacture_date DATE NULL,
    expiry_date DATE NULL,
    received_date DATE NULL,
    
    -- Quantity (for lot tracking)
    initial_quantity DECIMAL(18,4) DEFAULT 0,
    current_quantity DECIMAL(18,4) DEFAULT 0,
    unit VARCHAR(50) NOT NULL DEFAULT 'Adet',
    
    -- Cost
    unit_cost DECIMAL(18,4) DEFAULT 0,
    
    -- Status
    status ENUM('available', 'reserved', 'sold', 'expired', 'damaged') DEFAULT 'available',
    
    -- Supplier Info
    supplier_id INT UNSIGNED NULL,
    supplier_lot_number VARCHAR(100) NULL,
    purchase_order_number VARCHAR(100) NULL,
    
    -- Additional Info
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Indexes
    UNIQUE KEY uk_serial (company_id, product_id, serial_number),
    INDEX idx_company (company_id),
    INDEX idx_product (product_id),
    INDEX idx_variant (variant_id),
    INDEX idx_warehouse (warehouse_id),
    INDEX idx_location (location_id),
    INDEX idx_lot (lot_number),
    INDEX idx_serial (serial_number),
    INDEX idx_status (status),
    INDEX idx_expiry (expiry_date),
    
    -- Foreign Keys
    CONSTRAINT fk_lot_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_lot_product FOREIGN KEY (product_id) 
        REFERENCES products(id) ON DELETE RESTRICT,
    CONSTRAINT fk_lot_variant FOREIGN KEY (variant_id) 
        REFERENCES product_variants(id) ON DELETE RESTRICT,
    CONSTRAINT fk_lot_warehouse FOREIGN KEY (warehouse_id) 
        REFERENCES warehouses(id) ON DELETE RESTRICT,
    CONSTRAINT fk_lot_location FOREIGN KEY (location_id) 
        REFERENCES warehouse_locations(id) ON DELETE SET NULL,
    CONSTRAINT fk_lot_supplier FOREIGN KEY (supplier_id) 
        REFERENCES cari_accounts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Views
-- ============================================

-- Current Stock View (Per Warehouse)
CREATE OR REPLACE VIEW view_current_stock AS
SELECT 
    sm.company_id,
    sm.warehouse_id,
    w.warehouse_name,
    sm.product_id,
    p.product_code,
    p.product_name,
    sm.variant_id,
    pv.variant_sku,
    SUM(CASE 
        WHEN sm.movement_type IN ('purchase_in', 'transfer_in', 'production_in', 'adjustment_in', 'return_in', 'initial') 
        THEN sm.quantity 
        ELSE -sm.quantity 
    END) as current_stock,
    sm.unit,
    AVG(sm.unit_cost) as average_cost,
    MAX(sm.movement_date) as last_movement_date
FROM stock_movements sm
INNER JOIN warehouses w ON sm.warehouse_id = w.id
INNER JOIN products p ON sm.product_id = p.id
LEFT JOIN product_variants pv ON sm.variant_id = pv.id
WHERE sm.deleted_at IS NULL
GROUP BY sm.company_id, sm.warehouse_id, sm.product_id, sm.variant_id
HAVING current_stock > 0;

-- Low Stock Alert View
CREATE OR REPLACE VIEW view_low_stock_products AS
SELECT 
    p.id as product_id,
    p.company_id,
    p.product_code,
    p.product_name,
    p.min_stock_level,
    p.max_stock_level,
    COALESCE(SUM(CASE 
        WHEN sm.movement_type IN ('purchase_in', 'transfer_in', 'production_in', 'adjustment_in', 'return_in', 'initial') 
        THEN sm.quantity 
        ELSE -sm.quantity 
    END), 0) as current_stock,
    p.unit,
    (p.min_stock_level - COALESCE(SUM(CASE 
        WHEN sm.movement_type IN ('purchase_in', 'transfer_in', 'production_in', 'adjustment_in', 'return_in', 'initial') 
        THEN sm.quantity 
        ELSE -sm.quantity 
    END), 0)) as shortage_quantity
FROM products p
LEFT JOIN stock_movements sm ON p.id = sm.product_id AND sm.deleted_at IS NULL
WHERE p.stock_tracking = TRUE 
    AND p.deleted_at IS NULL
GROUP BY p.id
HAVING current_stock < p.min_stock_level;

-- ============================================
-- Triggers
-- ============================================

-- Trigger: Update transfer totals after item insert
DELIMITER //
CREATE TRIGGER trg_update_transfer_totals_insert
AFTER INSERT ON stock_transfer_items
FOR EACH ROW
BEGIN
    UPDATE stock_transfers SET
        total_items = (SELECT COUNT(*) FROM stock_transfer_items WHERE transfer_id = NEW.transfer_id),
        total_quantity = (SELECT COALESCE(SUM(requested_quantity), 0) FROM stock_transfer_items WHERE transfer_id = NEW.transfer_id),
        total_value = (SELECT COALESCE(SUM(total_cost), 0) FROM stock_transfer_items WHERE transfer_id = NEW.transfer_id)
    WHERE id = NEW.transfer_id;
END//
DELIMITER ;

-- Trigger: Update transfer totals after item update
DELIMITER //
CREATE TRIGGER trg_update_transfer_totals_update
AFTER UPDATE ON stock_transfer_items
FOR EACH ROW
BEGIN
    UPDATE stock_transfers SET
        total_items = (SELECT COUNT(*) FROM stock_transfer_items WHERE transfer_id = NEW.transfer_id),
        total_quantity = (SELECT COALESCE(SUM(requested_quantity), 0) FROM stock_transfer_items WHERE transfer_id = NEW.transfer_id),
        total_value = (SELECT COALESCE(SUM(total_cost), 0) FROM stock_transfer_items WHERE transfer_id = NEW.transfer_id)
    WHERE id = NEW.transfer_id;
END//
DELIMITER ;

-- Trigger: Calculate variance before count item insert
DELIMITER //
CREATE TRIGGER trg_calculate_count_variance_insert
BEFORE INSERT ON stock_count_items
FOR EACH ROW
BEGIN
    SET NEW.variance_quantity = NEW.counted_quantity - NEW.system_quantity;
    SET NEW.variance_value = NEW.variance_quantity * NEW.unit_cost;
END//
DELIMITER ;

-- Trigger: Calculate variance before count item update
DELIMITER //
CREATE TRIGGER trg_calculate_count_variance_update
BEFORE UPDATE ON stock_count_items
FOR EACH ROW
BEGIN
    SET NEW.variance_quantity = NEW.counted_quantity - NEW.system_quantity;
    SET NEW.variance_value = NEW.variance_quantity * NEW.unit_cost;
END//
DELIMITER ;

-- ============================================
-- End of Migration 008
-- ============================================

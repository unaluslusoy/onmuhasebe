-- ============================================
-- Migration: 007_create_products_tables
-- Description: Create products and related tables
-- Created: 2025-10-03
-- ============================================

-- ============================================
-- 1. Product Categories Table
-- ============================================
CREATE TABLE IF NOT EXISTS product_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    
    category_name VARCHAR(100) NOT NULL,
    parent_category_id INT UNSIGNED NULL COMMENT 'Alt kategori için',
    
    description TEXT,
    
    -- Görsel
    image_path VARCHAR(255),
    
    -- Renk (UI için)
    color_code VARCHAR(7) DEFAULT '#6366F1',
    icon VARCHAR(50),
    
    -- Sıralama
    sort_order INT DEFAULT 0,
    
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_category_id) REFERENCES product_categories(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_company_id (company_id),
    INDEX idx_parent_category (parent_category_id),
    INDEX idx_sort_order (sort_order),
    UNIQUE KEY unique_category_name (company_id, category_name, parent_category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. Products Table
-- ============================================
CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    
    -- Temel bilgiler
    product_code VARCHAR(50) NOT NULL,
    barcode VARCHAR(100),
    product_name VARCHAR(200) NOT NULL,
    product_type ENUM('urun', 'hizmet', 'dijital', 'hammadde') DEFAULT 'urun',
    
    -- Kategori
    category_id INT UNSIGNED,
    
    -- Açıklama
    description TEXT,
    short_description VARCHAR(500),
    
    -- Birim
    unit ENUM('adet', 'kg', 'gr', 'litre', 'ml', 'metre', 'm2', 'm3', 
              'paket', 'kutu', 'koli', 'ton', 'saat', 'gun', 'ay') DEFAULT 'adet',
    
    -- Fiyatlandırma
    purchase_price DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Alış fiyatı',
    sale_price DECIMAL(15,2) NOT NULL COMMENT 'Satış fiyatı',
    discount_rate DECIMAL(5,2) DEFAULT 0.00 COMMENT 'İskonto oranı',
    
    -- KDV
    kdv_rate DECIMAL(5,2) DEFAULT 20.00,
    kdv_included BOOLEAN DEFAULT FALSE COMMENT 'KDV dahil mi?',
    
    -- Para birimi
    currency VARCHAR(3) DEFAULT 'TRY',
    
    -- Stok bilgileri
    stock_tracking BOOLEAN DEFAULT TRUE COMMENT 'Stok takibi yapılsın mı?',
    current_stock DECIMAL(10,2) DEFAULT 0.00,
    min_stock_level DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Minimum stok seviyesi',
    max_stock_level DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Maksimum stok seviyesi',
    critical_stock_level DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Kritik stok seviyesi',
    
    -- Boyut ve ağırlık
    weight DECIMAL(10,2) COMMENT 'Ağırlık (kg)',
    width DECIMAL(10,2) COMMENT 'Genişlik (cm)',
    height DECIMAL(10,2) COMMENT 'Yükseklik (cm)',
    depth DECIMAL(10,2) COMMENT 'Derinlik (cm)',
    
    -- Görseller
    image_path VARCHAR(255),
    images TEXT COMMENT 'JSON array of image paths',
    
    -- Varyantlar
    has_variants BOOLEAN DEFAULT FALSE,
    variant_attributes TEXT COMMENT 'JSON: beden, renk, vs.',
    
    -- Tedarikçi
    supplier_id INT UNSIGNED COMMENT 'Varsayılan tedarikçi (cari)',
    supplier_code VARCHAR(50) COMMENT 'Tedarikçi ürün kodu',
    
    -- Muhasebe kodu
    accounting_code VARCHAR(50) COMMENT 'Muhasebe hesap kodu',
    
    -- Etiketler
    tags TEXT COMMENT 'JSON array',
    
    -- Durum
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Tarih bilgileri
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    
    -- Foreign Keys
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES cari_accounts(id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_company_id (company_id),
    INDEX idx_product_code (product_code),
    INDEX idx_barcode (barcode),
    INDEX idx_product_name (product_name),
    INDEX idx_category_id (category_id),
    INDEX idx_current_stock (current_stock),
    INDEX idx_is_active (is_active),
    INDEX idx_deleted_at (deleted_at),
    UNIQUE KEY unique_product_code (company_id, product_code),
    UNIQUE KEY unique_barcode_per_company (company_id, barcode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fulltext index for search
ALTER TABLE products ADD FULLTEXT INDEX ft_search (product_name, description, barcode);

-- ============================================
-- 3. Product Variants Table
-- ============================================
CREATE TABLE IF NOT EXISTS product_variants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    
    variant_name VARCHAR(100) NOT NULL COMMENT 'Örn: Kırmızı - L',
    variant_sku VARCHAR(100) COMMENT 'Varyant SKU kodu',
    
    -- Varyant özellikleri
    attributes TEXT COMMENT 'JSON: {"renk": "kirmizi", "beden": "L"}',
    
    -- Fiyat farklılığı
    price_difference DECIMAL(15,2) DEFAULT 0.00 COMMENT '+/- fiyat farkı',
    
    -- Stok
    stock_quantity DECIMAL(10,2) DEFAULT 0.00,
    
    -- Barkod
    barcode VARCHAR(100),
    
    -- Görseller
    image_path VARCHAR(255),
    
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_product_id (product_id),
    INDEX idx_barcode (barcode),
    INDEX idx_variant_sku (variant_sku)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. Product Price History Table
-- ============================================
CREATE TABLE IF NOT EXISTS product_price_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    
    old_purchase_price DECIMAL(15,2),
    new_purchase_price DECIMAL(15,2),
    
    old_sale_price DECIMAL(15,2),
    new_sale_price DECIMAL(15,2),
    
    changed_by INT UNSIGNED NOT NULL COMMENT 'User ID',
    change_reason VARCHAR(255),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_product_id (product_id),
    INDEX idx_created_at (created_at),
    INDEX idx_changed_by (changed_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

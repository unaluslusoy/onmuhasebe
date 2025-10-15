-- ============================================
-- Migration: 006_create_categories_table
-- Description: Create categories and labels tables for various types
-- Created: 2025-10-07
-- ============================================

-- Categories table for all category types
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('sales', 'expense', 'income_expense_label', 'service_product', 'employee', 'customer_supplier') NOT NULL,
    color_bg VARCHAR(7) DEFAULT '#3B82F6' COMMENT 'Background color hex',
    color_text VARCHAR(7) DEFAULT '#FFFFFF' COMMENT 'Text color hex',
    description TEXT,
    parent_id INT UNSIGNED NULL COMMENT 'For hierarchical categories',
    sort_order INT DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_company_id (company_id),
    INDEX idx_type (type),
    INDEX idx_parent_id (parent_id),
    INDEX idx_is_active (is_active),
    INDEX idx_deleted_at (deleted_at),
    INDEX idx_company_type (company_id, type),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO categories (company_id, name, type, color_bg, color_text, sort_order) VALUES
-- Sales Categories (will be created per company)
(1, 'Ürün Satışı', 'sales', '#3B82F6', '#FFFFFF', 1),
(1, 'Hizmet Satışı', 'sales', '#10B981', '#FFFFFF', 2),
(1, 'Danışmanlık', 'sales', '#8B5CF6', '#FFFFFF', 3),

-- Expense Categories
(1, 'Personel Giderleri', 'expense', '#EF4444', '#FFFFFF', 1),
(1, 'Kira', 'expense', '#F59E0B', '#FFFFFF', 2),
(1, 'Elektrik-Su-Doğalgaz', 'expense', '#06B6D4', '#FFFFFF', 3),
(1, 'Pazarlama', 'expense', '#EC4899', '#FFFFFF', 4),
(1, 'Ofis Malzemeleri', 'expense', '#6366F1', '#FFFFFF', 5),

-- Income/Expense Labels
(1, 'Acil', 'income_expense_label', '#DC2626', '#FFFFFF', 1),
(1, 'Önemli', 'income_expense_label', '#EA580C', '#FFFFFF', 2),
(1, 'Normal', 'income_expense_label', '#2563EB', '#FFFFFF', 3),
(1, 'Düşük Öncelik', 'income_expense_label', '#64748B', '#FFFFFF', 4),

-- Service/Product Categories
(1, 'Yazılım Hizmetleri', 'service_product', '#8B5CF6', '#FFFFFF', 1),
(1, 'Donanım Ürünleri', 'service_product', '#3B82F6', '#FFFFFF', 2),
(1, 'Danışmanlık Hizmetleri', 'service_product', '#10B981', '#FFFFFF', 3),

-- Employee Categories
(1, 'Tam Zamanlı', 'employee', '#10B981', '#FFFFFF', 1),
(1, 'Yarı Zamanlı', 'employee', '#F59E0B', '#FFFFFF', 2),
(1, 'Stajyer', 'employee', '#06B6D4', '#FFFFFF', 3),
(1, 'Serbest Çalışan', 'employee', '#8B5CF6', '#FFFFFF', 4),

-- Customer/Supplier Categories
(1, 'Bireysel Müşteri', 'customer_supplier', '#3B82F6', '#FFFFFF', 1),
(1, 'Kurumsal Müşteri', 'customer_supplier', '#8B5CF6', '#FFFFFF', 2),
(1, 'Tedarikçi', 'customer_supplier', '#10B981', '#FFFFFF', 3),
(1, 'Distribütör', 'customer_supplier', '#F59E0B', '#FFFFFF', 4);

-- Note: Above default categories use company_id = 1 as example
-- In production, these should be created per company during company creation

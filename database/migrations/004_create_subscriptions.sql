-- ============================================
-- Subscription System Migration
-- 30-day free trial, payment tracking
-- ============================================

USE onmuhasebe;

-- 1. Subscriptions table
CREATE TABLE IF NOT EXISTS subscriptions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id INT UNSIGNED NOT NULL,
    plan_type ENUM('trial', 'basic', 'professional', 'enterprise') NOT NULL DEFAULT 'trial',
    status ENUM('active', 'trial', 'expired', 'cancelled', 'suspended') NOT NULL DEFAULT 'trial',
    trial_ends_at DATETIME NULL,
    current_period_start DATETIME NOT NULL,
    current_period_end DATETIME NOT NULL,
    cancelled_at DATETIME NULL,
    cancel_reason VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_company (company_id),
    INDEX idx_status (status),
    INDEX idx_period_end (current_period_end),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Subscription plans
CREATE TABLE IF NOT EXISTS subscription_plans (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NULL,
    price_monthly DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    price_yearly DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    features JSON NULL,
    max_users INT DEFAULT 1,
    max_invoices_per_month INT DEFAULT 100,
    max_storage_gb INT DEFAULT 5,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Payment history
CREATE TABLE IF NOT EXISTS payments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id INT UNSIGNED NOT NULL,
    subscription_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'TRY',
    payment_method ENUM('credit_card', 'bank_transfer', 'paypal', 'stripe', 'iyzico') NULL,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    transaction_id VARCHAR(255) NULL,
    payment_date DATETIME NULL,
    invoice_number VARCHAR(50) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_company (company_id),
    INDEX idx_subscription (subscription_id),
    INDEX idx_status (payment_status),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Insert default plans
INSERT INTO subscription_plans (name, slug, description, price_monthly, price_yearly, features, max_users, max_invoices_per_month, max_storage_gb, sort_order) VALUES
('Deneme', 'trial', '30 gün ücretsiz deneme', 0.00, 0.00, '["Tüm temel özellikler", "Sınırsız fatura", "5 GB depolama", "Email desteği"]', 1, 999999, 5, 1),
('Temel', 'basic', 'Küçük işletmeler için', 99.00, 990.00, '["Sınırsız fatura", "10 GB depolama", "Temel raporlar", "Email desteği"]', 3, 999999, 10, 2),
('Profesyonel', 'professional', 'Büyüyen işletmeler için', 199.00, 1990.00, '["Sınırsız fatura", "50 GB depolama", "Gelişmiş raporlar", "Öncelikli destek", "API erişimi"]', 10, 999999, 50, 3),
('Kurumsal', 'enterprise', 'Büyük şirketler için', 499.00, 4990.00, '["Sınırsız her şey", "200 GB depolama", "Özel raporlar", "7/24 destek", "API erişimi", "Özel entegrasyonlar"]', 999, 999999, 200, 4);

-- 5. Create initial subscriptions for existing companies (30-day trial)
INSERT INTO subscriptions (company_id, plan_type, status, trial_ends_at, current_period_start, current_period_end)
SELECT 
    id,
    'trial',
    'trial',
    DATE_ADD(NOW(), INTERVAL 30 DAY),
    NOW(),
    DATE_ADD(NOW(), INTERVAL 30 DAY)
FROM companies
WHERE NOT EXISTS (
    SELECT 1 FROM subscriptions WHERE subscriptions.company_id = companies.id
);

-- 6. Update users table - add is_super_admin flag
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS is_super_admin TINYINT(1) DEFAULT 0 AFTER role,
ADD INDEX idx_super_admin (is_super_admin);

-- 7. Set admin user as super admin (no subscription needed)
UPDATE users SET is_super_admin = 1 WHERE role = 'admin' AND email = 'admin@onmuhasebe.com';

-- 8. Fix admin password (Admin123!)
UPDATE users 
SET password = '$2y$10$Fz3Xhs7QWWibAp/tvwozF.nebZb2hC54I0wqnFFZs6EgvbDK66rve'
WHERE email = 'admin@onmuhasebe.com';

-- Done
SELECT 'Subscription system created successfully!' as message;
SELECT COUNT(*) as total_subscriptions FROM subscriptions;
SELECT COUNT(*) as total_plans FROM subscription_plans;

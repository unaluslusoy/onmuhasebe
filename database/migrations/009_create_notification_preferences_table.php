<?php

/**
 * Create kullanici_bildirim_tercihleri table
 * User notification preferences for email and push notifications
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

try {
    // Direct PDO connection
    $dsn = "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']};charset=utf8mb4";
    $db = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $sql = "CREATE TABLE IF NOT EXISTS user_notification_preferences (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,

        -- Email notification preferences
        email_invoice BOOLEAN DEFAULT TRUE COMMENT 'Invoice notifications via email',
        email_payment BOOLEAN DEFAULT TRUE COMMENT 'Payment notifications via email',
        email_reminder BOOLEAN DEFAULT TRUE COMMENT 'Reminder notifications via email',
        email_stock BOOLEAN DEFAULT TRUE COMMENT 'Stock alerts via email',

        -- Push notification preferences
        push_invoice BOOLEAN DEFAULT TRUE COMMENT 'Invoice notifications via push',
        push_payment BOOLEAN DEFAULT TRUE COMMENT 'Payment notifications via push',
        push_reminder BOOLEAN DEFAULT TRUE COMMENT 'Reminder notifications via push',
        push_stock BOOLEAN DEFAULT TRUE COMMENT 'Stock alerts via push',
        push_order BOOLEAN DEFAULT TRUE COMMENT 'Order notifications via push',

        -- Timestamps
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        UNIQUE KEY unique_user (user_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $db->exec($sql);

    echo "✓ user_notification_preferences table created\n";

} catch (Exception $e) {
    echo "✗ Hata: " . $e->getMessage() . "\n";
    exit(1);
}

<?php

/**
 * Create email_queue table for queued email sending
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

    $sql = "CREATE TABLE IF NOT EXISTS email_queue (
        id INT AUTO_INCREMENT PRIMARY KEY,

        -- Recipient info
        to_email VARCHAR(255) NOT NULL,
        to_name VARCHAR(255) NULL,

        -- Email content
        subject VARCHAR(500) NOT NULL,
        body TEXT NOT NULL,
        alt_body TEXT NULL,

        -- Additional recipients
        cc JSON NULL COMMENT 'CC recipients as JSON array',
        bcc JSON NULL COMMENT 'BCC recipients as JSON array',
        reply_to VARCHAR(255) NULL,

        -- Attachments
        attachments JSON NULL COMMENT 'Attachment paths as JSON array',

        -- Queue management
        status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
        priority ENUM('high', 'normal', 'low') DEFAULT 'normal',
        attempts INT DEFAULT 0,

        -- Scheduling
        scheduled_at DATETIME NULL COMMENT 'When to send (NULL = immediate)',

        -- Tracking
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        sent_at DATETIME NULL,
        last_attempt_at DATETIME NULL,
        error_message TEXT NULL,

        INDEX idx_status (status),
        INDEX idx_priority (priority),
        INDEX idx_scheduled (scheduled_at),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $db->exec($sql);

    echo "✓ email_queue tablosu oluşturuldu\n";

} catch (Exception $e) {
    echo "✗ Hata: " . $e->getMessage() . "\n";
    exit(1);
}

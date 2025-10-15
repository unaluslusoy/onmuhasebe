<?php

/**
 * Database Backup Script
 * Automatically backs up MySQL database and cleans old backups
 *
 * Usage:
 *   php scripts/backup-database.php
 *
 * Cron (Linux):
 *   0 3 * * * cd /path/to/onmuhasebe && php scripts/backup-database.php
 *
 * Task Scheduler (Windows):
 *   Program: php
 *   Arguments: c:\xampp\htdocs\onmuhasebe\scripts\backup-database.php
 *   Schedule: Daily at 03:00
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Configuration
$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$database = $_ENV['DB_DATABASE'] ?? 'onmuhasebe';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';
$backupPath = $_ENV['BACKUP_PATH'] ?? 'storage/backups';
$retentionDays = (int)($_ENV['BACKUP_RETENTION_DAYS'] ?? 30);

// Ensure backup directory exists
$backupDir = __DIR__ . '/../' . $backupPath;
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    echo "✓ Created backup directory: {$backupDir}\n";
}

// Generate backup filename
$timestamp = date('Y-m-d_H-i-s');
$filename = "backup_{$database}_{$timestamp}.sql";
$filepath = $backupDir . '/' . $filename;

echo "\n" . str_repeat('=', 60) . "\n";
echo "DATABASE BACKUP SCRIPT\n";
echo str_repeat('=', 60) . "\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "Database: {$database}\n";
echo "Backup file: {$filename}\n";
echo str_repeat('=', 60) . "\n\n";

// Build mysqldump command
$command = sprintf(
    'mysqldump --host=%s --user=%s %s %s > %s 2>&1',
    escapeshellarg($host),
    escapeshellarg($username),
    $password ? '--password=' . escapeshellarg($password) : '',
    escapeshellarg($database),
    escapeshellarg($filepath)
);

echo "→ Starting database backup...\n";

// Execute backup
$output = [];
$returnCode = 0;
exec($command, $output, $returnCode);

if ($returnCode === 0 && file_exists($filepath)) {
    $filesize = filesize($filepath);
    $filesizeMB = round($filesize / 1024 / 1024, 2);

    echo "✓ Backup completed successfully!\n";
    echo "  File: {$filepath}\n";
    echo "  Size: {$filesizeMB} MB\n\n";

    // Log success
    logBackup('success', $filename, $filesizeMB);

} else {
    echo "✗ Backup failed!\n";
    if (!empty($output)) {
        echo "  Error: " . implode("\n", $output) . "\n";
    }

    // Log failure
    logBackup('error', $filename, 0, implode("\n", $output));

    exit(1);
}

// Clean old backups
echo "→ Cleaning old backups (older than {$retentionDays} days)...\n";

$cutoffTime = time() - ($retentionDays * 24 * 60 * 60);
$files = glob($backupDir . '/backup_*.sql');
$deletedCount = 0;
$deletedSize = 0;

foreach ($files as $file) {
    if (filemtime($file) < $cutoffTime) {
        $size = filesize($file);
        if (unlink($file)) {
            $deletedCount++;
            $deletedSize += $size;
            echo "  ✓ Deleted: " . basename($file) . "\n";
        }
    }
}

if ($deletedCount > 0) {
    $deletedSizeMB = round($deletedSize / 1024 / 1024, 2);
    echo "✓ Cleaned {$deletedCount} old backup(s), freed {$deletedSizeMB} MB\n\n";
} else {
    echo "  No old backups to delete\n\n";
}

// Show backup statistics
$backupFiles = glob($backupDir . '/backup_*.sql');
$totalCount = count($backupFiles);
$totalSize = 0;

foreach ($backupFiles as $file) {
    $totalSize += filesize($file);
}

$totalSizeMB = round($totalSize / 1024 / 1024, 2);

echo str_repeat('=', 60) . "\n";
echo "BACKUP STATISTICS\n";
echo str_repeat('=', 60) . "\n";
echo "Total backups: {$totalCount}\n";
echo "Total size: {$totalSizeMB} MB\n";
echo "Backup directory: {$backupDir}\n";
echo "Retention: {$retentionDays} days\n";
echo str_repeat('=', 60) . "\n";

echo "\n✓ Backup process completed successfully!\n\n";

/**
 * Log backup operation
 */
function logBackup(string $status, string $filename, float $sizeMB, string $error = ''): void
{
    $logDir = __DIR__ . '/../storage/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/backup_' . date('Y-m-d') . '.log';

    $logEntry = sprintf(
        "[%s] [%s] %s | Size: %.2f MB%s\n",
        date('Y-m-d H:i:s'),
        strtoupper($status),
        $filename,
        $sizeMB,
        $error ? ' | Error: ' . $error : ''
    );

    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

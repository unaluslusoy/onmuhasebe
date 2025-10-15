<?php

/**
 * Database Backup Script (PDO Version - No mysqldump required)
 * Works on Windows without mysqldump in PATH
 *
 * Usage:
 *   php scripts/backup-database-pdo.php
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
echo "DATABASE BACKUP SCRIPT (PDO)\n";
echo str_repeat('=', 60) . "\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "Database: {$database}\n";
echo "Backup file: {$filename}\n";
echo str_repeat('=', 60) . "\n\n";

echo "→ Connecting to database...\n";

try {
    // Connect to database
    $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "✓ Connected successfully\n\n";

    echo "→ Starting backup...\n";

    // Open backup file
    $handle = fopen($filepath, 'w');

    // Write header
    fwrite($handle, "-- MySQL Database Backup\n");
    fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
    fwrite($handle, "-- Database: {$database}\n");
    fwrite($handle, "-- PHP Version: " . phpversion() . "\n");
    fwrite($handle, "--\n\n");

    fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
    fwrite($handle, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");

    // Get all tables
    $tables = [];
    $result = $pdo->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    echo "  Found " . count($tables) . " tables\n";

    $tableCount = 0;
    $totalRows = 0;

    // Backup each table
    foreach ($tables as $table) {
        $tableCount++;
        echo "  [{$tableCount}/" . count($tables) . "] Backing up table: {$table}... ";

        // Table structure
        fwrite($handle, "-- Table: {$table}\n");
        fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

        $createTable = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
        fwrite($handle, $createTable['Create Table'] . ";\n\n");

        // Table data
        $rows = $pdo->query("SELECT * FROM `{$table}`");
        $rowCount = 0;

        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            if ($rowCount === 0) {
                fwrite($handle, "INSERT INTO `{$table}` VALUES\n");
            }

            $values = array_map(function($value) use ($pdo) {
                if ($value === null) {
                    return 'NULL';
                }
                return $pdo->quote($value);
            }, array_values($row));

            $rowCount++;
            $totalRows++;

            $comma = ($rowCount > 1) ? ',' : '';
            fwrite($handle, $comma . '(' . implode(', ', $values) . ")\n");

            // Commit every 1000 rows
            if ($rowCount % 1000 === 0) {
                fwrite($handle, ";\n");
                fwrite($handle, "INSERT INTO `{$table}` VALUES\n");
                $rowCount = 0;
            }
        }

        if ($rowCount > 0) {
            fwrite($handle, ";\n");
        }

        fwrite($handle, "\n");
        echo "{$rowCount} rows\n";
    }

    fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");

    fclose($handle);

    $filesize = filesize($filepath);
    $filesizeMB = round($filesize / 1024 / 1024, 2);

    echo "\n✓ Backup completed successfully!\n";
    echo "  File: {$filepath}\n";
    echo "  Size: {$filesizeMB} MB\n";
    echo "  Tables: {$tableCount}\n";
    echo "  Total rows: {$totalRows}\n\n";

    // Log success
    logBackup('success', $filename, $filesizeMB);

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

} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n\n";

    // Log failure
    logBackup('error', $filename, 0, $e->getMessage());

    exit(1);
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";

    // Log failure
    logBackup('error', $filename, 0, $e->getMessage());

    exit(1);
}

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

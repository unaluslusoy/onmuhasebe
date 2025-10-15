<?php

/**
 * Database Restore Script
 * Restores MySQL database from backup file
 *
 * Usage:
 *   php scripts/restore-database.php backup_onmuhasebe_2025-10-15_03-00-00.sql
 *   php scripts/restore-database.php                  (lists available backups)
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

$backupDir = __DIR__ . '/../' . $backupPath;

echo "\n" . str_repeat('=', 60) . "\n";
echo "DATABASE RESTORE SCRIPT\n";
echo str_repeat('=', 60) . "\n\n";

// Check if backup file is provided
if ($argc < 2) {
    // List available backups
    echo "Available backups:\n\n";

    $backupFiles = glob($backupDir . '/backup_*.sql');

    if (empty($backupFiles)) {
        echo "  No backup files found in {$backupDir}\n\n";
        exit(1);
    }

    // Sort by date (newest first)
    usort($backupFiles, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    foreach ($backupFiles as $index => $file) {
        $filename = basename($file);
        $size = filesize($file);
        $sizeMB = round($size / 1024 / 1024, 2);
        $date = date('Y-m-d H:i:s', filemtime($file));

        printf("  %2d. %-50s %8s MB  %s\n", $index + 1, $filename, $sizeMB, $date);
    }

    echo "\nUsage:\n";
    echo "  php scripts/restore-database.php <backup-filename>\n\n";
    echo "Example:\n";
    echo "  php scripts/restore-database.php " . basename($backupFiles[0]) . "\n\n";
    exit(0);
}

$backupFilename = $argv[1];
$backupFilepath = $backupDir . '/' . $backupFilename;

// Check if backup file exists
if (!file_exists($backupFilepath)) {
    echo "✗ Error: Backup file not found: {$backupFilepath}\n\n";
    exit(1);
}

$filesize = filesize($backupFilepath);
$filesizeMB = round($filesize / 1024 / 1024, 2);

echo "Database: {$database}\n";
echo "Backup file: {$backupFilename}\n";
echo "File size: {$filesizeMB} MB\n";
echo "File date: " . date('Y-m-d H:i:s', filemtime($backupFilepath)) . "\n";
echo str_repeat('=', 60) . "\n\n";

// Confirmation
echo "⚠️  WARNING: This will OVERWRITE the current database!\n";
echo "   All current data will be LOST!\n\n";
echo "Type 'YES' to confirm restore: ";

$handle = fopen("php://stdin", "r");
$confirmation = trim(fgets($handle));
fclose($handle);

if ($confirmation !== 'YES') {
    echo "\n✗ Restore cancelled.\n\n";
    exit(0);
}

echo "\n→ Starting database restore...\n";

// Build mysql command
$command = sprintf(
    'mysql --host=%s --user=%s %s %s < %s 2>&1',
    escapeshellarg($host),
    escapeshellarg($username),
    $password ? '--password=' . escapeshellarg($password) : '',
    escapeshellarg($database),
    escapeshellarg($backupFilepath)
);

// Execute restore
$output = [];
$returnCode = 0;
exec($command, $output, $returnCode);

if ($returnCode === 0) {
    echo "✓ Database restored successfully!\n\n";

    // Log success
    logRestore('success', $backupFilename);

    echo str_repeat('=', 60) . "\n";
    echo "✓ Restore completed!\n";
    echo str_repeat('=', 60) . "\n\n";

} else {
    echo "✗ Restore failed!\n";
    if (!empty($output)) {
        echo "  Error: " . implode("\n", $output) . "\n\n";
    }

    // Log failure
    logRestore('error', $backupFilename, implode("\n", $output));

    exit(1);
}

/**
 * Log restore operation
 */
function logRestore(string $status, string $filename, string $error = ''): void
{
    $logDir = __DIR__ . '/../storage/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/backup_' . date('Y-m-d') . '.log';

    $logEntry = sprintf(
        "[%s] [RESTORE-%s] %s%s\n",
        date('Y-m-d H:i:s'),
        strtoupper($status),
        $filename,
        $error ? ' | Error: ' . $error : ''
    );

    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

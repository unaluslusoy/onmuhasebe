#!/usr/bin/env php
<?php

/**
 * Email Queue Processor
 * Process pending emails from the queue
 *
 * Usage:
 *   php scripts/process-email-queue.php [limit]
 *
 * Cron example (every 5 minutes):
 *   */5 * * * * cd /path/to/onmuhasebe && php scripts/process-email-queue.php >> logs/email-queue.log 2>&1
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\Notification\EmailService;
use App\Helpers\Logger;

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Get limit from command line argument (default: 10)
$limit = isset($argv[1]) ? (int)$argv[1] : 10;

echo "=================================================\n";
echo "Email Queue Processor\n";
echo "=================================================\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n";
echo "Batch limit: {$limit}\n";
echo "-------------------------------------------------\n";

try {
    $emailService = new EmailService();
    $stats = $emailService->processQueue($limit);

    echo "\nResults:\n";
    echo "  Processed: {$stats['processed']}\n";
    echo "  Sent:      {$stats['sent']}\n";
    echo "  Failed:    {$stats['failed']}\n";
    echo "-------------------------------------------------\n";
    echo "Completed at: " . date('Y-m-d H:i:s') . "\n";
    echo "=================================================\n";

    // Exit with error code if there were failures
    exit($stats['failed'] > 0 ? 1 : 0);

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "=================================================\n";

    Logger::error('Email queue processing failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);

    exit(1);
}

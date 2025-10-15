<?php

/**
 * Application Bootstrap
 * Entry point for all requests
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Set UTF-8 encoding for all responses
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

// Error reporting (after .env is loaded)
error_reporting(E_ALL);
ini_set('display_errors', config('app.debug') ? '1' : '0');

// Set timezone
date_default_timezone_set(config('app.timezone', 'Europe/Istanbul'));

// Set error handler
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Set exception handler
set_exception_handler(function ($exception) {
    error_log('Exception: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());
    error_log($exception->getTraceAsString());
    
    logger('Exception: ' . $exception->getMessage(), 'error', [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);

    if (config('app.debug')) {
        echo "<pre>";
        echo "ERROR: " . $exception->getMessage() . "\n";
        echo "File: " . $exception->getFile() . ":" . $exception->getLine() . "\n";
        echo "Trace:\n" . $exception->getTraceAsString();
        echo "</pre>";
        exit(1);
    } else {
        \App\Helpers\Response::serverError('An error occurred');
    }
});

// Start session if not in API mode
$requestUri = $_SERVER['REQUEST_URI'] ?? '';

// Serve storage files (for PHP built-in server)
if (preg_match('#^/storage/uploads/(.+)$#', $requestUri, $matches)) {
    $relativePath = $matches[1];
    $filePath = __DIR__ . '/../storage/uploads/' . $relativePath;
    
    if (file_exists($filePath) && is_file($filePath)) {
        $mimeType = mime_content_type($filePath);
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        // Set proper MIME type
        $mimeTypes = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml'
        ];
        
        $contentType = $mimeTypes[$extension] ?? $mimeType;
        
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: public, max-age=86400');
        readfile($filePath);
        exit;
    }
    
    http_response_code(404);
    echo '404 Not Found';
    exit;
}

if (!str_starts_with($requestUri, '/api/')) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Create router instance
$router = new App\Config\Router();

// Load routes
require_once __DIR__ . '/../app/Config/routes.php';

// Dispatch request
$router->dispatch();

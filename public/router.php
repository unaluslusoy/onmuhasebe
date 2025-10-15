<?php

/**
 * Router for PHP Built-in Server
 * This handles static files and custom routes
 */

$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Serve storage files
if (preg_match('#^/storage/uploads/(.+)$#', $requestPath, $matches)) {
    $relativePath = $matches[1];
    // Use DIRECTORY_SEPARATOR for cross-platform compatibility
    $filePath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    
    // Debug: Log file path
    error_log("Router: Requested path: $requestPath");
    error_log("Router: File path: $filePath");
    error_log("Router: File exists: " . (file_exists($filePath) ? 'yes' : 'no'));
    
    if (file_exists($filePath) && is_file($filePath)) {
        // Get mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        // Set headers
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: public, max-age=31536000');
        
        // Output file
        readfile($filePath);
        return true;
    }
    
    // File not found
    http_response_code(404);
    error_log("Router: File not found - $filePath");
    echo "File not found: $requestPath (searched: $filePath)";
    return true;
}

// Check if the requested file exists in public directory
$publicFile = __DIR__ . $requestPath;

// Serve static files directly
if (file_exists($publicFile) && is_file($publicFile)) {
    return false; // Let PHP built-in server handle it
}

// All other requests go through index.php
require_once __DIR__ . '/index.php';
return true;

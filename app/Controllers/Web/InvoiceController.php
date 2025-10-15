<?php

namespace App\Controllers\Web;

use App\Helpers\Response;

class InvoiceController
{
    /**
     * Show invoice list page
     */
    public function index()
    {
        // Authentication handled by WebAuthMiddleware
        require_once __DIR__ . '/../../Views/invoices/list.php';
    }
    
    /**
     * Show invoice create form
     */
    public function create()
    {
        // Authentication handled by WebAuthMiddleware
        require_once __DIR__ . '/../../Views/invoices/form.php';
    }
    
    /**
     * Show invoice edit form
     */
    public function edit($params)
    {
        // Authentication handled by WebAuthMiddleware
        // Extract ID from params array
        $invoiceId = is_array($params) ? ($params['id'] ?? '') : $params;
        
        require_once __DIR__ . '/../../Views/invoices/form.php';
    }
    
    /**
     * Show invoice detail page
     */
    public function show($params)
    {
        // Authentication handled by WebAuthMiddleware
        // Extract ID from params array
        $invoiceId = is_array($params) ? ($params['id'] ?? '') : $params;
        
        require_once __DIR__ . '/../../Views/invoices/detail.php';
    }
}

<?php

/**
 * Application Routes
 * Define all application routes here
 */

use App\Controllers\Auth\AuthController;
use App\Controllers\SubscriptionController;
use App\Controllers\Web\CompanyController;
use App\Controllers\Web\CariController;
use App\Controllers\Web\ProductController;
use App\Controllers\WarehouseController;
use App\Controllers\StockMovementController;
use App\Controllers\StockTransferController;
use App\Controllers\StockCountController;
use App\Controllers\QuotationController;
use App\Controllers\InvoiceController;
use App\Middleware\AuthMiddleware;

// ============================================
// API Routes
// ============================================
$router->group(['prefix' => '/api'], function ($router) {
    
    // Health check
    $router->get('/health', function () {
        \App\Helpers\Response::success([
            'status' => 'ok',
            'version' => '1.0.0',
            'timestamp' => time()
        ]);
    });

    // Authentication routes
    $router->post('/auth/register', [AuthController::class, 'register']);
    $router->post('/auth/login', [AuthController::class, 'login']);
    $router->post('/auth/refresh', [AuthController::class, 'refresh']);
    
    // Protected routes
    $router->group(['middleware' => [AuthMiddleware::class]], function ($router) {
        $router->get('/auth/me', [AuthController::class, 'me']);
        $router->post('/auth/logout', [AuthController::class, 'logout']);
        $router->post('/auth/change-password', [AuthController::class, 'changePassword']);
        $router->post('/auth/create-session', [AuthController::class, 'createSession']);
        
        // Subscription routes
        $router->get('/subscriptions/current', [SubscriptionController::class, 'current']);
        $router->get('/subscriptions/plans', [SubscriptionController::class, 'plans']);
        $router->post('/subscriptions/upgrade', [SubscriptionController::class, 'upgrade']);
        $router->post('/subscriptions/cancel', [SubscriptionController::class, 'cancel']);
        
        // Company routes
        $router->get('/companies', [CompanyController::class, 'index']);
        $router->get('/company/me', [CompanyController::class, 'me']);
        $router->get('/companies/{id}', [CompanyController::class, 'show']);
        $router->post('/companies', [CompanyController::class, 'store']);
        $router->put('/companies/{id}', [CompanyController::class, 'update']);
        $router->delete('/companies/{id}', [CompanyController::class, 'delete']);
        $router->post('/companies/{id}/logo', [CompanyController::class, 'uploadLogo']);
        
        // Cari (Customer/Supplier) routes
        $router->get('/cari', [CariController::class, 'index']);
        $router->get('/cari/stats', [CariController::class, 'stats']);
        $router->get('/cari/overdue', [CariController::class, 'overdue']);
        $router->get('/cari/{id}', [CariController::class, 'show']);
        $router->get('/cari/code/{code}', [CariController::class, 'showByCode']);
        $router->post('/cari', [CariController::class, 'store']);
        $router->put('/cari/{id}', [CariController::class, 'update']);
        $router->delete('/cari/{id}', [CariController::class, 'delete']);
        $router->get('/cari/{id}/balance', [CariController::class, 'balance']);
        $router->get('/cari/{id}/statement', [CariController::class, 'statement']);
        
        // Product routes
        $router->get('/products', [ProductController::class, 'index']);
        $router->get('/products/stats', [ProductController::class, 'stats']);
        $router->get('/products/search', [ProductController::class, 'search']);
        $router->get('/products/low-stock', [ProductController::class, 'lowStock']);
        $router->get('/products/out-of-stock', [ProductController::class, 'outOfStock']);
        $router->get('/products/barcode/{barcode}', [ProductController::class, 'findByBarcode']);
        $router->get('/products/{id}', [ProductController::class, 'show']);
        $router->post('/products', [ProductController::class, 'store']);
        $router->put('/products/{id}', [ProductController::class, 'update']);
        $router->put('/products/{id}/stock', [ProductController::class, 'updateStock']);
        $router->delete('/products/{id}', [ProductController::class, 'delete']);
        
        // Product Categories routes
        $router->get('/product-categories', [ProductController::class, 'categories']);
        $router->get('/product-categories/{id}', [ProductController::class, 'showCategory']);
        $router->post('/product-categories', [ProductController::class, 'storeCategory']);
        $router->put('/product-categories/{id}', [ProductController::class, 'updateCategory']);
        $router->delete('/product-categories/{id}', [ProductController::class, 'deleteCategory']);
        
        // Warehouse routes
        $router->get('/warehouses', [WarehouseController::class, 'index']);
        $router->get('/warehouses/statistics', [WarehouseController::class, 'statistics']);
        $router->get('/warehouses/{id}', [WarehouseController::class, 'show']);
        $router->post('/warehouses', [WarehouseController::class, 'store']);
        $router->put('/warehouses/{id}', [WarehouseController::class, 'update']);
        $router->delete('/warehouses/{id}', [WarehouseController::class, 'destroy']);
        $router->get('/warehouses/{id}/locations', [WarehouseController::class, 'locations']);
        $router->post('/warehouses/{id}/locations', [WarehouseController::class, 'createLocation']);
        $router->post('/warehouses/{id}/set-default', [WarehouseController::class, 'setDefault']);
        
        // Stock Movement routes
        $router->get('/stock/movements', [StockMovementController::class, 'index']);
        $router->get('/stock/movements/statistics', [StockMovementController::class, 'statistics']);
        $router->get('/stock/movements/current-stock', [StockMovementController::class, 'currentStock']);
        $router->get('/stock/movements/history', [StockMovementController::class, 'history']);
        $router->get('/stock/movements/by-reference', [StockMovementController::class, 'byReference']);
        $router->get('/stock/movements/fifo-cost', [StockMovementController::class, 'fifoCost']);
        $router->get('/stock/movements/lifo-cost', [StockMovementController::class, 'lifoCost']);
        $router->get('/stock/movements/low-stock', [StockMovementController::class, 'lowStock']);
        $router->get('/stock/movements/stock-value', [StockMovementController::class, 'stockValue']);
        $router->get('/stock/movements/{id}', [StockMovementController::class, 'show']);
        $router->post('/stock/movements', [StockMovementController::class, 'store']);
        
        // Stock Transfer routes
        $router->get('/stock/transfers', [StockTransferController::class, 'index']);
        $router->get('/stock/transfers/statistics', [StockTransferController::class, 'statistics']);
        $router->get('/stock/transfers/{id}', [StockTransferController::class, 'show']);
        $router->post('/stock/transfers', [StockTransferController::class, 'store']);
        $router->put('/stock/transfers/{id}', [StockTransferController::class, 'update']);
        $router->post('/stock/transfers/{id}/approve', [StockTransferController::class, 'approve']);
        $router->post('/stock/transfers/{id}/ship', [StockTransferController::class, 'ship']);
        $router->post('/stock/transfers/{id}/receive', [StockTransferController::class, 'receive']);
        $router->post('/stock/transfers/{id}/cancel', [StockTransferController::class, 'cancel']);
        
        // Stock Count routes
        $router->get('/stock/counts', [StockCountController::class, 'index']);
        $router->get('/stock/counts/statistics', [StockCountController::class, 'statistics']);
        $router->get('/stock/counts/{id}', [StockCountController::class, 'show']);
        $router->get('/stock/counts/{id}/variances', [StockCountController::class, 'variances']);
        $router->post('/stock/counts', [StockCountController::class, 'store']);
        $router->post('/stock/counts/{id}/start', [StockCountController::class, 'start']);
        $router->post('/stock/counts/{id}/update-counts', [StockCountController::class, 'updateCounts']);
        $router->post('/stock/counts/{id}/complete', [StockCountController::class, 'complete']);
        $router->post('/stock/counts/{id}/verify', [StockCountController::class, 'verify']);
        $router->post('/stock/counts/{id}/approve', [StockCountController::class, 'approve']);
        $router->post('/stock/counts/{id}/cancel', [StockCountController::class, 'cancel']);
        
        // Quotation routes
        $router->get('/quotations', [QuotationController::class, 'index']);
        $router->get('/quotations/statistics', [QuotationController::class, 'statistics']);
        $router->get('/quotations/by-status/{status}', [QuotationController::class, 'byStatus']);
        $router->get('/quotations/{id}', [QuotationController::class, 'show']);
        $router->post('/quotations', [QuotationController::class, 'store']);
        $router->put('/quotations/{id}', [QuotationController::class, 'update']);
        $router->delete('/quotations/{id}', [QuotationController::class, 'destroy']);
        $router->post('/quotations/{id}/send', [QuotationController::class, 'send']);
        $router->post('/quotations/{id}/accept', [QuotationController::class, 'accept']);
        $router->post('/quotations/{id}/reject', [QuotationController::class, 'reject']);
        $router->post('/quotations/{id}/duplicate', [QuotationController::class, 'duplicate']);
        
        // Invoice routes
        $router->get('/invoices', [InvoiceController::class, 'index']);
        $router->get('/invoices/overdue', [InvoiceController::class, 'getOverdue']);
        $router->get('/invoices/due-today', [InvoiceController::class, 'getDueToday']);
        $router->get('/invoices/statistics', [InvoiceController::class, 'getStatistics']);
        $router->get('/invoices/monthly-summary', [InvoiceController::class, 'getMonthlySummary']);
        $router->get('/invoices/recurring', [InvoiceController::class, 'getRecurring']);
        $router->post('/invoices/process-recurring', [InvoiceController::class, 'processRecurring']);
        $router->post('/invoices/convert-from-quotation/{id}', [InvoiceController::class, 'convertFromQuotation']);
        $router->get('/invoices/{id}', [InvoiceController::class, 'show']);
        $router->post('/invoices', [InvoiceController::class, 'store']);
        $router->put('/invoices/{id}', [InvoiceController::class, 'update']);
        $router->delete('/invoices/{id}', [InvoiceController::class, 'destroy']);
        $router->post('/invoices/{id}/approve', [InvoiceController::class, 'approve']);
        $router->post('/invoices/{id}/cancel', [InvoiceController::class, 'cancel']);
        $router->post('/invoices/{id}/lock', [InvoiceController::class, 'lock']);
        $router->get('/invoices/{id}/payments', [InvoiceController::class, 'getPayments']);
        $router->post('/invoices/{id}/payments', [InvoiceController::class, 'recordPayment']);
    });
});

// ============================================
// Web Routes (UI)
// ============================================

use App\Controllers\Web\PageController;
use App\Controllers\Web\AuthController as WebAuthController;
use App\Controllers\Web\DashboardController;

// Homepage (redirects to dashboard - handled by protected routes)

// Authentication pages
$router->get('/login', [WebAuthController::class, 'loginPage']);
$router->get('/register', [WebAuthController::class, 'registerPage']);

// Module routes (will be added later)
// Cari Yönetimi
// $router->get('/cari/liste', [CariController::class, 'index']);
// $router->get('/cari/ekle', [CariController::class, 'create']);

// Faturalar
// $router->get('/fatura/satis', [InvoiceController::class, 'sales']);
// $router->get('/fatura/alis', [InvoiceController::class, 'purchase']);

// Kasa/Banka
// $router->get('/kasa/liste', [CashController::class, 'index']);
// $router->get('/banka/liste', [BankController::class, 'index']);

// ============================================
// Auth Web Routes (Public Pages - No Auth Required)
// ============================================
$router->get('/giris', [\App\Controllers\Web\AuthController::class, 'loginPage']);
$router->get('/kayit', [\App\Controllers\Web\AuthController::class, 'registerPage']);
$router->get('/cikis', [\App\Controllers\Web\AuthController::class, 'logout']);

// Debug route (remove in production)
$router->get('/test-session', function() {
    require_once __DIR__ . '/../../public/test-session.php';
});

// ============================================
// Protected Web Routes (Require Session)
// ============================================
$router->group(['middleware' => [\App\Middleware\WebAuthMiddleware::class]], function ($router) {
    
    // Dashboard
    $router->get('/', [\App\Controllers\Web\DashboardController::class, 'index']);
    
    // Profile Routes
    $router->get('/profil', [\App\Controllers\Admin\ProfileController::class, 'index']);
    $router->get('/profil/duzenle', [\App\Controllers\Admin\ProfileController::class, 'edit']);
    $router->get('/profil/ayarlar', [\App\Controllers\Admin\ProfileController::class, 'settings']);
    $router->post('/profil/guncelle', [\App\Controllers\Admin\ProfileController::class, 'update']);
    $router->post('/profil/sifre-degistir', [\App\Controllers\Admin\ProfileController::class, 'changePassword']);
    $router->post('/profil/sirket-guncelle', [\App\Controllers\Admin\ProfileController::class, 'updateCompany']);
    $router->post('/profil/hesap-kapat', [\App\Controllers\Admin\ProfileController::class, 'deactivate']);
    
    // Settings Routes
    $router->get('/ayarlar/genel', [\App\Controllers\Admin\SettingsController::class, 'genel']);
    $router->get('/ayarlar/sirket', [\App\Controllers\Admin\SettingsController::class, 'sirket']);
    $router->get('/ayarlar/guvenlik', [\App\Controllers\Web\SettingsController::class, 'guvenlik']);
    // Redirect /ayarlar/firma to /ayarlar/sirket (merged pages)
    $router->get('/ayarlar/firma', function() {
        header('Location: /ayarlar/sirket');
        exit;
    });
    $router->get('/ayarlar/kullanicilar', [\App\Controllers\Web\SettingsController::class, 'kullanicilar']);
    $router->get('/ayarlar/kategoriler', [\App\Controllers\Web\SettingsController::class, 'kategoriler']);
    $router->post('/ayarlar/genel/guncelle', [\App\Controllers\Admin\SettingsController::class, 'updateGenel']);
    $router->post('/ayarlar/sirket/guncelle', [\App\Controllers\Admin\SettingsController::class, 'updateSirket']);
    $router->post('/ayarlar/sirket/gorseller', [\App\Controllers\Admin\SettingsController::class, 'updateCompanyImages']);
    // Keep old endpoint for backward compatibility (redirects to /ayarlar/sirket/guncelle)
    $router->post('/ayarlar/firma/guncelle', [\App\Controllers\Admin\SettingsController::class, 'updateSirket']);
    $router->post('/ayarlar/sifre-degistir', [\App\Controllers\Web\SettingsController::class, 'changePassword']);
    $router->post('/ayarlar/hesap-kapat', [\App\Controllers\Web\SettingsController::class, 'deactivateAccount']);
    
    // Company Registration Routes
    $router->get('/sirket/olustur', [\App\Controllers\Web\CompanyRegisterController::class, 'create']);
    $router->post('/sirket/kaydet', [\App\Controllers\Web\CompanyRegisterController::class, 'store']);
    
    // Invoice Routes
    $router->get('/faturalar', [\App\Controllers\Web\InvoiceController::class, 'index']);
    $router->get('/fatura/olustur', [\App\Controllers\Web\InvoiceController::class, 'create']);
    $router->get('/fatura/duzenle/{id}', [\App\Controllers\Web\InvoiceController::class, 'edit']);
    $router->get('/fatura/{id}', [\App\Controllers\Web\InvoiceController::class, 'show']);
    
    // Cari Routes (placeholder - will be implemented later)
    $router->get('/cari/liste', function() {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Cari modülü henüz tamamlanmadı. Yakında eklenecek!', 'timestamp' => time()], JSON_UNESCAPED_UNICODE);
    });
    $router->get('/cari/ekle', function() {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Cari modülü henüz tamamlanmadı. Yakında eklenecek!', 'timestamp' => time()], JSON_UNESCAPED_UNICODE);
    });
    
    // Kasa/Banka Routes (placeholder)
    $router->get('/kasa/liste', function() {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Kasa modülü henüz tamamlanmadı. Yakında eklenecek!', 'timestamp' => time()], JSON_UNESCAPED_UNICODE);
    });
    $router->get('/banka/liste', function() {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Banka modülü henüz tamamlanmadı. Yakında eklenecek!', 'timestamp' => time()], JSON_UNESCAPED_UNICODE);
    });
    
});

return $router;

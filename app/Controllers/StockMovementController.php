<?php

namespace App\Controllers;

use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\Product;
use App\Helpers\Response;

/**
 * Stock Movement Controller
 * Handles stock movements and inventory transactions
 */
class StockMovementController
{
    private StockMovement $movementModel;
    private Warehouse $warehouseModel;
    private Product $productModel;

    public function __construct()
    {
        $this->movementModel = new StockMovement();
        $this->warehouseModel = new Warehouse();
        $this->productModel = new Product();
    }

    /**
     * Get all stock movements
     * GET /api/stock/movements
     */
    public function index(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $filters = [
            'warehouse_id' => !empty($_GET['warehouse_id']) ? (int)$_GET['warehouse_id'] : null,
            'product_id' => !empty($_GET['product_id']) ? (int)$_GET['product_id'] : null,
            'movement_type' => $_GET['movement_type'] ?? '',
            'reference_type' => $_GET['reference_type'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'batch_number' => $_GET['batch_number'] ?? ''
        ];

        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 50);

        $result = $this->movementModel->getAll($user['company_id'], $filters, $page, $perPage);

        Response::success($result);
    }

    /**
     * Get movement by ID
     * GET /api/stock/movements/:id
     */
    public function show(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Movement ID is required');
        }

        $movement = $this->movementModel->find($id);

        if (!$movement || $movement['company_id'] != $user['company_id']) {
            Response::notFound('Movement not found');
        }

        Response::success($movement);
    }

    /**
     * Create stock movement
     * POST /api/stock/movements
     */
    public function store(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        // Validation
        $errors = [];
        if (empty($input['warehouse_id'])) {
            $errors['warehouse_id'] = 'Warehouse is required';
        }
        if (empty($input['product_id'])) {
            $errors['product_id'] = 'Product is required';
        }
        if (empty($input['movement_type'])) {
            $errors['movement_type'] = 'Movement type is required';
        }
        if (!isset($input['quantity']) || $input['quantity'] <= 0) {
            $errors['quantity'] = 'Quantity must be greater than zero';
        }

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        // Verify warehouse exists
        $warehouse = $this->warehouseModel->findByCompany($input['warehouse_id'], $user['company_id']);
        if (!$warehouse) {
            Response::badRequest('Warehouse not found');
        }

        // Verify product exists
        $product = $this->productModel->find($input['product_id']);
        if (!$product || $product['company_id'] != $user['company_id']) {
            Response::badRequest('Product not found');
        }

        // Check stock availability for outbound movements
        $outboundTypes = [
            StockMovement::TYPE_SALE_OUT,
            StockMovement::TYPE_TRANSFER_OUT,
            StockMovement::TYPE_PRODUCTION_OUT,
            StockMovement::TYPE_ADJUSTMENT_OUT,
            StockMovement::TYPE_DAMAGE_OUT,
            StockMovement::TYPE_SAMPLE_OUT
        ];

        if (in_array($input['movement_type'], $outboundTypes)) {
            $currentStock = $this->movementModel->getCurrentStock(
                $input['product_id'],
                $input['warehouse_id'],
                $input['variant_id'] ?? null
            );

            if ($currentStock < $input['quantity'] && !$warehouse['allow_negative_stock']) {
                Response::badRequest("Insufficient stock. Available: {$currentStock}");
            }
        }

        // Prepare data
        $data = [
            'company_id' => $user['company_id'],
            'warehouse_id' => $input['warehouse_id'],
            'location_id' => $input['location_id'] ?? null,
            'product_id' => $input['product_id'],
            'variant_id' => $input['variant_id'] ?? null,
            'movement_type' => $input['movement_type'],
            'movement_date' => $input['movement_date'] ?? date('Y-m-d H:i:s'),
            'quantity' => $input['quantity'],
            'unit' => $input['unit'] ?? 'Adet',
            'unit_cost' => $input['unit_cost'] ?? 0,
            'total_cost' => ($input['unit_cost'] ?? 0) * $input['quantity'],
            'cost_method' => $input['cost_method'] ?? 'fifo',
            'batch_number' => $input['batch_number'] ?? null,
            'lot_number_id' => $input['lot_number_id'] ?? null,
            'reference_type' => $input['reference_type'] ?? null,
            'reference_id' => $input['reference_id'] ?? null,
            'reference_number' => $input['reference_number'] ?? null,
            'from_warehouse_id' => $input['from_warehouse_id'] ?? null,
            'to_warehouse_id' => $input['to_warehouse_id'] ?? null,
            'notes' => $input['notes'] ?? null,
            'created_by' => $user['id']
        ];

        $movementId = $this->movementModel->createMovement($data);

        if (!$movementId) {
            Response::serverError('Failed to create movement');
        }

        $movement = $this->movementModel->find($movementId);

        Response::created($movement, 'Stock movement created successfully');
    }

    /**
     * Get current stock for product
     * GET /api/stock/movements/current-stock
     */
    public function currentStock(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $productId = (int)($_GET['product_id'] ?? 0);
        $warehouseId = !empty($_GET['warehouse_id']) ? (int)$_GET['warehouse_id'] : null;
        $variantId = !empty($_GET['variant_id']) ? (int)$_GET['variant_id'] : null;

        if (!$productId) {
            Response::badRequest('Product ID is required');
        }

        if ($warehouseId) {
            $stock = $this->movementModel->getCurrentStock($productId, $warehouseId, $variantId);
            Response::success([
                'warehouse_id' => $warehouseId,
                'current_stock' => $stock
            ]);
        } else {
            $stocks = $this->movementModel->getTotalStock($productId, $user['company_id'], $variantId);
            Response::success([
                'warehouses' => $stocks,
                'total_stock' => array_sum(array_column($stocks, 'current_stock'))
            ]);
        }
    }

    /**
     * Get product movement history
     * GET /api/stock/movements/history
     */
    public function history(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $productId = (int)($_GET['product_id'] ?? 0);
        if (!$productId) {
            Response::badRequest('Product ID is required');
        }

        $warehouseId = !empty($_GET['warehouse_id']) ? (int)$_GET['warehouse_id'] : null;
        $variantId = !empty($_GET['variant_id']) ? (int)$_GET['variant_id'] : null;
        $dateRange = [
            'from' => $_GET['date_from'] ?? '',
            'to' => $_GET['date_to'] ?? ''
        ];

        $history = $this->movementModel->getProductHistory(
            $productId,
            $user['company_id'],
            $warehouseId,
            $variantId,
            $dateRange
        );

        Response::success(['movements' => $history]);
    }

    /**
     * Get movements by reference
     * GET /api/stock/movements/by-reference
     */
    public function byReference(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $referenceType = $_GET['reference_type'] ?? '';
        $referenceId = (int)($_GET['reference_id'] ?? 0);

        if (!$referenceType || !$referenceId) {
            Response::badRequest('Reference type and ID are required');
        }

        $movements = $this->movementModel->getByReference($referenceType, $referenceId, $user['company_id']);

        Response::success(['movements' => $movements]);
    }

    /**
     * Calculate FIFO cost
     * GET /api/stock/movements/fifo-cost
     */
    public function fifoCost(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $productId = (int)($_GET['product_id'] ?? 0);
        $warehouseId = (int)($_GET['warehouse_id'] ?? 0);
        $quantity = (float)($_GET['quantity'] ?? 0);
        $variantId = !empty($_GET['variant_id']) ? (int)$_GET['variant_id'] : null;

        if (!$productId || !$warehouseId || $quantity <= 0) {
            Response::badRequest('Product ID, warehouse ID, and quantity are required');
        }

        $fifoData = $this->movementModel->getFIFOCost($productId, $warehouseId, $quantity, $variantId);

        Response::success($fifoData);
    }

    /**
     * Calculate LIFO cost
     * GET /api/stock/movements/lifo-cost
     */
    public function lifoCost(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $productId = (int)($_GET['product_id'] ?? 0);
        $warehouseId = (int)($_GET['warehouse_id'] ?? 0);
        $quantity = (float)($_GET['quantity'] ?? 0);
        $variantId = !empty($_GET['variant_id']) ? (int)$_GET['variant_id'] : null;

        if (!$productId || !$warehouseId || $quantity <= 0) {
            Response::badRequest('Product ID, warehouse ID, and quantity are required');
        }

        $lifoData = $this->movementModel->getLIFOCost($productId, $warehouseId, $quantity, $variantId);

        Response::success($lifoData);
    }

    /**
     * Get low stock products
     * GET /api/stock/movements/low-stock
     */
    public function lowStock(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $warehouseId = !empty($_GET['warehouse_id']) ? (int)$_GET['warehouse_id'] : null;

        $products = $this->movementModel->getLowStockProducts($user['company_id'], $warehouseId);

        Response::success(['products' => $products]);
    }

    /**
     * Get stock value by warehouse
     * GET /api/stock/movements/stock-value
     */
    public function stockValue(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $warehouseId = (int)($_GET['warehouse_id'] ?? 0);
        if (!$warehouseId) {
            Response::badRequest('Warehouse ID is required');
        }

        $value = $this->movementModel->getStockValue($warehouseId, $user['company_id']);

        Response::success($value);
    }

    /**
     * Get movement statistics
     * GET /api/stock/movements/statistics
     */
    public function statistics(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $dateRange = [
            'from' => $_GET['date_from'] ?? '',
            'to' => $_GET['date_to'] ?? ''
        ];

        $stats = $this->movementModel->getStatistics($user['company_id'], $dateRange);

        Response::success(['statistics' => $stats]);
    }
}

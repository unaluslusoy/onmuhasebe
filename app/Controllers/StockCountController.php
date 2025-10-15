<?php

namespace App\Controllers;

use App\Models\StockCount;
use App\Models\Warehouse;
use App\Helpers\Response;

/**
 * Stock Count Controller
 * Handles inventory counting and variance management
 */
class StockCountController
{
    private StockCount $countModel;
    private Warehouse $warehouseModel;

    public function __construct()
    {
        $this->countModel = new StockCount();
        $this->warehouseModel = new Warehouse();
    }

    /**
     * Get all counts
     * GET /api/stock/counts
     */
    public function index(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $filters = [
            'status' => $_GET['status'] ?? '',
            'warehouse_id' => !empty($_GET['warehouse_id']) ? (int)$_GET['warehouse_id'] : null,
            'count_type' => $_GET['count_type'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 25);

        $result = $this->countModel->getAll($user['company_id'], $filters, $page, $perPage);

        Response::success($result);
    }

    /**
     * Get count by ID
     * GET /api/stock/counts/:id
     */
    public function show(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Count ID is required');
        }

        $count = $this->countModel->findWithItems($id, $user['company_id']);

        if (!$count) {
            Response::notFound('Count not found');
        }

        // Get variance summary
        $count['variance_summary'] = $this->countModel->getVarianceSummary($id);

        Response::success($count);
    }

    /**
     * Create count
     * POST /api/stock/counts
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
        if (empty($input['count_type'])) {
            $errors['count_type'] = 'Count type is required';
        }

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        // Verify warehouse exists
        $warehouse = $this->warehouseModel->findByCompany($input['warehouse_id'], $user['company_id']);
        if (!$warehouse) {
            Response::badRequest('Warehouse not found');
        }

        // Prepare count data
        $countData = [
            'company_id' => $user['company_id'],
            'count_date' => $input['count_date'] ?? date('Y-m-d'),
            'warehouse_id' => $input['warehouse_id'],
            'count_type' => $input['count_type'],
            'status' => StockCount::STATUS_DRAFT,
            'notes' => $input['notes'] ?? null
        ];

        // Items are optional at creation - will be loaded when starting count
        $items = $input['items'] ?? [];

        $countId = $this->countModel->createWithItems($countData, $items);

        if (!$countId) {
            Response::serverError('Failed to create count');
        }

        $count = $this->countModel->findWithItems($countId, $user['company_id']);

        Response::created($count, 'Count created successfully');
    }

    /**
     * Start count (load current stock)
     * POST /api/stock/counts/:id/start
     */
    public function start(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Count ID is required');
        }

        // Get product IDs for partial count (optional)
        $input = json_decode(file_get_contents('php://input'), true);
        $productIds = $input['product_ids'] ?? [];

        $success = $this->countModel->start($id, $user['company_id'], $productIds);

        if (!$success) {
            Response::badRequest('Count cannot be started. Check status and try again.');
        }

        $count = $this->countModel->findWithItems($id, $user['company_id']);

        Response::success($count, 'Count started successfully. Stock loaded.');
    }

    /**
     * Update counted quantities
     * POST /api/stock/counts/:id/update-counts
     */
    public function updateCounts(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Count ID is required');
        }

        // Get counted quantities
        $input = json_decode(file_get_contents('php://input'), true);
        $counts = $input['counts'] ?? [];

        if (empty($counts)) {
            Response::badRequest('No counts provided');
        }

        $success = $this->countModel->updateCounts($id, $user['company_id'], $counts);

        if (!$success) {
            Response::badRequest('Failed to update counts. Check status and try again.');
        }

        $count = $this->countModel->findWithItems($id, $user['company_id']);

        Response::success($count, 'Counts updated successfully');
    }

    /**
     * Complete count
     * POST /api/stock/counts/:id/complete
     */
    public function complete(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Count ID is required');
        }

        $success = $this->countModel->complete($id, $user['company_id'], $user['id']);

        if (!$success) {
            Response::badRequest('Count cannot be completed. Check status and try again.');
        }

        $count = $this->countModel->findWithItems($id, $user['company_id']);
        $count['variance_summary'] = $this->countModel->getVarianceSummary($id);

        Response::success($count, 'Count completed successfully');
    }

    /**
     * Verify count
     * POST /api/stock/counts/:id/verify
     */
    public function verify(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Count ID is required');
        }

        $success = $this->countModel->verify($id, $user['company_id'], $user['id']);

        if (!$success) {
            Response::badRequest('Count cannot be verified. Check status and try again.');
        }

        $count = $this->countModel->findWithItems($id, $user['company_id']);
        $count['variance_summary'] = $this->countModel->getVarianceSummary($id);

        Response::success($count, 'Count verified successfully');
    }

    /**
     * Approve count and create adjustments
     * POST /api/stock/counts/:id/approve
     */
    public function approve(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Count ID is required');
        }

        $success = $this->countModel->approve($id, $user['company_id'], $user['id']);

        if (!$success) {
            Response::badRequest('Count cannot be approved. Check status and try again.');
        }

        $count = $this->countModel->findWithItems($id, $user['company_id']);
        $count['variance_summary'] = $this->countModel->getVarianceSummary($id);

        Response::success($count, 'Count approved and stock adjustments created successfully');
    }

    /**
     * Cancel count
     * POST /api/stock/counts/:id/cancel
     */
    public function cancel(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Count ID is required');
        }

        // Get cancellation reason
        $input = json_decode(file_get_contents('php://input'), true);
        $reason = $input['reason'] ?? 'Cancelled by user';

        $success = $this->countModel->cancel($id, $user['company_id'], $user['id'], $reason);

        if (!$success) {
            Response::badRequest('Count cannot be cancelled. Check status and try again.');
        }

        $count = $this->countModel->findWithItems($id, $user['company_id']);

        Response::success($count, 'Count cancelled successfully');
    }

    /**
     * Get variance items only
     * GET /api/stock/counts/:id/variances
     */
    public function variances(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Count ID is required');
        }

        // Verify count exists
        $count = $this->countModel->findByCompany($id, $user['company_id']);
        if (!$count) {
            Response::notFound('Count not found');
        }

        $varianceItems = $this->countModel->getVarianceItems($id);
        $varianceSummary = $this->countModel->getVarianceSummary($id);

        Response::success([
            'items' => $varianceItems,
            'summary' => $varianceSummary
        ]);
    }

    /**
     * Get count statistics
     * GET /api/stock/counts/statistics
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

        $stats = $this->countModel->getStatistics($user['company_id'], $dateRange);

        Response::success(['statistics' => $stats]);
    }
}

<?php

namespace App\Controllers;

use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Helpers\Response;

/**
 * Stock Transfer Controller
 * Handles stock transfers between warehouses
 */
class StockTransferController
{
    private StockTransfer $transferModel;
    private Warehouse $warehouseModel;

    public function __construct()
    {
        $this->transferModel = new StockTransfer();
        $this->warehouseModel = new Warehouse();
    }

    /**
     * Get all transfers
     * GET /api/stock/transfers
     */
    public function index(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $filters = [
            'status' => $_GET['status'] ?? '',
            'from_warehouse_id' => !empty($_GET['from_warehouse_id']) ? (int)$_GET['from_warehouse_id'] : null,
            'to_warehouse_id' => !empty($_GET['to_warehouse_id']) ? (int)$_GET['to_warehouse_id'] : null,
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 25);

        $result = $this->transferModel->getAll($user['company_id'], $filters, $page, $perPage);

        Response::success($result);
    }

    /**
     * Get transfer by ID
     * GET /api/stock/transfers/:id
     */
    public function show(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Transfer ID is required');
        }

        $transfer = $this->transferModel->findWithItems($id, $user['company_id']);

        if (!$transfer) {
            Response::notFound('Transfer not found');
        }

        Response::success($transfer);
    }

    /**
     * Create transfer
     * POST /api/stock/transfers
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
        if (empty($input['from_warehouse_id'])) {
            $errors['from_warehouse_id'] = 'From warehouse is required';
        }
        if (empty($input['to_warehouse_id'])) {
            $errors['to_warehouse_id'] = 'To warehouse is required';
        }
        if ($input['from_warehouse_id'] === $input['to_warehouse_id']) {
            $errors['to_warehouse_id'] = 'Source and destination warehouses must be different';
        }
        if (empty($input['items']) || !is_array($input['items']) || count($input['items']) === 0) {
            $errors['items'] = 'At least one item is required';
        }

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        // Verify warehouses exist
        $fromWarehouse = $this->warehouseModel->findByCompany($input['from_warehouse_id'], $user['company_id']);
        if (!$fromWarehouse) {
            Response::badRequest('From warehouse not found');
        }

        $toWarehouse = $this->warehouseModel->findByCompany($input['to_warehouse_id'], $user['company_id']);
        if (!$toWarehouse) {
            Response::badRequest('To warehouse not found');
        }

        // Prepare transfer data
        $transferData = [
            'company_id' => $user['company_id'],
            'transfer_date' => $input['transfer_date'] ?? date('Y-m-d'),
            'from_warehouse_id' => $input['from_warehouse_id'],
            'to_warehouse_id' => $input['to_warehouse_id'],
            'status' => $input['status'] ?? StockTransfer::STATUS_DRAFT,
            'requested_by' => $user['id'],
            'notes' => $input['notes'] ?? null
        ];

        // Prepare items
        $items = [];
        foreach ($input['items'] as $item) {
            if (empty($item['product_id']) || empty($item['requested_quantity'])) {
                continue;
            }

            $items[] = [
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'] ?? null,
                'requested_quantity' => $item['requested_quantity'],
                'unit' => $item['unit'] ?? 'Adet',
                'unit_cost' => $item['unit_cost'] ?? 0,
                'total_cost' => ($item['unit_cost'] ?? 0) * $item['requested_quantity'],
                'notes' => $item['notes'] ?? null
            ];
        }

        if (empty($items)) {
            Response::badRequest('No valid items provided');
        }

        $transferId = $this->transferModel->createWithItems($transferData, $items);

        if (!$transferId) {
            Response::serverError('Failed to create transfer');
        }

        $transfer = $this->transferModel->findWithItems($transferId, $user['company_id']);

        Response::created($transfer, 'Transfer created successfully');
    }

    /**
     * Update transfer
     * PUT /api/stock/transfers/:id
     */
    public function update(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Transfer ID is required');
        }

        // Check if transfer exists and is editable
        $transfer = $this->transferModel->findByCompany($id, $user['company_id']);
        if (!$transfer) {
            Response::notFound('Transfer not found');
        }

        if ($transfer['status'] !== StockTransfer::STATUS_DRAFT) {
            Response::badRequest('Only draft transfers can be edited');
        }

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        // Prepare update data
        $data = [];
        $allowedFields = ['transfer_date', 'notes'];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $data[$field] = $input[$field];
            }
        }

        if (empty($data)) {
            Response::badRequest('No data to update');
        }

        $success = $this->transferModel->update($id, $data);

        if (!$success) {
            Response::serverError('Failed to update transfer');
        }

        $updated = $this->transferModel->findWithItems($id, $user['company_id']);

        Response::success($updated, 'Transfer updated successfully');
    }

    /**
     * Approve transfer
     * POST /api/stock/transfers/:id/approve
     */
    public function approve(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Transfer ID is required');
        }

        $success = $this->transferModel->approve($id, $user['company_id'], $user['id']);

        if (!$success) {
            Response::badRequest('Transfer cannot be approved. Check status and try again.');
        }

        $transfer = $this->transferModel->findWithItems($id, $user['company_id']);

        Response::success($transfer, 'Transfer approved successfully');
    }

    /**
     * Ship transfer
     * POST /api/stock/transfers/:id/ship
     */
    public function ship(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Transfer ID is required');
        }

        $success = $this->transferModel->ship($id, $user['company_id'], $user['id']);

        if (!$success) {
            Response::badRequest('Transfer cannot be shipped. Check status and stock availability.');
        }

        $transfer = $this->transferModel->findWithItems($id, $user['company_id']);

        Response::success($transfer, 'Transfer shipped successfully');
    }

    /**
     * Receive transfer
     * POST /api/stock/transfers/:id/receive
     */
    public function receive(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Transfer ID is required');
        }

        // Get received quantities (optional)
        $input = json_decode(file_get_contents('php://input'), true);
        $receivedQuantities = $input['received_quantities'] ?? [];

        $success = $this->transferModel->receive($id, $user['company_id'], $user['id'], $receivedQuantities);

        if (!$success) {
            Response::badRequest('Transfer cannot be received. Check status and try again.');
        }

        $transfer = $this->transferModel->findWithItems($id, $user['company_id']);

        Response::success($transfer, 'Transfer received successfully');
    }

    /**
     * Cancel transfer
     * POST /api/stock/transfers/:id/cancel
     */
    public function cancel(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Transfer ID is required');
        }

        // Get cancellation reason
        $input = json_decode(file_get_contents('php://input'), true);
        $reason = $input['reason'] ?? 'Cancelled by user';

        $success = $this->transferModel->cancel($id, $user['company_id'], $user['id'], $reason);

        if (!$success) {
            Response::badRequest('Transfer cannot be cancelled. Check status and try again.');
        }

        $transfer = $this->transferModel->findWithItems($id, $user['company_id']);

        Response::success($transfer, 'Transfer cancelled successfully');
    }

    /**
     * Get transfer statistics
     * GET /api/stock/transfers/statistics
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

        $stats = $this->transferModel->getStatistics($user['company_id'], $dateRange);

        Response::success(['statistics' => $stats]);
    }
}

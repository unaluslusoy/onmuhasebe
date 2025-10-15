<?php

namespace App\Controllers;

use App\Models\Warehouse;
use App\Helpers\Response;

/**
 * Warehouse Controller
 * Handles warehouse and location management
 */
class WarehouseController
{
    private Warehouse $warehouseModel;

    public function __construct()
    {
        $this->warehouseModel = new Warehouse();
    }

    /**
     * Get all warehouses
     * GET /api/warehouses
     */
    public function index(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $filters = [
            'warehouse_type' => $_GET['warehouse_type'] ?? '',
            'is_active' => isset($_GET['is_active']) ? (int)$_GET['is_active'] : null,
            'search' => $_GET['search'] ?? ''
        ];

        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 25);

        $result = $this->warehouseModel->getAll($user['company_id'], $filters, $page, $perPage);

        Response::success($result);
    }

    /**
     * Get warehouse by ID
     * GET /api/warehouses/:id
     */
    public function show(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Warehouse ID is required');
        }

        $warehouse = $this->warehouseModel->getWithStockSummary($id, $user['company_id']);

        if (!$warehouse) {
            Response::notFound('Warehouse not found');
        }

        Response::success($warehouse);
    }

    /**
     * Create warehouse
     * POST /api/warehouses
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
        if (empty($input['warehouse_code'])) {
            $errors['warehouse_code'] = 'Warehouse code is required';
        }
        if (empty($input['warehouse_name'])) {
            $errors['warehouse_name'] = 'Warehouse name is required';
        }
        if (empty($input['warehouse_type'])) {
            $errors['warehouse_type'] = 'Warehouse type is required';
        }

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        // Check if code exists
        if ($this->warehouseModel->codeExists($input['warehouse_code'], $user['company_id'])) {
            Response::badRequest('Warehouse code already exists');
        }

        // Prepare data
        $data = [
            'company_id' => $user['company_id'],
            'warehouse_code' => $input['warehouse_code'],
            'warehouse_name' => $input['warehouse_name'],
            'warehouse_type' => $input['warehouse_type'],
            'address' => $input['address'] ?? null,
            'city' => $input['city'] ?? null,
            'district' => $input['district'] ?? null,
            'postal_code' => $input['postal_code'] ?? null,
            'country' => $input['country'] ?? 'Türkiye',
            'phone' => $input['phone'] ?? null,
            'email' => $input['email'] ?? null,
            'manager_name' => $input['manager_name'] ?? null,
            'is_active' => $input['is_active'] ?? true,
            'is_default' => $input['is_default'] ?? false,
            'allow_negative_stock' => $input['allow_negative_stock'] ?? false,
            'auto_allocate' => $input['auto_allocate'] ?? true,
            'total_capacity' => $input['total_capacity'] ?? null,
            'description' => $input['description'] ?? null,
            'notes' => $input['notes'] ?? null
        ];

        $warehouseId = $this->warehouseModel->create($data);

        if (!$warehouseId) {
            Response::serverError('Failed to create warehouse');
        }

        // Set as default if requested
        if ($data['is_default']) {
            $this->warehouseModel->setAsDefault($warehouseId, $user['company_id']);
        }

        $warehouse = $this->warehouseModel->findByCompany($warehouseId, $user['company_id']);

        Response::created($warehouse, 'Warehouse created successfully');
    }

    /**
     * Update warehouse
     * PUT /api/warehouses/:id
     */
    public function update(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Warehouse ID is required');
        }

        // Check if warehouse exists
        $warehouse = $this->warehouseModel->findByCompany($id, $user['company_id']);
        if (!$warehouse) {
            Response::notFound('Warehouse not found');
        }

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        // Check if code exists (excluding current warehouse)
        if (!empty($input['warehouse_code']) && 
            $this->warehouseModel->codeExists($input['warehouse_code'], $user['company_id'], $id)) {
            Response::badRequest('Warehouse code already exists');
        }

        // Prepare update data
        $data = [];
        $allowedFields = [
            'warehouse_code', 'warehouse_name', 'warehouse_type', 'address', 
            'city', 'district', 'postal_code', 'country', 'phone', 'email',
            'manager_name', 'is_active', 'allow_negative_stock', 'auto_allocate',
            'total_capacity', 'description', 'notes'
        ];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $data[$field] = $input[$field];
            }
        }

        if (empty($data)) {
            Response::badRequest('No data to update');
        }

        $success = $this->warehouseModel->update($id, $data);

        if (!$success) {
            Response::serverError('Failed to update warehouse');
        }

        // Handle default warehouse change
        if (isset($input['is_default']) && $input['is_default']) {
            $this->warehouseModel->setAsDefault($id, $user['company_id']);
        }

        $updated = $this->warehouseModel->findByCompany($id, $user['company_id']);

        Response::success($updated, 'Warehouse updated successfully');
    }

    /**
     * Delete warehouse
     * DELETE /api/warehouses/:id
     */
    public function destroy(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Warehouse ID is required');
        }

        // Check if warehouse exists
        $warehouse = $this->warehouseModel->findByCompany($id, $user['company_id']);
        if (!$warehouse) {
            Response::notFound('Warehouse not found');
        }

        // Cannot delete default warehouse
        if ($warehouse['is_default']) {
            Response::badRequest('Cannot delete default warehouse');
        }

        $success = $this->warehouseModel->delete($id);

        if (!$success) {
            Response::serverError('Failed to delete warehouse');
        }

        Response::success(null, 'Warehouse deleted successfully');
    }

    /**
     * Get warehouse locations
     * GET /api/warehouses/:id/locations
     */
    public function locations(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Warehouse ID is required');
        }

        // Check if warehouse exists
        $warehouse = $this->warehouseModel->findByCompany($id, $user['company_id']);
        if (!$warehouse) {
            Response::notFound('Warehouse not found');
        }

        $tree = isset($_GET['tree']) && $_GET['tree'] === 'true';

        if ($tree) {
            $locations = $this->warehouseModel->getLocationTree($id);
        } else {
            $activeOnly = isset($_GET['active']) && $_GET['active'] === 'true';
            $locations = $this->warehouseModel->getLocations($id, $activeOnly);
        }

        Response::success(['locations' => $locations]);
    }

    /**
     * Create warehouse location
     * POST /api/warehouses/:id/locations
     */
    public function createLocation(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $warehouseId = (int)($params['id'] ?? 0);
        if (!$warehouseId) {
            Response::badRequest('Warehouse ID is required');
        }

        // Check if warehouse exists
        $warehouse = $this->warehouseModel->findByCompany($warehouseId, $user['company_id']);
        if (!$warehouse) {
            Response::notFound('Warehouse not found');
        }

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        // Validation
        $errors = [];
        if (empty($input['location_code'])) {
            $errors['location_code'] = 'Location code is required';
        }
        if (empty($input['location_name'])) {
            $errors['location_name'] = 'Location name is required';
        }

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        // Check if location code exists
        if ($this->warehouseModel->findLocationByCode($warehouseId, $input['location_code'])) {
            Response::badRequest('Location code already exists in this warehouse');
        }

        // Prepare data
        $data = [
            'location_code' => $input['location_code'],
            'location_name' => $input['location_name'],
            'parent_location_id' => $input['parent_location_id'] ?? null,
            'location_type' => $input['location_type'] ?? 'storage',
            'aisle' => $input['aisle'] ?? null,
            'rack' => $input['rack'] ?? null,
            'shelf' => $input['shelf'] ?? null,
            'bin' => $input['bin'] ?? null,
            'capacity' => $input['capacity'] ?? null,
            'is_active' => $input['is_active'] ?? true,
            'description' => $input['description'] ?? null
        ];

        $locationId = $this->warehouseModel->createLocation($warehouseId, $data);

        if (!$locationId) {
            Response::serverError('Failed to create location');
        }

        Response::created(['id' => $locationId], 'Location created successfully');
    }

    /**
     * Get warehouse statistics
     * GET /api/warehouses/statistics
     */
    public function statistics(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $stats = $this->warehouseModel->getStatistics($user['company_id']);

        Response::success($stats);
    }

    /**
     * Set warehouse as default
     * POST /api/warehouses/:id/set-default
     */
    public function setDefault(array $params = []): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user || !$user['company_id']) {
            Response::unauthorized('User not authenticated');
        }

        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            Response::badRequest('Warehouse ID is required');
        }

        // Check if warehouse exists
        $warehouse = $this->warehouseModel->findByCompany($id, $user['company_id']);
        if (!$warehouse) {
            Response::notFound('Warehouse not found');
        }

        if (!$warehouse['is_active']) {
            Response::badRequest('Cannot set inactive warehouse as default');
        }

        $success = $this->warehouseModel->setAsDefault($id, $user['company_id']);

        if (!$success) {
            Response::serverError('Failed to set default warehouse');
        }

        Response::success(null, 'Default warehouse updated successfully');
    }
}

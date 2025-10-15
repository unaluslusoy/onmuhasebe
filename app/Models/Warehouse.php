<?php

namespace App\Models;

/**
 * Warehouse Model
 * Handles warehouse management and location operations
 */
class Warehouse extends BaseModel
{
    protected string $table = 'warehouses';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'company_id',
        'warehouse_code',
        'warehouse_name',
        'warehouse_type',
        'address',
        'city',
        'district',
        'postal_code',
        'country',
        'phone',
        'email',
        'manager_name',
        'is_active',
        'is_default',
        'allow_negative_stock',
        'auto_allocate',
        'total_capacity',
        'used_capacity',
        'description',
        'notes'
    ];

    protected array $hidden = [];

    protected array $casts = [
        'id' => 'int',
        'company_id' => 'int',
        'is_active' => 'bool',
        'is_default' => 'bool',
        'allow_negative_stock' => 'bool',
        'auto_allocate' => 'bool',
        'total_capacity' => 'float',
        'used_capacity' => 'float'
    ];

    /**
     * Get all warehouses with filters
     */
    public function getAll(int $companyId, array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table} WHERE company_id = ? AND deleted_at IS NULL";
        $params = [$companyId];

        // Filters
        if (!empty($filters['warehouse_type'])) {
            $sql .= " AND warehouse_type = ?";
            $params[] = $filters['warehouse_type'];
        }

        if (isset($filters['is_active'])) {
            $sql .= " AND is_active = ?";
            $params[] = (int)$filters['is_active'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (warehouse_name LIKE ? OR warehouse_code LIKE ? OR city LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as filtered";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        // Get paginated data
        $sql .= " ORDER BY is_default DESC, warehouse_name ASC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $warehouses = $stmt->fetchAll();

        return [
            'data' => array_map(fn($w) => $this->transformResult($w), $warehouses),
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total)
            ]
        ];
    }

    /**
     * Find warehouse by ID (with company check)
     */
    public function findByCompany(int $id, int $companyId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND company_id = ? AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $companyId]);
        
        $result = $stmt->fetch();
        return $result ? $this->transformResult($result) : null;
    }

    /**
     * Find warehouse by code
     */
    public function findByCode(string $code, int $companyId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE warehouse_code = ? AND company_id = ? AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$code, $companyId]);
        
        $result = $stmt->fetch();
        return $result ? $this->transformResult($result) : null;
    }

    /**
     * Get default warehouse for company
     */
    public function getDefault(int $companyId): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE company_id = ? AND is_default = 1 AND is_active = 1 AND deleted_at IS NULL 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId]);
        
        $result = $stmt->fetch();
        return $result ? $this->transformResult($result) : null;
    }

    /**
     * Set warehouse as default (unset others first)
     */
    public function setAsDefault(int $id, int $companyId): bool
    {
        $this->beginTransaction();
        
        try {
            // Unset all defaults for company
            $sql = "UPDATE {$this->table} SET is_default = 0 WHERE company_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$companyId]);

            // Set new default
            $sql = "UPDATE {$this->table} SET is_default = 1 WHERE id = ? AND company_id = ?";
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([$id, $companyId]);

            $this->commit();
            return $success;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Check if warehouse code exists
     */
    public function codeExists(string $code, int $companyId, ?int $excludeId = null): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE warehouse_code = ? AND company_id = ? AND deleted_at IS NULL";
        $params = [$code, $companyId];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    /**
     * Get warehouse with current stock summary
     */
    public function getWithStockSummary(int $id, int $companyId): ?array
    {
        $warehouse = $this->findByCompany($id, $companyId);
        if (!$warehouse) {
            return null;
        }

        // Get stock summary
        $sql = "SELECT 
                    COUNT(DISTINCT product_id) as total_products,
                    SUM(current_stock) as total_quantity
                FROM view_current_stock 
                WHERE company_id = ? AND warehouse_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId, $id]);
        $summary = $stmt->fetch();

        $warehouse['stock_summary'] = [
            'total_products' => (int)($summary['total_products'] ?? 0),
            'total_quantity' => (float)($summary['total_quantity'] ?? 0)
        ];

        return $warehouse;
    }

    /**
     * Get active warehouses for company
     */
    public function getActive(int $companyId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE company_id = ? AND is_active = 1 AND deleted_at IS NULL 
                ORDER BY is_default DESC, warehouse_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId]);
        
        $warehouses = $stmt->fetchAll();
        return array_map(fn($w) => $this->transformResult($w), $warehouses);
    }

    /**
     * Update capacity
     */
    public function updateCapacity(int $id, float $usedCapacity): bool
    {
        $sql = "UPDATE {$this->table} SET used_capacity = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$usedCapacity, $id]);
    }

    /**
     * Get warehouse locations
     */
    public function getLocations(int $warehouseId, bool $activeOnly = false): array
    {
        $sql = "SELECT * FROM warehouse_locations WHERE warehouse_id = ? AND deleted_at IS NULL";
        $params = [$warehouseId];

        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }

        $sql .= " ORDER BY location_path ASC, location_code ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Create warehouse location
     */
    public function createLocation(int $warehouseId, array $data): ?int
    {
        $data['warehouse_id'] = $warehouseId;
        
        // Build location path if parent exists
        if (!empty($data['parent_location_id'])) {
            $parentSql = "SELECT location_path, level FROM warehouse_locations WHERE id = ?";
            $parentStmt = $this->db->prepare($parentSql);
            $parentStmt->execute([$data['parent_location_id']]);
            $parent = $parentStmt->fetch();
            
            if ($parent) {
                $data['location_path'] = $parent['location_path'] . '/' . $data['location_code'];
                $data['level'] = $parent['level'] + 1;
            }
        } else {
            $data['location_path'] = $data['location_code'];
            $data['level'] = 1;
        }

        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = sprintf(
            "INSERT INTO warehouse_locations (%s) VALUES (%s)",
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute(array_values($data));

        return $success ? (int)$this->db->lastInsertId() : null;
    }

    /**
     * Find location by code
     */
    public function findLocationByCode(int $warehouseId, string $locationCode): ?array
    {
        $sql = "SELECT * FROM warehouse_locations 
                WHERE warehouse_id = ? AND location_code = ? AND deleted_at IS NULL 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$warehouseId, $locationCode]);
        
        return $stmt->fetch() ?: null;
    }

    /**
     * Get location tree (hierarchical)
     */
    public function getLocationTree(int $warehouseId): array
    {
        $locations = $this->getLocations($warehouseId, true);
        
        // Build tree structure
        $tree = [];
        $indexed = [];

        // First pass: index by id
        foreach ($locations as $location) {
            $location['children'] = [];
            $indexed[$location['id']] = $location;
        }

        // Second pass: build tree
        foreach ($indexed as $id => $location) {
            if ($location['parent_location_id']) {
                $indexed[$location['parent_location_id']]['children'][] = &$indexed[$id];
            } else {
                $tree[] = &$indexed[$id];
            }
        }

        return $tree;
    }

    /**
     * Delete warehouse location (soft delete)
     */
    public function deleteLocation(int $locationId): bool
    {
        // Check if location has stock
        $checkSql = "SELECT COUNT(*) as count FROM stock_movements 
                     WHERE location_id = ? AND deleted_at IS NULL";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute([$locationId]);
        $result = $checkStmt->fetch();

        if ($result['count'] > 0) {
            return false; // Cannot delete location with stock
        }

        // Soft delete
        $sql = "UPDATE warehouse_locations SET deleted_at = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([date('Y-m-d H:i:s'), $locationId]);
    }

    /**
     * Get warehouse statistics
     */
    public function getStatistics(int $companyId): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_warehouses,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_warehouses,
                    SUM(CASE WHEN warehouse_type = 'main' THEN 1 ELSE 0 END) as main_warehouses,
                    SUM(CASE WHEN warehouse_type = 'branch' THEN 1 ELSE 0 END) as branch_warehouses,
                    SUM(total_capacity) as total_capacity,
                    SUM(used_capacity) as total_used_capacity
                FROM {$this->table}
                WHERE company_id = ? AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId]);
        
        return $stmt->fetch() ?: [];
    }
}

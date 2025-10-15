<?php

namespace App\Models;

/**
 * StockMovement Model
 * Handles stock movements with FIFO/LIFO support
 */
class StockMovement extends BaseModel
{
    protected string $table = 'stock_movements';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'company_id',
        'warehouse_id',
        'location_id',
        'product_id',
        'variant_id',
        'movement_type',
        'movement_date',
        'quantity',
        'unit',
        'unit_cost',
        'total_cost',
        'cost_method',
        'batch_number',
        'lot_number_id',
        'reference_type',
        'reference_id',
        'reference_number',
        'from_warehouse_id',
        'to_warehouse_id',
        'notes',
        'created_by'
    ];

    protected array $casts = [
        'id' => 'int',
        'company_id' => 'int',
        'warehouse_id' => 'int',
        'location_id' => 'int',
        'product_id' => 'int',
        'variant_id' => 'int',
        'quantity' => 'float',
        'unit_cost' => 'float',
        'total_cost' => 'float',
        'lot_number_id' => 'int',
        'reference_id' => 'int',
        'from_warehouse_id' => 'int',
        'to_warehouse_id' => 'int',
        'created_by' => 'int'
    ];

    // Movement type constants
    public const TYPE_PURCHASE_IN = 'purchase_in';
    public const TYPE_SALE_OUT = 'sale_out';
    public const TYPE_TRANSFER_IN = 'transfer_in';
    public const TYPE_TRANSFER_OUT = 'transfer_out';
    public const TYPE_PRODUCTION_IN = 'production_in';
    public const TYPE_PRODUCTION_OUT = 'production_out';
    public const TYPE_ADJUSTMENT_IN = 'adjustment_in';
    public const TYPE_ADJUSTMENT_OUT = 'adjustment_out';
    public const TYPE_RETURN_IN = 'return_in';
    public const TYPE_RETURN_OUT = 'return_out';
    public const TYPE_DAMAGE_OUT = 'damage_out';
    public const TYPE_SAMPLE_OUT = 'sample_out';
    public const TYPE_INITIAL = 'initial';

    /**
     * Get all movements with filters
     */
    public function getAll(int $companyId, array $filters = [], int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT sm.*, 
                       p.product_name, p.sku,
                       pv.variant_name, pv.variant_sku,
                       w.warehouse_name,
                       u.name as created_by_name
                FROM {$this->table} sm
                LEFT JOIN products p ON sm.product_id = p.id
                LEFT JOIN product_variants pv ON sm.variant_id = pv.id
                LEFT JOIN warehouses w ON sm.warehouse_id = w.id
                LEFT JOIN users u ON sm.created_by = u.id
                WHERE sm.company_id = ? AND sm.deleted_at IS NULL";
        
        $params = [$companyId];

        // Filters
        if (!empty($filters['warehouse_id'])) {
            $sql .= " AND sm.warehouse_id = ?";
            $params[] = $filters['warehouse_id'];
        }

        if (!empty($filters['product_id'])) {
            $sql .= " AND sm.product_id = ?";
            $params[] = $filters['product_id'];
        }

        if (!empty($filters['movement_type'])) {
            $sql .= " AND sm.movement_type = ?";
            $params[] = $filters['movement_type'];
        }

        if (!empty($filters['reference_type'])) {
            $sql .= " AND sm.reference_type = ?";
            $params[] = $filters['reference_type'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND sm.movement_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND sm.movement_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['batch_number'])) {
            $sql .= " AND sm.batch_number = ?";
            $params[] = $filters['batch_number'];
        }

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as filtered";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        // Get paginated data
        $sql .= " ORDER BY sm.movement_date DESC, sm.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $movements = $stmt->fetchAll();

        return [
            'data' => array_map(fn($m) => $this->transformResult($m), $movements),
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
     * Get current stock for product in warehouse
     */
    public function getCurrentStock(int $productId, int $warehouseId, ?int $variantId = null): float
    {
        $sql = "SELECT current_stock FROM view_current_stock 
                WHERE product_id = ? AND warehouse_id = ?";
        $params = [$productId, $warehouseId];

        if ($variantId) {
            $sql .= " AND variant_id = ?";
            $params[] = $variantId;
        } else {
            $sql .= " AND variant_id IS NULL";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return $result ? (float)$result['current_stock'] : 0.0;
    }

    /**
     * Get current stock across all warehouses for a product
     */
    public function getTotalStock(int $productId, int $companyId, ?int $variantId = null): array
    {
        $sql = "SELECT 
                    warehouse_id,
                    warehouse_name,
                    current_stock,
                    avg_cost
                FROM view_current_stock 
                WHERE company_id = ? AND product_id = ?";
        $params = [$companyId, $productId];

        if ($variantId) {
            $sql .= " AND variant_id = ?";
            $params[] = $variantId;
        } else {
            $sql .= " AND variant_id IS NULL";
        }

        $sql .= " ORDER BY warehouse_name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Create stock movement
     */
    public function createMovement(array $data): ?int
    {
        // Calculate total cost if not provided
        if (isset($data['quantity']) && isset($data['unit_cost']) && !isset($data['total_cost'])) {
            $data['total_cost'] = $data['quantity'] * $data['unit_cost'];
        }

        // Set default cost method from warehouse settings if not provided
        if (!isset($data['cost_method'])) {
            $data['cost_method'] = 'fifo';
        }

        // Set movement date to now if not provided
        if (!isset($data['movement_date'])) {
            $data['movement_date'] = date('Y-m-d H:i:s');
        }

        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = sprintf(
            "INSERT INTO {$this->table} (%s) VALUES (%s)",
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute(array_values($data));

        return $success ? (int)$this->db->lastInsertId() : null;
    }

    /**
     * Calculate average cost (weighted average)
     */
    public function calculateAverageCost(int $productId, int $warehouseId, ?int $variantId = null): float
    {
        $sql = "SELECT 
                    SUM(CASE 
                        WHEN movement_type IN ('purchase_in', 'transfer_in', 'production_in', 'adjustment_in', 'return_in', 'initial')
                        THEN quantity 
                        ELSE 0 
                    END) as total_in_qty,
                    SUM(CASE 
                        WHEN movement_type IN ('purchase_in', 'transfer_in', 'production_in', 'adjustment_in', 'return_in', 'initial')
                        THEN total_cost 
                        ELSE 0 
                    END) as total_in_cost
                FROM {$this->table}
                WHERE product_id = ? AND warehouse_id = ? AND deleted_at IS NULL";
        
        $params = [$productId, $warehouseId];

        if ($variantId) {
            $sql .= " AND variant_id = ?";
            $params[] = $variantId;
        } else {
            $sql .= " AND variant_id IS NULL";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        if ($result && $result['total_in_qty'] > 0) {
            return $result['total_in_cost'] / $result['total_in_qty'];
        }

        return 0.0;
    }

    /**
     * Get FIFO cost for quantity
     */
    public function getFIFOCost(int $productId, int $warehouseId, float $quantity, ?int $variantId = null): array
    {
        $sql = "SELECT id, quantity, unit_cost, movement_date
                FROM {$this->table}
                WHERE product_id = ? AND warehouse_id = ? 
                AND movement_type IN ('purchase_in', 'transfer_in', 'production_in', 'initial')
                AND deleted_at IS NULL";
        
        $params = [$productId, $warehouseId];

        if ($variantId) {
            $sql .= " AND variant_id = ?";
            $params[] = $variantId;
        } else {
            $sql .= " AND variant_id IS NULL";
        }

        $sql .= " ORDER BY movement_date ASC, id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $movements = $stmt->fetchAll();

        $allocations = [];
        $remainingQty = $quantity;
        $totalCost = 0;

        foreach ($movements as $movement) {
            if ($remainingQty <= 0) {
                break;
            }

            $availableQty = min($movement['quantity'], $remainingQty);
            $cost = $availableQty * $movement['unit_cost'];

            $allocations[] = [
                'movement_id' => $movement['id'],
                'quantity' => $availableQty,
                'unit_cost' => $movement['unit_cost'],
                'cost' => $cost,
                'movement_date' => $movement['movement_date']
            ];

            $totalCost += $cost;
            $remainingQty -= $availableQty;
        }

        return [
            'allocations' => $allocations,
            'total_cost' => $totalCost,
            'average_cost' => $quantity > 0 ? $totalCost / $quantity : 0,
            'remaining_qty' => $remainingQty
        ];
    }

    /**
     * Get LIFO cost for quantity
     */
    public function getLIFOCost(int $productId, int $warehouseId, float $quantity, ?int $variantId = null): array
    {
        $sql = "SELECT id, quantity, unit_cost, movement_date
                FROM {$this->table}
                WHERE product_id = ? AND warehouse_id = ? 
                AND movement_type IN ('purchase_in', 'transfer_in', 'production_in', 'initial')
                AND deleted_at IS NULL";
        
        $params = [$productId, $warehouseId];

        if ($variantId) {
            $sql .= " AND variant_id = ?";
            $params[] = $variantId;
        } else {
            $sql .= " AND variant_id IS NULL";
        }

        $sql .= " ORDER BY movement_date DESC, id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $movements = $stmt->fetchAll();

        $allocations = [];
        $remainingQty = $quantity;
        $totalCost = 0;

        foreach ($movements as $movement) {
            if ($remainingQty <= 0) {
                break;
            }

            $availableQty = min($movement['quantity'], $remainingQty);
            $cost = $availableQty * $movement['unit_cost'];

            $allocations[] = [
                'movement_id' => $movement['id'],
                'quantity' => $availableQty,
                'unit_cost' => $movement['unit_cost'],
                'cost' => $cost,
                'movement_date' => $movement['movement_date']
            ];

            $totalCost += $cost;
            $remainingQty -= $availableQty;
        }

        return [
            'allocations' => $allocations,
            'total_cost' => $totalCost,
            'average_cost' => $quantity > 0 ? $totalCost / $quantity : 0,
            'remaining_qty' => $remainingQty
        ];
    }

    /**
     * Get movement history for product
     */
    public function getProductHistory(
        int $productId, 
        int $companyId, 
        ?int $warehouseId = null,
        ?int $variantId = null,
        array $dateRange = []
    ): array {
        $sql = "SELECT sm.*, 
                       w.warehouse_name,
                       u.name as created_by_name
                FROM {$this->table} sm
                LEFT JOIN warehouses w ON sm.warehouse_id = w.id
                LEFT JOIN users u ON sm.created_by = u.id
                WHERE sm.company_id = ? AND sm.product_id = ? AND sm.deleted_at IS NULL";
        
        $params = [$companyId, $productId];

        if ($warehouseId) {
            $sql .= " AND sm.warehouse_id = ?";
            $params[] = $warehouseId;
        }

        if ($variantId) {
            $sql .= " AND sm.variant_id = ?";
            $params[] = $variantId;
        } else {
            $sql .= " AND sm.variant_id IS NULL";
        }

        if (!empty($dateRange['from'])) {
            $sql .= " AND sm.movement_date >= ?";
            $params[] = $dateRange['from'];
        }

        if (!empty($dateRange['to'])) {
            $sql .= " AND sm.movement_date <= ?";
            $params[] = $dateRange['to'];
        }

        $sql .= " ORDER BY sm.movement_date DESC, sm.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return array_map(fn($m) => $this->transformResult($m), $stmt->fetchAll());
    }

    /**
     * Get movements by reference
     */
    public function getByReference(string $referenceType, int $referenceId, int $companyId): array
    {
        $sql = "SELECT sm.*, 
                       p.product_name, p.sku,
                       pv.variant_name,
                       w.warehouse_name
                FROM {$this->table} sm
                LEFT JOIN products p ON sm.product_id = p.id
                LEFT JOIN product_variants pv ON sm.variant_id = pv.id
                LEFT JOIN warehouses w ON sm.warehouse_id = w.id
                WHERE sm.company_id = ? AND sm.reference_type = ? AND sm.reference_id = ? 
                AND sm.deleted_at IS NULL
                ORDER BY sm.movement_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId, $referenceType, $referenceId]);
        
        return array_map(fn($m) => $this->transformResult($m), $stmt->fetchAll());
    }

    /**
     * Get stock value by warehouse
     */
    public function getStockValue(int $warehouseId, int $companyId): array
    {
        $sql = "SELECT 
                    COUNT(DISTINCT product_id) as total_products,
                    SUM(current_stock) as total_quantity,
                    SUM(current_stock * avg_cost) as total_value
                FROM view_current_stock
                WHERE company_id = ? AND warehouse_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId, $warehouseId]);
        
        return $stmt->fetch() ?: [];
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(int $companyId, ?int $warehouseId = null): array
    {
        $sql = "SELECT * FROM view_low_stock_products WHERE company_id = ?";
        $params = [$companyId];

        if ($warehouseId) {
            $sql .= " AND warehouse_id = ?";
            $params[] = $warehouseId;
        }

        $sql .= " ORDER BY shortage_percentage DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Get movement statistics
     */
    public function getStatistics(int $companyId, array $dateRange = []): array
    {
        $sql = "SELECT 
                    movement_type,
                    COUNT(*) as movement_count,
                    SUM(quantity) as total_quantity,
                    SUM(total_cost) as total_cost
                FROM {$this->table}
                WHERE company_id = ? AND deleted_at IS NULL";
        
        $params = [$companyId];

        if (!empty($dateRange['from'])) {
            $sql .= " AND movement_date >= ?";
            $params[] = $dateRange['from'];
        }

        if (!empty($dateRange['to'])) {
            $sql .= " AND movement_date <= ?";
            $params[] = $dateRange['to'];
        }

        $sql .= " GROUP BY movement_type ORDER BY movement_count DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Bulk create movements (for transfers, production, etc.)
     */
    public function bulkCreate(array $movements): bool
    {
        if (empty($movements)) {
            return false;
        }

        $this->beginTransaction();
        
        try {
            foreach ($movements as $movement) {
                $this->createMovement($movement);
            }
            
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }
}

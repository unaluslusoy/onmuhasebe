<?php

namespace App\Models;

/**
 * StockCount Model
 * Handles inventory counting and variance management
 */
class StockCount extends BaseModel
{
    protected string $table = 'stock_counts';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'company_id',
        'count_number',
        'count_date',
        'warehouse_id',
        'count_type',
        'status',
        'counted_by',
        'verified_by',
        'verified_at',
        'approved_by',
        'approved_at',
        'total_items',
        'variance_items',
        'variance_quantity',
        'variance_value',
        'adjustment_created',
        'notes',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at'
    ];

    protected array $casts = [
        'id' => 'int',
        'company_id' => 'int',
        'warehouse_id' => 'int',
        'counted_by' => 'int',
        'verified_by' => 'int',
        'approved_by' => 'int',
        'cancelled_by' => 'int',
        'total_items' => 'int',
        'variance_items' => 'int',
        'variance_quantity' => 'float',
        'variance_value' => 'float',
        'adjustment_created' => 'bool'
    ];

    // Count type constants
    public const TYPE_FULL = 'full';
    public const TYPE_PARTIAL = 'partial';
    public const TYPE_CYCLE = 'cycle';
    public const TYPE_SPOT = 'spot';

    // Status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Get all counts with filters
     */
    public function getAll(int $companyId, array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT sc.*, 
                       w.warehouse_name,
                       u1.name as counted_by_name,
                       u2.name as verified_by_name,
                       u3.name as approved_by_name
                FROM {$this->table} sc
                LEFT JOIN warehouses w ON sc.warehouse_id = w.id
                LEFT JOIN users u1 ON sc.counted_by = u1.id
                LEFT JOIN users u2 ON sc.verified_by = u2.id
                LEFT JOIN users u3 ON sc.approved_by = u3.id
                WHERE sc.company_id = ? AND sc.deleted_at IS NULL";
        
        $params = [$companyId];

        // Filters
        if (!empty($filters['status'])) {
            $sql .= " AND sc.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['warehouse_id'])) {
            $sql .= " AND sc.warehouse_id = ?";
            $params[] = $filters['warehouse_id'];
        }

        if (!empty($filters['count_type'])) {
            $sql .= " AND sc.count_type = ?";
            $params[] = $filters['count_type'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND sc.count_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND sc.count_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND sc.count_number LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as filtered";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        // Get paginated data
        $sql .= " ORDER BY sc.count_date DESC, sc.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $counts = $stmt->fetchAll();

        return [
            'data' => array_map(fn($c) => $this->transformResult($c), $counts),
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
     * Find count with items
     */
    public function findWithItems(int $id, int $companyId): ?array
    {
        $count = $this->findByCompany($id, $companyId);
        if (!$count) {
            return null;
        }

        // Get count items
        $sql = "SELECT sci.*, 
                       p.product_name, p.sku,
                       pv.variant_name, pv.variant_sku
                FROM stock_count_items sci
                LEFT JOIN products p ON sci.product_id = p.id
                LEFT JOIN product_variants pv ON sci.variant_id = pv.id
                WHERE sci.count_id = ? AND sci.deleted_at IS NULL
                ORDER BY sci.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $count['items'] = $stmt->fetchAll();

        return $count;
    }

    /**
     * Find count by company
     */
    public function findByCompany(int $id, int $companyId): ?array
    {
        $sql = "SELECT sc.*, 
                       w.warehouse_name,
                       u1.name as counted_by_name,
                       u2.name as verified_by_name,
                       u3.name as approved_by_name
                FROM {$this->table} sc
                LEFT JOIN warehouses w ON sc.warehouse_id = w.id
                LEFT JOIN users u1 ON sc.counted_by = u1.id
                LEFT JOIN users u2 ON sc.verified_by = u2.id
                LEFT JOIN users u3 ON sc.approved_by = u3.id
                WHERE sc.id = ? AND sc.company_id = ? AND sc.deleted_at IS NULL 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $companyId]);
        
        $result = $stmt->fetch();
        return $result ? $this->transformResult($result) : null;
    }

    /**
     * Create count with items
     */
    public function createWithItems(array $countData, array $items): ?int
    {
        $this->beginTransaction();
        
        try {
            // Generate count number if not provided
            if (empty($countData['count_number'])) {
                $countData['count_number'] = $this->generateCountNumber($countData['company_id']);
            }

            // Set default status
            if (!isset($countData['status'])) {
                $countData['status'] = self::STATUS_DRAFT;
            }

            // Create count
            $columns = array_keys($countData);
            $placeholders = array_fill(0, count($columns), '?');
            
            $sql = sprintf(
                "INSERT INTO {$this->table} (%s) VALUES (%s)",
                implode(', ', $columns),
                implode(', ', $placeholders)
            );

            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($countData));
            $countId = (int)$this->db->lastInsertId();

            // Create count items
            foreach ($items as $item) {
                $item['count_id'] = $countId;
                $this->createItem($item);
            }

            $this->commit();
            return $countId;
        } catch (\Exception $e) {
            $this->rollback();
            return null;
        }
    }

    /**
     * Create count item
     */
    private function createItem(array $data): ?int
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = sprintf(
            "INSERT INTO stock_count_items (%s) VALUES (%s)",
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute(array_values($data));

        return $success ? (int)$this->db->lastInsertId() : null;
    }

    /**
     * Start count (load current stock)
     */
    public function start(int $id, int $companyId, array $productIds = []): bool
    {
        $count = $this->findByCompany($id, $companyId);
        if (!$count || $count['status'] !== self::STATUS_DRAFT) {
            return false;
        }

        $this->beginTransaction();
        
        try {
            // Load current stock for warehouse
            $sql = "SELECT 
                        product_id,
                        variant_id,
                        current_stock,
                        avg_cost
                    FROM view_current_stock 
                    WHERE company_id = ? AND warehouse_id = ?";
            
            $params = [$companyId, $count['warehouse_id']];

            // Filter by product IDs if partial count
            if (!empty($productIds)) {
                $placeholders = implode(',', array_fill(0, count($productIds), '?'));
                $sql .= " AND product_id IN ($placeholders)";
                $params = array_merge($params, $productIds);
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $stocks = $stmt->fetchAll();

            // Create count items
            foreach ($stocks as $stock) {
                $this->createItem([
                    'count_id' => $id,
                    'product_id' => $stock['product_id'],
                    'variant_id' => $stock['variant_id'],
                    'system_quantity' => $stock['current_stock'],
                    'counted_quantity' => 0,
                    'unit_cost' => $stock['avg_cost']
                ]);
            }

            // Update count status
            $updateSql = "UPDATE {$this->table} SET status = ? WHERE id = ?";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([self::STATUS_IN_PROGRESS, $id]);

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Update counted quantities
     */
    public function updateCounts(int $id, int $companyId, array $counts): bool
    {
        $count = $this->findByCompany($id, $companyId);
        if (!$count || !in_array($count['status'], [self::STATUS_IN_PROGRESS, self::STATUS_DRAFT])) {
            return false;
        }

        $this->beginTransaction();
        
        try {
            foreach ($counts as $itemId => $countedQty) {
                $sql = "UPDATE stock_count_items 
                        SET counted_quantity = ? 
                        WHERE id = ? AND count_id = ?";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$countedQty, $itemId, $id]);
            }

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Complete count (finalize counting)
     */
    public function complete(int $id, int $companyId, int $userId): bool
    {
        $count = $this->findByCompany($id, $companyId);
        if (!$count || $count['status'] !== self::STATUS_IN_PROGRESS) {
            return false;
        }

        // Update status
        $sql = "UPDATE {$this->table} 
                SET status = ?, counted_by = ? 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([self::STATUS_COMPLETED, $userId, $id]);
    }

    /**
     * Verify count
     */
    public function verify(int $id, int $companyId, int $userId): bool
    {
        $count = $this->findByCompany($id, $companyId);
        if (!$count || $count['status'] !== self::STATUS_COMPLETED) {
            return false;
        }

        $sql = "UPDATE {$this->table} 
                SET status = ?, verified_by = ?, verified_at = ? 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            self::STATUS_VERIFIED,
            $userId,
            date('Y-m-d H:i:s'),
            $id
        ]);
    }

    /**
     * Approve count and create adjustment movements
     */
    public function approve(int $id, int $companyId, int $userId): bool
    {
        $count = $this->findWithItems($id, $companyId);
        if (!$count || $count['status'] !== self::STATUS_VERIFIED) {
            return false;
        }

        $this->beginTransaction();
        
        try {
            $stockMovement = new StockMovement();
            
            // Create adjustment movements for items with variance
            foreach ($count['items'] as $item) {
                if ($item['variance_quantity'] == 0) {
                    continue;
                }

                $movementType = $item['variance_quantity'] > 0 
                    ? StockMovement::TYPE_ADJUSTMENT_IN 
                    : StockMovement::TYPE_ADJUSTMENT_OUT;

                $stockMovement->createMovement([
                    'company_id' => $companyId,
                    'warehouse_id' => $count['warehouse_id'],
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'movement_type' => $movementType,
                    'movement_date' => date('Y-m-d H:i:s'),
                    'quantity' => abs($item['variance_quantity']),
                    'unit' => $item['unit'] ?? 'Adet',
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => abs($item['variance_value']),
                    'reference_type' => 'stock_count',
                    'reference_id' => $id,
                    'reference_number' => $count['count_number'],
                    'notes' => "Sayım düzeltmesi: {$count['count_number']}",
                    'created_by' => $userId
                ]);
            }

            // Update count
            $sql = "UPDATE {$this->table} 
                    SET status = ?, approved_by = ?, approved_at = ?, adjustment_created = 1 
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                self::STATUS_APPROVED,
                $userId,
                date('Y-m-d H:i:s'),
                $id
            ]);

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Cancel count
     */
    public function cancel(int $id, int $companyId, int $userId, string $reason): bool
    {
        $count = $this->findByCompany($id, $companyId);
        if (!$count || in_array($count['status'], [self::STATUS_APPROVED, self::STATUS_CANCELLED])) {
            return false;
        }

        $sql = "UPDATE {$this->table} 
                SET status = ?, cancellation_reason = ?, cancelled_by = ?, cancelled_at = ? 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            self::STATUS_CANCELLED,
            $reason,
            $userId,
            date('Y-m-d H:i:s'),
            $id
        ]);
    }

    /**
     * Get variance summary
     */
    public function getVarianceSummary(int $id): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_items,
                    SUM(CASE WHEN variance_quantity != 0 THEN 1 ELSE 0 END) as variance_items,
                    SUM(CASE WHEN variance_quantity > 0 THEN variance_quantity ELSE 0 END) as positive_variance,
                    SUM(CASE WHEN variance_quantity < 0 THEN ABS(variance_quantity) ELSE 0 END) as negative_variance,
                    SUM(variance_value) as total_variance_value,
                    SUM(CASE WHEN variance_value > 0 THEN variance_value ELSE 0 END) as positive_value,
                    SUM(CASE WHEN variance_value < 0 THEN ABS(variance_value) ELSE 0 END) as negative_value
                FROM stock_count_items
                WHERE count_id = ? AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->fetch() ?: [];
    }

    /**
     * Get items with variance only
     */
    public function getVarianceItems(int $id): array
    {
        $sql = "SELECT sci.*, 
                       p.product_name, p.sku,
                       pv.variant_name, pv.variant_sku
                FROM stock_count_items sci
                LEFT JOIN products p ON sci.product_id = p.id
                LEFT JOIN product_variants pv ON sci.variant_id = pv.id
                WHERE sci.count_id = ? AND sci.variance_quantity != 0 AND sci.deleted_at IS NULL
                ORDER BY ABS(sci.variance_value) DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->fetchAll();
    }

    /**
     * Generate count number
     */
    private function generateCountNumber(int $companyId): string
    {
        $prefix = 'CNT';
        $year = date('Y');
        $month = date('m');

        // Get last number for this month
        $sql = "SELECT count_number FROM {$this->table} 
                WHERE company_id = ? 
                AND count_number LIKE ? 
                ORDER BY id DESC LIMIT 1";
        
        $pattern = "{$prefix}-{$year}{$month}-%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId, $pattern]);
        $last = $stmt->fetch();

        if ($last) {
            $lastNumber = (int)substr($last['count_number'], -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}-{$year}{$month}-{$newNumber}";
    }

    /**
     * Get count statistics
     */
    public function getStatistics(int $companyId, array $dateRange = []): array
    {
        $sql = "SELECT 
                    count_type,
                    status,
                    COUNT(*) as count_total,
                    SUM(total_items) as total_items,
                    SUM(variance_items) as variance_items,
                    SUM(ABS(variance_value)) as total_variance_value
                FROM {$this->table}
                WHERE company_id = ? AND deleted_at IS NULL";
        
        $params = [$companyId];

        if (!empty($dateRange['from'])) {
            $sql .= " AND count_date >= ?";
            $params[] = $dateRange['from'];
        }

        if (!empty($dateRange['to'])) {
            $sql .= " AND count_date <= ?";
            $params[] = $dateRange['to'];
        }

        $sql .= " GROUP BY count_type, status";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
}

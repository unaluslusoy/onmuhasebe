<?php

namespace App\Models;

/**
 * StockTransfer Model
 * Handles stock transfers between warehouses with workflow
 */
class StockTransfer extends BaseModel
{
    protected string $table = 'stock_transfers';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'company_id',
        'transfer_number',
        'transfer_date',
        'from_warehouse_id',
        'to_warehouse_id',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'shipped_by',
        'shipped_at',
        'received_by',
        'received_at',
        'total_items',
        'total_quantity',
        'total_cost',
        'notes',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at'
    ];

    protected array $casts = [
        'id' => 'int',
        'company_id' => 'int',
        'from_warehouse_id' => 'int',
        'to_warehouse_id' => 'int',
        'requested_by' => 'int',
        'approved_by' => 'int',
        'shipped_by' => 'int',
        'received_by' => 'int',
        'cancelled_by' => 'int',
        'total_items' => 'int',
        'total_quantity' => 'float',
        'total_cost' => 'float'
    ];

    // Status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Get all transfers with filters
     */
    public function getAll(int $companyId, array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT st.*, 
                       wf.warehouse_name as from_warehouse_name,
                       wt.warehouse_name as to_warehouse_name,
                       u1.name as requested_by_name,
                       u2.name as approved_by_name
                FROM {$this->table} st
                LEFT JOIN warehouses wf ON st.from_warehouse_id = wf.id
                LEFT JOIN warehouses wt ON st.to_warehouse_id = wt.id
                LEFT JOIN users u1 ON st.requested_by = u1.id
                LEFT JOIN users u2 ON st.approved_by = u2.id
                WHERE st.company_id = ? AND st.deleted_at IS NULL";
        
        $params = [$companyId];

        // Filters
        if (!empty($filters['status'])) {
            $sql .= " AND st.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['from_warehouse_id'])) {
            $sql .= " AND st.from_warehouse_id = ?";
            $params[] = $filters['from_warehouse_id'];
        }

        if (!empty($filters['to_warehouse_id'])) {
            $sql .= " AND st.to_warehouse_id = ?";
            $params[] = $filters['to_warehouse_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND st.transfer_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND st.transfer_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND st.transfer_number LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as filtered";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        // Get paginated data
        $sql .= " ORDER BY st.transfer_date DESC, st.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $transfers = $stmt->fetchAll();

        return [
            'data' => array_map(fn($t) => $this->transformResult($t), $transfers),
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
     * Find transfer with items
     */
    public function findWithItems(int $id, int $companyId): ?array
    {
        $transfer = $this->findByCompany($id, $companyId);
        if (!$transfer) {
            return null;
        }

        // Get transfer items
        $sql = "SELECT sti.*, 
                       p.product_name, p.sku,
                       pv.variant_name, pv.variant_sku
                FROM stock_transfer_items sti
                LEFT JOIN products p ON sti.product_id = p.id
                LEFT JOIN product_variants pv ON sti.variant_id = pv.id
                WHERE sti.transfer_id = ? AND sti.deleted_at IS NULL
                ORDER BY sti.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $transfer['items'] = $stmt->fetchAll();

        return $transfer;
    }

    /**
     * Find transfer by company
     */
    public function findByCompany(int $id, int $companyId): ?array
    {
        $sql = "SELECT st.*, 
                       wf.warehouse_name as from_warehouse_name,
                       wt.warehouse_name as to_warehouse_name,
                       u1.name as requested_by_name,
                       u2.name as approved_by_name,
                       u3.name as shipped_by_name,
                       u4.name as received_by_name
                FROM {$this->table} st
                LEFT JOIN warehouses wf ON st.from_warehouse_id = wf.id
                LEFT JOIN warehouses wt ON st.to_warehouse_id = wt.id
                LEFT JOIN users u1 ON st.requested_by = u1.id
                LEFT JOIN users u2 ON st.approved_by = u2.id
                LEFT JOIN users u3 ON st.shipped_by = u3.id
                LEFT JOIN users u4 ON st.received_by = u4.id
                WHERE st.id = ? AND st.company_id = ? AND st.deleted_at IS NULL 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $companyId]);
        
        $result = $stmt->fetch();
        return $result ? $this->transformResult($result) : null;
    }

    /**
     * Create transfer with items
     */
    public function createWithItems(array $transferData, array $items): ?int
    {
        $this->beginTransaction();
        
        try {
            // Generate transfer number if not provided
            if (empty($transferData['transfer_number'])) {
                $transferData['transfer_number'] = $this->generateTransferNumber($transferData['company_id']);
            }

            // Set default status
            if (!isset($transferData['status'])) {
                $transferData['status'] = self::STATUS_DRAFT;
            }

            // Create transfer
            $columns = array_keys($transferData);
            $placeholders = array_fill(0, count($columns), '?');
            
            $sql = sprintf(
                "INSERT INTO {$this->table} (%s) VALUES (%s)",
                implode(', ', $columns),
                implode(', ', $placeholders)
            );

            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($transferData));
            $transferId = (int)$this->db->lastInsertId();

            // Create transfer items
            foreach ($items as $item) {
                $item['transfer_id'] = $transferId;
                $this->createItem($item);
            }

            $this->commit();
            return $transferId;
        } catch (\Exception $e) {
            $this->rollback();
            return null;
        }
    }

    /**
     * Create transfer item
     */
    private function createItem(array $data): ?int
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = sprintf(
            "INSERT INTO stock_transfer_items (%s) VALUES (%s)",
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute(array_values($data));

        return $success ? (int)$this->db->lastInsertId() : null;
    }

    /**
     * Update transfer status
     */
    public function updateStatus(int $id, string $status, int $userId): bool
    {
        $data = ['status' => $status];
        $now = date('Y-m-d H:i:s');

        switch ($status) {
            case self::STATUS_PENDING:
                $data['approved_by'] = $userId;
                $data['approved_at'] = $now;
                break;
            case self::STATUS_IN_TRANSIT:
                $data['shipped_by'] = $userId;
                $data['shipped_at'] = $now;
                break;
            case self::STATUS_COMPLETED:
                $data['received_by'] = $userId;
                $data['received_at'] = $now;
                break;
            case self::STATUS_CANCELLED:
                $data['cancelled_by'] = $userId;
                $data['cancelled_at'] = $now;
                break;
        }

        $sets = [];
        $values = [];
        foreach ($data as $key => $value) {
            $sets[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($values);
    }

    /**
     * Approve transfer
     */
    public function approve(int $id, int $companyId, int $userId): bool
    {
        $transfer = $this->findByCompany($id, $companyId);
        if (!$transfer || $transfer['status'] !== self::STATUS_DRAFT) {
            return false;
        }

        return $this->updateStatus($id, self::STATUS_PENDING, $userId);
    }

    /**
     * Ship transfer (create outbound stock movements)
     */
    public function ship(int $id, int $companyId, int $userId): bool
    {
        $transfer = $this->findWithItems($id, $companyId);
        if (!$transfer || $transfer['status'] !== self::STATUS_PENDING) {
            return false;
        }

        $this->beginTransaction();
        
        try {
            // Create stock movements for each item (outbound)
            $stockMovement = new StockMovement();
            
            foreach ($transfer['items'] as $item) {
                $stockMovement->createMovement([
                    'company_id' => $companyId,
                    'warehouse_id' => $transfer['from_warehouse_id'],
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'movement_type' => StockMovement::TYPE_TRANSFER_OUT,
                    'movement_date' => date('Y-m-d H:i:s'),
                    'quantity' => $item['requested_quantity'],
                    'unit' => $item['unit'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['total_cost'],
                    'reference_type' => 'stock_transfer',
                    'reference_id' => $id,
                    'reference_number' => $transfer['transfer_number'],
                    'to_warehouse_id' => $transfer['to_warehouse_id'],
                    'notes' => "Transfer çıkışı: {$transfer['transfer_number']}",
                    'created_by' => $userId
                ]);

                // Update shipped quantity
                $updateSql = "UPDATE stock_transfer_items 
                              SET shipped_quantity = ?, shipped_at = ? 
                              WHERE id = ?";
                $updateStmt = $this->db->prepare($updateSql);
                $updateStmt->execute([
                    $item['requested_quantity'],
                    date('Y-m-d H:i:s'),
                    $item['id']
                ]);
            }

            // Update transfer status
            $this->updateStatus($id, self::STATUS_IN_TRANSIT, $userId);

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Receive transfer (create inbound stock movements)
     */
    public function receive(int $id, int $companyId, int $userId, array $receivedQuantities = []): bool
    {
        $transfer = $this->findWithItems($id, $companyId);
        if (!$transfer || $transfer['status'] !== self::STATUS_IN_TRANSIT) {
            return false;
        }

        $this->beginTransaction();
        
        try {
            // Create stock movements for each item (inbound)
            $stockMovement = new StockMovement();
            
            foreach ($transfer['items'] as $item) {
                $receivedQty = $receivedQuantities[$item['id']] ?? $item['shipped_quantity'];
                
                $stockMovement->createMovement([
                    'company_id' => $companyId,
                    'warehouse_id' => $transfer['to_warehouse_id'],
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'movement_type' => StockMovement::TYPE_TRANSFER_IN,
                    'movement_date' => date('Y-m-d H:i:s'),
                    'quantity' => $receivedQty,
                    'unit' => $item['unit'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $receivedQty * $item['unit_cost'],
                    'reference_type' => 'stock_transfer',
                    'reference_id' => $id,
                    'reference_number' => $transfer['transfer_number'],
                    'from_warehouse_id' => $transfer['from_warehouse_id'],
                    'notes' => "Transfer girişi: {$transfer['transfer_number']}",
                    'created_by' => $userId
                ]);

                // Update received quantity
                $updateSql = "UPDATE stock_transfer_items 
                              SET received_quantity = ?, received_at = ? 
                              WHERE id = ?";
                $updateStmt = $this->db->prepare($updateSql);
                $updateStmt->execute([
                    $receivedQty,
                    date('Y-m-d H:i:s'),
                    $item['id']
                ]);
            }

            // Update transfer status
            $this->updateStatus($id, self::STATUS_COMPLETED, $userId);

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Cancel transfer
     */
    public function cancel(int $id, int $companyId, int $userId, string $reason): bool
    {
        $transfer = $this->findByCompany($id, $companyId);
        if (!$transfer || in_array($transfer['status'], [self::STATUS_COMPLETED, self::STATUS_CANCELLED])) {
            return false;
        }

        $this->beginTransaction();
        
        try {
            // If already shipped, create reverse movements
            if ($transfer['status'] === self::STATUS_IN_TRANSIT) {
                $items = $this->findWithItems($id, $companyId)['items'];
                $stockMovement = new StockMovement();
                
                foreach ($items as $item) {
                    if ($item['shipped_quantity'] > 0) {
                        // Return to source warehouse
                        $stockMovement->createMovement([
                            'company_id' => $companyId,
                            'warehouse_id' => $transfer['from_warehouse_id'],
                            'product_id' => $item['product_id'],
                            'variant_id' => $item['variant_id'],
                            'movement_type' => StockMovement::TYPE_ADJUSTMENT_IN,
                            'movement_date' => date('Y-m-d H:i:s'),
                            'quantity' => $item['shipped_quantity'],
                            'unit' => $item['unit'],
                            'unit_cost' => $item['unit_cost'],
                            'total_cost' => $item['total_cost'],
                            'reference_type' => 'stock_transfer_cancellation',
                            'reference_id' => $id,
                            'reference_number' => $transfer['transfer_number'],
                            'notes' => "Transfer iptali: {$transfer['transfer_number']} - {$reason}",
                            'created_by' => $userId
                        ]);
                    }
                }
            }

            // Update transfer
            $sql = "UPDATE {$this->table} 
                    SET status = ?, cancellation_reason = ?, cancelled_by = ?, cancelled_at = ? 
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                self::STATUS_CANCELLED,
                $reason,
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
     * Generate transfer number
     */
    private function generateTransferNumber(int $companyId): string
    {
        $prefix = 'TRF';
        $year = date('Y');
        $month = date('m');

        // Get last number for this month
        $sql = "SELECT transfer_number FROM {$this->table} 
                WHERE company_id = ? 
                AND transfer_number LIKE ? 
                ORDER BY id DESC LIMIT 1";
        
        $pattern = "{$prefix}-{$year}{$month}-%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId, $pattern]);
        $last = $stmt->fetch();

        if ($last) {
            $lastNumber = (int)substr($last['transfer_number'], -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}-{$year}{$month}-{$newNumber}";
    }

    /**
     * Get transfer statistics
     */
    public function getStatistics(int $companyId, array $dateRange = []): array
    {
        $sql = "SELECT 
                    status,
                    COUNT(*) as transfer_count,
                    SUM(total_items) as total_items,
                    SUM(total_quantity) as total_quantity,
                    SUM(total_cost) as total_cost
                FROM {$this->table}
                WHERE company_id = ? AND deleted_at IS NULL";
        
        $params = [$companyId];

        if (!empty($dateRange['from'])) {
            $sql .= " AND transfer_date >= ?";
            $params[] = $dateRange['from'];
        }

        if (!empty($dateRange['to'])) {
            $sql .= " AND transfer_date <= ?";
            $params[] = $dateRange['to'];
        }

        $sql .= " GROUP BY status";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
}

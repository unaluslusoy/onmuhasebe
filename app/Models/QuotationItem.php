<?php

namespace App\Models;

/**
 * QuotationItem Model
 * Handles quotation line items
 */
class QuotationItem extends BaseModel
{
    protected string $table = 'quotation_items';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'quotation_id',
        'product_id',
        'variant_id',
        'item_order',
        'item_type',
        'item_code',
        'item_name',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_type',
        'discount_value',
        'tax_rate'
    ];

    protected array $casts = [
        'id' => 'int',
        'quotation_id' => 'int',
        'product_id' => 'int',
        'variant_id' => 'int',
        'item_order' => 'int',
        'quantity' => 'float',
        'unit_price' => 'float',
        'discount_value' => 'float',
        'discount_amount' => 'float',
        'tax_rate' => 'float',
        'tax_amount' => 'float',
        'subtotal' => 'float',
        'total' => 'float'
    ];

    // Item type constants
    public const TYPE_PRODUCT = 'product';
    public const TYPE_SERVICE = 'service';
    public const TYPE_TEXT = 'text';
    public const TYPE_SUBTOTAL = 'subtotal';

    /**
     * Get all items for a quotation
     */
    public function getByQuotation(int $quotationId): array
    {
        $sql = "SELECT qi.*, 
                       p.product_name,
                       p.sku,
                       pv.variant_name,
                       pv.sku as variant_sku
                FROM {$this->table} qi
                LEFT JOIN products p ON qi.product_id = p.id
                LEFT JOIN product_variants pv ON qi.variant_id = pv.id
                WHERE qi.quotation_id = ? AND qi.deleted_at IS NULL
                ORDER BY qi.item_order ASC, qi.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quotationId]);
        
        return array_map(fn($item) => $this->transformResult($item), $stmt->fetchAll());
    }

    /**
     * Add item to quotation
     */
    public function addItem(array $data): ?int
    {
        // Set item order if not provided
        if (!isset($data['item_order'])) {
            $data['item_order'] = $this->getNextItemOrder($data['quotation_id']);
        }

        // Set default item type
        if (!isset($data['item_type'])) {
            $data['item_type'] = self::TYPE_PRODUCT;
        }

        return $this->create($data);
    }

    /**
     * Update item
     */
    public function updateItem(int $id, int $quotationId, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET ";
        $sets = [];
        $values = [];

        foreach ($data as $key => $value) {
            $sets[] = "$key = ?";
            $values[] = $value;
        }

        $sql .= implode(', ', $sets);
        $sql .= " WHERE id = ? AND quotation_id = ?";
        $values[] = $id;
        $values[] = $quotationId;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Delete item
     */
    public function deleteItem(int $id, int $quotationId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET deleted_at = ? 
                WHERE id = ? AND quotation_id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            date('Y-m-d H:i:s'),
            $id,
            $quotationId
        ]);
    }

    /**
     * Delete all items for a quotation (soft delete)
     */
    public function deleteAllByQuotation(int $quotationId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET deleted_at = ? 
                WHERE quotation_id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            date('Y-m-d H:i:s'),
            $quotationId
        ]);
    }

    /**
     * Reorder items
     */
    public function reorderItems(int $quotationId, array $itemOrders): bool
    {
        $this->beginTransaction();
        
        try {
            foreach ($itemOrders as $itemId => $order) {
                $sql = "UPDATE {$this->table} 
                        SET item_order = ? 
                        WHERE id = ? AND quotation_id = ?";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$order, $itemId, $quotationId]);
            }
            
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Get next item order
     */
    private function getNextItemOrder(int $quotationId): int
    {
        $sql = "SELECT MAX(item_order) as max_order 
                FROM {$this->table} 
                WHERE quotation_id = ? AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quotationId]);
        $result = $stmt->fetch();
        
        return ($result['max_order'] ?? 0) + 1;
    }

    /**
     * Calculate item totals (for manual calculation if needed)
     */
    public function calculateTotals(array $item): array
    {
        $quantity = (float)($item['quantity'] ?? 0);
        $unitPrice = (float)($item['unit_price'] ?? 0);
        $discountType = $item['discount_type'] ?? null;
        $discountValue = (float)($item['discount_value'] ?? 0);
        $taxRate = (float)($item['tax_rate'] ?? 0);

        // Calculate subtotal
        $subtotal = $quantity * $unitPrice;

        // Calculate discount
        $discountAmount = 0;
        if ($discountType === 'percentage') {
            $discountAmount = $subtotal * ($discountValue / 100);
        } elseif ($discountType === 'fixed') {
            $discountAmount = $discountValue;
        }

        // Calculate tax
        $taxableAmount = $subtotal - $discountAmount;
        $taxAmount = $taxableAmount * ($taxRate / 100);

        // Calculate total
        $total = $taxableAmount + $taxAmount;

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'tax_amount' => round($taxAmount, 2),
            'total' => round($total, 2)
        ];
    }

    /**
     * Get item count for quotation
     */
    public function getItemCount(int $quotationId): int
    {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE quotation_id = ? AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quotationId]);
        
        return (int)$stmt->fetch()['count'];
    }
}

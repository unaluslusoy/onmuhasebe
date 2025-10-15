<?php

namespace App\Models;

/**
 * InvoiceItem Model
 * Handles invoice line items with calculations
 */
class InvoiceItem extends BaseModel
{
    protected string $table = 'invoice_items';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'invoice_id',
        'product_id',
        'variant_id',
        'item_order',
        'item_type',
        'item_code',
        'item_name',
        'description',
        'barcode',
        'quantity',
        'unit',
        'unit_price',
        'discount_type',
        'discount_value',
        'tax_rate',
        'tax_exception_code',
        'tax_exception_reason',
        'withholding_rate',
        'gtip_code',
        'stock_movement_id',
        'affects_stock'
    ];

    protected array $casts = [
        'id' => 'int',
        'invoice_id' => 'int',
        'product_id' => 'int',
        'variant_id' => 'int',
        'item_order' => 'int',
        'quantity' => 'float',
        'unit_price' => 'float',
        'discount_value' => 'float',
        'discount_amount' => 'float',
        'tax_rate' => 'float',
        'tax_amount' => 'float',
        'withholding_rate' => 'float',
        'withholding_amount' => 'float',
        'subtotal' => 'float',
        'total' => 'float',
        'stock_movement_id' => 'int',
        'affects_stock' => 'bool'
    ];

    // Item type constants
    public const TYPE_PRODUCT = 'product';
    public const TYPE_SERVICE = 'service';
    public const TYPE_TEXT = 'text';
    public const TYPE_SUBTOTAL = 'subtotal';

    /**
     * Get all items for an invoice
     */
    public function getByInvoice(int $invoiceId): array
    {
        $sql = "SELECT ii.*, 
                       p.product_name,
                       p.product_code,
                       pv.variant_name
                FROM {$this->table} ii
                LEFT JOIN products p ON ii.product_id = p.id
                LEFT JOIN product_variants pv ON ii.variant_id = pv.id
                WHERE ii.invoice_id = ? AND ii.deleted_at IS NULL
                ORDER BY ii.item_order ASC, ii.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$invoiceId]);
        
        return array_map(fn($item) => $this->transformResult($item), $stmt->fetchAll());
    }

    /**
     * Add item to invoice
     */
    public function addItem(array $data): ?int
    {
        // Set item order if not provided
        if (!isset($data['item_order'])) {
            $data['item_order'] = $this->getNextItemOrder($data['invoice_id']);
        }

        // Set defaults
        $data['item_type'] = $data['item_type'] ?? self::TYPE_PRODUCT;
        $data['unit'] = $data['unit'] ?? 'Adet';
        $data['discount_type'] = $data['discount_type'] ?? 'percentage';
        $data['discount_value'] = $data['discount_value'] ?? 0;
        $data['tax_rate'] = $data['tax_rate'] ?? 0;
        $data['withholding_rate'] = $data['withholding_rate'] ?? 0;

        return $this->create($data);
    }

    /**
     * Update item
     */
    public function updateItem(int $id, int $invoiceId, array $data): bool
    {
        // Verify item belongs to invoice
        $item = $this->find($id);
        if (!$item || (int)($item['invoice_id'] ?? 0) !== $invoiceId) {
            return false;
        }

        return $this->update($id, $data);
    }

    /**
     * Delete item
     */
    public function deleteItem(int $id, int $invoiceId): bool
    {
        // Verify item belongs to invoice
        $item = $this->find($id);
        if (!$item || (int)($item['invoice_id'] ?? 0) !== $invoiceId) {
            return false;
        }

        return $this->delete($id);
    }

    /**
     * Reorder items
     */
    public function reorderItems(int $invoiceId, array $itemIds): bool
    {
        $this->beginTransaction();
        
        try {
            $order = 1;
            foreach ($itemIds as $itemId) {
                $sql = "UPDATE {$this->table} 
                        SET item_order = ? 
                        WHERE id = ? AND invoice_id = ?";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$order, $itemId, $invoiceId]);
                $order++;
            }

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            error_log("Item reorder failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get next item order number
     */
    private function getNextItemOrder(int $invoiceId): int
    {
        $sql = "SELECT MAX(item_order) as max_order 
                FROM {$this->table} 
                WHERE invoice_id = ? AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$invoiceId]);
        $result = $stmt->fetch();
        
        return ($result && $result['max_order']) ? (int)$result['max_order'] + 1 : 1;
    }

    /**
     * Calculate item totals (manual calculation for validation)
     */
    public function calculateTotals(array $item): array
    {
        $quantity = (float)$item['quantity'];
        $unitPrice = (float)$item['unit_price'];
        $discountType = $item['discount_type'] ?? 'percentage';
        $discountValue = (float)($item['discount_value'] ?? 0);
        $taxRate = (float)($item['tax_rate'] ?? 0);
        $withholdingRate = (float)($item['withholding_rate'] ?? 0);

        // Calculate subtotal
        $subtotal = round($quantity * $unitPrice, 2);

        // Calculate discount
        if ($discountType === 'percentage') {
            $discountAmount = round($subtotal * ($discountValue / 100), 2);
        } else {
            $discountAmount = round($discountValue, 2);
        }

        // Calculate amounts after discount
        $amountAfterDiscount = $subtotal - $discountAmount;

        // Calculate tax
        $taxAmount = round($amountAfterDiscount * ($taxRate / 100), 2);

        // Calculate withholding
        $withholdingAmount = round($amountAfterDiscount * ($withholdingRate / 100), 2);

        // Calculate total
        $total = $amountAfterDiscount + $taxAmount - $withholdingAmount;

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'withholding_amount' => $withholdingAmount,
            'total' => round($total, 2)
        ];
    }

    /**
     * Get items by product
     */
    public function getByProduct(int $productId, ?int $companyId = null): array
    {
        $sql = "SELECT ii.*, 
                       i.invoice_number,
                       i.invoice_date,
                       i.invoice_type,
                       i.company_id
                FROM {$this->table} ii
                INNER JOIN invoices i ON ii.invoice_id = i.id
                WHERE ii.product_id = ? AND ii.deleted_at IS NULL AND i.deleted_at IS NULL";
        
        $params = [$productId];

        if ($companyId !== null) {
            $sql .= " AND i.company_id = ?";
            $params[] = $companyId;
        }

        $sql .= " ORDER BY i.invoice_date DESC LIMIT 100";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Get items summary for invoice
     */
    public function getSummary(int $invoiceId): array
    {
        $sql = "SELECT 
                    COUNT(*) as item_count,
                    SUM(quantity) as total_quantity,
                    SUM(subtotal) as total_subtotal,
                    SUM(discount_amount) as total_discount,
                    SUM(tax_amount) as total_tax,
                    SUM(withholding_amount) as total_withholding,
                    SUM(total) as total_amount
                FROM {$this->table}
                WHERE invoice_id = ? AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$invoiceId]);
        
        $result = $stmt->fetch();
        
        return [
            'item_count' => (int)($result['item_count'] ?? 0),
            'total_quantity' => (float)($result['total_quantity'] ?? 0),
            'total_subtotal' => (float)($result['total_subtotal'] ?? 0),
            'total_discount' => (float)($result['total_discount'] ?? 0),
            'total_tax' => (float)($result['total_tax'] ?? 0),
            'total_withholding' => (float)($result['total_withholding'] ?? 0),
            'total_amount' => (float)($result['total_amount'] ?? 0)
        ];
    }

    /**
     * Duplicate items from one invoice to another
     */
    public function duplicateItems(int $sourceInvoiceId, int $targetInvoiceId): bool
    {
        $this->beginTransaction();
        
        try {
            $sourceItems = $this->getByInvoice($sourceInvoiceId);
            
            foreach ($sourceItems as $item) {
                $newItem = [
                    'invoice_id' => $targetInvoiceId,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'item_order' => $item['item_order'],
                    'item_type' => $item['item_type'],
                    'item_code' => $item['item_code'],
                    'item_name' => $item['item_name'],
                    'description' => $item['description'],
                    'barcode' => $item['barcode'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'discount_type' => $item['discount_type'],
                    'discount_value' => $item['discount_value'],
                    'tax_rate' => $item['tax_rate'],
                    'tax_exception_code' => $item['tax_exception_code'],
                    'tax_exception_reason' => $item['tax_exception_reason'],
                    'withholding_rate' => $item['withholding_rate'],
                    'gtip_code' => $item['gtip_code']
                ];
                
                $this->create($newItem);
            }

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            error_log("Item duplication failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Bulk update items
     */
    public function bulkUpdate(int $invoiceId, array $items): bool
    {
        $this->beginTransaction();
        
        try {
            foreach ($items as $item) {
                if (isset($item['id'])) {
                    // Update existing item
                    $this->updateItem($item['id'], $invoiceId, $item);
                } else {
                    // Create new item
                    $item['invoice_id'] = $invoiceId;
                    $this->addItem($item);
                }
            }

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            error_log("Bulk update failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get items grouped by product
     */
    public function getGroupedByProduct(int $invoiceId): array
    {
        $sql = "SELECT 
                    ii.product_id,
                    ii.item_code,
                    ii.item_name,
                    ii.unit,
                    SUM(ii.quantity) as total_quantity,
                    AVG(ii.unit_price) as avg_price,
                    SUM(ii.total) as total_amount
                FROM {$this->table} ii
                WHERE ii.invoice_id = ? AND ii.deleted_at IS NULL
                GROUP BY ii.product_id, ii.item_code, ii.item_name, ii.unit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$invoiceId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Check if item has stock movement
     */
    public function hasStockMovement(int $itemId): bool
    {
        $item = $this->find($itemId);
        return $item && !empty($item['stock_movement_id']);
    }

    /**
     * Link stock movement to item
     */
    public function linkStockMovement(int $itemId, int $stockMovementId): bool
    {
        return $this->update($itemId, [
            'stock_movement_id' => $stockMovementId,
            'affects_stock' => true
        ]);
    }

    /**
     * Get items affecting stock
     */
    public function getStockAffectingItems(int $invoiceId): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE invoice_id = ? 
                AND affects_stock = TRUE 
                AND deleted_at IS NULL
                ORDER BY item_order ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$invoiceId]);
        
        return array_map(fn($item) => $this->transformResult($item), $stmt->fetchAll());
    }
}

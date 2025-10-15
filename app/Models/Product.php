<?php

namespace App\Models;

use PDO;

/**
 * Product Model
 * Handles product and service management
 */
class Product extends BaseModel
{
    protected string $table = 'products';

    /**
     * Get all products with pagination and filters
     *
     * @param int $companyId Company ID
     * @param array $filters Filter conditions
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Products and pagination info
     */
    public function getAll(int $companyId, array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $offset = ($page - 1) * $perPage;
        $where = ["p.company_id = :company_id", "p.deleted_at IS NULL"];
        $params = [':company_id' => $companyId];

        // Filters
        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }

        if (!empty($filters['product_type'])) {
            $where[] = "p.product_type = :product_type";
            $params[':product_type'] = $filters['product_type'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(p.product_name LIKE :search OR p.product_code LIKE :search OR p.barcode LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['is_active'])) {
            $where[] = "p.is_active = :is_active";
            $params[':is_active'] = (int) $filters['is_active'];
        }

        if (isset($filters['stock_tracking'])) {
            $where[] = "p.stock_tracking = :stock_tracking";
            $params[':stock_tracking'] = (int) $filters['stock_tracking'];
        }

        // Stock status filters
        if (!empty($filters['stock_status'])) {
            switch ($filters['stock_status']) {
                case 'low':
                    $where[] = "p.current_stock > 0 AND p.current_stock <= p.min_stock_level";
                    break;
                case 'out':
                    $where[] = "p.current_stock <= 0";
                    break;
                case 'in':
                    $where[] = "p.current_stock > 0";
                    break;
            }
        }

        $whereClause = implode(' AND ', $where);

        // Count total
        $countSql = "SELECT COUNT(*) as total FROM products p WHERE " . $whereClause;
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Get data with category name
        $orderBy = $filters['order_by'] ?? 'p.product_name';
        $orderDir = $filters['order_dir'] ?? 'ASC';
        
        $sql = "SELECT p.*, pc.category_name
                FROM products p
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                WHERE " . $whereClause . "
                ORDER BY $orderBy $orderDir
                LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'data' => $products,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage)
                ]
            ];
        } catch (\PDOException $e) {
            logger('Product getAll failed: ' . $e->getMessage(), 'error');
            return [
                'data' => [],
                'pagination' => [
                    'total' => 0,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => 0
                ]
            ];
        }
    }

    /**
     * Find product by ID
     *
     * @param int $id Product ID
     * @param int|null $companyId Company ID filter
     * @return array|null Product data or null if not found
     */
    public function find(int $id, ?int $companyId = null): ?array
    {
        $sql = "SELECT p.*, pc.category_name
                FROM products p
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                WHERE p.id = :id";
        $params = [':id' => $id];

        if ($companyId) {
            $sql .= " AND p.company_id = :company_id";
            $params[':company_id'] = $companyId;
        }

        $sql .= " AND p.deleted_at IS NULL LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            return $product ?: null;
        } catch (\PDOException $e) {
            logger('Find product failed: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Find by product code
     *
     * @param string $code Product code
     * @param int $companyId Company ID
     * @return array|null
     */
    public function findByCode(string $code, int $companyId): ?array
    {
        $sql = "SELECT * FROM products 
                WHERE product_code = :code AND company_id = :company_id 
                AND deleted_at IS NULL LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':code' => $code,
                ':company_id' => $companyId
            ]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            return $product ?: null;
        } catch (\PDOException $e) {
            logger('Find by code failed: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Find by barcode (checks both products and variants)
     *
     * @param string $barcode Barcode
     * @param int $companyId Company ID
     * @return array|null
     */
    public function findByBarcode(string $barcode, int $companyId): ?array
    {
        // First check main products
        $sql = "SELECT p.*, pc.category_name
                FROM products p
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                WHERE p.barcode = :barcode AND p.company_id = :company_id 
                AND p.deleted_at IS NULL LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':barcode' => $barcode,
                ':company_id' => $companyId
            ]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                $product['is_variant'] = false;
                return $product;
            }

            // Check variants
            $sql = "SELECT p.*, pv.variant_name, pv.price_difference, pv.stock_quantity as variant_stock,
                           pv.barcode as variant_barcode, pc.category_name
                    FROM product_variants pv
                    JOIN products p ON pv.product_id = p.id
                    LEFT JOIN product_categories pc ON p.category_id = pc.id
                    WHERE pv.barcode = :barcode AND p.company_id = :company_id 
                    AND p.deleted_at IS NULL AND pv.is_active = TRUE LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':barcode' => $barcode,
                ':company_id' => $companyId
            ]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                // Calculate variant price
                $product['sale_price'] = $product['sale_price'] + $product['price_difference'];
                $product['current_stock'] = $product['variant_stock'];
                $product['is_variant'] = true;
            }

            return $product ?: null;
        } catch (\PDOException $e) {
            logger('Find by barcode failed: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Create new product
     *
     * @param array $data Product data
     * @return int|null Product ID or null on failure
     */
    public function create(array $data): ?int
    {
        // Generate product code if not provided
        if (empty($data['product_code'])) {
            $data['product_code'] = $this->generateProductCode($data['company_id']);
        }

        $sql = "INSERT INTO products (
                    company_id, product_code, barcode, product_name, product_type,
                    category_id, description, short_description, unit,
                    purchase_price, sale_price, discount_rate,
                    kdv_rate, kdv_included, currency,
                    stock_tracking, current_stock, min_stock_level, max_stock_level, critical_stock_level,
                    weight, width, height, depth,
                    image_path, images, has_variants, variant_attributes,
                    supplier_id, supplier_code, accounting_code, tags, is_active
                ) VALUES (
                    :company_id, :product_code, :barcode, :product_name, :product_type,
                    :category_id, :description, :short_description, :unit,
                    :purchase_price, :sale_price, :discount_rate,
                    :kdv_rate, :kdv_included, :currency,
                    :stock_tracking, :current_stock, :min_stock_level, :max_stock_level, :critical_stock_level,
                    :weight, :width, :height, :depth,
                    :image_path, :images, :has_variants, :variant_attributes,
                    :supplier_id, :supplier_code, :accounting_code, :tags, :is_active
                )";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':company_id' => $data['company_id'],
                ':product_code' => $data['product_code'],
                ':barcode' => $data['barcode'] ?? null,
                ':product_name' => $data['product_name'],
                ':product_type' => $data['product_type'] ?? 'urun',
                ':category_id' => $data['category_id'] ?? null,
                ':description' => $data['description'] ?? null,
                ':short_description' => $data['short_description'] ?? null,
                ':unit' => $data['unit'] ?? 'adet',
                ':purchase_price' => $data['purchase_price'] ?? 0.00,
                ':sale_price' => $data['sale_price'],
                ':discount_rate' => $data['discount_rate'] ?? 0.00,
                ':kdv_rate' => $data['kdv_rate'] ?? 20.00,
                ':kdv_included' => $data['kdv_included'] ?? false,
                ':currency' => $data['currency'] ?? 'TRY',
                ':stock_tracking' => $data['stock_tracking'] ?? true,
                ':current_stock' => $data['current_stock'] ?? 0.00,
                ':min_stock_level' => $data['min_stock_level'] ?? 0.00,
                ':max_stock_level' => $data['max_stock_level'] ?? 0.00,
                ':critical_stock_level' => $data['critical_stock_level'] ?? 0.00,
                ':weight' => $data['weight'] ?? null,
                ':width' => $data['width'] ?? null,
                ':height' => $data['height'] ?? null,
                ':depth' => $data['depth'] ?? null,
                ':image_path' => $data['image_path'] ?? null,
                ':images' => isset($data['images']) ? json_encode($data['images']) : null,
                ':has_variants' => $data['has_variants'] ?? false,
                ':variant_attributes' => isset($data['variant_attributes']) ? json_encode($data['variant_attributes']) : null,
                ':supplier_id' => $data['supplier_id'] ?? null,
                ':supplier_code' => $data['supplier_code'] ?? null,
                ':accounting_code' => $data['accounting_code'] ?? null,
                ':tags' => isset($data['tags']) ? json_encode($data['tags']) : null,
                ':is_active' => $data['is_active'] ?? true
            ]);

            return (int) $this->db->lastInsertId();
        } catch (\PDOException $e) {
            logger('Product creation failed: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Update product
     *
     * @param int $id Product ID
     * @param array $data Update data
     * @param int|null $companyId Company ID filter
     * @return bool Success status
     */
    public function update(int $id, array $data, ?int $companyId = null): bool
    {
        // Log price change if prices changed
        if (isset($data['sale_price']) || isset($data['purchase_price'])) {
            $current = $this->find($id, $companyId);
            if ($current) {
                $this->logPriceChange($id, $data['changed_by'] ?? null, $current, $data);
            }
        }

        $allowedFields = [
            'product_name', 'product_type', 'category_id', 'description', 'short_description', 'unit',
            'purchase_price', 'sale_price', 'discount_rate', 'kdv_rate', 'kdv_included', 'currency',
            'stock_tracking', 'min_stock_level', 'max_stock_level', 'critical_stock_level',
            'weight', 'width', 'height', 'depth', 'image_path', 'images',
            'has_variants', 'variant_attributes', 'supplier_id', 'supplier_code',
            'accounting_code', 'tags', 'is_active'
        ];

        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "$key = :$key";
                $params[":$key"] = is_array($value) ? json_encode($value) : $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = :id";

        if ($companyId) {
            $sql .= " AND company_id = :company_id";
            $params[':company_id'] = $companyId;
        }

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            logger('Product update failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Update stock
     *
     * @param int $id Product ID
     * @param float $quantity Quantity
     * @param string $operation 'set', 'add', 'subtract'
     * @return bool
     */
    public function updateStock(int $id, float $quantity, string $operation = 'set'): bool
    {
        switch ($operation) {
            case 'add':
                $sql = "UPDATE products SET current_stock = current_stock + :quantity WHERE id = :id";
                break;
            case 'subtract':
                $sql = "UPDATE products SET current_stock = current_stock - :quantity WHERE id = :id";
                break;
            default: // 'set'
                $sql = "UPDATE products SET current_stock = :quantity WHERE id = :id";
        }

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':quantity' => $quantity, ':id' => $id]);
        } catch (\PDOException $e) {
            logger('Stock update failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Soft delete product
     *
     * @param int $id Product ID
     * @param int|null $companyId Company ID filter
     * @return bool Success status
     */
    public function delete(int $id, ?int $companyId = null): bool
    {
        $sql = "UPDATE products SET deleted_at = NOW() WHERE id = :id";
        $params = [':id' => $id];

        if ($companyId) {
            $sql .= " AND company_id = :company_id";
            $params[':company_id'] = $companyId;
        }

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            logger('Product deletion failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Get low stock products
     *
     * @param int $companyId Company ID
     * @return array
     */
    public function getLowStock(int $companyId): array
    {
        $sql = "SELECT * FROM products
                WHERE company_id = :company_id
                AND stock_tracking = TRUE
                AND is_active = TRUE
                AND deleted_at IS NULL
                AND current_stock > 0
                AND current_stock <= min_stock_level
                ORDER BY current_stock ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':company_id' => $companyId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            logger('Get low stock failed: ' . $e->getMessage(), 'error');
            return [];
        }
    }

    /**
     * Get out of stock products
     *
     * @param int $companyId Company ID
     * @return array
     */
    public function getOutOfStock(int $companyId): array
    {
        $sql = "SELECT * FROM products
                WHERE company_id = :company_id
                AND stock_tracking = TRUE
                AND is_active = TRUE
                AND deleted_at IS NULL
                AND current_stock <= 0
                ORDER BY product_name ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':company_id' => $companyId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            logger('Get out of stock failed: ' . $e->getMessage(), 'error');
            return [];
        }
    }

    /**
     * Get product statistics
     *
     * @param int $companyId Company ID
     * @return array
     */
    public function getStats(int $companyId): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_products,
                    COUNT(CASE WHEN is_active = TRUE THEN 1 END) as active_products,
                    COUNT(CASE WHEN stock_tracking = TRUE THEN 1 END) as tracked_products,
                    COUNT(CASE WHEN current_stock <= 0 AND stock_tracking = TRUE THEN 1 END) as out_of_stock,
                    COUNT(CASE WHEN current_stock > 0 AND current_stock <= min_stock_level AND stock_tracking = TRUE THEN 1 END) as low_stock,
                    SUM(current_stock * purchase_price) as total_stock_value,
                    AVG(sale_price) as avg_sale_price,
                    SUM(current_stock) as total_stock_quantity
                FROM products
                WHERE company_id = :company_id
                AND deleted_at IS NULL";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':company_id' => $companyId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            logger('Get stats failed: ' . $e->getMessage(), 'error');
            return [];
        }
    }

    /**
     * Generate unique product code
     *
     * @param int $companyId Company ID
     * @return string Generated code
     */
    private function generateProductCode(int $companyId): string
    {
        $prefix = 'PRD';
        
        $sql = "SELECT product_code FROM products 
                WHERE company_id = :company_id 
                AND product_code LIKE :prefix 
                ORDER BY product_code DESC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':company_id' => $companyId,
            ':prefix' => $prefix . '%'
        ]);
        
        $lastCode = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lastCode) {
            $number = (int) substr($lastCode['product_code'], 3) + 1;
        } else {
            $number = 1;
        }
        
        return $prefix . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Log price change
     *
     * @param int $productId Product ID
     * @param int|null $userId User ID
     * @param array $oldData Old data
     * @param array $newData New data
     * @return void
     */
    private function logPriceChange(int $productId, ?int $userId, array $oldData, array $newData): void
    {
        if (!$userId) return;

        $sql = "INSERT INTO product_price_history (
                    product_id, old_purchase_price, new_purchase_price,
                    old_sale_price, new_sale_price, changed_by, change_reason
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $productId,
                $oldData['purchase_price'] ?? 0,
                $newData['purchase_price'] ?? $oldData['purchase_price'] ?? 0,
                $oldData['sale_price'] ?? 0,
                $newData['sale_price'] ?? $oldData['sale_price'] ?? 0,
                $userId,
                $newData['price_change_reason'] ?? 'Manuel güncelleme'
            ]);
        } catch (\PDOException $e) {
            logger('Price change log failed: ' . $e->getMessage(), 'error');
        }
    }
}

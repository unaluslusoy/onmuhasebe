<?php

namespace App\Models;

use PDO;

/**
 * ProductCategory Model
 * Handles product category operations with parent-child hierarchy
 */
class ProductCategory extends BaseModel
{
    protected string $table = 'product_categories';

    /**
     * Get all categories (tree structure)
     *
     * @param int $companyId Company ID
     * @param bool $activeOnly Get only active categories
     * @return array Categories in tree structure
     */
    public function getAll(int $companyId, bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM product_categories WHERE company_id = :company_id";
        
        if ($activeOnly) {
            $sql .= " AND is_active = TRUE";
        }
        
        $sql .= " ORDER BY sort_order ASC, category_name ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':company_id' => $companyId]);
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Build tree structure
            return $this->buildTree($categories);
        } catch (\PDOException $e) {
            logger('Get all categories failed: ' . $e->getMessage(), 'error');
            return [];
        }
    }

    /**
     * Get flat list of categories (no tree)
     *
     * @param int $companyId Company ID
     * @param bool $activeOnly Get only active categories
     * @return array Flat list of categories
     */
    public function getFlat(int $companyId, bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM product_categories WHERE company_id = :company_id";
        
        if ($activeOnly) {
            $sql .= " AND is_active = TRUE";
        }
        
        $sql .= " ORDER BY sort_order ASC, category_name ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':company_id' => $companyId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            logger('Get flat categories failed: ' . $e->getMessage(), 'error');
            return [];
        }
    }

    /**
     * Find category by ID
     *
     * @param int $id Category ID
     * @param int|null $companyId Company ID filter
     * @return array|null Category data or null if not found
     */
    public function find(int $id, ?int $companyId = null): ?array
    {
        $sql = "SELECT * FROM product_categories WHERE id = :id";
        $params = [':id' => $id];

        if ($companyId) {
            $sql .= " AND company_id = :company_id";
            $params[':company_id'] = $companyId;
        }

        $sql .= " LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);

            return $category ?: null;
        } catch (\PDOException $e) {
            logger('Find category failed: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Create new category
     *
     * @param array $data Category data
     * @return int|null Category ID or null on failure
     */
    public function create(array $data): ?int
    {
        $sql = "INSERT INTO product_categories (
                    company_id, category_name, parent_category_id,
                    description, image_path, color_code, icon, sort_order, is_active
                ) VALUES (
                    :company_id, :category_name, :parent_category_id,
                    :description, :image_path, :color_code, :icon, :sort_order, :is_active
                )";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':company_id' => $data['company_id'],
                ':category_name' => $data['category_name'],
                ':parent_category_id' => $data['parent_category_id'] ?? null,
                ':description' => $data['description'] ?? null,
                ':image_path' => $data['image_path'] ?? null,
                ':color_code' => $data['color_code'] ?? '#6366F1',
                ':icon' => $data['icon'] ?? null,
                ':sort_order' => $data['sort_order'] ?? 0,
                ':is_active' => $data['is_active'] ?? true
            ]);

            return (int) $this->db->lastInsertId();
        } catch (\PDOException $e) {
            logger('Category creation failed: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Update category
     *
     * @param int $id Category ID
     * @param array $data Update data
     * @param int|null $companyId Company ID filter
     * @return bool Success status
     */
    public function update(int $id, array $data, ?int $companyId = null): bool
    {
        $allowedFields = [
            'category_name', 'parent_category_id', 'description', 
            'image_path', 'color_code', 'icon', 'sort_order', 'is_active'
        ];

        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE product_categories SET " . implode(', ', $fields) . " WHERE id = :id";

        if ($companyId) {
            $sql .= " AND company_id = :company_id";
            $params[':company_id'] = $companyId;
        }

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            logger('Category update failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Delete category
     *
     * @param int $id Category ID
     * @param int|null $companyId Company ID filter
     * @return bool Success status
     */
    public function delete(int $id, ?int $companyId = null): bool
    {
        // Check if category has subcategories
        $sql = "SELECT COUNT(*) as count FROM product_categories 
                WHERE parent_category_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $hasChildren = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;

        if ($hasChildren) {
            logger('Cannot delete category with subcategories', 'warning');
            return false;
        }

        // Check if category has products
        $sql = "SELECT COUNT(*) as count FROM products 
                WHERE category_id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $hasProducts = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;

        if ($hasProducts) {
            logger('Cannot delete category with products', 'warning');
            return false;
        }

        // Delete category
        $sql = "DELETE FROM product_categories WHERE id = :id";
        $params = [':id' => $id];

        if ($companyId) {
            $sql .= " AND company_id = :company_id";
            $params[':company_id'] = $companyId;
        }

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            logger('Category deletion failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Get category breadcrumb
     *
     * @param int $categoryId Category ID
     * @return array Breadcrumb array
     */
    public function getBreadcrumb(int $categoryId): array
    {
        $breadcrumb = [];
        $currentId = $categoryId;

        while ($currentId) {
            $sql = "SELECT id, category_name, parent_category_id 
                    FROM product_categories WHERE id = :id LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $currentId]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$category) {
                break;
            }

            array_unshift($breadcrumb, [
                'id' => $category['id'],
                'name' => $category['category_name']
            ]);

            $currentId = $category['parent_category_id'];
        }

        return $breadcrumb;
    }

    /**
     * Get all subcategories (recursive)
     *
     * @param int $categoryId Parent category ID
     * @return array Subcategory IDs
     */
    public function getSubcategoryIds(int $categoryId): array
    {
        $ids = [$categoryId];
        
        $sql = "SELECT id FROM product_categories 
                WHERE parent_category_id = :parent_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':parent_id' => $categoryId]);
        $children = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($children as $childId) {
            $ids = array_merge($ids, $this->getSubcategoryIds($childId));
        }

        return $ids;
    }

    /**
     * Build tree structure from flat array
     *
     * @param array $categories Flat array of categories
     * @param int|null $parentId Parent ID to build from
     * @return array Tree structure
     */
    private function buildTree(array $categories, ?int $parentId = null): array
    {
        $branch = [];

        foreach ($categories as $category) {
            if ($category['parent_category_id'] == $parentId) {
                $children = $this->buildTree($categories, $category['id']);
                if ($children) {
                    $category['children'] = $children;
                    $category['has_children'] = true;
                } else {
                    $category['has_children'] = false;
                }
                $branch[] = $category;
            }
        }

        return $branch;
    }

    /**
     * Get product count by category
     *
     * @param int $companyId Company ID
     * @return array Category ID => Product count
     */
    public function getProductCounts(int $companyId): array
    {
        $sql = "SELECT category_id, COUNT(*) as product_count 
                FROM products 
                WHERE company_id = :company_id AND deleted_at IS NULL
                GROUP BY category_id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':company_id' => $companyId]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $counts = [];
            foreach ($result as $row) {
                $counts[$row['category_id']] = (int) $row['product_count'];
            }

            return $counts;
        } catch (\PDOException $e) {
            logger('Get product counts failed: ' . $e->getMessage(), 'error');
            return [];
        }
    }
}

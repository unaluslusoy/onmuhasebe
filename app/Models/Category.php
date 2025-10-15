<?php

namespace App\Models;

use PDO;

/**
 * Category Model
 * Handles categories for different entity types
 */
class Category extends BaseModel
{
    protected string $table = 'categories';

    // Category types
    const TYPE_SALES = 'sales';
    const TYPE_EXPENSE = 'expense';
    const TYPE_INCOME_EXPENSE_LABEL = 'income_expense_label';
    const TYPE_SERVICE_PRODUCT = 'service_product';
    const TYPE_EMPLOYEE = 'employee';
    const TYPE_CUSTOMER_SUPPLIER = 'customer_supplier';

    /**
     * Get all category types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_SALES => 'Satış Kategorileri',
            self::TYPE_EXPENSE => 'Gider Kategorileri',
            self::TYPE_INCOME_EXPENSE_LABEL => 'Gelir ve Gider Etiketleri',
            self::TYPE_SERVICE_PRODUCT => 'Hizmet ve Ürün Kategorileri',
            self::TYPE_EMPLOYEE => 'Çalışan Kategorileri',
            self::TYPE_CUSTOMER_SUPPLIER => 'Müşteri ve Tedarikçi Kategorileri'
        ];
    }

    /**
     * Create a new category
     */
    public function create(array $data): ?int
    {
        $sql = "INSERT INTO categories (
                    company_id,
                    name,
                    type,
                    color_bg,
                    color_text,
                    description,
                    parent_id,
                    sort_order,
                    created_by
                ) VALUES (
                    :company_id,
                    :name,
                    :type,
                    :color_bg,
                    :color_text,
                    :description,
                    :parent_id,
                    :sort_order,
                    :created_by
                )";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':company_id' => $data['company_id'],
                ':name' => $data['name'],
                ':type' => $data['type'],
                ':color_bg' => $data['color_bg'] ?? '#3B82F6',
                ':color_text' => $data['color_text'] ?? '#FFFFFF',
                ':description' => $data['description'] ?? null,
                ':parent_id' => $data['parent_id'] ?? null,
                ':sort_order' => $data['sort_order'] ?? 0,
                ':created_by' => $data['created_by'] ?? null
            ]);

            return (int) $this->db->lastInsertId();
        } catch (\PDOException $e) {
            logger('Category creation failed: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Get categories by company and type
     */
    public function getByCompanyAndType(int $companyId, string $type): array
    {
        $sql = "SELECT * FROM categories 
                WHERE company_id = :company_id 
                AND type = :type 
                AND is_active = 1 
                AND deleted_at IS NULL
                ORDER BY sort_order ASC, name ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':company_id' => $companyId,
                ':type' => $type
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            logger('Get categories by type failed: ' . $e->getMessage(), 'error');
            return [];
        }
    }

    /**
     * Get all categories by company
     */
    public function getByCompany(int $companyId): array
    {
        $sql = "SELECT * FROM categories 
                WHERE company_id = :company_id 
                AND is_active = 1 
                AND deleted_at IS NULL
                ORDER BY type ASC, sort_order ASC, name ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':company_id' => $companyId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            logger('Get categories by company failed: ' . $e->getMessage(), 'error');
            return [];
        }
    }

    /**
     * Update category
     */
    public function update(int $id, array $data): bool
    {
        $allowedFields = [
            'name', 'color_bg', 'color_text', 'description', 
            'parent_id', 'sort_order', 'is_active'
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

        $sql = "UPDATE categories SET " . implode(', ', $fields) . " WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            logger('Category update failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Soft delete category
     */
    public function delete(int $id): bool
    {
        $sql = "UPDATE categories SET deleted_at = NOW(), is_active = 0 WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            logger('Category deletion failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Check if category name exists for company and type
     */
    public function nameExists(string $name, int $companyId, string $type, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM categories 
                WHERE name = :name 
                AND company_id = :company_id 
                AND type = :type 
                AND deleted_at IS NULL";
        
        $params = [
            ':name' => $name,
            ':company_id' => $companyId,
            ':type' => $type
        ];

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $sql .= " LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            logger('Category name check failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Get category count by type
     */
    public function countByType(int $companyId, string $type): int
    {
        $sql = "SELECT COUNT(*) as total FROM categories 
                WHERE company_id = :company_id 
                AND type = :type 
                AND is_active = 1 
                AND deleted_at IS NULL";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':company_id' => $companyId,
                ':type' => $type
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) ($result['total'] ?? 0);
        } catch (\PDOException $e) {
            logger('Count categories by type failed: ' . $e->getMessage(), 'error');
            return 0;
        }
    }
}

<?php

namespace App\Models;

use PDO;

/**
 * Company Model
 * Handles company data and operations
 */
class Company extends BaseModel
{
    protected string $table = 'companies';

    /**
     * Create a new company
     *
     * @param array $data Company data
     * @return int|null Company ID or null on failure
     */
    public function create(array $data): ?int
    {
        $sql = "INSERT INTO companies (
                    owner_id,
                    name,
                    trade_name,
                    tax_office,
                    tax_number,
                    vkn,
                    tckn,
                    mersis_no,
                    company_type,
                    address,
                    district,
                    city,
                    country,
                    postal_code,
                    phone,
                    fax,
                    email,
                    website
                ) VALUES (
                    :owner_id,
                    :name,
                    :trade_name,
                    :tax_office,
                    :tax_number,
                    :vkn,
                    :tckn,
                    :mersis_no,
                    :company_type,
                    :address,
                    :district,
                    :city,
                    :country,
                    :postal_code,
                    :phone,
                    :fax,
                    :email,
                    :website
                )";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':owner_id' => $data['owner_id'],
                ':name' => $data['name'],
                ':trade_name' => $data['trade_name'] ?? null,
                ':tax_office' => $data['tax_office'] ?? null,
                ':tax_number' => $data['tax_number'] ?? null,
                ':vkn' => $data['vkn'] ?? null,
                ':tckn' => $data['tckn'] ?? null,
                ':mersis_no' => $data['mersis_no'] ?? null,
                ':company_type' => $data['company_type'] ?? 'limited',
                ':address' => $data['address'] ?? null,
                ':district' => $data['district'] ?? null,
                ':city' => $data['city'] ?? null,
                ':country' => $data['country'] ?? 'Türkiye',
                ':postal_code' => $data['postal_code'] ?? null,
                ':phone' => $data['phone'] ?? null,
                ':fax' => $data['fax'] ?? null,
                ':email' => $data['email'] ?? null,
                ':website' => $data['website'] ?? null
            ]);

            return (int) $this->db->lastInsertId();
        } catch (\PDOException $e) {
            logger('Company creation failed: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Find company by ID
     *
     * @param int $id Company ID
     * @return array|null Company data or null if not found
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM companies WHERE id = :id LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $company = $stmt->fetch(PDO::FETCH_ASSOC);

            return $company ?: null;
        } catch (\PDOException $e) {
            logger('Find company failed: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Update company
     *
     * @param int $id Company ID
     * @param array $data Update data
     * @return bool Success status
     */
    public function update(int $id, array $data): bool
    {
        $allowedFields = [
            'name', 'trade_name', 'tax_office', 'tax_number', 'vkn', 'tckn',
            'mersis_no', 'company_type', 'address', 'district', 'city', 
            'country', 'postal_code', 'phone', 'fax', 'email', 'website',
            'company_logo', 'company_stamp', 'company_signature',
            'document_type', 'sector', 'annual_revenue', 'employee_count',
            'foundation_year', 'business_description'
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

        $sql = "UPDATE companies SET " . implode(', ', $fields) . " WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            logger('Company update failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Delete company (soft delete - set is_active to false)
     *
     * @param int $id Company ID
     * @return bool Success status
     */
    public function delete(int $id): bool
    {
        $sql = "UPDATE companies SET is_active = 0 WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            logger('Company deletion failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Check if company name exists
     *
     * @param string $name Company name
     * @param int|null $excludeId Exclude this company ID from check
     * @return bool True if exists
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM companies WHERE name = :name";
        $params = [':name' => $name];

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
            logger('Company name check failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Get all companies with pagination
     *
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param array $conditions Filter conditions
     * @return array Companies and pagination info
     */
    public function paginate(int $page = 1, int $perPage = 15, array $conditions = []): array
    {
        $offset = ($page - 1) * $perPage;

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM companies WHERE is_active = 1";
        $totalStmt = $this->db->query($countSql);
        $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Get companies
        $sql = "SELECT * FROM companies WHERE is_active = 1 ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'data' => $companies,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage)
                ]
            ];
        } catch (\PDOException $e) {
            logger('Company pagination failed: ' . $e->getMessage(), 'error');
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
}

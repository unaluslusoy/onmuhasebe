<?php

namespace App\Models;

use PDO;

/**
 * CariAccount Model
 * Handles customer and supplier account operations
 */
class CariAccount extends BaseModel
{
    protected string $table = 'cari_accounts';

    /**
     * Create a new cari account
     *
     * @param array $data Account data
     * @return int|null Account ID or null on failure
     */
    public function create(array $data): ?int
    {
        // Cari kodu oluştur (yoksa)
        if (empty($data['code'])) {
            $data['code'] = $this->generateAccountCode($data['company_id'], $data['account_type']);
        }

        $sql = "INSERT INTO cari_accounts (
                    company_id, account_type, code, title, name, surname,
                    tax_office, tax_number, vkn, tckn,
                    email, phone, mobile, fax, website,
                    billing_address, billing_district, billing_city, billing_country, billing_postal_code,
                    shipping_address, shipping_district, shipping_city, shipping_country, shipping_postal_code,
                    currency, payment_term, credit_limit,
                    bank_name, bank_branch, bank_account_no, iban,
                    notes, tags, risk_group, customer_group,
                    efatura_enabled, efatura_alias, is_active
                ) VALUES (
                    :company_id, :account_type, :code, :title, :name, :surname,
                    :tax_office, :tax_number, :vkn, :tckn,
                    :email, :phone, :mobile, :fax, :website,
                    :billing_address, :billing_district, :billing_city, :billing_country, :billing_postal_code,
                    :shipping_address, :shipping_district, :shipping_city, :shipping_country, :shipping_postal_code,
                    :currency, :payment_term, :credit_limit,
                    :bank_name, :bank_branch, :bank_account_no, :iban,
                    :notes, :tags, :risk_group, :customer_group,
                    :efatura_enabled, :efatura_alias, :is_active
                )";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':company_id' => $data['company_id'],
                ':account_type' => $data['account_type'] ?? 'customer',
                ':code' => $data['code'],
                ':title' => $data['title'],
                ':name' => $data['name'],
                ':surname' => $data['surname'] ?? null,
                ':tax_office' => $data['tax_office'] ?? null,
                ':tax_number' => $data['tax_number'] ?? null,
                ':vkn' => $data['vkn'] ?? null,
                ':tckn' => $data['tckn'] ?? null,
                ':email' => $data['email'] ?? null,
                ':phone' => $data['phone'] ?? null,
                ':mobile' => $data['mobile'] ?? null,
                ':fax' => $data['fax'] ?? null,
                ':website' => $data['website'] ?? null,
                ':billing_address' => $data['billing_address'] ?? null,
                ':billing_district' => $data['billing_district'] ?? null,
                ':billing_city' => $data['billing_city'] ?? null,
                ':billing_country' => $data['billing_country'] ?? 'Türkiye',
                ':billing_postal_code' => $data['billing_postal_code'] ?? null,
                ':shipping_address' => $data['shipping_address'] ?? $data['billing_address'] ?? null,
                ':shipping_district' => $data['shipping_district'] ?? $data['billing_district'] ?? null,
                ':shipping_city' => $data['shipping_city'] ?? $data['billing_city'] ?? null,
                ':shipping_country' => $data['shipping_country'] ?? $data['billing_country'] ?? 'Türkiye',
                ':shipping_postal_code' => $data['shipping_postal_code'] ?? $data['billing_postal_code'] ?? null,
                ':currency' => $data['currency'] ?? 'TRY',
                ':payment_term' => $data['payment_term'] ?? 0,
                ':credit_limit' => $data['credit_limit'] ?? 0.00,
                ':bank_name' => $data['bank_name'] ?? null,
                ':bank_branch' => $data['bank_branch'] ?? null,
                ':bank_account_no' => $data['bank_account_no'] ?? null,
                ':iban' => $data['iban'] ?? null,
                ':notes' => $data['notes'] ?? null,
                ':tags' => $data['tags'] ?? null,
                ':risk_group' => $data['risk_group'] ?? 'low',
                ':customer_group' => $data['customer_group'] ?? null,
                ':efatura_enabled' => $data['efatura_enabled'] ?? false,
                ':efatura_alias' => $data['efatura_alias'] ?? null,
                ':is_active' => $data['is_active'] ?? true
            ]);

            return (int) $this->db->lastInsertId();
        } catch (\PDOException $e) {
            logger('Cari account creation failed: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Generate unique account code
     *
     * @param int $companyId Company ID
     * @param string $type Account type (customer/supplier/both)
     * @return string Generated code
     */
    private function generateAccountCode(int $companyId, string $type): string
    {
        $prefix = $type === 'customer' ? 'C' : ($type === 'supplier' ? 'S' : 'B');
        
        // Son kodu bul
        $sql = "SELECT code FROM cari_accounts 
                WHERE company_id = :company_id 
                AND code LIKE :prefix 
                ORDER BY code DESC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':company_id' => $companyId,
            ':prefix' => $prefix . '%'
        ]);
        
        $lastCode = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lastCode) {
            $number = (int) substr($lastCode['code'], 1) + 1;
        } else {
            $number = 1;
        }
        
        return $prefix . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Find cari account by ID
     *
     * @param int $id Account ID
     * @param int|null $companyId Company ID filter
     * @return array|null Account data or null if not found
     */
    public function find(int $id, ?int $companyId = null): ?array
    {
        $sql = "SELECT * FROM cari_accounts WHERE id = :id";
        $params = [':id' => $id];

        if ($companyId) {
            $sql .= " AND company_id = :company_id";
            $params[':company_id'] = $companyId;
        }

        $sql .= " AND deleted_at IS NULL LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $account = $stmt->fetch(PDO::FETCH_ASSOC);

            return $account ?: null;
        } catch (\PDOException $e) {
            logger('Find cari account failed: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Find by code
     *
     * @param string $code Account code
     * @param int $companyId Company ID
     * @return array|null
     */
    public function findByCode(string $code, int $companyId): ?array
    {
        $sql = "SELECT * FROM cari_accounts 
                WHERE code = :code AND company_id = :company_id 
                AND deleted_at IS NULL LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':code' => $code,
                ':company_id' => $companyId
            ]);
            $account = $stmt->fetch(PDO::FETCH_ASSOC);

            return $account ?: null;
        } catch (\PDOException $e) {
            logger('Find by code failed: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Update cari account
     *
     * @param int $id Account ID
     * @param array $data Update data
     * @param int|null $companyId Company ID filter
     * @return bool Success status
     */
    public function update(int $id, array $data, ?int $companyId = null): bool
    {
        $allowedFields = [
            'account_type', 'title', 'name', 'surname',
            'tax_office', 'tax_number', 'vkn', 'tckn',
            'email', 'phone', 'mobile', 'fax', 'website',
            'billing_address', 'billing_district', 'billing_city', 'billing_country', 'billing_postal_code',
            'shipping_address', 'shipping_district', 'shipping_city', 'shipping_country', 'shipping_postal_code',
            'currency', 'payment_term', 'credit_limit',
            'bank_name', 'bank_branch', 'bank_account_no', 'iban',
            'notes', 'tags', 'risk_group', 'customer_group',
            'efatura_enabled', 'efatura_alias', 'is_active'
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

        $sql = "UPDATE cari_accounts SET " . implode(', ', $fields) . " WHERE id = :id";

        if ($companyId) {
            $sql .= " AND company_id = :company_id";
            $params[':company_id'] = $companyId;
        }

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            logger('Cari account update failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Soft delete cari account
     *
     * @param int $id Account ID
     * @param int|null $companyId Company ID filter
     * @return bool Success status
     */
    public function delete(int $id, ?int $companyId = null): bool
    {
        $sql = "UPDATE cari_accounts SET deleted_at = NOW() WHERE id = :id";
        $params = [':id' => $id];

        if ($companyId) {
            $sql .= " AND company_id = :company_id";
            $params[':company_id'] = $companyId;
        }

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            logger('Cari account deletion failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Get all cari accounts with pagination and filters
     *
     * @param int $companyId Company ID
     * @param array $filters Filter conditions
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Accounts and pagination info
     */
    public function getAll(int $companyId, array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $offset = ($page - 1) * $perPage;
        $where = ["company_id = :company_id", "deleted_at IS NULL"];
        $params = [':company_id' => $companyId];

        // Filters
        if (!empty($filters['account_type'])) {
            $where[] = "account_type = :account_type";
            $params[':account_type'] = $filters['account_type'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(code LIKE :search OR title LIKE :search OR name LIKE :search OR email LIKE :search OR tax_number LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['is_active'])) {
            $where[] = "is_active = :is_active";
            $params[':is_active'] = (int) $filters['is_active'];
        }

        $whereClause = implode(' AND ', $where);

        // Count total
        $countSql = "SELECT COUNT(*) as total FROM cari_accounts WHERE " . $whereClause;
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Get data
        $sql = "SELECT * FROM cari_accounts 
                WHERE " . $whereClause . "
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'data' => $accounts,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage)
                ]
            ];
        } catch (\PDOException $e) {
            logger('Cari account pagination failed: ' . $e->getMessage(), 'error');
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
     * Get account balance
     *
     * @param int $id Account ID
     * @return float Current balance
     */
    public function getBalance(int $id): float
    {
        $sql = "SELECT current_balance FROM cari_accounts WHERE id = :id LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? (float) $result['current_balance'] : 0.00;
        } catch (\PDOException $e) {
            logger('Get balance failed: ' . $e->getMessage(), 'error');
            return 0.00;
        }
    }

    /**
     * Get account statement (ekstre)
     *
     * @param int $id Account ID
     * @param string|null $startDate Start date (Y-m-d)
     * @param string|null $endDate End date (Y-m-d)
     * @return array Transactions list
     */
    public function getStatement(int $id, ?string $startDate = null, ?string $endDate = null): array
    {
        $where = ["cari_account_id = :cari_account_id", "deleted_at IS NULL"];
        $params = [':cari_account_id' => $id];

        if ($startDate) {
            $where[] = "transaction_date >= :start_date";
            $params[':start_date'] = $startDate;
        }

        if ($endDate) {
            $where[] = "transaction_date <= :end_date";
            $params[':end_date'] = $endDate;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT * FROM cari_transactions 
                WHERE " . $whereClause . "
                ORDER BY transaction_date ASC, created_at ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            logger('Get statement failed: ' . $e->getMessage(), 'error');
            return [];
        }
    }

    /**
     * Get accounts with overdue payments
     *
     * @param int $companyId Company ID
     * @return array Overdue accounts
     */
    public function getOverdue(int $companyId): array
    {
        $sql = "SELECT ca.*, 
                       COUNT(ct.id) as overdue_count,
                       SUM(ct.amount) as overdue_amount
                FROM cari_accounts ca
                INNER JOIN cari_transactions ct ON ca.id = ct.cari_account_id
                WHERE ca.company_id = :company_id
                AND ca.deleted_at IS NULL
                AND ct.deleted_at IS NULL
                AND ct.due_date < CURDATE()
                AND ct.is_reconciled = 0
                GROUP BY ca.id
                ORDER BY overdue_amount DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':company_id' => $companyId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            logger('Get overdue failed: ' . $e->getMessage(), 'error');
            return [];
        }
    }

    /**
     * Get summary statistics
     *
     * @param int $companyId Company ID
     * @return array Statistics
     */
    public function getStats(int $companyId): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_accounts,
                    COUNT(CASE WHEN account_type = 'customer' THEN 1 END) as total_customers,
                    COUNT(CASE WHEN account_type = 'supplier' THEN 1 END) as total_suppliers,
                    COUNT(CASE WHEN account_type = 'both' THEN 1 END) as total_both,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_accounts,
                    SUM(CASE WHEN current_balance > 0 THEN current_balance ELSE 0 END) as total_receivables,
                    SUM(CASE WHEN current_balance < 0 THEN ABS(current_balance) ELSE 0 END) as total_payables
                FROM cari_accounts 
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
}

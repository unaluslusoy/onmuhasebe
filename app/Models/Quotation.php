<?php

namespace App\Models;

/**
 * Quotation Model
 * Handles quotation/offer management with workflow
 */
class Quotation extends BaseModel
{
    protected string $table = 'quotations';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'company_id',
        'quotation_number',
        'quotation_date',
        'valid_until',
        'cari_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_tax_number',
        'customer_tax_office',
        'customer_address',
        'customer_city',
        'customer_district',
        'customer_postal_code',
        'status',
        'discount_type',
        'discount_value',
        'currency',
        'exchange_rate',
        'notes',
        'terms_conditions',
        'internal_notes',
        'created_by',
        'updated_by'
    ];

    protected array $casts = [
        'id' => 'int',
        'company_id' => 'int',
        'cari_id' => 'int',
        'subtotal' => 'float',
        'discount_value' => 'float',
        'discount_amount' => 'float',
        'tax_amount' => 'float',
        'total' => 'float',
        'exchange_rate' => 'float',
        'created_by' => 'int',
        'updated_by' => 'int',
        'sent_by' => 'int',
        'converted_to_invoice_id' => 'int'
    ];

    // Status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CONVERTED = 'converted';

    /**
     * Get all quotations with filters
     */
    public function getAll(int $companyId, array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT q.*, 
                       c.title as cari_name,
                       c.code as cari_code,
                       u.full_name as created_by_name
                FROM {$this->table} q
                LEFT JOIN cari_accounts c ON q.cari_id = c.id
                LEFT JOIN users u ON q.created_by = u.id
                WHERE q.company_id = ? AND q.deleted_at IS NULL";
        
        $params = [$companyId];

        // Filters
        if (!empty($filters['status'])) {
            $sql .= " AND q.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['cari_id'])) {
            $sql .= " AND q.cari_id = ?";
            $params[] = $filters['cari_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND q.quotation_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND q.quotation_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (q.quotation_number LIKE ? OR q.customer_name LIKE ? OR q.customer_email LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as filtered";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        // Get paginated data
        $sql .= " ORDER BY q.quotation_date DESC, q.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $quotations = $stmt->fetchAll();

        return [
            'data' => array_map(fn($q) => $this->transformResult($q), $quotations),
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
     * Find quotation with items
     */
    public function findWithItems(int $id, int $companyId): ?array
    {
        $quotation = $this->findByCompany($id, $companyId);
        if (!$quotation) {
            return null;
        }

        // Get items
        $sql = "SELECT qi.*, 
                       p.product_name,
                       p.sku,
                       pv.variant_name
                FROM quotation_items qi
                LEFT JOIN products p ON qi.product_id = p.id
                LEFT JOIN product_variants pv ON qi.variant_id = pv.id
                WHERE qi.quotation_id = ? AND qi.deleted_at IS NULL
                ORDER BY qi.item_order ASC, qi.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $quotation['items'] = $stmt->fetchAll();

        return $quotation;
    }

    /**
     * Find quotation by company
     */
    public function findByCompany(int $id, int $companyId): ?array
    {
        $sql = "SELECT q.*, 
                       c.title as cari_name,
                       c.code as cari_code,
                       u1.full_name as created_by_name,
                       u2.full_name as sent_by_name
                FROM {$this->table} q
                LEFT JOIN cari_accounts c ON q.cari_id = c.id
                LEFT JOIN users u1 ON q.created_by = u1.id
                LEFT JOIN users u2 ON q.sent_by = u2.id
                WHERE q.id = ? AND q.company_id = ? AND q.deleted_at IS NULL 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $companyId]);
        
        $result = $stmt->fetch();
        return $result ? $this->transformResult($result) : null;
    }

    /**
     * Find quotation by number
     */
    public function findByNumber(string $number, int $companyId): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE quotation_number = ? AND company_id = ? AND deleted_at IS NULL 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$number, $companyId]);
        
        $result = $stmt->fetch();
        return $result ? $this->transformResult($result) : null;
    }

    /**
     * Create quotation with items
     */
    public function createWithItems(array $quotationData, array $items): ?int
    {
        $this->beginTransaction();
        
        try {
            // Generate quotation number if not provided
            if (empty($quotationData['quotation_number'])) {
                $quotationData['quotation_number'] = $this->generateQuotationNumber($quotationData['company_id']);
            }

            // Set default status
            if (!isset($quotationData['status'])) {
                $quotationData['status'] = self::STATUS_DRAFT;
            }

            // Create quotation
            $columns = array_keys($quotationData);
            $placeholders = array_fill(0, count($columns), '?');
            
            $sql = sprintf(
                "INSERT INTO {$this->table} (%s) VALUES (%s)",
                implode(', ', $columns),
                implode(', ', $placeholders)
            );

            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($quotationData));
            $quotationId = (int)$this->db->lastInsertId();

            // Create items
            foreach ($items as $item) {
                $item['quotation_id'] = $quotationId;
                $this->createItem($item);
            }

            $this->commit();
            return $quotationId;
        } catch (\Exception $e) {
            $this->rollback();
            return null;
        }
    }

    /**
     * Create quotation item
     */
    private function createItem(array $data): ?int
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = sprintf(
            "INSERT INTO quotation_items (%s) VALUES (%s)",
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute(array_values($data));

        return $success ? (int)$this->db->lastInsertId() : null;
    }

    /**
     * Update quotation status
     */
    public function updateStatus(int $id, string $status, int $userId): bool
    {
        $data = ['status' => $status];
        $now = date('Y-m-d H:i:s');

        switch ($status) {
            case self::STATUS_SENT:
                $data['sent_by'] = $userId;
                $data['sent_at'] = $now;
                break;
            case self::STATUS_ACCEPTED:
                $data['accepted_at'] = $now;
                break;
            case self::STATUS_REJECTED:
                $data['rejected_at'] = $now;
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
     * Send quotation
     */
    public function send(int $id, int $companyId, int $userId): bool
    {
        $quotation = $this->findByCompany($id, $companyId);
        if (!$quotation || $quotation['status'] !== self::STATUS_DRAFT) {
            return false;
        }

        return $this->updateStatus($id, self::STATUS_SENT, $userId);
    }

    /**
     * Accept quotation
     */
    public function accept(int $id, int $companyId, int $userId): bool
    {
        $quotation = $this->findByCompany($id, $companyId);
        if (!$quotation || $quotation['status'] !== self::STATUS_SENT) {
            return false;
        }

        return $this->updateStatus($id, self::STATUS_ACCEPTED, $userId);
    }

    /**
     * Reject quotation
     */
    public function reject(int $id, int $companyId, int $userId, string $reason): bool
    {
        $quotation = $this->findByCompany($id, $companyId);
        if (!$quotation || !in_array($quotation['status'], [self::STATUS_SENT, self::STATUS_ACCEPTED])) {
            return false;
        }

        $sql = "UPDATE {$this->table} 
                SET status = ?, rejection_reason = ?, rejected_at = ? 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            self::STATUS_REJECTED,
            $reason,
            date('Y-m-d H:i:s'),
            $id
        ]);
    }

    /**
     * Mark quotation as converted to invoice
     */
    public function markAsConverted(int $id, int $invoiceId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET status = ?, converted_to_invoice_id = ?, converted_at = ? 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            self::STATUS_CONVERTED,
            $invoiceId,
            date('Y-m-d H:i:s'),
            $id
        ]);
    }

    /**
     * Duplicate quotation
     */
    public function duplicate(int $id, int $companyId, int $userId): ?int
    {
        $original = $this->findWithItems($id, $companyId);
        if (!$original) {
            return null;
        }

        // Prepare new quotation data
        $newData = [
            'company_id' => $companyId,
            'quotation_date' => date('Y-m-d'),
            'valid_until' => date('Y-m-d', strtotime('+30 days')),
            'cari_id' => $original['cari_id'],
            'customer_name' => $original['customer_name'],
            'customer_email' => $original['customer_email'],
            'customer_phone' => $original['customer_phone'],
            'customer_tax_number' => $original['customer_tax_number'],
            'customer_tax_office' => $original['customer_tax_office'],
            'customer_address' => $original['customer_address'],
            'customer_city' => $original['customer_city'],
            'customer_district' => $original['customer_district'],
            'customer_postal_code' => $original['customer_postal_code'],
            'discount_type' => $original['discount_type'],
            'discount_value' => $original['discount_value'],
            'currency' => $original['currency'],
            'exchange_rate' => $original['exchange_rate'],
            'notes' => $original['notes'],
            'terms_conditions' => $original['terms_conditions'],
            'status' => self::STATUS_DRAFT,
            'created_by' => $userId
        ];

        // Prepare items
        $newItems = [];
        foreach ($original['items'] as $item) {
            $newItems[] = [
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'item_order' => $item['item_order'],
                'item_type' => $item['item_type'],
                'item_code' => $item['item_code'],
                'item_name' => $item['item_name'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'unit_price' => $item['unit_price'],
                'discount_type' => $item['discount_type'],
                'discount_value' => $item['discount_value'],
                'tax_rate' => $item['tax_rate']
            ];
        }

        return $this->createWithItems($newData, $newItems);
    }

    /**
     * Generate quotation number
     */
    private function generateQuotationNumber(int $companyId): string
    {
        $prefix = 'TKL';
        $year = date('Y');
        $month = date('m');

        // Get last number for this month
        $sql = "SELECT quotation_number FROM {$this->table} 
                WHERE company_id = ? 
                AND quotation_number LIKE ? 
                ORDER BY id DESC LIMIT 1";
        
        $pattern = "{$prefix}-{$year}{$month}-%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId, $pattern]);
        $last = $stmt->fetch();

        if ($last) {
            $lastNumber = (int)substr($last['quotation_number'], -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}-{$year}{$month}-{$newNumber}";
    }

    /**
     * Get quotations by status
     */
    public function getByStatus(int $companyId, string $status): array
    {
        $sql = "SELECT q.*, 
                       c.title as cari_name,
                       COUNT(qi.id) as item_count
                FROM {$this->table} q
                LEFT JOIN cari_accounts c ON q.cari_id = c.id
                LEFT JOIN quotation_items qi ON q.id = qi.quotation_id AND qi.deleted_at IS NULL
                WHERE q.company_id = ? AND q.status = ? AND q.deleted_at IS NULL
                GROUP BY q.id
                ORDER BY q.quotation_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId, $status]);
        
        return array_map(fn($q) => $this->transformResult($q), $stmt->fetchAll());
    }

    /**
     * Get expired quotations
     */
    public function getExpired(int $companyId): array
    {
        $sql = "SELECT q.*, 
                       c.title as cari_name
                FROM {$this->table} q
                LEFT JOIN cari_accounts c ON q.cari_id = c.id
                WHERE q.company_id = ? 
                AND q.status = ? 
                AND q.valid_until < CURDATE()
                AND q.deleted_at IS NULL
                ORDER BY q.valid_until DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId, self::STATUS_SENT]);
        
        return array_map(fn($q) => $this->transformResult($q), $stmt->fetchAll());
    }

    /**
     * Get quotation statistics
     */
    public function getStatistics(int $companyId, array $dateRange = []): array
    {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count,
                    SUM(total) as total_amount,
                    AVG(total) as avg_amount
                FROM {$this->table}
                WHERE company_id = ? AND deleted_at IS NULL";
        
        $params = [$companyId];

        if (!empty($dateRange['from'])) {
            $sql .= " AND quotation_date >= ?";
            $params[] = $dateRange['from'];
        }

        if (!empty($dateRange['to'])) {
            $sql .= " AND quotation_date <= ?";
            $params[] = $dateRange['to'];
        }

        $sql .= " GROUP BY status";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Get quotations expiring soon
     */
    public function getExpiringSoon(int $companyId, int $days = 7): array
    {
        $sql = "SELECT q.*, 
                       c.title as cari_name,
                       DATEDIFF(q.valid_until, CURDATE()) as days_remaining
                FROM {$this->table} q
                LEFT JOIN cari_accounts c ON q.cari_id = c.id
                WHERE q.company_id = ? 
                AND q.status = ? 
                AND q.valid_until BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                AND q.deleted_at IS NULL
                ORDER BY q.valid_until ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId, self::STATUS_SENT, $days]);
        
        return array_map(fn($q) => $this->transformResult($q), $stmt->fetchAll());
    }
}

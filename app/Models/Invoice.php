<?php

namespace App\Models;

/**
 * Invoice Model
 * Handles invoice management with payment tracking and recurring invoices
 */
class Invoice extends BaseModel
{
    protected string $table = 'invoices';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'company_id',
        'cari_id',
        'warehouse_id',
        'invoice_number',
        'invoice_uuid',
        'invoice_type',
        'invoice_category',
        'invoice_date',
        'due_date',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_tax_number',
        'customer_tax_office',
        'customer_address',
        'customer_city',
        'customer_district',
        'customer_postal_code',
        'discount_type',
        'discount_value',
        'currency',
        'exchange_rate',
        'waybill_number',
        'waybill_date',
        'order_number',
        'quotation_id',
        'notes',
        'internal_notes',
        'terms_conditions',
        'is_recurring',
        'recurring_frequency',
        'recurring_interval',
        'recurring_start_date',
        'recurring_end_date',
        'parent_invoice_id',
        'created_by',
        'updated_by'
    ];

    protected array $casts = [
        'id' => 'int',
        'company_id' => 'int',
        'cari_id' => 'int',
        'warehouse_id' => 'int',
        'quotation_id' => 'int',
        'parent_invoice_id' => 'int',
        'original_invoice_id' => 'int',
        'subtotal' => 'float',
        'discount_value' => 'float',
        'discount_amount' => 'float',
        'tax_amount' => 'float',
        'withholding_amount' => 'float',
        'total' => 'float',
        'paid_amount' => 'float',
        'remaining_amount' => 'float',
        'exchange_rate' => 'float',
        'recurring_interval' => 'int',
        'is_recurring' => 'bool',
        'is_cancelled' => 'bool',
        'is_approved' => 'bool',
        'is_draft' => 'bool',
        'is_locked' => 'bool',
        'created_by' => 'int',
        'updated_by' => 'int'
    ];

    // Invoice type constants
    public const TYPE_SALES = 'sales';
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_SALES_RETURN = 'sales_return';
    public const TYPE_PURCHASE_RETURN = 'purchase_return';

    // Payment status constants
    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Get all invoices with filters
     */
    public function getAll(int $companyId, array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT i.*, 
                       c.title as cari_name,
                       c.code as cari_code,
                       u.full_name as created_by_name,
                       (SELECT COUNT(*) FROM invoice_items WHERE invoice_id = i.id AND deleted_at IS NULL) as item_count
                FROM {$this->table} i
                LEFT JOIN cari_accounts c ON i.cari_id = c.id
                LEFT JOIN users u ON i.created_by = u.id
                WHERE i.company_id = ? AND i.deleted_at IS NULL";
        
        $params = [$companyId];

        // Filters
        if (!empty($filters['invoice_type'])) {
            $sql .= " AND i.invoice_type = ?";
            $params[] = $filters['invoice_type'];
        }

        if (!empty($filters['payment_status'])) {
            $sql .= " AND i.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        if (!empty($filters['cari_id'])) {
            $sql .= " AND i.cari_id = ?";
            $params[] = $filters['cari_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND i.invoice_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND i.invoice_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['is_draft'])) {
            $sql .= " AND i.is_draft = ?";
            $params[] = (int)$filters['is_draft'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (i.invoice_number LIKE ? OR i.customer_name LIKE ? OR i.customer_email LIKE ?)";
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
        $sql .= " ORDER BY i.invoice_date DESC, i.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $invoices = $stmt->fetchAll();

        return [
            'data' => array_map(fn($inv) => $this->transformResult($inv), $invoices),
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
     * Find invoice with items
     */
    public function findWithItems(int $id, int $companyId): ?array
    {
        $invoice = $this->findByCompany($id, $companyId);
        if (!$invoice) {
            return null;
        }

        // Get items
        $sql = "SELECT ii.*, 
                       p.product_name,
                       p.product_code,
                       pv.variant_name
                FROM invoice_items ii
                LEFT JOIN products p ON ii.product_id = p.id
                LEFT JOIN product_variants pv ON ii.variant_id = pv.id
                WHERE ii.invoice_id = ? AND ii.deleted_at IS NULL
                ORDER BY ii.item_order ASC, ii.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $invoice['items'] = $stmt->fetchAll();

        // Get payments
        $sql = "SELECT * FROM invoice_payments 
                WHERE invoice_id = ? AND deleted_at IS NULL
                ORDER BY payment_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $invoice['payments'] = $stmt->fetchAll();

        return $invoice;
    }

    /**
     * Find invoice by company
     */
    public function findByCompany(int $id, int $companyId): ?array
    {
        $sql = "SELECT i.*, 
                       c.title as cari_name,
                       c.code as cari_code,
                       u1.full_name as created_by_name,
                       u2.full_name as approved_by_name
                FROM {$this->table} i
                LEFT JOIN cari_accounts c ON i.cari_id = c.id
                LEFT JOIN users u1 ON i.created_by = u1.id
                LEFT JOIN users u2 ON i.approved_by = u2.id
                WHERE i.id = ? AND i.company_id = ? AND i.deleted_at IS NULL 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $companyId]);
        
        $result = $stmt->fetch();
        return $result ? $this->transformResult($result) : null;
    }

    /**
     * Find invoice by number
     */
    public function findByNumber(string $number, int $companyId): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE invoice_number = ? AND company_id = ? AND deleted_at IS NULL 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$number, $companyId]);
        
        $result = $stmt->fetch();
        return $result ? $this->transformResult($result) : null;
    }

    /**
     * Create invoice with items
     */
    public function createWithItems(array $invoiceData, array $items): ?int
    {
        $this->beginTransaction();
        
        try {
            // Generate invoice number if not provided
            if (empty($invoiceData['invoice_number'])) {
                $invoiceData['invoice_number'] = $this->generateInvoiceNumber(
                    $invoiceData['company_id'],
                    $invoiceData['invoice_type']
                );
            }

            // Create invoice
            $columns = array_keys($invoiceData);
            $placeholders = array_fill(0, count($columns), '?');
            
            $sql = sprintf(
                "INSERT INTO {$this->table} (%s) VALUES (%s)",
                implode(', ', $columns),
                implode(', ', $placeholders)
            );

            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($invoiceData));
            $invoiceId = (int)$this->db->lastInsertId();

            // Create items
            foreach ($items as $item) {
                $item['invoice_id'] = $invoiceId;
                $this->createItem($item);
            }

            $this->commit();
            return $invoiceId;
        } catch (\Exception $e) {
            $this->rollback();
            error_log("Invoice creation failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create invoice item
     */
    private function createItem(array $data): ?int
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = sprintf(
            "INSERT INTO invoice_items (%s) VALUES (%s)",
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute(array_values($data));

        return $success ? (int)$this->db->lastInsertId() : null;
    }

    /**
     * Generate invoice number
     */
    private function generateInvoiceNumber(int $companyId, string $invoiceType): string
    {
        $prefix = match($invoiceType) {
            self::TYPE_SALES => 'SF',
            self::TYPE_PURCHASE => 'AF',
            self::TYPE_SALES_RETURN => 'SI',
            self::TYPE_PURCHASE_RETURN => 'AI',
            default => 'INV'
        };

        $year = date('Y');
        $month = date('m');

        // Get last number for this month and type
        $sql = "SELECT invoice_number FROM {$this->table} 
                WHERE company_id = ? 
                AND invoice_type = ?
                AND invoice_number LIKE ? 
                ORDER BY id DESC LIMIT 1";
        
        $pattern = "{$prefix}-{$year}{$month}-%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId, $invoiceType, $pattern]);
        $last = $stmt->fetch();

        if ($last) {
            $lastNumber = (int)substr($last['invoice_number'], -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}-{$year}{$month}-{$newNumber}";
    }

    /**
     * Record payment
     */
    public function recordPayment(int $invoiceId, array $paymentData): ?int
    {
        $paymentData['invoice_id'] = $invoiceId;
        
        $columns = array_keys($paymentData);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = sprintf(
            "INSERT INTO invoice_payments (%s) VALUES (%s)",
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute(array_values($paymentData));

        return $success ? (int)$this->db->lastInsertId() : null;
    }

    /**
     * Get payments for invoice
     */
    public function getPayments(int $invoiceId): array
    {
        $sql = "SELECT ip.*, 
                       u.full_name as created_by_name
                FROM invoice_payments ip
                LEFT JOIN users u ON ip.created_by = u.id
                WHERE ip.invoice_id = ? AND ip.deleted_at IS NULL
                ORDER BY ip.payment_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$invoiceId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Approve invoice
     */
    public function approve(int $id, int $companyId, int $userId): bool
    {
        $invoice = $this->findByCompany($id, $companyId);
        if (!$invoice || $invoice['is_approved']) {
            return false;
        }

        $sql = "UPDATE {$this->table} 
                SET is_approved = 1, approved_by = ?, approved_at = ?, is_draft = 0
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $userId,
            date('Y-m-d H:i:s'),
            $id
        ]);
    }

    /**
     * Cancel invoice
     */
    public function cancel(int $id, int $companyId, int $userId, string $reason): bool
    {
        $invoice = $this->findByCompany($id, $companyId);
        if (!$invoice || $invoice['is_cancelled'] || $invoice['payment_status'] === self::STATUS_PAID) {
            return false;
        }

        $sql = "UPDATE {$this->table} 
                SET is_cancelled = 1, cancelled_at = ?, cancelled_by = ?, 
                    cancellation_reason = ?, payment_status = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            date('Y-m-d H:i:s'),
            $userId,
            $reason,
            self::STATUS_CANCELLED,
            $id
        ]);
    }

    /**
     * Lock/unlock invoice
     */
    public function setLock(int $id, int $companyId, bool $locked): bool
    {
        $sql = "UPDATE {$this->table} 
                SET is_locked = ?
                WHERE id = ? AND company_id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$locked ? 1 : 0, $id, $companyId]);
    }

    /**
     * Convert quotation to invoice
     */
    public function convertFromQuotation(int $quotationId, int $companyId, int $userId, array $overrides = []): ?int
    {
        // Get quotation with items
        $quotationModel = new \App\Models\Quotation();
        $quotation = $quotationModel->findWithItems($quotationId, $companyId);
        
        if (!$quotation) {
            return null;
        }

        // Prepare invoice data
        $invoiceData = [
            'company_id' => $companyId,
            'cari_id' => $quotation['cari_id'],
            'invoice_type' => self::TYPE_SALES,
            'invoice_date' => $overrides['invoice_date'] ?? date('Y-m-d'),
            'due_date' => $overrides['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
            'customer_name' => $quotation['customer_name'],
            'customer_email' => $quotation['customer_email'],
            'customer_phone' => $quotation['customer_phone'],
            'customer_tax_number' => $quotation['customer_tax_number'],
            'customer_tax_office' => $quotation['customer_tax_office'],
            'customer_address' => $quotation['customer_address'],
            'customer_city' => $quotation['customer_city'],
            'customer_district' => $quotation['customer_district'],
            'customer_postal_code' => $quotation['customer_postal_code'],
            'discount_type' => $quotation['discount_type'],
            'discount_value' => $quotation['discount_value'],
            'currency' => $quotation['currency'],
            'exchange_rate' => $quotation['exchange_rate'],
            'notes' => $quotation['notes'],
            'terms_conditions' => $quotation['terms_conditions'],
            'quotation_id' => $quotationId,
            'is_draft' => false,
            'created_by' => $userId
        ];

        // Prepare items
        $items = [];
        foreach ($quotation['items'] as $item) {
            $items[] = [
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

        // Create invoice
        $invoiceId = $this->createWithItems($invoiceData, $items);
        
        if ($invoiceId) {
            // Mark quotation as converted
            $quotationModel->markAsConverted($quotationId, $invoiceId);
        }

        return $invoiceId;
    }

    /**
     * Get overdue invoices
     */
    public function getOverdue(int $companyId): array
    {
        $sql = "SELECT i.*, 
                       c.title as cari_name,
                       DATEDIFF(CURDATE(), i.due_date) as days_overdue
                FROM {$this->table} i
                LEFT JOIN cari_accounts c ON i.cari_id = c.id
                WHERE i.company_id = ? 
                AND i.payment_status IN (?, ?)
                AND i.due_date < CURDATE()
                AND i.is_cancelled = FALSE
                AND i.deleted_at IS NULL
                ORDER BY i.due_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId, self::STATUS_UNPAID, self::STATUS_PARTIAL]);
        
        return array_map(fn($inv) => $this->transformResult($inv), $stmt->fetchAll());
    }

    /**
     * Get due today invoices
     */
    public function getDueToday(int $companyId): array
    {
        $sql = "SELECT i.*, 
                       c.title as cari_name
                FROM {$this->table} i
                LEFT JOIN cari_accounts c ON i.cari_id = c.id
                WHERE i.company_id = ? 
                AND i.payment_status IN (?, ?)
                AND i.due_date = CURDATE()
                AND i.is_cancelled = FALSE
                AND i.deleted_at IS NULL
                ORDER BY i.invoice_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId, self::STATUS_UNPAID, self::STATUS_PARTIAL]);
        
        return array_map(fn($inv) => $this->transformResult($inv), $stmt->fetchAll());
    }

    /**
     * Get invoice statistics
     */
    public function getStatistics(int $companyId, array $dateRange = []): array
    {
        $sql = "SELECT 
                    invoice_type,
                    payment_status,
                    COUNT(*) as count,
                    SUM(total) as total_amount,
                    SUM(paid_amount) as paid_amount,
                    SUM(remaining_amount) as remaining_amount
                FROM {$this->table}
                WHERE company_id = ? 
                AND is_cancelled = FALSE
                AND deleted_at IS NULL";
        
        $params = [$companyId];

        if (!empty($dateRange['from'])) {
            $sql .= " AND invoice_date >= ?";
            $params[] = $dateRange['from'];
        }

        if (!empty($dateRange['to'])) {
            $sql .= " AND invoice_date <= ?";
            $params[] = $dateRange['to'];
        }

        $sql .= " GROUP BY invoice_type, payment_status";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Get monthly summary
     */
    public function getMonthlySummary(int $companyId, int $year, int $month): array
    {
        $sql = "SELECT 
                    invoice_type,
                    COUNT(*) as count,
                    SUM(total) as total,
                    SUM(paid_amount) as paid,
                    SUM(remaining_amount) as remaining
                FROM {$this->table}
                WHERE company_id = ? 
                AND YEAR(invoice_date) = ?
                AND MONTH(invoice_date) = ?
                AND is_cancelled = FALSE
                AND deleted_at IS NULL
                GROUP BY invoice_type";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId, $year, $month]);
        
        return $stmt->fetchAll();
    }

    /**
     * Get recurring invoices due for generation
     */
    public function getRecurringDue(): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE is_recurring = TRUE
                AND is_cancelled = FALSE
                AND deleted_at IS NULL
                AND (
                    (recurring_last_generated IS NULL AND recurring_start_date <= CURDATE())
                    OR
                    (recurring_last_generated IS NOT NULL AND 
                     CASE recurring_frequency
                        WHEN 'daily' THEN DATE_ADD(recurring_last_generated, INTERVAL recurring_interval DAY) <= CURDATE()
                        WHEN 'weekly' THEN DATE_ADD(recurring_last_generated, INTERVAL recurring_interval WEEK) <= CURDATE()
                        WHEN 'monthly' THEN DATE_ADD(recurring_last_generated, INTERVAL recurring_interval MONTH) <= CURDATE()
                        WHEN 'yearly' THEN DATE_ADD(recurring_last_generated, INTERVAL recurring_interval YEAR) <= CURDATE()
                     END
                    )
                )
                AND (recurring_end_date IS NULL OR recurring_end_date >= CURDATE())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return array_map(fn($inv) => $this->transformResult($inv), $stmt->fetchAll());
    }

    /**
     * Generate invoice from recurring template
     */
    public function generateFromRecurring(int $templateId): ?int
    {
        $template = $this->findWithItems($templateId, 0); // 0 to skip company check for internal use
        
        if (!$template || !$template['is_recurring']) {
            return null;
        }

        // Calculate new invoice date
        $newDate = date('Y-m-d');

        // Prepare new invoice data
        $newData = [
            'company_id' => $template['company_id'],
            'cari_id' => $template['cari_id'],
            'warehouse_id' => $template['warehouse_id'],
            'invoice_type' => $template['invoice_type'],
            'invoice_category' => $template['invoice_category'],
            'invoice_date' => $newDate,
            'due_date' => date('Y-m-d', strtotime($newDate . ' +30 days')),
            'customer_name' => $template['customer_name'],
            'customer_email' => $template['customer_email'],
            'customer_phone' => $template['customer_phone'],
            'customer_tax_number' => $template['customer_tax_number'],
            'customer_tax_office' => $template['customer_tax_office'],
            'customer_address' => $template['customer_address'],
            'customer_city' => $template['customer_city'],
            'customer_district' => $template['customer_district'],
            'customer_postal_code' => $template['customer_postal_code'],
            'discount_type' => $template['discount_type'],
            'discount_value' => $template['discount_value'],
            'currency' => $template['currency'],
            'exchange_rate' => $template['exchange_rate'],
            'notes' => $template['notes'],
            'terms_conditions' => $template['terms_conditions'],
            'parent_invoice_id' => $templateId,
            'is_draft' => false,
            'created_by' => $template['created_by']
        ];

        // Prepare items
        $newItems = [];
        foreach ($template['items'] as $item) {
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

        // Create new invoice
        $newId = $this->createWithItems($newData, $newItems);

        if ($newId) {
            // Update template's last generated date
            $this->update($templateId, [
                'recurring_last_generated' => $newDate
            ]);
        }

        return $newId;
    }

    /**
     * Log invoice action
     */
    public function logAction(int $invoiceId, int $userId, string $action, ?string $description = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        $sql = "INSERT INTO invoice_logs (invoice_id, user_id, action_type, description, old_values, new_values, ip_address)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $invoiceId,
            $userId,
            $action,
            $description,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
}

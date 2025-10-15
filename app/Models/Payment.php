<?php

namespace App\Models;

use PDO;

/**
 * Payment Model
 * Manages payment transactions
 */
class Payment extends BaseModel
{
    protected string $table = 'payments';

    /**
     * Get payments by company ID
     */
    public function getByCompanyId(int $companyId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE company_id = ? ORDER BY payment_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get latest payment for subscription
     */
    public function getLatestForSubscription(int $subscriptionId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE subscription_id = ? ORDER BY payment_date DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$subscriptionId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get total revenue for company
     */
    public function getTotalRevenue(int $companyId): float
    {
        $sql = "SELECT SUM(amount) as total FROM {$this->table} WHERE company_id = ? AND status = 'completed'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float) ($result['total'] ?? 0);
    }
}

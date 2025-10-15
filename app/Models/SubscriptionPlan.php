<?php

namespace App\Models;

use PDO;

/**
 * Subscription Plan Model
 * Manages subscription plan data
 */
class SubscriptionPlan extends BaseModel
{
    protected string $table = 'subscription_plans';

    /**
     * Get all plans (override to skip deleted_at check)
     */
    public function all(array $conditions = []): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY sort_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all active plans
     */
    public function getActivePlans(): array
    {
        return $this->all();
    }

    /**
     * Get plan by slug
     */
    public function getBySlug(string $slug): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE slug = ? AND is_active = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Check if plan exists
     */
    public function planExists(string $slug): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE slug = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch() !== false;
    }
}

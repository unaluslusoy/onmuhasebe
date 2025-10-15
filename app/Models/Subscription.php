<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Subscription Model
 * Manages company subscriptions and trial periods
 */
class Subscription extends BaseModel
{
    protected string $table = 'subscriptions';

    /**
     * Get subscription by company ID
     */
    public function getByCompanyId(int $companyId): ?array
    {
        $sql = "SELECT s.*, sp.name as plan_name, sp.features, sp.max_users, sp.max_invoices_per_month, sp.max_storage_gb
                FROM {$this->table} s
                LEFT JOIN subscription_plans sp ON sp.slug = s.plan_type
                WHERE s.company_id = ? 
                ORDER BY s.id DESC 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Check if subscription is active (including trial)
     */
    public function isActive(int $companyId): bool
    {
        $subscription = $this->getByCompanyId($companyId);
        
        if (!$subscription) {
            return false;
        }

        // Check if in trial period
        if ($subscription['status'] === 'trial') {
            $trialEnd = strtotime($subscription['trial_ends_at']);
            if (time() < $trialEnd) {
                return true;
            }
            // Trial expired
            $this->expireSubscription($subscription['id']);
            return false;
        }

        // Check if active subscription
        if ($subscription['status'] === 'active') {
            $periodEnd = strtotime($subscription['current_period_end']);
            if (time() < $periodEnd) {
                return true;
            }
            // Period expired
            $this->expireSubscription($subscription['id']);
            return false;
        }

        return false;
    }

    /**
     * Check if in trial period
     */
    public function isInTrial(int $companyId): bool
    {
        $subscription = $this->getByCompanyId($companyId);
        
        if (!$subscription || $subscription['status'] !== 'trial') {
            return false;
        }

        $trialEnd = strtotime($subscription['trial_ends_at']);
        return time() < $trialEnd;
    }

    /**
     * Get days remaining in trial/subscription
     */
    public function getDaysRemaining(int $companyId): int
    {
        $subscription = $this->getByCompanyId($companyId);
        
        if (!$subscription) {
            return 0;
        }

        $endDate = $subscription['status'] === 'trial' 
            ? $subscription['trial_ends_at'] 
            : $subscription['current_period_end'];
        
        $daysRemaining = floor((strtotime($endDate) - time()) / 86400);
        
        return max(0, $daysRemaining);
    }

    /**
     * Create trial subscription for new company
     */
    public function createTrial(int $companyId): ?int
    {
        $now = date('Y-m-d H:i:s');
        $trialEnd = date('Y-m-d H:i:s', strtotime('+30 days'));

        $sql = "INSERT INTO {$this->table} 
                (company_id, plan_type, status, trial_ends_at, current_period_start, current_period_end, created_at) 
                VALUES (?, 'trial', 'trial', ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId, $trialEnd, $now, $trialEnd, $now]);
        
        return $this->db->lastInsertId() ?: null;
    }

    /**
     * Upgrade from trial to paid plan
     */
    public function upgradePlan(int $companyId, string $planType, int $months = 1): bool
    {
        $currentEnd = date('Y-m-d H:i:s', strtotime("+{$months} months"));
        
        $sql = "UPDATE {$this->table} 
                SET plan_type = ?, 
                    status = 'active', 
                    trial_ends_at = NULL,
                    current_period_start = NOW(),
                    current_period_end = ?,
                    updated_at = NOW()
                WHERE company_id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$planType, $currentEnd, $companyId]);
    }

    /**
     * Expire subscription
     */
    public function expireSubscription(int $subscriptionId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET status = 'expired', 
                    updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$subscriptionId]);
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(int $companyId, string $reason = null): bool
    {
        $sql = "UPDATE {$this->table} 
                SET status = 'cancelled', 
                    cancelled_at = NOW(),
                    cancel_reason = ?,
                    updated_at = NOW()
                WHERE company_id = ? AND status IN ('active', 'trial')";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$reason, $companyId]);
    }

    /**
     * Get all active subscriptions expiring soon (for notifications)
     */
    public function getExpiringSoon(int $days = 7): array
    {
        $sql = "SELECT s.*, c.name as company_name, u.email as owner_email
                FROM {$this->table} s
                INNER JOIN companies c ON c.id = s.company_id
                INNER JOIN users u ON u.id = c.owner_id
                WHERE s.status IN ('active', 'trial')
                AND (
                    (s.status = 'trial' AND s.trial_ends_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? DAY))
                    OR
                    (s.status = 'active' AND s.current_period_end BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? DAY))
                )
                ORDER BY 
                    CASE 
                        WHEN s.status = 'trial' THEN s.trial_ends_at 
                        ELSE s.current_period_end 
                    END ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$days, $days]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get subscription statistics for dashboard
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'trial' THEN 1 ELSE 0 END) as trial_count,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
                    SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired_count,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count
                FROM {$this->table}";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}

<?php

namespace App\Services\Dashboard;

use App\Helpers\Database;
use App\Helpers\Logger;

/**
 * Dashboard Service
 * Provides comprehensive dashboard data and analytics
 * Adapted to current database schema
 */
class DashboardService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get complete dashboard data
     */
    public function getDashboardData(int $userId, ?int $companyId = null, string $period = '30days'): array
    {
        try {
            $dateRange = $this->getDateRange($period);

            return [
                'financial_summary' => $this->getFinancialSummary($companyId, $dateRange),
                'invoice_statistics' => $this->getInvoiceStatistics($companyId, $dateRange),
                'recent_invoices' => $this->getRecentInvoices($companyId, 10),
                'quick_stats' => $this->getQuickStats($companyId),
                'period' => $period,
                'date_range' => $dateRange
            ];

        } catch (\Exception $e) {
            Logger::error('Dashboard data fetch failed', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Get financial summary
     */
    public function getFinancialSummary(?int $companyId, array $dateRange): array
    {
        $whereClause = $companyId ? "AND company_id = ?" : "";
        $params = $companyId ? [$dateRange['start'], $dateRange['end'], $companyId] : [$dateRange['start'], $dateRange['end']];

        // Total income from invoices
        $sql = "SELECT
                    COALESCE(SUM(total), 0) as total_income,
                    COALESCE(SUM(paid_amount), 0) as received_income,
                    COUNT(*) as invoice_count
                FROM invoices
                WHERE created_at BETWEEN ? AND ?
                AND deleted_at IS NULL
                {$whereClause}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $income = $stmt->fetch();

        $profit = $income['received_income'];
        $profit_margin = $income['total_income'] > 0
            ? ($income['received_income'] / $income['total_income']) * 100
            : 0;

        return [
            'total_income' => (float)$income['total_income'],
            'received_income' => (float)$income['received_income'],
            'pending_income' => (float)($income['total_income'] - $income['received_income']),
            'invoice_count' => (int)$income['invoice_count'],
            'collection_rate' => round($profit_margin, 2)
        ];
    }

    /**
     * Get invoice statistics
     */
    public function getInvoiceStatistics(?int $companyId, array $dateRange): array
    {
        $whereClause = $companyId ? "AND company_id = ?" : "";
        $params = $companyId ? [$dateRange['start'], $dateRange['end'], $companyId] : [$dateRange['start'], $dateRange['end']];

        $sql = "SELECT
                    COUNT(*) as total_invoices,
                    SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                    SUM(CASE WHEN payment_status = 'partial' THEN 1 ELSE 0 END) as partial_count,
                    SUM(CASE WHEN payment_status = 'unpaid' THEN 1 ELSE 0 END) as unpaid_count,
                    SUM(CASE WHEN payment_status = 'overdue' THEN 1 ELSE 0 END) as overdue_count,
                    COALESCE(SUM(total), 0) as total_amount,
                    COALESCE(SUM(paid_amount), 0) as paid_amount,
                    COALESCE(SUM(remaining_amount), 0) as remaining_amount
                FROM invoices
                WHERE created_at BETWEEN ? AND ?
                AND deleted_at IS NULL
                {$whereClause}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stats = $stmt->fetch();

        $avgInvoice = $stats['total_invoices'] > 0
            ? $stats['total_amount'] / $stats['total_invoices']
            : 0;

        return [
            'total' => (int)$stats['total_invoices'],
            'paid' => (int)$stats['paid_count'],
            'partial' => (int)$stats['partial_count'],
            'unpaid' => (int)$stats['unpaid_count'],
            'overdue' => (int)$stats['overdue_count'],
            'total_amount' => (float)$stats['total_amount'],
            'paid_amount' => (float)$stats['paid_amount'],
            'remaining_amount' => (float)$stats['remaining_amount'],
            'average_invoice' => round($avgInvoice, 2)
        ];
    }

    /**
     * Get recent invoices
     */
    public function getRecentInvoices(?int $companyId, int $limit = 10): array
    {
        $whereClause = $companyId ? "WHERE company_id = ? AND deleted_at IS NULL" : "WHERE deleted_at IS NULL";
        $params = $companyId ? [$companyId, $limit] : [$limit];

        $sql = "SELECT
                    id,
                    invoice_number,
                    customer_name,
                    total,
                    payment_status,
                    created_at
                FROM invoices
                {$whereClause}
                ORDER BY created_at DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $invoices = $stmt->fetchAll();

        return array_map(function($inv) {
            return [
                'id' => (int)$inv['id'],
                'invoice_number' => $inv['invoice_number'],
                'customer_name' => $inv['customer_name'],
                'total' => (float)$inv['total'],
                'status' => $inv['payment_status'],
                'created_at' => $inv['created_at']
            ];
        }, $invoices);
    }

    /**
     * Get quick statistics
     */
    public function getQuickStats(?int $companyId): array
    {
        $whereClause = $companyId ? "AND company_id = ?" : "";
        $params = $companyId ? [$companyId] : [];

        // Total customers (cari_accounts)
        $sql = "SELECT COUNT(*) FROM cari_accounts WHERE deleted_at IS NULL";
        if ($companyId) {
            $sql .= " AND company_id = ?";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $totalCustomers = $stmt->fetchColumn();

        // Total products
        $sql = "SELECT COUNT(*) FROM products WHERE deleted_at IS NULL";
        if ($companyId) {
            $sql .= " AND company_id = ?";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $totalProducts = $stmt->fetchColumn();

        // Pending invoices
        $sql = "SELECT COUNT(*) FROM invoices
                WHERE payment_status IN ('unpaid', 'partial')
                AND deleted_at IS NULL";
        if ($companyId) {
            $sql .= " AND company_id = ?";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $pendingInvoices = $stmt->fetchColumn();

        return [
            'total_customers' => (int)$totalCustomers,
            'total_products' => (int)$totalProducts,
            'pending_invoices' => (int)$pendingInvoices
        ];
    }

    /**
     * Get date range based on period
     */
    private function getDateRange(string $period): array
    {
        $end = new \DateTime('now');
        $start = new \DateTime('now');

        switch ($period) {
            case 'today':
                $start->setTime(0, 0, 0);
                break;
            case '7days':
                $start->modify('-7 days');
                break;
            case '30days':
                $start->modify('-30 days');
                break;
            case '90days':
                $start->modify('-90 days');
                break;
            case 'this_month':
                $start->modify('first day of this month')->setTime(0, 0, 0);
                break;
            case 'last_month':
                $start->modify('first day of last month')->setTime(0, 0, 0);
                $end->modify('last day of last month')->setTime(23, 59, 59);
                break;
            case 'this_year':
                $start->modify('first day of January this year')->setTime(0, 0, 0);
                break;
            default:
                $start->modify('-30 days');
        }

        return [
            'start' => $start->format('Y-m-d H:i:s'),
            'end' => $end->format('Y-m-d H:i:s')
        ];
    }
}

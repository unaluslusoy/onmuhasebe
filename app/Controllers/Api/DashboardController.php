<?php

namespace App\Controllers\Api;

use App\Services\Dashboard\DashboardService;
use App\Helpers\Response;
use App\Helpers\Logger;

/**
 * Dashboard API Controller
 * Provides dashboard data endpoints
 */
class DashboardController
{
    private DashboardService $dashboardService;

    public function __construct()
    {
        $this->dashboardService = new DashboardService();
    }

    /**
     * Get complete dashboard data
     * GET /api/dashboard
     */
    public function index(): void
    {
        try {
            // Get authenticated user (implement your auth logic)
            $userId = $_SESSION['user_id'] ?? null;
            $companyId = $_SESSION['company_id'] ?? null;

            if (!$userId) {
                Response::unauthorized('Authentication required');
                return;
            }

            // Get period from query string (default: 30days)
            $period = $_GET['period'] ?? '30days';

            // Validate period
            $validPeriods = ['today', '7days', '30days', '90days', 'this_month', 'last_month', 'this_year'];
            if (!in_array($period, $validPeriods)) {
                Response::badRequest('Invalid period parameter');
                return;
            }

            $data = $this->dashboardService->getDashboardData($userId, $companyId, $period);

            Logger::info('Dashboard data retrieved', [
                'user_id' => $userId,
                'company_id' => $companyId,
                'period' => $period
            ]);

            Response::success($data, 'Dashboard data retrieved successfully');

        } catch (\Exception $e) {
            Logger::error('Dashboard data retrieval failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Response::serverError('Failed to retrieve dashboard data');
        }
    }

    /**
     * Get financial summary only
     * GET /api/dashboard/financial-summary
     */
    public function financialSummary(): void
    {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $companyId = $_SESSION['company_id'] ?? null;

            if (!$userId) {
                Response::unauthorized('Authentication required');
                return;
            }

            $period = $_GET['period'] ?? '30days';
            $dateRange = $this->getDateRange($period);

            $data = $this->dashboardService->getFinancialSummary($companyId, $dateRange);

            Response::success($data, 'Financial summary retrieved successfully');

        } catch (\Exception $e) {
            Logger::error('Financial summary retrieval failed', [
                'error' => $e->getMessage()
            ]);

            Response::serverError('Failed to retrieve financial summary');
        }
    }

    /**
     * Get invoice statistics only
     * GET /api/dashboard/invoice-statistics
     */
    public function invoiceStatistics(): void
    {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $companyId = $_SESSION['company_id'] ?? null;

            if (!$userId) {
                Response::unauthorized('Authentication required');
                return;
            }

            $period = $_GET['period'] ?? '30days';
            $dateRange = $this->getDateRange($period);

            $data = $this->dashboardService->getInvoiceStatistics($companyId, $dateRange);

            Response::success($data, 'Invoice statistics retrieved successfully');

        } catch (\Exception $e) {
            Logger::error('Invoice statistics retrieval failed', [
                'error' => $e->getMessage()
            ]);

            Response::serverError('Failed to retrieve invoice statistics');
        }
    }

    /**
     * Get cash flow data only
     * GET /api/dashboard/cash-flow
     */
    public function cashFlow(): void
    {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $companyId = $_SESSION['company_id'] ?? null;

            if (!$userId) {
                Response::unauthorized('Authentication required');
                return;
            }

            $period = $_GET['period'] ?? '30days';
            $dateRange = $this->getDateRange($period);

            $data = $this->dashboardService->getCashFlowData($companyId, $dateRange);

            Response::success($data, 'Cash flow data retrieved successfully');

        } catch (\Exception $e) {
            Logger::error('Cash flow data retrieval failed', [
                'error' => $e->getMessage()
            ]);

            Response::serverError('Failed to retrieve cash flow data');
        }
    }

    /**
     * Get top customers only
     * GET /api/dashboard/top-customers
     */
    public function topCustomers(): void
    {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $companyId = $_SESSION['company_id'] ?? null;

            if (!$userId) {
                Response::unauthorized('Authentication required');
                return;
            }

            $period = $_GET['period'] ?? '30days';
            $limit = (int)($_GET['limit'] ?? 5);
            $dateRange = $this->getDateRange($period);

            $data = $this->dashboardService->getTopCustomers($companyId, $dateRange, $limit);

            Response::success($data, 'Top customers retrieved successfully');

        } catch (\Exception $e) {
            Logger::error('Top customers retrieval failed', [
                'error' => $e->getMessage()
            ]);

            Response::serverError('Failed to retrieve top customers');
        }
    }

    /**
     * Get recent activities only
     * GET /api/dashboard/recent-activities
     */
    public function recentActivities(): void
    {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $companyId = $_SESSION['company_id'] ?? null;

            if (!$userId) {
                Response::unauthorized('Authentication required');
                return;
            }

            $limit = (int)($_GET['limit'] ?? 10);

            $data = $this->dashboardService->getRecentActivities($companyId, $limit);

            Response::success($data, 'Recent activities retrieved successfully');

        } catch (\Exception $e) {
            Logger::error('Recent activities retrieval failed', [
                'error' => $e->getMessage()
            ]);

            Response::serverError('Failed to retrieve recent activities');
        }
    }

    /**
     * Get quick statistics only
     * GET /api/dashboard/quick-stats
     */
    public function quickStats(): void
    {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $companyId = $_SESSION['company_id'] ?? null;

            if (!$userId) {
                Response::unauthorized('Authentication required');
                return;
            }

            $data = $this->dashboardService->getQuickStats($companyId);

            Response::success($data, 'Quick statistics retrieved successfully');

        } catch (\Exception $e) {
            Logger::error('Quick statistics retrieval failed', [
                'error' => $e->getMessage()
            ]);

            Response::serverError('Failed to retrieve quick statistics');
        }
    }

    /**
     * Helper: Get date range based on period
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

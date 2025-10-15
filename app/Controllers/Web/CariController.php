<?php

namespace App\Controllers\Web;

use App\Models\CariAccount;
use App\Helpers\Response;

/**
 * CariController
 * Handles customer/supplier (cari) account operations
 */
class CariController
{
    private CariAccount $cariModel;

    public function __construct()
    {
        $this->cariModel = new CariAccount();
    }

    /**
     * Get all cari accounts with filters and pagination
     * GET /api/cari
     */
    public function index(): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        if (!$companyId) {
            Response::json([
                'success' => false,
                'message' => 'Şirket bilgisi bulunamadı'
            ], 400);
            return;
        }

        $filters = [
            'account_type' => $_GET['account_type'] ?? null,
            'search' => $_GET['search'] ?? null,
            'is_active' => isset($_GET['is_active']) ? (int)$_GET['is_active'] : null
        ];

        $page = $_GET['page'] ?? 1;
        $perPage = $_GET['per_page'] ?? 25;

        $result = $this->cariModel->getAll($companyId, $filters, (int)$page, (int)$perPage);

        Response::json([
            'success' => true,
            'data' => $result['data'],
            'pagination' => $result['pagination']
        ]);
    }

    /**
     * Get cari account by ID
     * GET /api/cari/{id}
     */
    public function show(int $id): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        $account = $this->cariModel->find($id, $companyId);

        if (!$account) {
            Response::json([
                'success' => false,
                'message' => 'Cari hesap bulunamadı'
            ], 404);
            return;
        }

        Response::json([
            'success' => true,
            'data' => $account
        ]);
    }

    /**
     * Get cari account by code
     * GET /api/cari/code/{code}
     */
    public function showByCode(string $code): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        $account = $this->cariModel->findByCode($code, $companyId);

        if (!$account) {
            Response::json([
                'success' => false,
                'message' => 'Cari hesap bulunamadı'
            ], 404);
            return;
        }

        Response::json([
            'success' => true,
            'data' => $account
        ]);
    }

    /**
     * Create new cari account
     * POST /api/cari
     */
    public function store(): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        if (!$companyId) {
            Response::json([
                'success' => false,
                'message' => 'Şirket bilgisi bulunamadı'
            ], 400);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $data['company_id'] = $companyId;

        // Validation
        $errors = [];
        if (empty($data['title'])) {
            $errors['title'] = 'Ünvan zorunludur';
        }
        if (empty($data['name'])) {
            $errors['name'] = 'Ad zorunludur';
        }
        if (empty($data['account_type'])) {
            $errors['account_type'] = 'Hesap tipi zorunludur';
        }

        if (!empty($errors)) {
            Response::json([
                'success' => false,
                'message' => 'Validasyon hatası',
                'errors' => $errors
            ], 400);
            return;
        }

        // Check if code exists (if provided)
        if (!empty($data['code'])) {
            $existing = $this->cariModel->findByCode($data['code'], $companyId);
            if ($existing) {
                Response::json([
                    'success' => false,
                    'message' => 'Bu cari kodu zaten kullanılıyor'
                ], 409);
                return;
            }
        }

        $accountId = $this->cariModel->create($data);

        if (!$accountId) {
            Response::json([
                'success' => false,
                'message' => 'Cari hesap oluşturulamadı'
            ], 500);
            return;
        }

        $account = $this->cariModel->find($accountId, $companyId);

        Response::json([
            'success' => true,
            'message' => 'Cari hesap başarıyla oluşturuldu',
            'data' => $account
        ], 201);
    }

    /**
     * Update cari account
     * PUT /api/cari/{id}
     */
    public function update(int $id): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        $data = json_decode(file_get_contents('php://input'), true);

        // Check if account exists
        $account = $this->cariModel->find($id, $companyId);
        if (!$account) {
            Response::json([
                'success' => false,
                'message' => 'Cari hesap bulunamadı'
            ], 404);
            return;
        }

        $success = $this->cariModel->update($id, $data, $companyId);

        if (!$success) {
            Response::json([
                'success' => false,
                'message' => 'Cari hesap güncellenemedi'
            ], 500);
            return;
        }

        $updated = $this->cariModel->find($id, $companyId);

        Response::json([
            'success' => true,
            'message' => 'Cari hesap başarıyla güncellendi',
            'data' => $updated
        ]);
    }

    /**
     * Delete cari account (soft delete)
     * DELETE /api/cari/{id}
     */
    public function delete(int $id): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        $account = $this->cariModel->find($id, $companyId);
        if (!$account) {
            Response::json([
                'success' => false,
                'message' => 'Cari hesap bulunamadı'
            ], 404);
            return;
        }

        $success = $this->cariModel->delete($id, $companyId);

        if (!$success) {
            Response::json([
                'success' => false,
                'message' => 'Cari hesap silinemedi'
            ], 500);
            return;
        }

        Response::json([
            'success' => true,
            'message' => 'Cari hesap başarıyla silindi'
        ]);
    }

    /**
     * Get cari account balance
     * GET /api/cari/{id}/balance
     */
    public function balance(int $id): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        $account = $this->cariModel->find($id, $companyId);
        if (!$account) {
            Response::json([
                'success' => false,
                'message' => 'Cari hesap bulunamadı'
            ], 404);
            return;
        }

        $balance = $this->cariModel->getBalance($id);

        Response::json([
            'success' => true,
            'data' => [
                'account_id' => $id,
                'account_code' => $account['code'],
                'account_title' => $account['title'],
                'current_balance' => $balance,
                'balance_type' => $balance > 0 ? 'receivable' : ($balance < 0 ? 'payable' : 'zero'),
                'currency' => $account['currency']
            ]
        ]);
    }

    /**
     * Get cari account statement (ekstre)
     * GET /api/cari/{id}/statement
     */
    public function statement(int $id): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        $account = $this->cariModel->find($id, $companyId);
        if (!$account) {
            Response::json([
                'success' => false,
                'message' => 'Cari hesap bulunamadı'
            ], 404);
            return;
        }

        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;

        $transactions = $this->cariModel->getStatement($id, $startDate, $endDate);

        Response::json([
            'success' => true,
            'data' => [
                'account' => [
                    'id' => $id,
                    'code' => $account['code'],
                    'title' => $account['title'],
                    'current_balance' => $account['current_balance']
                ],
                'transactions' => $transactions,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]
        ]);
    }

    /**
     * Get overdue accounts
     * GET /api/cari/overdue
     */
    public function overdue(): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        if (!$companyId) {
            Response::json([
                'success' => false,
                'message' => 'Şirket bilgisi bulunamadı'
            ], 400);
            return;
        }

        $overdueAccounts = $this->cariModel->getOverdue($companyId);

        Response::json([
            'success' => true,
            'data' => $overdueAccounts
        ]);
    }

    /**
     * Get cari statistics
     * GET /api/cari/stats
     */
    public function stats(): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        if (!$companyId) {
            Response::json([
                'success' => false,
                'message' => 'Şirket bilgisi bulunamadı'
            ], 400);
            return;
        }

        $stats = $this->cariModel->getStats($companyId);

        Response::json([
            'success' => true,
            'data' => $stats
        ]);
    }
}


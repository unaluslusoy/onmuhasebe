<?php

namespace App\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Quotation;
use App\Helpers\Response;

/**
 * Invoice Controller
 * Handles invoice management operations
 */
class InvoiceController
{
    private Invoice $invoiceModel;
    private InvoiceItem $itemModel;

    public function __construct()
    {
        $this->invoiceModel = new Invoice();
        $this->itemModel = new InvoiceItem();
    }

    /**
     * Get all invoices with pagination and filters
     * GET /api/invoices
     */
    public function index(): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Geçersiz oturum');
                return;
            }

            $companyId = (int)$user['company_id'];
            $page = (int)($_GET['page'] ?? 1);
            $perPage = min((int)($_GET['per_page'] ?? 25), 100);

            // Filters
            $filters = [
                'invoice_type' => $_GET['invoice_type'] ?? null,
                'payment_status' => $_GET['payment_status'] ?? null,
                'cari_id' => isset($_GET['cari_id']) ? (int)$_GET['cari_id'] : null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null,
                'is_draft' => isset($_GET['is_draft']) ? (bool)$_GET['is_draft'] : null,
                'search' => $_GET['search'] ?? null
            ];

            // Remove null filters
            $filters = array_filter($filters, fn($value) => $value !== null);

            $result = $this->invoiceModel->getAll($companyId, $filters, $page, $perPage);

            Response::json([
                'success' => true,
                'message' => 'Faturalar başarıyla listelendi',
                'data' => $result['data'],
                'pagination' => $result['pagination']
            ]);
        } catch (\Exception $e) {
            error_log("Invoice index error: " . $e->getMessage());
            Response::serverError('Faturalar listelenirken bir hata oluştu');
        }
    }

    /**
     * Get single invoice with items
     * GET /api/invoices/:id
     */
    public function show(int $id): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Geçersiz oturum');
                return;
            }

            $companyId = (int)$user['company_id'];
            $invoice = $this->invoiceModel->findWithItems($id, $companyId);

            if (!$invoice) {
                Response::notFound('Fatura bulunamadı');
                return;
            }

            Response::json([
                'success' => true,
                'message' => 'Fatura başarıyla getirildi',
                'data' => $invoice
            ]);
        } catch (\Exception $e) {
            error_log("Invoice show error: " . $e->getMessage());
            Response::serverError('Fatura getirilirken bir hata oluştu');
        }
    }

    /**
     * Create new invoice
     * POST /api/invoices
     */
    public function store(): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Geçersiz oturum');
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validation
            $errors = $this->validateInvoice($data);
            if (!empty($errors)) {
                Response::badRequest('Validasyon hatası', ['errors' => $errors]);
                return;
            }

            $companyId = (int)$user['company_id'];
            $userId = (int)$user['id'];

            // Prepare invoice data
            $invoiceData = [
                'company_id' => $companyId,
                'cari_id' => $data['cari_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'invoice_type' => $data['invoice_type'],
                'invoice_category' => $data['invoice_category'] ?? 'normal',
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'],
                'customer_name' => $data['customer_name'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_tax_number' => $data['customer_tax_number'] ?? null,
                'customer_tax_office' => $data['customer_tax_office'] ?? null,
                'customer_address' => $data['customer_address'] ?? null,
                'customer_city' => $data['customer_city'] ?? null,
                'customer_district' => $data['customer_district'] ?? null,
                'customer_postal_code' => $data['customer_postal_code'] ?? null,
                'discount_type' => $data['discount_type'] ?? 'percentage',
                'discount_value' => $data['discount_value'] ?? 0,
                'currency' => $data['currency'] ?? 'TRY',
                'exchange_rate' => $data['exchange_rate'] ?? 1.0,
                'waybill_number' => $data['waybill_number'] ?? null,
                'waybill_date' => $data['waybill_date'] ?? null,
                'order_number' => $data['order_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'terms_conditions' => $data['terms_conditions'] ?? null,
                'is_draft' => $data['is_draft'] ?? false,
                'created_by' => $userId
            ];

            // Invoice number
            if (!empty($data['invoice_number'])) {
                $invoiceData['invoice_number'] = $data['invoice_number'];
            }

            // Items
            $items = $data['items'] ?? [];
            if (empty($items) && !($invoiceData['is_draft'] ?? false)) {
                Response::badRequest('Fatura en az bir kalem içermelidir');
                return;
            }

            // Create invoice
            $invoiceId = $this->invoiceModel->createWithItems($invoiceData, $items);

            if (!$invoiceId) {
                Response::serverError('Fatura oluşturulamadı');
                return;
            }

            // Log action
            $this->invoiceModel->logAction(
                $invoiceId,
                $userId,
                'created',
                'Fatura oluşturuldu',
                null,
                $invoiceData
            );

            $invoice = $this->invoiceModel->findWithItems($invoiceId, $companyId);

            Response::created([
                'success' => true,
                'message' => 'Fatura başarıyla oluşturuldu',
                'data' => $invoice
            ]);
        } catch (\Exception $e) {
            error_log("Invoice store error: " . $e->getMessage());
            Response::serverError('Fatura oluşturulurken bir hata oluştu');
        }
    }

    /**
     * Update invoice
     * PUT /api/invoices/:id
     */
    public function update(int $id): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Geçersiz oturum');
                return;
            }

            $companyId = (int)$user['company_id'];
            $userId = (int)$user['id'];

            $invoice = $this->invoiceModel->findByCompany($id, $companyId);
            if (!$invoice) {
                Response::notFound('Fatura bulunamadı');
                return;
            }

            // Check if locked
            if ($invoice['is_locked']) {
                Response::badRequest('Kilitli fatura düzenlenemez');
                return;
            }

            // Check if cancelled
            if ($invoice['is_cancelled']) {
                Response::badRequest('İptal edilmiş fatura düzenlenemez');
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);

            // Prepare update data
            $updateData = [];
            $allowedFields = [
                'cari_id', 'warehouse_id', 'invoice_date', 'due_date',
                'customer_name', 'customer_email', 'customer_phone',
                'customer_tax_number', 'customer_tax_office', 'customer_address',
                'customer_city', 'customer_district', 'customer_postal_code',
                'discount_type', 'discount_value', 'currency', 'exchange_rate',
                'waybill_number', 'waybill_date', 'order_number',
                'notes', 'internal_notes', 'terms_conditions', 'is_draft'
            ];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            if (empty($updateData)) {
                Response::badRequest('Güncellenecek veri bulunamadı');
                return;
            }

            $updateData['updated_by'] = $userId;

            // Update invoice
            $success = $this->invoiceModel->update($id, $updateData);

            if (!$success) {
                Response::serverError('Fatura güncellenemedi');
                return;
            }

            // Update items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                $this->itemModel->bulkUpdate($id, $data['items']);
            }

            // Log action
            $this->invoiceModel->logAction(
                $id,
                $userId,
                'updated',
                'Fatura güncellendi',
                $invoice,
                $updateData
            );

            $updatedInvoice = $this->invoiceModel->findWithItems($id, $companyId);

            Response::json([
                'success' => true,
                'message' => 'Fatura başarıyla güncellendi',
                'data' => $updatedInvoice
            ]);
        } catch (\Exception $e) {
            error_log("Invoice update error: " . $e->getMessage());
            Response::serverError('Fatura güncellenirken bir hata oluştu');
        }
    }

    /**
     * Delete invoice (soft delete)
     * DELETE /api/invoices/:id
     */
    public function destroy(int $id): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Geçersiz oturum');
                return;
            }

            $companyId = (int)$user['company_id'];
            $userId = (int)$user['id'];

            $invoice = $this->invoiceModel->findByCompany($id, $companyId);
            if (!$invoice) {
                Response::notFound('Fatura bulunamadı');
                return;
            }

            // Check if can delete
            if ($invoice['is_locked']) {
                Response::badRequest('Kilitli fatura silinemez');
                return;
            }

            if ($invoice['payment_status'] === Invoice::STATUS_PAID) {
                Response::badRequest('Ödemesi tamamlanmış fatura silinemez');
                return;
            }

            $success = $this->invoiceModel->delete($id);

            if (!$success) {
                Response::serverError('Fatura silinemedi');
                return;
            }

            // Log action
            $this->invoiceModel->logAction(
                $id,
                $userId,
                'deleted',
                'Fatura silindi',
                $invoice,
                null
            );

            Response::json([
                'success' => true,
                'message' => 'Fatura başarıyla silindi'
            ]);
        } catch (\Exception $e) {
            error_log("Invoice destroy error: " . $e->getMessage());
            Response::serverError('Fatura silinirken bir hata oluştu');
        }
    }

    /**
     * Approve invoice
     * POST /api/invoices/:id/approve
     */
    public function approve(int $id): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Geçersiz oturum');
                return;
            }

            $companyId = (int)$user['company_id'];
            $userId = (int)$user['id'];

            $invoice = $this->invoiceModel->findByCompany($id, $companyId);
            if (!$invoice) {
                Response::notFound('Fatura bulunamadı');
                return;
            }

            if ($invoice['is_approved']) {
                Response::badRequest('Fatura zaten onaylanmış');
                return;
            }

            if ($invoice['is_cancelled']) {
                Response::badRequest('İptal edilmiş fatura onaylanamaz');
                return;
            }

            $success = $this->invoiceModel->approve($id, $companyId, $userId);

            if (!$success) {
                Response::serverError('Fatura onaylanamadı');
                return;
            }

            // Log action
            $this->invoiceModel->logAction(
                $id,
                $userId,
                'approved',
                'Fatura onaylandı'
            );

            $updatedInvoice = $this->invoiceModel->findByCompany($id, $companyId);

            Response::json([
                'success' => true,
                'message' => 'Fatura başarıyla onaylandı',
                'data' => $updatedInvoice
            ]);
        } catch (\Exception $e) {
            error_log("Invoice approve error: " . $e->getMessage());
            Response::serverError('Fatura onaylanırken bir hata oluştu');
        }
    }

    /**
     * Cancel invoice
     * POST /api/invoices/:id/cancel
     */
    public function cancel(int $id): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Geçersiz oturum');
                return;
            }

            $companyId = (int)$user['company_id'];
            $userId = (int)$user['id'];

            $invoice = $this->invoiceModel->findByCompany($id, $companyId);
            if (!$invoice) {
                Response::notFound('Fatura bulunamadı');
                return;
            }

            if ($invoice['is_cancelled']) {
                Response::badRequest('Fatura zaten iptal edilmiş');
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $reason = $data['reason'] ?? 'Belirtilmedi';

            $success = $this->invoiceModel->cancel($id, $companyId, $userId, $reason);

            if (!$success) {
                Response::serverError('Fatura iptal edilemedi');
                return;
            }

            // Log action
            $this->invoiceModel->logAction(
                $id,
                $userId,
                'cancelled',
                'Fatura iptal edildi: ' . $reason
            );

            $updatedInvoice = $this->invoiceModel->findByCompany($id, $companyId);

            Response::json([
                'success' => true,
                'message' => 'Fatura başarıyla iptal edildi',
                'data' => $updatedInvoice
            ]);
        } catch (\Exception $e) {
            error_log("Invoice cancel error: " . $e->getMessage());
            Response::serverError('Fatura iptal edilirken bir hata oluştu');
        }
    }

    /**
     * Lock/unlock invoice
     * POST /api/invoices/:id/lock
     */
    public function lock(int $id): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Geçersiz oturum');
                return;
            }

            $companyId = (int)$user['company_id'];
            $userId = (int)$user['id'];

            $invoice = $this->invoiceModel->findByCompany($id, $companyId);
            if (!$invoice) {
                Response::notFound('Fatura bulunamadı');
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $locked = $data['locked'] ?? true;

            $success = $this->invoiceModel->setLock($id, $companyId, $locked);

            if (!$success) {
                Response::serverError('Fatura kilitleme durumu değiştirilemedi');
                return;
            }

            // Log action
            $action = $locked ? 'locked' : 'unlocked';
            $message = $locked ? 'Fatura kilitlendi' : 'Fatura kilidi açıldı';
            
            $this->invoiceModel->logAction($id, $userId, $action, $message);

            $updatedInvoice = $this->invoiceModel->findByCompany($id, $companyId);

            Response::json([
                'success' => true,
                'message' => $message,
                'data' => $updatedInvoice
            ]);
        } catch (\Exception $e) {
            error_log("Invoice lock error: " . $e->getMessage());
            Response::serverError('İşlem sırasında bir hata oluştu');
        }
    }

    /**
     * Record payment
     * POST /api/invoices/:id/payments
     */
    public function recordPayment(int $id): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Geçersiz oturum');
                return;
            }

            $companyId = (int)$user['company_id'];
            $userId = (int)$user['id'];

            $invoice = $this->invoiceModel->findByCompany($id, $companyId);
            if (!$invoice) {
                Response::notFound('Fatura bulunamadı');
                return;
            }

            if ($invoice['is_cancelled']) {
                Response::badRequest('İptal edilmiş faturaya ödeme kaydedilemez');
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);

            // Validation
            if (empty($data['amount']) || $data['amount'] <= 0) {
                Response::badRequest('Geçerli bir ödeme tutarı giriniz');
                return;
            }

            if (empty($data['payment_date'])) {
                Response::badRequest('Ödeme tarihi gereklidir');
                return;
            }

            if (empty($data['payment_method'])) {
                Response::badRequest('Ödeme yöntemi gereklidir');
                return;
            }

            // Check if overpayment
            $remainingAmount = (float)$invoice['remaining_amount'];
            if ($data['amount'] > $remainingAmount) {
                Response::badRequest('Ödeme tutarı kalan tutardan fazla olamaz');
                return;
            }

            $paymentData = [
                'company_id' => $companyId,
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'bank_account_id' => $data['bank_account_id'] ?? null,
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'check_number' => $data['check_number'] ?? null,
                'check_date' => $data['check_date'] ?? null,
                'check_bank' => $data['check_bank'] ?? null,
                'receipt_number' => $data['receipt_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId
            ];

            $paymentId = $this->invoiceModel->recordPayment($id, $paymentData);

            if (!$paymentId) {
                Response::serverError('Ödeme kaydedilemedi');
                return;
            }

            // Log action
            $this->invoiceModel->logAction(
                $id,
                $userId,
                'paid',
                sprintf('Ödeme kaydedildi: %.2f TL', $data['amount'])
            );

            $updatedInvoice = $this->invoiceModel->findWithItems($id, $companyId);

            Response::created([
                'success' => true,
                'message' => 'Ödeme başarıyla kaydedildi',
                'data' => $updatedInvoice
            ]);
        } catch (\Exception $e) {
            error_log("Payment record error: " . $e->getMessage());
            Response::serverError('Ödeme kaydedilirken bir hata oluştu');
        }
    }

    /**
     * Get invoice payments
     * GET /api/invoices/:id/payments
     */
    public function getPayments(int $id): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Geçersiz oturum');
                return;
            }

            $companyId = (int)$user['company_id'];

            $invoice = $this->invoiceModel->findByCompany($id, $companyId);
            if (!$invoice) {
                Response::notFound('Fatura bulunamadı');
                return;
            }

            $payments = $this->invoiceModel->getPayments($id);

            Response::json([
                'success' => true,
                'message' => 'Ödemeler başarıyla listelendi',
                'data' => $payments
            ]);
        } catch (\Exception $e) {
            error_log("Get payments error: " . $e->getMessage());
            Response::serverError('Ödemeler listelenirken bir hata oluştu');
        }
    }

    /**
     * Convert quotation to invoice
     * POST /api/invoices/convert-from-quotation/:quotationId
     */
    public function convertFromQuotation(int $quotationId): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Geçersiz oturum');
                return;
            }

            $companyId = (int)$user['company_id'];
            $userId = (int)$user['id'];

            $data = json_decode(file_get_contents('php://input'), true);
            $overrides = [
                'invoice_date' => $data['invoice_date'] ?? date('Y-m-d'),
                'due_date' => $data['due_date'] ?? date('Y-m-d', strtotime('+30 days'))
            ];

            $invoiceId = $this->invoiceModel->convertFromQuotation(
                $quotationId,
                $companyId,
                $userId,
                $overrides
            );

            if (!$invoiceId) {
                Response::serverError('Teklif faturaya dönüştürülemedi');
                return;
            }

            // Log action
            $this->invoiceModel->logAction(
                $invoiceId,
                $userId,
                'created',
                "Teklif (#$quotationId) faturaya dönüştürüldü"
            );

            $invoice = $this->invoiceModel->findWithItems($invoiceId, $companyId);

            Response::created([
                'success' => true,
                'message' => 'Teklif başarıyla faturaya dönüştürüldü',
                'data' => $invoice
            ]);
        } catch (\Exception $e) {
            error_log("Convert quotation error: " . $e->getMessage());
            Response::serverError('Teklif dönüştürülürken bir hata oluştu');
        }
    }

    /**
     * Get overdue invoices
     * GET /api/invoices/overdue
     */
    public function getOverdue(): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Geçersiz oturum');
                return;
            }

            $companyId = (int)$user['company_id'];
            $invoices = $this->invoiceModel->getOverdue($companyId);

            Response::json([
                'success' => true,
                'message' => 'Vadesi geçmiş faturalar listelendi',
                'data' => $invoices
            ]);
        } catch (\Exception $e) {
            error_log("Get overdue error: " . $e->getMessage());
            Response::serverError('Faturalar listelenirken bir hata oluştu');
        }
    }

    /**
     * Get due today invoices
     * GET /api/invoices/due-today
     */
    public function getDueToday(): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Geçersiz oturum');
                return;
            }

            $companyId = (int)$user['company_id'];
            $invoices = $this->invoiceModel->getDueToday($companyId);

            Response::json([
                'success' => true,
                'message' => 'Bugün vadesi dolan faturalar listelendi',
                'data' => $invoices
            ]);
        } catch (\Exception $e) {
            error_log("Get due today error: " . $e->getMessage());
            Response::serverError('Faturalar listelenirken bir hata oluştu');
        }
    }

    /**
     * Get invoice statistics
     * GET /api/invoices/statistics
     */
    public function getStatistics(): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Geçersiz oturum');
                return;
            }

            $companyId = (int)$user['company_id'];
            
            $dateRange = [];
            if (!empty($_GET['date_from'])) {
                $dateRange['from'] = $_GET['date_from'];
            }
            if (!empty($_GET['date_to'])) {
                $dateRange['to'] = $_GET['date_to'];
            }

            $statistics = $this->invoiceModel->getStatistics($companyId, $dateRange);

            Response::json([
                'success' => true,
                'message' => 'İstatistikler başarıyla getirildi',
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            error_log("Get statistics error: " . $e->getMessage());
            Response::serverError('İstatistikler getirilirken bir hata oluştu');
        }
    }

    /**
     * Get monthly summary
     * GET /api/invoices/monthly-summary
     */
    public function getMonthlySummary(): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Geçersiz oturum');
                return;
            }

            $companyId = (int)$user['company_id'];
            $year = (int)($_GET['year'] ?? date('Y'));
            $month = (int)($_GET['month'] ?? date('m'));

            $summary = $this->invoiceModel->getMonthlySummary($companyId, $year, $month);

            Response::json([
                'success' => true,
                'message' => 'Aylık özet başarıyla getirildi',
                'data' => [
                    'year' => $year,
                    'month' => $month,
                    'summary' => $summary
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Get monthly summary error: " . $e->getMessage());
            Response::serverError('Aylık özet getirilirken bir hata oluştu');
        }
    }

    /**
     * Get recurring invoices
     * GET /api/invoices/recurring
     */
    public function getRecurring(): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Geçersiz oturum');
                return;
            }

            $companyId = (int)$user['company_id'];
            
            $sql = "SELECT * FROM invoices 
                    WHERE company_id = ? 
                    AND is_recurring = TRUE 
                    AND deleted_at IS NULL 
                    ORDER BY recurring_start_date DESC";
            
            $stmt = $this->invoiceModel->query($sql, [$companyId]);
            $invoices = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            Response::json([
                'success' => true,
                'message' => 'Periyodik faturalar listelendi',
                'data' => $invoices
            ]);
        } catch (\Exception $e) {
            error_log("Get recurring error: " . $e->getMessage());
            Response::serverError('Periyodik faturalar listelenirken bir hata oluştu');
        }
    }

    /**
     * Process recurring invoices (cron job)
     * POST /api/invoices/process-recurring
     */
    public function processRecurring(): void
    {
        try {
            // This should be called by a cron job, not directly by users
            // Add additional authentication check for cron jobs if needed
            
            $recurringInvoices = $this->invoiceModel->getRecurringDue();
            $generated = [];
            $errors = [];

            foreach ($recurringInvoices as $template) {
                $newId = $this->invoiceModel->generateFromRecurring($template['id']);
                
                if ($newId) {
                    $generated[] = [
                        'template_id' => $template['id'],
                        'new_invoice_id' => $newId
                    ];
                } else {
                    $errors[] = [
                        'template_id' => $template['id'],
                        'error' => 'Fatura oluşturulamadı'
                    ];
                }
            }

            Response::json([
                'success' => true,
                'message' => sprintf('%d fatura oluşturuldu', count($generated)),
                'data' => [
                    'generated' => $generated,
                    'errors' => $errors,
                    'total_processed' => count($recurringInvoices)
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Process recurring error: " . $e->getMessage());
            Response::serverError('Periyodik faturalar işlenirken bir hata oluştu');
        }
    }

    /**
     * Validate invoice data
     */
    private function validateInvoice(array $data): array
    {
        $errors = [];

        if (empty($data['invoice_type'])) {
            $errors[] = 'Fatura tipi gereklidir';
        } elseif (!in_array($data['invoice_type'], ['sales', 'purchase', 'sales_return', 'purchase_return'])) {
            $errors[] = 'Geçersiz fatura tipi';
        }

        if (empty($data['invoice_date'])) {
            $errors[] = 'Fatura tarihi gereklidir';
        }

        if (empty($data['due_date'])) {
            $errors[] = 'Vade tarihi gereklidir';
        }

        if (empty($data['cari_id']) && empty($data['customer_name'])) {
            $errors[] = 'Cari hesap veya müşteri adı gereklidir';
        }

        return $errors;
    }
}

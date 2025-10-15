<?php

namespace App\Controllers;

use App\Helpers\Response;
use App\Models\Quotation;
use App\Models\QuotationItem;

/**
 * Quotation Controller
 * Handles quotation/offer management
 */
class QuotationController
{
    private Quotation $quotationModel;
    private QuotationItem $itemModel;

    public function __construct()
    {
        $this->quotationModel = new Quotation();
        $this->itemModel = new QuotationItem();
    }

    /**
     * Get all quotations
     * GET /api/quotations
     */
    public function index(): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Unauthorized access');
                return;
            }

            $companyId = (int)$user['company_id'];
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 25);

            // Filters
            $filters = [
                'status' => $_GET['status'] ?? null,
                'cari_id' => isset($_GET['cari_id']) ? (int)$_GET['cari_id'] : null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null,
                'search' => $_GET['search'] ?? null
            ];

            // Remove null filters
            $filters = array_filter($filters, fn($v) => $v !== null);

            $result = $this->quotationModel->getAll($companyId, $filters, $page, $perPage);

            Response::success($result);
        } catch (\Exception $e) {
            error_log("Quotation index error: " . $e->getMessage());
            Response::error('Failed to fetch quotations', 500);
        }
    }

    /**
     * Get single quotation with items
     * GET /api/quotations/{id}
     */
    public function show(int $id): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Unauthorized access');
                return;
            }

            $companyId = (int)$user['company_id'];
            $quotation = $this->quotationModel->findWithItems($id, $companyId);

            if (!$quotation) {
                Response::notFound('Quotation not found');
                return;
            }

            Response::success($quotation);
        } catch (\Exception $e) {
            error_log("Quotation show error: " . $e->getMessage());
            Response::error('Failed to fetch quotation', 500);
        }
    }

    /**
     * Create new quotation
     * POST /api/quotations
     */
    public function store(): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Unauthorized access');
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $errors = $this->validateQuotation($data);
            if (!empty($errors)) {
                Response::error('Validation failed', 422, ['errors' => $errors]);
                return;
            }

            $companyId = (int)$user['company_id'];
            $userId = (int)$user['id'];

            // Prepare quotation data
            $quotationData = [
                'company_id' => $companyId,
                'quotation_date' => $data['quotation_date'] ?? date('Y-m-d'),
                'valid_until' => $data['valid_until'] ?? date('Y-m-d', strtotime('+30 days')),
                'cari_id' => $data['cari_id'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_tax_number' => $data['customer_tax_number'] ?? null,
                'customer_tax_office' => $data['customer_tax_office'] ?? null,
                'customer_address' => $data['customer_address'] ?? null,
                'customer_city' => $data['customer_city'] ?? null,
                'customer_district' => $data['customer_district'] ?? null,
                'customer_postal_code' => $data['customer_postal_code'] ?? null,
                'discount_type' => $data['discount_type'] ?? null,
                'discount_value' => $data['discount_value'] ?? 0,
                'currency' => $data['currency'] ?? 'TRY',
                'exchange_rate' => $data['exchange_rate'] ?? 1.00,
                'notes' => $data['notes'] ?? null,
                'terms_conditions' => $data['terms_conditions'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'status' => $data['status'] ?? Quotation::STATUS_DRAFT,
                'created_by' => $userId
            ];

            // Get items
            $items = $data['items'] ?? [];
            if (empty($items)) {
                Response::error('At least one item is required', 422);
                return;
            }

            // Validate items
            $itemErrors = $this->validateItems($items);
            if (!empty($itemErrors)) {
                Response::error('Item validation failed', 422, ['errors' => $itemErrors]);
                return;
            }

            // Create quotation with items
            $quotationId = $this->quotationModel->createWithItems($quotationData, $items);

            if (!$quotationId) {
                Response::error('Failed to create quotation', 500);
                return;
            }

            // Get created quotation
            $quotation = $this->quotationModel->findWithItems($quotationId, $companyId);

            Response::success($quotation, 'Quotation created successfully', 201);
        } catch (\Exception $e) {
            error_log("Quotation store error: " . $e->getMessage());
            Response::error('Failed to create quotation', 500);
        }
    }

    /**
     * Update quotation
     * PUT /api/quotations/{id}
     */
    public function update(int $id): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Unauthorized access');
                return;
            }

            $companyId = (int)$user['company_id'];
            $userId = (int)$user['id'];

            // Check if quotation exists
            $quotation = $this->quotationModel->findByCompany($id, $companyId);
            if (!$quotation) {
                Response::notFound('Quotation not found');
                return;
            }

            // Check if quotation can be updated
            if (!in_array($quotation['status'], [Quotation::STATUS_DRAFT])) {
                Response::error('Only draft quotations can be updated', 422);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);

            // Update quotation
            $updateData = array_intersect_key($data, array_flip([
                'quotation_date', 'valid_until', 'cari_id',
                'customer_name', 'customer_email', 'customer_phone',
                'customer_tax_number', 'customer_tax_office',
                'customer_address', 'customer_city', 'customer_district', 'customer_postal_code',
                'discount_type', 'discount_value', 'currency', 'exchange_rate',
                'notes', 'terms_conditions', 'internal_notes'
            ]));

            $updateData['updated_by'] = $userId;

            if (!empty($updateData)) {
                $this->quotationModel->update($id, $updateData);
            }

            // Update items if provided
            if (isset($data['items'])) {
                // Validate items
                $itemErrors = $this->validateItems($data['items']);
                if (!empty($itemErrors)) {
                    Response::error('Item validation failed', 422, ['errors' => $itemErrors]);
                    return;
                }

                // Delete existing items (soft delete)
                $this->itemModel->deleteAllByQuotation($id);

                // Create new items
                foreach ($data['items'] as $item) {
                    $item['quotation_id'] = $id;
                    $this->itemModel->create($item);
                }
            }

            // Get updated quotation
            $quotation = $this->quotationModel->findWithItems($id, $companyId);

            Response::success($quotation, 'Quotation updated successfully');
        } catch (\Exception $e) {
            error_log("Quotation update error: " . $e->getMessage());
            Response::error('Failed to update quotation', 500);
        }
    }

    /**
     * Delete quotation (soft delete)
     * DELETE /api/quotations/{id}
     */
    public function destroy(int $id): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Unauthorized access');
                return;
            }

            $companyId = (int)$user['company_id'];

            // Check if quotation exists
            $quotation = $this->quotationModel->findByCompany($id, $companyId);
            if (!$quotation) {
                Response::notFound('Quotation not found');
                return;
            }

            // Check if quotation can be deleted
            if ($quotation['status'] === Quotation::STATUS_CONVERTED) {
                Response::error('Converted quotations cannot be deleted', 422);
                return;
            }

            // Soft delete
            $this->quotationModel->delete($id);

            Response::success(null, 'Quotation deleted successfully');
        } catch (\Exception $e) {
            error_log("Quotation destroy error: " . $e->getMessage());
            Response::error('Failed to delete quotation', 500);
        }
    }

    /**
     * Send quotation
     * POST /api/quotations/{id}/send
     */
    public function send(int $id): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Unauthorized access');
                return;
            }

            $companyId = (int)$user['company_id'];
            $userId = (int)$user['id'];

            $success = $this->quotationModel->send($id, $companyId, $userId);

            if (!$success) {
                Response::error('Failed to send quotation. Only draft quotations can be sent.', 422);
                return;
            }

            $quotation = $this->quotationModel->findByCompany($id, $companyId);

            Response::success($quotation, 'Quotation sent successfully');
        } catch (\Exception $e) {
            error_log("Quotation send error: " . $e->getMessage());
            Response::error('Failed to send quotation', 500);
        }
    }

    /**
     * Accept quotation
     * POST /api/quotations/{id}/accept
     */
    public function accept(int $id): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Unauthorized access');
                return;
            }

            $companyId = (int)$user['company_id'];
            $userId = (int)$user['id'];

            $success = $this->quotationModel->accept($id, $companyId, $userId);

            if (!$success) {
                Response::error('Failed to accept quotation. Only sent quotations can be accepted.', 422);
                return;
            }

            $quotation = $this->quotationModel->findByCompany($id, $companyId);

            Response::success($quotation, 'Quotation accepted successfully');
        } catch (\Exception $e) {
            error_log("Quotation accept error: " . $e->getMessage());
            Response::error('Failed to accept quotation', 500);
        }
    }

    /**
     * Reject quotation
     * POST /api/quotations/{id}/reject
     */
    public function reject(int $id): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Unauthorized access');
                return;
            }

            $companyId = (int)$user['company_id'];
            $userId = (int)$user['id'];

            $data = json_decode(file_get_contents('php://input'), true);
            $reason = $data['reason'] ?? 'No reason provided';

            $success = $this->quotationModel->reject($id, $companyId, $userId, $reason);

            if (!$success) {
                Response::error('Failed to reject quotation', 422);
                return;
            }

            $quotation = $this->quotationModel->findByCompany($id, $companyId);

            Response::success($quotation, 'Quotation rejected successfully');
        } catch (\Exception $e) {
            error_log("Quotation reject error: " . $e->getMessage());
            Response::error('Failed to reject quotation', 500);
        }
    }

    /**
     * Duplicate quotation
     * POST /api/quotations/{id}/duplicate
     */
    public function duplicate(int $id): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Unauthorized access');
                return;
            }

            $companyId = (int)$user['company_id'];
            $userId = (int)$user['id'];

            $newId = $this->quotationModel->duplicate($id, $companyId, $userId);

            if (!$newId) {
                Response::error('Failed to duplicate quotation', 500);
                return;
            }

            $quotation = $this->quotationModel->findWithItems($newId, $companyId);

            Response::success($quotation, 'Quotation duplicated successfully', 201);
        } catch (\Exception $e) {
            error_log("Quotation duplicate error: " . $e->getMessage());
            Response::error('Failed to duplicate quotation', 500);
        }
    }

    /**
     * Get quotation statistics
     * GET /api/quotations/statistics
     */
    public function statistics(): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Unauthorized access');
                return;
            }

            $companyId = (int)$user['company_id'];

            $dateRange = [
                'from' => $_GET['date_from'] ?? null,
                'to' => $_GET['date_to'] ?? null
            ];

            $stats = $this->quotationModel->getStatistics($companyId, array_filter($dateRange));

            Response::success([
                'by_status' => $stats,
                'expiring_soon' => $this->quotationModel->getExpiringSoon($companyId)
            ]);
        } catch (\Exception $e) {
            error_log("Quotation statistics error: " . $e->getMessage());
            Response::error('Failed to fetch statistics', 500);
        }
    }

    /**
     * Get quotations by status
     * GET /api/quotations/by-status/{status}
     */
    public function byStatus(string $status): void
    {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['company_id'])) {
                Response::unauthorized('Unauthorized access');
                return;
            }

            $companyId = (int)$user['company_id'];
            $quotations = $this->quotationModel->getByStatus($companyId, $status);

            Response::success(['data' => $quotations]);
        } catch (\Exception $e) {
            error_log("Quotation byStatus error: " . $e->getMessage());
            Response::error('Failed to fetch quotations', 500);
        }
    }

    /**
     * Validate quotation data
     */
    private function validateQuotation(array $data): array
    {
        $errors = [];

        // Either cari_id or customer name required
        if (empty($data['cari_id']) && empty($data['customer_name'])) {
            $errors['customer'] = 'Either cari_id or customer_name is required';
        }

        // Date validation
        if (!empty($data['quotation_date']) && !$this->isValidDate($data['quotation_date'])) {
            $errors['quotation_date'] = 'Invalid date format';
        }

        if (!empty($data['valid_until']) && !$this->isValidDate($data['valid_until'])) {
            $errors['valid_until'] = 'Invalid date format';
        }

        // Currency validation
        if (!empty($data['currency']) && !in_array($data['currency'], ['TRY', 'USD', 'EUR', 'GBP'])) {
            $errors['currency'] = 'Invalid currency';
        }

        return $errors;
    }

    /**
     * Validate items
     */
    private function validateItems(array $items): array
    {
        $errors = [];

        foreach ($items as $index => $item) {
            if (empty($item['item_name'])) {
                $errors["items.{$index}.item_name"] = 'Item name is required';
            }

            if (!isset($item['quantity']) || $item['quantity'] <= 0) {
                $errors["items.{$index}.quantity"] = 'Quantity must be greater than 0';
            }

            if (!isset($item['unit_price']) || $item['unit_price'] < 0) {
                $errors["items.{$index}.unit_price"] = 'Unit price must be 0 or greater';
            }
        }

        return $errors;
    }

    /**
     * Check if date is valid
     */
    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}

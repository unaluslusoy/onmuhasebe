<?php

namespace App\Controllers\Api;

use App\Services\Excel\ExcelService;
use App\Helpers\Response;
use App\Helpers\Logger;

/**
 * Excel API Controller
 * Handles Excel import/export operations
 */
class ExcelController
{
    private ExcelService $excelService;

    public function __construct()
    {
        $this->excelService = new ExcelService();
    }

    /**
     * Export invoices to Excel
     * GET /api/excel/export/invoices
     */
    public function exportInvoices(): void
    {
        try {
            $companyId = $_SESSION['company_id'] ?? null;

            $filepath = $this->excelService->exportInvoices($companyId);
            $filename = basename($filepath);

            // Download file
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);

            // Delete file after download
            unlink($filepath);

            exit;

        } catch (\Exception $e) {
            Logger::error('Invoice export failed', [
                'error' => $e->getMessage()
            ]);

            Response::serverError('Failed to export invoices');
        }
    }

    /**
     * Export products to Excel
     * GET /api/excel/export/products
     */
    public function exportProducts(): void
    {
        try {
            $companyId = $_SESSION['company_id'] ?? null;

            $filepath = $this->excelService->exportProducts($companyId);
            $filename = basename($filepath);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);

            unlink($filepath);

            exit;

        } catch (\Exception $e) {
            Logger::error('Product export failed', [
                'error' => $e->getMessage()
            ]);

            Response::serverError('Failed to export products');
        }
    }

    /**
     * Export customers to Excel
     * GET /api/excel/export/customers
     */
    public function exportCustomers(): void
    {
        try {
            $companyId = $_SESSION['company_id'] ?? null;

            $filepath = $this->excelService->exportCustomers($companyId);
            $filename = basename($filepath);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);

            unlink($filepath);

            exit;

        } catch (\Exception $e) {
            Logger::error('Customer export failed', [
                'error' => $e->getMessage()
            ]);

            Response::serverError('Failed to export customers');
        }
    }

    /**
     * Import products from Excel
     * POST /api/excel/import/products
     */
    public function importProducts(): void
    {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $companyId = $_SESSION['company_id'] ?? null;

            if (!$userId || !$companyId) {
                Response::unauthorized('Authentication required');
                return;
            }

            // Check if file uploaded
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                Response::badRequest('No file uploaded');
                return;
            }

            $file = $_FILES['file'];

            // Validate file type
            $allowedTypes = [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ];

            if (!in_array($file['type'], $allowedTypes)) {
                Response::badRequest('Invalid file type. Only Excel files are allowed.');
                return;
            }

            // Save uploaded file
            $uploadPath = $this->excelService->getExportPath();
            $filename = 'import_' . time() . '_' . $file['name'];
            $filepath = $uploadPath . $filename;

            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                Response::serverError('Failed to save uploaded file');
                return;
            }

            // Import products
            $result = $this->excelService->importProducts($filepath, $companyId, $userId);

            // Delete uploaded file
            unlink($filepath);

            if ($result['success']) {
                Response::success($result, 'Products imported successfully');
            } else {
                Response::badRequest('Import failed', $result);
            }

        } catch (\Exception $e) {
            Logger::error('Product import failed', [
                'error' => $e->getMessage()
            ]);

            Response::serverError('Failed to import products');
        }
    }

    /**
     * Download product import template
     * GET /api/excel/template/products
     */
    public function downloadProductTemplate(): void
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Products');

            // Headers
            $headers = [
                'A1' => 'SKU*',
                'B1' => 'Name*',
                'C1' => 'Category',
                'D1' => 'Description',
                'E1' => 'Unit Price',
                'F1' => 'Cost Price',
                'G1' => 'Stock Quantity',
                'H1' => 'Min Stock',
                'I1' => 'Unit',
                'J1' => 'Barcode',
                'K1' => 'Tax Rate (%)',
                'L1' => 'Status'
            ];

            foreach ($headers as $cell => $header) {
                $sheet->setCellValue($cell, $header);
            }

            // Sample data
            $sheet->setCellValue('A2', 'PRD-001');
            $sheet->setCellValue('B2', 'Sample Product');
            $sheet->setCellValue('C2', 'Electronics');
            $sheet->setCellValue('D2', 'Sample product description');
            $sheet->setCellValue('E2', 100.00);
            $sheet->setCellValue('F2', 80.00);
            $sheet->setCellValue('G2', 50);
            $sheet->setCellValue('H2', 10);
            $sheet->setCellValue('I2', 'pcs');
            $sheet->setCellValue('J2', '1234567890123');
            $sheet->setCellValue('K2', 18);
            $sheet->setCellValue('L2', 'Active');

            // Auto-size columns
            foreach (range('A', 'L') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $filename = 'product_import_template.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');

            exit;

        } catch (\Exception $e) {
            Logger::error('Template download failed', [
                'error' => $e->getMessage()
            ]);

            Response::serverError('Failed to download template');
        }
    }
}

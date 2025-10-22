<?php

namespace App\Services\Excel;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Helpers\Logger;
use App\Helpers\Database;

/**
 * Excel Service
 * Handles Excel import/export operations
 */
class ExcelService
{
    private \PDO $db;
    private string $exportPath;
    private string $importPath;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->exportPath = __DIR__ . '/../../../storage/exports/';
        $this->importPath = __DIR__ . '/../../../storage/imports/';

        // Create directories if they don't exist
        if (!is_dir($this->exportPath)) {
            mkdir($this->exportPath, 0755, true);
        }
        if (!is_dir($this->importPath)) {
            mkdir($this->importPath, 0755, true);
        }
    }

    /**
     * Export invoices to Excel
     */
    public function exportInvoices(?int $companyId = null, array $filters = []): string
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set title
            $sheet->setTitle('Invoices');

            // Headers
            $headers = [
                'A1' => 'Invoice Number',
                'B1' => 'Date',
                'C1' => 'Due Date',
                'D1' => 'Customer',
                'E1' => 'Subtotal',
                'F1' => 'Tax',
                'G1' => 'Total',
                'H1' => 'Paid Amount',
                'I1' => 'Remaining',
                'J1' => 'Status',
                'K1' => 'Currency'
            ];

            foreach ($headers as $cell => $header) {
                $sheet->setCellValue($cell, $header);
            }

            // Style headers
            $this->styleHeader($sheet, 'A1:K1');

            // Fetch data
            $whereClause = $companyId ? "WHERE company_id = ? AND deleted_at IS NULL" : "WHERE deleted_at IS NULL";
            $params = $companyId ? [$companyId] : [];

            $sql = "SELECT
                        invoice_number,
                        invoice_date,
                        due_date,
                        customer_name,
                        subtotal,
                        tax_amount,
                        total,
                        paid_amount,
                        remaining_amount,
                        payment_status,
                        currency
                    FROM invoices
                    {$whereClause}
                    ORDER BY invoice_date DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $invoices = $stmt->fetchAll();

            // Fill data
            $row = 2;
            foreach ($invoices as $invoice) {
                $sheet->setCellValue('A' . $row, $invoice['invoice_number']);
                $sheet->setCellValue('B' . $row, $invoice['invoice_date']);
                $sheet->setCellValue('C' . $row, $invoice['due_date']);
                $sheet->setCellValue('D' . $row, $invoice['customer_name']);
                $sheet->setCellValue('E' . $row, $invoice['subtotal']);
                $sheet->setCellValue('F' . $row, $invoice['tax_amount']);
                $sheet->setCellValue('G' . $row, $invoice['total']);
                $sheet->setCellValue('H' . $row, $invoice['paid_amount']);
                $sheet->setCellValue('I' . $row, $invoice['remaining_amount']);
                $sheet->setCellValue('J' . $row, ucfirst($invoice['payment_status']));
                $sheet->setCellValue('K' . $row, $invoice['currency']);

                // Format currency columns
                $sheet->getStyle('E' . $row . ':I' . $row)->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'K') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Save file
            $filename = 'invoices_' . date('Y-m-d_His') . '.xlsx';
            $filepath = $this->exportPath . $filename;

            $writer = new Xlsx($spreadsheet);
            $writer->save($filepath);

            Logger::info('Invoices exported to Excel', [
                'filename' => $filename,
                'count' => count($invoices)
            ]);

            return $filepath;

        } catch (\Exception $e) {
            Logger::error('Invoice export failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Export products to Excel
     */
    public function exportProducts(?int $companyId = null): string
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Products');

            // Headers
            $headers = [
                'A1' => 'SKU',
                'B1' => 'Name',
                'C1' => 'Category',
                'D1' => 'Description',
                'E1' => 'Unit Price',
                'F1' => 'Cost Price',
                'G1' => 'Stock Quantity',
                'H1' => 'Min Stock',
                'I1' => 'Unit',
                'J1' => 'Barcode',
                'K1' => 'Tax Rate',
                'L1' => 'Status'
            ];

            foreach ($headers as $cell => $header) {
                $sheet->setCellValue($cell, $header);
            }

            $this->styleHeader($sheet, 'A1:L1');

            // Fetch data
            $whereClause = $companyId ? "WHERE p.company_id = ? AND p.deleted_at IS NULL" : "WHERE p.deleted_at IS NULL";
            $params = $companyId ? [$companyId] : [];

            $sql = "SELECT
                        p.sku,
                        p.name,
                        pc.name as category_name,
                        p.description,
                        p.unit_price,
                        p.cost_price,
                        p.stock_quantity,
                        p.min_stock_level,
                        p.unit,
                        p.barcode,
                        p.tax_rate,
                        p.is_active
                    FROM products p
                    LEFT JOIN product_categories pc ON p.category_id = pc.id
                    {$whereClause}
                    ORDER BY p.name ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll();

            // Fill data
            $row = 2;
            foreach ($products as $product) {
                $sheet->setCellValue('A' . $row, $product['sku']);
                $sheet->setCellValue('B' . $row, $product['name']);
                $sheet->setCellValue('C' . $row, $product['category_name']);
                $sheet->setCellValue('D' . $row, $product['description']);
                $sheet->setCellValue('E' . $row, $product['unit_price']);
                $sheet->setCellValue('F' . $row, $product['cost_price']);
                $sheet->setCellValue('G' . $row, $product['stock_quantity']);
                $sheet->setCellValue('H' . $row, $product['min_stock_level']);
                $sheet->setCellValue('I' . $row, $product['unit']);
                $sheet->setCellValue('J' . $row, $product['barcode']);
                $sheet->setCellValue('K' . $row, $product['tax_rate']);
                $sheet->setCellValue('L' . $row, $product['is_active'] ? 'Active' : 'Inactive');

                // Format price columns
                $sheet->getStyle('E' . $row . ':F' . $row)->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'L') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $filename = 'products_' . date('Y-m-d_His') . '.xlsx';
            $filepath = $this->exportPath . $filename;

            $writer = new Xlsx($spreadsheet);
            $writer->save($filepath);

            Logger::info('Products exported to Excel', [
                'filename' => $filename,
                'count' => count($products)
            ]);

            return $filepath;

        } catch (\Exception $e) {
            Logger::error('Product export failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Export customers to Excel
     */
    public function exportCustomers(?int $companyId = null): string
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Customers');

            // Headers
            $headers = [
                'A1' => 'Name',
                'B1' => 'Type',
                'C1' => 'Tax Number',
                'D1' => 'Email',
                'E1' => 'Phone',
                'F1' => 'Address',
                'G1' => 'City',
                'H1' => 'Country',
                'I1' => 'Balance',
                'J1' => 'Status'
            ];

            foreach ($headers as $cell => $header) {
                $sheet->setCellValue($cell, $header);
            }

            $this->styleHeader($sheet, 'A1:J1');

            // Fetch data
            $whereClause = $companyId ? "WHERE company_id = ? AND deleted_at IS NULL" : "WHERE deleted_at IS NULL";
            $params = $companyId ? [$companyId] : [];

            $sql = "SELECT
                        name,
                        type,
                        tax_number,
                        email,
                        phone,
                        address,
                        city,
                        country,
                        balance,
                        is_active
                    FROM cari_accounts
                    {$whereClause}
                    ORDER BY name ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $customers = $stmt->fetchAll();

            // Fill data
            $row = 2;
            foreach ($customers as $customer) {
                $sheet->setCellValue('A' . $row, $customer['name']);
                $sheet->setCellValue('B' . $row, ucfirst($customer['type']));
                $sheet->setCellValue('C' . $row, $customer['tax_number']);
                $sheet->setCellValue('D' . $row, $customer['email']);
                $sheet->setCellValue('E' . $row, $customer['phone']);
                $sheet->setCellValue('F' . $row, $customer['address']);
                $sheet->setCellValue('G' . $row, $customer['city']);
                $sheet->setCellValue('H' . $row, $customer['country']);
                $sheet->setCellValue('I' . $row, $customer['balance']);
                $sheet->setCellValue('J' . $row, $customer['is_active'] ? 'Active' : 'Inactive');

                // Format balance
                $sheet->getStyle('I' . $row)->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $filename = 'customers_' . date('Y-m-d_His') . '.xlsx';
            $filepath = $this->exportPath . $filename;

            $writer = new Xlsx($spreadsheet);
            $writer->save($filepath);

            Logger::info('Customers exported to Excel', [
                'filename' => $filename,
                'count' => count($customers)
            ]);

            return $filepath;

        } catch (\Exception $e) {
            Logger::error('Customer export failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Import products from Excel
     */
    public function importProducts(string $filepath, int $companyId, int $userId): array
    {
        try {
            $spreadsheet = IOFactory::load($filepath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Remove header row
            array_shift($rows);

            $imported = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;

                // Skip empty rows
                if (empty($row[0]) && empty($row[1])) {
                    continue;
                }

                try {
                    // Validate required fields
                    if (empty($row[0])) { // SKU
                        $errors[] = "Row {$rowNumber}: SKU is required";
                        continue;
                    }
                    if (empty($row[1])) { // Name
                        $errors[] = "Row {$rowNumber}: Name is required";
                        continue;
                    }

                    // Check if product exists
                    $stmt = $this->db->prepare("SELECT id FROM products WHERE sku = ? AND company_id = ?");
                    $stmt->execute([$row[0], $companyId]);
                    $existing = $stmt->fetch();

                    if ($existing) {
                        // Update existing
                        $sql = "UPDATE products SET
                                name = ?,
                                description = ?,
                                unit_price = ?,
                                cost_price = ?,
                                stock_quantity = ?,
                                min_stock_level = ?,
                                unit = ?,
                                barcode = ?,
                                tax_rate = ?,
                                updated_at = NOW()
                                WHERE id = ?";

                        $stmt = $this->db->prepare($sql);
                        $stmt->execute([
                            $row[1], // name
                            $row[3] ?? null, // description
                            $row[4] ?? 0, // unit_price
                            $row[5] ?? 0, // cost_price
                            $row[6] ?? 0, // stock_quantity
                            $row[7] ?? 0, // min_stock_level
                            $row[8] ?? 'pcs', // unit
                            $row[9] ?? null, // barcode
                            $row[10] ?? 18, // tax_rate
                            $existing['id']
                        ]);
                    } else {
                        // Insert new
                        $sql = "INSERT INTO products (
                                company_id, sku, name, description, unit_price, cost_price,
                                stock_quantity, min_stock_level, unit, barcode, tax_rate,
                                is_active, created_by, created_at
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())";

                        $stmt = $this->db->prepare($sql);
                        $stmt->execute([
                            $companyId,
                            $row[0], // sku
                            $row[1], // name
                            $row[3] ?? null, // description
                            $row[4] ?? 0, // unit_price
                            $row[5] ?? 0, // cost_price
                            $row[6] ?? 0, // stock_quantity
                            $row[7] ?? 0, // min_stock_level
                            $row[8] ?? 'pcs', // unit
                            $row[9] ?? null, // barcode
                            $row[10] ?? 18, // tax_rate
                            $userId
                        ]);
                    }

                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                }
            }

            Logger::info('Products imported from Excel', [
                'imported' => $imported,
                'errors' => count($errors)
            ]);

            return [
                'success' => true,
                'imported' => $imported,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            Logger::error('Product import failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'imported' => 0,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Style header row
     */
    private function styleHeader($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
    }

    /**
     * Get export directory
     */
    public function getExportPath(): string
    {
        return $this->exportPath;
    }
}

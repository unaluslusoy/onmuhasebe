<?php
// Test Invoice Model directly
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Invoice;

try {
    $invoice = new Invoice();
    
    $testData = [
        'company_id' => 23,
        'invoice_type' => 'sales',
        'invoice_date' => date('Y-m-d'),
        'due_date' => date('Y-m-d', strtotime('+30 days')),
        'customer_name' => 'Test Customer Direct',
        'currency' => 'TRY',
        'exchange_rate' => 1.0,
        'discount_type' => 'percentage',
        'discount_value' => 0,
        'is_draft' => false,
        'created_by' => 23
    ];
    
    $items = [
        [
            'item_type' => 'product',
            'item_name' => 'Test Product',
            'quantity' => 1,
            'unit' => 'Adet',
            'unit_price' => 100,
            'discount_type' => 'percentage',
            'discount_value' => 0,
            'tax_rate' => 20
        ]
    ];
    
    echo "Creating invoice with items...\n";
    $id = $invoice->createWithItems($testData, $items);
    
    if ($id) {
        echo "✓ Invoice created successfully with ID: $id\n";
        
        // Get details
        $details = $invoice->findWithItems($id, 23);
        echo "Invoice Number: " . $details['invoice_number'] . "\n";
        echo "Total: " . $details['total'] . " TL\n";
        echo "Items count: " . count($details['items']) . "\n";
    } else {
        echo "✗ Failed to create invoice\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

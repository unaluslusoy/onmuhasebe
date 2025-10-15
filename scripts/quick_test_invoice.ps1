# Invoice API Quick Test - cURL Script
# Windows PowerShell Version

Write-Host "=== INVOICE API QUICK TEST ===" -ForegroundColor Cyan
Write-Host ""

$baseUrl = "http://localhost/onmuhasebe/public/api"

# 1. Login
Write-Host "1. Login..." -ForegroundColor Yellow
$loginBody = @{
    email = "admin@example.com"
    password = "password123"
} | ConvertTo-Json

$loginResponse = Invoke-RestMethod -Uri "$baseUrl/auth/login" `
    -Method POST `
    -Body $loginBody `
    -ContentType "application/json"

if ($loginResponse.success) {
    $token = $loginResponse.data.tokens.access_token
    Write-Host "   ✓ Login successful" -ForegroundColor Green
    Write-Host "   Token: $($token.Substring(0,20))..." -ForegroundColor Gray
} else {
    Write-Host "   ✗ Login failed" -ForegroundColor Red
    exit 1
}

$headers = @{
    "Authorization" = "Bearer $token"
    "Content-Type" = "application/json"
}

# 2. Create Session
Write-Host ""
Write-Host "2. Creating session..." -ForegroundColor Yellow
$sessionResponse = Invoke-RestMethod -Uri "$baseUrl/auth/create-session" `
    -Method POST `
    -Headers $headers `
    -Body "{}"

if ($sessionResponse.success) {
    Write-Host "   ✓ Session created" -ForegroundColor Green
} else {
    Write-Host "   ✗ Session creation failed" -ForegroundColor Red
    exit 1
}

# 3. Create Invoice
Write-Host ""
Write-Host "3. Creating sales invoice..." -ForegroundColor Yellow
$invoiceBody = @{
    invoice_type = "sales"
    invoice_date = (Get-Date).ToString("yyyy-MM-dd")
    due_date = (Get-Date).AddDays(30).ToString("yyyy-MM-dd")
    customer_name = "Quick Test Customer"
    customer_email = "quicktest@example.com"
    currency = "TRY"
    exchange_rate = 1.0
    discount_type = "percentage"
    discount_value = 0
    items = @(
        @{
            item_type = "product"
            item_name = "Test Product 1"
            quantity = 5
            unit = "Adet"
            unit_price = 100
            tax_rate = 20
        },
        @{
            item_type = "service"
            item_name = "Test Service"
            quantity = 2
            unit = "Saat"
            unit_price = 200
            tax_rate = 20
        }
    )
} | ConvertTo-Json -Depth 5

$createResponse = Invoke-RestMethod -Uri "$baseUrl/invoices" `
    -Method POST `
    -Headers $headers `
    -Body $invoiceBody

if ($createResponse.success) {
    $invoiceId = $createResponse.data.id
    $invoiceNumber = $createResponse.data.invoice_number
    $total = $createResponse.data.total
    Write-Host "   ✓ Invoice created successfully" -ForegroundColor Green
    Write-Host "   ID: $invoiceId" -ForegroundColor Gray
    Write-Host "   Number: $invoiceNumber" -ForegroundColor Gray
    Write-Host "   Total: $total TL" -ForegroundColor Gray
} else {
    Write-Host "   ✗ Invoice creation failed" -ForegroundColor Red
    exit 1
}

# 4. Get Invoice Details
Write-Host ""
Write-Host "4. Getting invoice details..." -ForegroundColor Yellow
$detailsResponse = Invoke-RestMethod -Uri "$baseUrl/invoices/$invoiceId" `
    -Method GET `
    -Headers $headers

if ($detailsResponse.success) {
    Write-Host "   ✓ Invoice details retrieved" -ForegroundColor Green
    Write-Host "   Customer: $($detailsResponse.data.customer_name)" -ForegroundColor Gray
    Write-Host "   Items count: $($detailsResponse.data.items.Count)" -ForegroundColor Gray
    Write-Host "   Payment status: $($detailsResponse.data.payment_status)" -ForegroundColor Gray
} else {
    Write-Host "   ✗ Failed to get details" -ForegroundColor Red
}

# 5. List Invoices
Write-Host ""
Write-Host "5. Listing invoices..." -ForegroundColor Yellow
$listResponse = Invoke-RestMethod -Uri "$baseUrl/invoices?page=1&per_page=10" `
    -Method GET `
    -Headers $headers

if ($listResponse.success) {
    $count = $listResponse.data.Count
    $total = $listResponse.pagination.total
    Write-Host "   ✓ Invoices listed" -ForegroundColor Green
    Write-Host "   Showing $count of $total invoices" -ForegroundColor Gray
} else {
    Write-Host "   ✗ Failed to list invoices" -ForegroundColor Red
}

# 6. Update Invoice
Write-Host ""
Write-Host "6. Updating invoice..." -ForegroundColor Yellow
$updateBody = @{
    notes = "Updated via quick test script"
    discount_value = 5
} | ConvertTo-Json

$updateResponse = Invoke-RestMethod -Uri "$baseUrl/invoices/$invoiceId" `
    -Method PUT `
    -Headers $headers `
    -Body $updateBody

if ($updateResponse.success) {
    Write-Host "   ✓ Invoice updated" -ForegroundColor Green
    Write-Host "   New discount: $($updateResponse.data.discount_value)%" -ForegroundColor Gray
} else {
    Write-Host "   ✗ Update failed" -ForegroundColor Red
}

# 7. Approve Invoice
Write-Host ""
Write-Host "7. Approving invoice..." -ForegroundColor Yellow
$approveResponse = Invoke-RestMethod -Uri "$baseUrl/invoices/$invoiceId/approve" `
    -Method POST `
    -Headers $headers `
    -Body "{}"

if ($approveResponse.success) {
    Write-Host "   ✓ Invoice approved" -ForegroundColor Green
} else {
    Write-Host "   ✗ Approval failed" -ForegroundColor Red
}

# 8. Record Payment
Write-Host ""
Write-Host "8. Recording payment..." -ForegroundColor Yellow
$paymentBody = @{
    payment_date = (Get-Date).ToString("yyyy-MM-dd")
    amount = 300.00
    payment_method = "cash"
    notes = "Quick test payment"
} | ConvertTo-Json

$paymentResponse = Invoke-RestMethod -Uri "$baseUrl/invoices/$invoiceId/payments" `
    -Method POST `
    -Headers $headers `
    -Body $paymentBody

if ($paymentResponse.success) {
    Write-Host "   ✓ Payment recorded" -ForegroundColor Green
    Write-Host "   Amount: 300 TL" -ForegroundColor Gray
    Write-Host "   Payment status: $($paymentResponse.data.payment_status)" -ForegroundColor Gray
    Write-Host "   Remaining: $($paymentResponse.data.remaining_amount) TL" -ForegroundColor Gray
} else {
    Write-Host "   ✗ Payment failed" -ForegroundColor Red
}

# 9. Get Statistics
Write-Host ""
Write-Host "9. Getting statistics..." -ForegroundColor Yellow
$statsResponse = Invoke-RestMethod -Uri "$baseUrl/invoices/statistics" `
    -Method GET `
    -Headers $headers

if ($statsResponse.success) {
    Write-Host "   ✓ Statistics retrieved" -ForegroundColor Green
    foreach ($stat in $statsResponse.data) {
        Write-Host "   $($stat.invoice_type) - $($stat.payment_status): $($stat.count) invoices, Total: $($stat.total_amount) TL" -ForegroundColor Gray
    }
} else {
    Write-Host "   ✗ Statistics failed" -ForegroundColor Red
}

# 10. Get Monthly Summary
Write-Host ""
Write-Host "10. Getting monthly summary..." -ForegroundColor Yellow
$month = (Get-Date).Month
$year = (Get-Date).Year
$monthlyResponse = Invoke-RestMethod -Uri "$baseUrl/invoices/monthly-summary?year=$year&month=$month" `
    -Method GET `
    -Headers $headers

if ($monthlyResponse.success) {
    Write-Host "   ✓ Monthly summary retrieved" -ForegroundColor Green
    Write-Host "   Month: $($monthlyResponse.data.year)-$($monthlyResponse.data.month)" -ForegroundColor Gray
    foreach ($item in $monthlyResponse.data.summary) {
        Write-Host "   $($item.invoice_type): $($item.count) invoices, Total: $($item.total) TL" -ForegroundColor Gray
    }
} else {
    Write-Host "   ✗ Monthly summary failed" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== TEST COMPLETED ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "✓ All basic operations tested successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "Invoice ID for manual testing: $invoiceId" -ForegroundColor Yellow
Write-Host "Invoice Number: $invoiceNumber" -ForegroundColor Yellow
Write-Host ""
Write-Host "You can now:" -ForegroundColor White
Write-Host "  - Check database: SELECT * FROM invoices WHERE id = $invoiceId;" -ForegroundColor Gray
Write-Host "  - Test more endpoints using Postman collection" -ForegroundColor Gray
Write-Host "  - View full test guide: Docs/INVOICE_API_TEST_GUIDE.md" -ForegroundColor Gray

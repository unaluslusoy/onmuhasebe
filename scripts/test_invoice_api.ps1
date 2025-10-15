# Invoice API Test Script
# Tests all invoice endpoints with comprehensive scenarios

$baseUrl = "http://localhost/onmuhasebe/public"
$apiUrl = "$baseUrl/api"

# Color functions
function Write-Success { param($message) Write-Host "✓ $message" -ForegroundColor Green }
function Write-Error { param($message) Write-Host "✗ $message" -ForegroundColor Red }
function Write-Info { param($message) Write-Host "ℹ $message" -ForegroundColor Cyan }
function Write-Title { param($message) Write-Host "`n=== $message ===" -ForegroundColor Yellow }

# Test counters
$script:totalTests = 0
$script:passedTests = 0
$script:failedTests = 0

# Test result function
function Test-Result {
    param(
        [string]$TestName,
        [bool]$Passed,
        [string]$Message = ""
    )
    
    $script:totalTests++
    if ($Passed) {
        $script:passedTests++
        Write-Success "$TestName"
        if ($Message) { Write-Host "  → $Message" -ForegroundColor Gray }
    } else {
        $script:failedTests++
        Write-Error "$TestName"
        if ($Message) { Write-Host "  → $Message" -ForegroundColor Gray }
    }
}

# HTTP Request helper
function Invoke-ApiRequest {
    param(
        [string]$Method,
        [string]$Endpoint,
        [object]$Body = $null,
        [hashtable]$Headers = @{}
    )
    
    $uri = "$apiUrl$Endpoint"
    
    try {
        $params = @{
            Uri = $uri
            Method = $Method
            Headers = $Headers
            ContentType = "application/json"
        }
        
        if ($Body) {
            $params.Body = ($Body | ConvertTo-Json -Depth 10)
        }
        
        $response = Invoke-RestMethod @params -ErrorAction Stop
        return @{
            Success = $true
            Data = $response
            StatusCode = 200
        }
    } catch {
        $statusCode = $_.Exception.Response.StatusCode.Value__
        $errorBody = $null
        
        try {
            $stream = $_.Exception.Response.GetResponseStream()
            $reader = New-Object System.IO.StreamReader($stream)
            $errorBody = $reader.ReadToEnd() | ConvertFrom-Json
        } catch {}
        
        return @{
            Success = $false
            Error = $_.Exception.Message
            StatusCode = $statusCode
            ErrorBody = $errorBody
        }
    }
}

Write-Title "INVOICE API TESTS"

# ===========================================
# 1. AUTHENTICATION
# ===========================================
Write-Title "1. Authentication"

$loginData = @{
    email = "admin@example.com"
    password = "password123"
}

Write-Info "Logging in..."
$loginResponse = Invoke-ApiRequest -Method POST -Endpoint "/auth/login" -Body $loginData

if ($loginResponse.Success -and $loginResponse.Data.data.tokens.access_token) {
    $token = $loginResponse.Data.data.tokens.access_token
    $headers = @{ "Authorization" = "Bearer $token" }
    Test-Result "Login successful" $true "Token: $($token.Substring(0,20))..."
} else {
    Test-Result "Login failed" $false $loginResponse.Error
    Write-Error "Cannot continue without authentication"
    exit 1
}

# ===========================================
# 2. GET CARI ACCOUNTS (for invoice creation)
# ===========================================
Write-Title "2. Get Cari Accounts"

$cariResponse = Invoke-ApiRequest -Method GET -Endpoint "/cari?per_page=5" -Headers $headers
if ($cariResponse.Success -and $cariResponse.Data.data) {
    $cariAccounts = $cariResponse.Data.data
    if ($cariAccounts.Count -gt 0) {
        $testCariId = $cariAccounts[0].id
        Test-Result "Get cari accounts" $true "Found $($cariAccounts.Count) accounts, using ID: $testCariId"
    } else {
        Write-Info "No cari accounts found, will create invoice with customer details"
        $testCariId = $null
    }
} else {
    Test-Result "Get cari accounts" $false $cariResponse.Error
    $testCariId = $null
}

# ===========================================
# 3. CREATE SALES INVOICE
# ===========================================
Write-Title "3. Create Sales Invoice"

$invoiceData = @{
    invoice_type = "sales"
    invoice_category = "normal"
    invoice_date = (Get-Date).ToString("yyyy-MM-dd")
    due_date = (Get-Date).AddDays(30).ToString("yyyy-MM-dd")
    customer_name = "Test Müşteri A.Ş."
    customer_email = "test@musteri.com"
    customer_phone = "0555 123 4567"
    customer_tax_number = "1234567890"
    customer_tax_office = "Kadıköy"
    customer_address = "Test Mahallesi Test Sokak No:1"
    customer_city = "İstanbul"
    customer_district = "Kadıköy"
    discount_type = "percentage"
    discount_value = 5
    currency = "TRY"
    exchange_rate = 1.0
    notes = "Test faturası - PowerShell API test"
    is_draft = $false
    items = @(
        @{
            item_type = "product"
            item_code = "PROD001"
            item_name = "Test Ürün 1"
            description = "Test ürün açıklaması"
            quantity = 10
            unit = "Adet"
            unit_price = 100.00
            discount_type = "percentage"
            discount_value = 0
            tax_rate = 20
        },
        @{
            item_type = "product"
            item_code = "PROD002"
            item_name = "Test Ürün 2"
            description = "İkinci test ürünü"
            quantity = 5
            unit = "Adet"
            unit_price = 200.00
            discount_type = "fixed"
            discount_value = 50
            tax_rate = 20
        }
    )
}

if ($testCariId) {
    $invoiceData.cari_id = $testCariId
}

$createResponse = Invoke-ApiRequest -Method POST -Endpoint "/invoices" -Body $invoiceData -Headers $headers
if ($createResponse.Success -and $createResponse.Data.data.id) {
    $invoiceId = $createResponse.Data.data.id
    $invoiceNumber = $createResponse.Data.data.invoice_number
    Test-Result "Create sales invoice" $true "ID: $invoiceId, Number: $invoiceNumber"
} else {
    Test-Result "Create sales invoice" $false $createResponse.Error
    exit 1
}

# ===========================================
# 4. GET INVOICE DETAILS
# ===========================================
Write-Title "4. Get Invoice Details"

$detailsResponse = Invoke-ApiRequest -Method GET -Endpoint "/invoices/$invoiceId" -Headers $headers
if ($detailsResponse.Success -and $detailsResponse.Data.data) {
    $invoice = $detailsResponse.Data.data
    Test-Result "Get invoice details" $true "Total: $($invoice.total) TL, Items: $($invoice.items.Count)"
    Write-Info "Payment Status: $($invoice.payment_status)"
    Write-Info "Subtotal: $($invoice.subtotal) TL"
    Write-Info "Tax: $($invoice.tax_amount) TL"
} else {
    Test-Result "Get invoice details" $false $detailsResponse.Error
}

# ===========================================
# 5. LIST INVOICES WITH FILTERS
# ===========================================
Write-Title "5. List Invoices"

$listResponse = Invoke-ApiRequest -Method GET -Endpoint "/invoices?page=1&per_page=10" -Headers $headers
if ($listResponse.Success -and $listResponse.Data.data) {
    $count = $listResponse.Data.data.Count
    $total = $listResponse.Data.pagination.total
    Test-Result "List all invoices" $true "Showing $count of $total invoices"
} else {
    Test-Result "List all invoices" $false $listResponse.Error
}

# Filter by type
$filterResponse = Invoke-ApiRequest -Method GET -Endpoint "/invoices?invoice_type=sales" -Headers $headers
Test-Result "Filter by type (sales)" $filterResponse.Success

# Filter by status
$statusResponse = Invoke-ApiRequest -Method GET -Endpoint "/invoices?payment_status=unpaid" -Headers $headers
Test-Result "Filter by status (unpaid)" $statusResponse.Success

# Search
$searchResponse = Invoke-ApiRequest -Method GET -Endpoint "/invoices?search=Test" -Headers $headers
Test-Result "Search invoices" $searchResponse.Success

# ===========================================
# 6. UPDATE INVOICE
# ===========================================
Write-Title "6. Update Invoice"

$updateData = @{
    notes = "Updated notes - Test güncellendi"
    internal_notes = "Internal note for testing"
    discount_value = 10
}

$updateResponse = Invoke-ApiRequest -Method PUT -Endpoint "/invoices/$invoiceId" -Body $updateData -Headers $headers
if ($updateResponse.Success) {
    Test-Result "Update invoice" $true "Notes and discount updated"
} else {
    Test-Result "Update invoice" $false $updateResponse.Error
}

# ===========================================
# 7. APPROVE INVOICE
# ===========================================
Write-Title "7. Approve Invoice"

$approveResponse = Invoke-ApiRequest -Method POST -Endpoint "/invoices/$invoiceId/approve" -Headers $headers
if ($approveResponse.Success) {
    Test-Result "Approve invoice" $true "Invoice approved"
} else {
    Test-Result "Approve invoice" $false $approveResponse.Error
}

# ===========================================
# 8. LOCK/UNLOCK INVOICE
# ===========================================
Write-Title "8. Lock/Unlock Invoice"

# Lock
$lockData = @{ locked = $true }
$lockResponse = Invoke-ApiRequest -Method POST -Endpoint "/invoices/$invoiceId/lock" -Body $lockData -Headers $headers
Test-Result "Lock invoice" $lockResponse.Success

# Try to update locked invoice (should fail)
$updateLockedResponse = Invoke-ApiRequest -Method PUT -Endpoint "/invoices/$invoiceId" -Body @{ notes = "Should fail" } -Headers $headers
Test-Result "Update locked invoice (should fail)" (-not $updateLockedResponse.Success) "Correctly prevented update"

# Unlock
$unlockData = @{ locked = $false }
$unlockResponse = Invoke-ApiRequest -Method POST -Endpoint "/invoices/$invoiceId/lock" -Body $unlockData -Headers $headers
Test-Result "Unlock invoice" $unlockResponse.Success

# ===========================================
# 9. RECORD PAYMENTS
# ===========================================
Write-Title "9. Record Payments"

# First payment (partial)
$payment1Data = @{
    payment_date = (Get-Date).ToString("yyyy-MM-dd")
    amount = 500.00
    payment_method = "bank_transfer"
    transaction_reference = "TRX123456"
    notes = "İlk ödeme - 500 TL"
}

$payment1Response = Invoke-ApiRequest -Method POST -Endpoint "/invoices/$invoiceId/payments" -Body $payment1Data -Headers $headers
if ($payment1Response.Success) {
    Test-Result "Record first payment (partial)" $true "500 TL paid"
} else {
    Test-Result "Record first payment (partial)" $false $payment1Response.Error
}

# Get payments
$getPaymentsResponse = Invoke-ApiRequest -Method GET -Endpoint "/invoices/$invoiceId/payments" -Headers $headers
if ($getPaymentsResponse.Success -and $getPaymentsResponse.Data.data) {
    $paymentCount = $getPaymentsResponse.Data.data.Count
    Test-Result "Get payments" $true "$paymentCount payment(s) found"
} else {
    Test-Result "Get payments" $false $getPaymentsResponse.Error
}

# Second payment (remaining)
$detailsAfterPayment = Invoke-ApiRequest -Method GET -Endpoint "/invoices/$invoiceId" -Headers $headers
if ($detailsAfterPayment.Success) {
    $remaining = $detailsAfterPayment.Data.data.remaining_amount
    
    $payment2Data = @{
        payment_date = (Get-Date).AddDays(1).ToString("yyyy-MM-dd")
        amount = $remaining
        payment_method = "cash"
        notes = "Kalan ödeme - $remaining TL"
    }
    
    $payment2Response = Invoke-ApiRequest -Method POST -Endpoint "/invoices/$invoiceId/payments" -Body $payment2Data -Headers $headers
    Test-Result "Record second payment (full)" $payment2Response.Success "Paid remaining: $remaining TL"
}

# ===========================================
# 10. STATISTICS & REPORTS
# ===========================================
Write-Title "10. Statistics & Reports"

# General statistics
$statsResponse = Invoke-ApiRequest -Method GET -Endpoint "/invoices/statistics" -Headers $headers
Test-Result "Get statistics" $statsResponse.Success

# Monthly summary
$month = (Get-Date).Month
$year = (Get-Date).Year
$monthlyResponse = Invoke-ApiRequest -Method GET -Endpoint "/invoices/monthly-summary?year=$year&month=$month" -Headers $headers
Test-Result "Get monthly summary" $monthlyResponse.Success

# Overdue invoices
$overdueResponse = Invoke-ApiRequest -Method GET -Endpoint "/invoices/overdue" -Headers $headers
if ($overdueResponse.Success) {
    $overdueCount = if ($overdueResponse.Data.data) { $overdueResponse.Data.data.Count } else { 0 }
    Test-Result "Get overdue invoices" $true "Found $overdueCount overdue invoice(s)"
} else {
    Test-Result "Get overdue invoices" $false
}

# Due today
$dueTodayResponse = Invoke-ApiRequest -Method GET -Endpoint "/invoices/due-today" -Headers $headers
if ($dueTodayResponse.Success) {
    $dueTodayCount = if ($dueTodayResponse.Data.data) { $dueTodayResponse.Data.data.Count } else { 0 }
    Test-Result "Get due today invoices" $true "Found $dueTodayCount due today"
} else {
    Test-Result "Get due today invoices" $false
}

# ===========================================
# 11. CREATE DRAFT INVOICE
# ===========================================
Write-Title "11. Create Draft Invoice"

$draftData = @{
    invoice_type = "sales"
    invoice_date = (Get-Date).ToString("yyyy-MM-dd")
    due_date = (Get-Date).AddDays(30).ToString("yyyy-MM-dd")
    customer_name = "Draft Customer"
    is_draft = $true
    items = @(
        @{
            item_type = "product"
            item_name = "Draft Item"
            quantity = 1
            unit_price = 100
            tax_rate = 20
        }
    )
}

$draftResponse = Invoke-ApiRequest -Method POST -Endpoint "/invoices" -Body $draftData -Headers $headers
if ($draftResponse.Success -and $draftResponse.Data.data.id) {
    $draftId = $draftResponse.Data.data.id
    Test-Result "Create draft invoice" $true "Draft ID: $draftId"
    
    # Delete draft
    $deleteDraftResponse = Invoke-ApiRequest -Method DELETE -Endpoint "/invoices/$draftId" -Headers $headers
    Test-Result "Delete draft invoice" $deleteDraftResponse.Success
} else {
    Test-Result "Create draft invoice" $false
}

# ===========================================
# 12. PURCHASE INVOICE
# ===========================================
Write-Title "12. Create Purchase Invoice"

$purchaseData = @{
    invoice_type = "purchase"
    invoice_date = (Get-Date).ToString("yyyy-MM-dd")
    due_date = (Get-Date).AddDays(30).ToString("yyyy-MM-dd")
    customer_name = "Tedarikçi A.Ş."
    customer_tax_number = "9876543210"
    items = @(
        @{
            item_type = "product"
            item_name = "Satın Alınan Ürün"
            quantity = 20
            unit_price = 50
            tax_rate = 20
        }
    )
}

$purchaseResponse = Invoke-ApiRequest -Method POST -Endpoint "/invoices" -Body $purchaseData -Headers $headers
if ($purchaseResponse.Success -and $purchaseResponse.Data.data.id) {
    $purchaseId = $purchaseResponse.Data.data.id
    $purchaseNumber = $purchaseResponse.Data.data.invoice_number
    Test-Result "Create purchase invoice" $true "Number: $purchaseNumber"
} else {
    Test-Result "Create purchase invoice" $false
}

# ===========================================
# 13. CANCEL INVOICE
# ===========================================
Write-Title "13. Cancel Invoice"

if ($purchaseId) {
    $cancelData = @{
        reason = "Test amaçlı iptal"
    }
    
    $cancelResponse = Invoke-ApiRequest -Method POST -Endpoint "/invoices/$purchaseId/cancel" -Body $cancelData -Headers $headers
    Test-Result "Cancel purchase invoice" $cancelResponse.Success
    
    # Try to record payment on cancelled invoice (should fail)
    $paymentData = @{
        payment_date = (Get-Date).ToString("yyyy-MM-dd")
        amount = 100
        payment_method = "cash"
    }
    $paymentCancelledResponse = Invoke-ApiRequest -Method POST -Endpoint "/invoices/$purchaseId/payments" -Body $paymentData -Headers $headers
    Test-Result "Payment on cancelled invoice (should fail)" (-not $paymentCancelledResponse.Success) "Correctly prevented"
}

# ===========================================
# 14. RECURRING INVOICE
# ===========================================
Write-Title "14. Create Recurring Invoice"

$recurringData = @{
    invoice_type = "sales"
    invoice_date = (Get-Date).ToString("yyyy-MM-dd")
    due_date = (Get-Date).AddDays(30).ToString("yyyy-MM-dd")
    customer_name = "Recurring Customer"
    is_recurring = $true
    recurring_frequency = "monthly"
    recurring_interval = 1
    recurring_start_date = (Get-Date).ToString("yyyy-MM-dd")
    recurring_end_date = (Get-Date).AddMonths(12).ToString("yyyy-MM-dd")
    items = @(
        @{
            item_type = "service"
            item_name = "Monthly Subscription"
            quantity = 1
            unit_price = 1000
            tax_rate = 20
        }
    )
}

$recurringResponse = Invoke-ApiRequest -Method POST -Endpoint "/invoices" -Body $recurringData -Headers $headers
if ($recurringResponse.Success -and $recurringResponse.Data.data.id) {
    $recurringId = $recurringResponse.Data.data.id
    Test-Result "Create recurring invoice" $true "ID: $recurringId"
} else {
    Test-Result "Create recurring invoice" $false
}

# Get recurring invoices
$getRecurringResponse = Invoke-ApiRequest -Method GET -Endpoint "/invoices/recurring" -Headers $headers
if ($getRecurringResponse.Success) {
    $recurringCount = if ($getRecurringResponse.Data.data) { $getRecurringResponse.Data.data.Count } else { 0 }
    Test-Result "Get recurring invoices" $true "Found $recurringCount recurring invoice(s)"
} else {
    Test-Result "Get recurring invoices" $false
}

# ===========================================
# 15. QUOTATION TO INVOICE CONVERSION
# ===========================================
Write-Title "15. Quotation to Invoice Conversion"

# First, create a quotation
$quotationData = @{
    quotation_date = (Get-Date).ToString("yyyy-MM-dd")
    valid_until = (Get-Date).AddDays(30).ToString("yyyy-MM-dd")
    customer_name = "Conversion Test Customer"
    customer_email = "conversion@test.com"
    items = @(
        @{
            item_type = "product"
            item_name = "Quotation Item"
            quantity = 5
            unit_price = 200
            tax_rate = 20
        }
    )
}

$quotationResponse = Invoke-ApiRequest -Method POST -Endpoint "/quotations" -Body $quotationData -Headers $headers
if ($quotationResponse.Success -and $quotationResponse.Data.data.id) {
    $quotationId = $quotationResponse.Data.data.id
    Test-Result "Create quotation for conversion" $true "Quotation ID: $quotationId"
    
    # Convert to invoice
    $convertData = @{
        invoice_date = (Get-Date).ToString("yyyy-MM-dd")
        due_date = (Get-Date).AddDays(30).ToString("yyyy-MM-dd")
    }
    
    $convertResponse = Invoke-ApiRequest -Method POST -Endpoint "/invoices/convert-from-quotation/$quotationId" -Body $convertData -Headers $headers
    if ($convertResponse.Success -and $convertResponse.Data.data.id) {
        $convertedInvoiceId = $convertResponse.Data.data.id
        Test-Result "Convert quotation to invoice" $true "New invoice ID: $convertedInvoiceId"
    } else {
        Test-Result "Convert quotation to invoice" $false $convertResponse.Error
    }
} else {
    Test-Result "Create quotation for conversion" $false
}

# ===========================================
# 16. VALIDATION TESTS
# ===========================================
Write-Title "16. Validation Tests"

# Missing required fields
$invalidData = @{
    customer_name = "Test"
}
$invalidResponse = Invoke-ApiRequest -Method POST -Endpoint "/invoices" -Body $invalidData -Headers $headers
Test-Result "Validation: Missing required fields" (-not $invalidResponse.Success) "Correctly rejected"

# Invalid invoice type
$invalidTypeData = @{
    invoice_type = "invalid_type"
    invoice_date = (Get-Date).ToString("yyyy-MM-dd")
    due_date = (Get-Date).AddDays(30).ToString("yyyy-MM-dd")
    customer_name = "Test"
}
$invalidTypeResponse = Invoke-ApiRequest -Method POST -Endpoint "/invoices" -Body $invalidTypeData -Headers $headers
Test-Result "Validation: Invalid invoice type" (-not $invalidTypeResponse.Success) "Correctly rejected"

# Overpayment attempt
if ($invoiceId) {
    $overpaymentData = @{
        payment_date = (Get-Date).ToString("yyyy-MM-dd")
        amount = 999999.99
        payment_method = "cash"
    }
    $overpaymentResponse = Invoke-ApiRequest -Method POST -Endpoint "/invoices/$invoiceId/payments" -Body $overpaymentData -Headers $headers
    Test-Result "Validation: Overpayment prevention" (-not $overpaymentResponse.Success) "Correctly prevented"
}

# ===========================================
# SUMMARY
# ===========================================
Write-Title "TEST SUMMARY"

Write-Host "`nTotal Tests: $script:totalTests" -ForegroundColor White
Write-Success "Passed: $script:passedTests"
Write-Error "Failed: $script:failedTests"

$successRate = [math]::Round(($script:passedTests / $script:totalTests) * 100, 2)
Write-Host "Success Rate: $successRate%" -ForegroundColor $(if ($successRate -ge 90) { "Green" } elseif ($successRate -ge 70) { "Yellow" } else { "Red" })

if ($script:failedTests -eq 0) {
    Write-Host "`n🎉 All tests passed successfully!" -ForegroundColor Green
} else {
    Write-Host "`n⚠ Some tests failed. Please review the results above." -ForegroundColor Yellow
}

exit $(if ($script:failedTests -eq 0) { 0 } else { 1 })

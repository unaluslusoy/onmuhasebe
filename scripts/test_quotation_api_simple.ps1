# ============================================
# Quotation API Test Script (Simplified)
# Tests all quotation management endpoints
# ============================================

$baseUrl = "http://localhost/onmuhasebe/public/api"
$token = ""
$companyId = 0
$cariId = 0
$quotationId = 0
$testNumber = 1

# ============================================
# Helper Functions
# ============================================

function Invoke-ApiRequest {
    param(
        [string]$Method,
        [string]$Endpoint,
        [object]$Body = $null,
        [bool]$UseAuth = $true
    )
    
    $headers = @{
        "Content-Type" = "application/json"
    }
    
    if ($UseAuth -and $script:token) {
        $headers["Authorization"] = "Bearer $script:token"
    }
    
    $params = @{
        Uri = "$baseUrl$Endpoint"
        Method = $Method
        Headers = $headers
    }
    
    if ($Body) {
        $params["Body"] = ($Body | ConvertTo-Json -Depth 10)
    }
    
    try {
        $response = Invoke-RestMethod @params
        return $response
    }
    catch {
        Write-Host "Request failed: $($_.Exception.Message)" -ForegroundColor Red
        if ($_.Exception.Response) {
            $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
            $responseBody = $reader.ReadToEnd()
            Write-Host $responseBody -ForegroundColor Red
        }
        return $null
    }
}

# ============================================
# Test Setup
# ============================================

Write-Host "`n======================================" -ForegroundColor Magenta
Write-Host "QUOTATION API TEST SUITE" -ForegroundColor Magenta
Write-Host "======================================`n" -ForegroundColor Magenta

# Register new test user
Write-Host "[$testNumber] Register Test User" -ForegroundColor Yellow
$testNumber++

$timestamp = [DateTimeOffset]::Now.ToUnixTimeSeconds()
$testEmail = "quotation_test_$timestamp@example.com"
$testPassword = "Test123!@#"

$registerData = @{
    full_name = "Quotation Test User"
    email = $testEmail
    password = $testPassword
    password_confirmation = $testPassword
    phone = "0555 000 0001"
    company_name = "Quotation Test Company"
}

$registerResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/auth/register" -Body $registerData -UseAuth $false

if ($registerResponse -and $registerResponse.success) {
    $script:token = $registerResponse.data.token
    $script:companyId = $registerResponse.data.user.company_id
    Write-Host "OK: Test user registered successfully" -ForegroundColor Green
    Write-Host "     Email: $testEmail" -ForegroundColor Cyan
    Write-Host "     Company ID: $($script:companyId)" -ForegroundColor Cyan
    Write-Host "     Token: $($script:token.Substring(0, 20))..." -ForegroundColor Cyan
}
else {
    Write-Host "FAIL: Registration failed" -ForegroundColor Red
    exit
}

# Get first cari for testing
Write-Host "`n[$testNumber] Get Cari for Testing" -ForegroundColor Yellow
$testNumber++

$cariResponse = Invoke-ApiRequest -Method "GET" -Endpoint "/cari?per_page=1"

if ($cariResponse -and $cariResponse.success -and $cariResponse.data.data.Count -gt 0) {
    $script:cariId = $cariResponse.data.data[0].id
    Write-Host "OK: Got cari ID: $($script:cariId)" -ForegroundColor Green
}
else {
    Write-Host "INFO: No cari found, will use customer name instead" -ForegroundColor Cyan
}

# ============================================
# Quotation CRUD Tests
# ============================================

Write-Host "`n======================================" -ForegroundColor Magenta
Write-Host "QUOTATION CRUD OPERATIONS" -ForegroundColor Magenta
Write-Host "======================================`n" -ForegroundColor Magenta

# Create quotation
Write-Host "[$testNumber] Create Quotation" -ForegroundColor Yellow
$testNumber++

$quotationData = @{
    quotation_date = (Get-Date).ToString("yyyy-MM-dd")
    valid_until = (Get-Date).AddDays(30).ToString("yyyy-MM-dd")
    customer_name = "Test Musteri A.S."
    customer_email = "test@example.com"
    customer_phone = "0555 123 4567"
    customer_tax_number = "1234567890"
    customer_tax_office = "Istanbul Vergi Dairesi"
    customer_address = "Test Mahallesi Test Sokak No:1"
    customer_city = "Istanbul"
    customer_district = "Kadikoy"
    discount_type = "percentage"
    discount_value = 10
    currency = "TRY"
    exchange_rate = 1.00
    notes = "Test teklifi"
    terms_conditions = "30 gun icinde odeme yapilmalidir"
    items = @(
        @{
            item_type = "product"
            item_code = "PRD001"
            item_name = "Test Urun 1"
            description = "Test urun aciklamasi"
            quantity = 5
            unit = "Adet"
            unit_price = 100.00
            discount_type = "percentage"
            discount_value = 5
            tax_rate = 20
        },
        @{
            item_type = "service"
            item_code = "SRV001"
            item_name = "Test Hizmet 1"
            description = "Test hizmet aciklamasi"
            quantity = 2
            unit = "Saat"
            unit_price = 250.00
            discount_type = "fixed"
            discount_value = 50
            tax_rate = 20
        },
        @{
            item_type = "product"
            item_code = "PRD002"
            item_name = "Test Urun 2"
            quantity = 10
            unit = "Adet"
            unit_price = 75.00
            tax_rate = 20
        }
    )
}

if ($script:cariId -gt 0) {
    $quotationData.cari_id = $script:cariId
}

$createResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/quotations" -Body $quotationData

if ($createResponse -and $createResponse.success) {
    $script:quotationId = $createResponse.data.id
    Write-Host "OK: Quotation created" -ForegroundColor Green
    Write-Host "     ID: $($script:quotationId)" -ForegroundColor Cyan
    Write-Host "     Number: $($createResponse.data.quotation_number)" -ForegroundColor Cyan
    Write-Host "     Total: $($createResponse.data.total) TRY" -ForegroundColor Cyan
    Write-Host "     Items: $($createResponse.data.items.Count)" -ForegroundColor Cyan
}
else {
    Write-Host "FAIL: Failed to create quotation" -ForegroundColor Red
    exit
}

# Get all quotations
Write-Host "`n[$testNumber] Get All Quotations" -ForegroundColor Yellow
$testNumber++

$listResponse = Invoke-ApiRequest -Method "GET" -Endpoint "/quotations?page=1&per_page=10"

if ($listResponse -and $listResponse.success) {
    Write-Host "OK: Retrieved $($listResponse.data.data.Count) quotations" -ForegroundColor Green
    Write-Host "     Total: $($listResponse.data.pagination.total)" -ForegroundColor Cyan
}
else {
    Write-Host "FAIL: Failed to get quotations" -ForegroundColor Red
}

# Get single quotation
Write-Host "`n[$testNumber] Get Single Quotation" -ForegroundColor Yellow
$testNumber++

$showResponse = Invoke-ApiRequest -Method "GET" -Endpoint "/quotations/$($script:quotationId)"

if ($showResponse -and $showResponse.success) {
    Write-Host "OK: Retrieved quotation: $($showResponse.data.quotation_number)" -ForegroundColor Green
    Write-Host "     Status: $($showResponse.data.status)" -ForegroundColor Cyan
    Write-Host "     Items: $($showResponse.data.items.Count)" -ForegroundColor Cyan
}
else {
    Write-Host "FAIL: Failed to get quotation" -ForegroundColor Red
}

# Update quotation
Write-Host "`n[$testNumber] Update Quotation" -ForegroundColor Yellow
$testNumber++

$updateData = @{
    notes = "Updated test notes"
    internal_notes = "Internal note for team"
}

$updateResponse = Invoke-ApiRequest -Method "PUT" -Endpoint "/quotations/$($script:quotationId)" -Body $updateData

if ($updateResponse -and $updateResponse.success) {
    Write-Host "OK: Quotation updated successfully" -ForegroundColor Green
}
else {
    Write-Host "FAIL: Failed to update quotation" -ForegroundColor Red
}

# ============================================
# Workflow Tests
# ============================================

Write-Host "`n======================================" -ForegroundColor Magenta
Write-Host "QUOTATION WORKFLOW" -ForegroundColor Magenta
Write-Host "======================================`n" -ForegroundColor Magenta

# Send quotation
Write-Host "[$testNumber] Send Quotation" -ForegroundColor Yellow
$testNumber++

$sendResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/quotations/$($script:quotationId)/send"

if ($sendResponse -and $sendResponse.success) {
    Write-Host "OK: Quotation sent successfully" -ForegroundColor Green
    Write-Host "     Status: $($sendResponse.data.status)" -ForegroundColor Cyan
    Write-Host "     Sent at: $($sendResponse.data.sent_at)" -ForegroundColor Cyan
}
else {
    Write-Host "FAIL: Failed to send quotation" -ForegroundColor Red
}

# Try to update after sending (should fail)
Write-Host "`n[$testNumber] Try Update After Send (Should Fail)" -ForegroundColor Yellow
$testNumber++

$failUpdateResponse = Invoke-ApiRequest -Method "PUT" -Endpoint "/quotations/$($script:quotationId)" -Body @{ notes = "Try update" }

if ($failUpdateResponse -and -not $failUpdateResponse.success) {
    Write-Host "OK: Update correctly blocked for sent quotation" -ForegroundColor Green
}
else {
    Write-Host "FAIL: Update should have been blocked" -ForegroundColor Red
}

# Accept quotation
Write-Host "`n[$testNumber] Accept Quotation" -ForegroundColor Yellow
$testNumber++

$acceptResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/quotations/$($script:quotationId)/accept"

if ($acceptResponse -and $acceptResponse.success) {
    Write-Host "OK: Quotation accepted successfully" -ForegroundColor Green
    Write-Host "     Status: $($acceptResponse.data.status)" -ForegroundColor Cyan
    Write-Host "     Accepted at: $($acceptResponse.data.accepted_at)" -ForegroundColor Cyan
}
else {
    Write-Host "FAIL: Failed to accept quotation" -ForegroundColor Red
}

# ============================================
# Duplicate & Reject Tests
# ============================================

Write-Host "`n======================================" -ForegroundColor Magenta
Write-Host "DUPLICATE & REJECT" -ForegroundColor Magenta
Write-Host "======================================`n" -ForegroundColor Magenta

# Duplicate quotation
Write-Host "[$testNumber] Duplicate Quotation" -ForegroundColor Yellow
$testNumber++

$duplicateResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/quotations/$($script:quotationId)/duplicate"

if ($duplicateResponse -and $duplicateResponse.success) {
    $duplicateId = $duplicateResponse.data.id
    Write-Host "OK: Quotation duplicated" -ForegroundColor Green
    Write-Host "     ID: $duplicateId" -ForegroundColor Cyan
    Write-Host "     Number: $($duplicateResponse.data.quotation_number)" -ForegroundColor Cyan
    Write-Host "     Status: $($duplicateResponse.data.status) (should be draft)" -ForegroundColor Cyan
    
    # Send duplicated quotation
    Write-Host "`n[$testNumber] Send Duplicated Quotation" -ForegroundColor Yellow
    $testNumber++
    
    $sendDupResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/quotations/$duplicateId/send"
    
    if ($sendDupResponse -and $sendDupResponse.success) {
        Write-Host "OK: Duplicated quotation sent" -ForegroundColor Green
        
        # Reject duplicated quotation
        Write-Host "`n[$testNumber] Reject Duplicated Quotation" -ForegroundColor Yellow
        $testNumber++
        
        $rejectData = @{
            reason = "Fiyat yuksek bulundu"
        }
        
        $rejectResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/quotations/$duplicateId/reject" -Body $rejectData
        
        if ($rejectResponse -and $rejectResponse.success) {
            Write-Host "OK: Quotation rejected successfully" -ForegroundColor Green
            Write-Host "     Status: $($rejectResponse.data.status)" -ForegroundColor Cyan
            Write-Host "     Reason: $($rejectResponse.data.rejection_reason)" -ForegroundColor Cyan
        }
        else {
            Write-Host "FAIL: Failed to reject quotation" -ForegroundColor Red
        }
    }
}
else {
    Write-Host "FAIL: Failed to duplicate quotation" -ForegroundColor Red
}

# ============================================
# Statistics Tests
# ============================================

Write-Host "`n======================================" -ForegroundColor Magenta
Write-Host "STATISTICS" -ForegroundColor Magenta
Write-Host "======================================`n" -ForegroundColor Magenta

# Get statistics
Write-Host "[$testNumber] Get Statistics" -ForegroundColor Yellow
$testNumber++

$statsResponse = Invoke-ApiRequest -Method "GET" -Endpoint "/quotations/statistics"

if ($statsResponse -and $statsResponse.success) {
    Write-Host "OK: Statistics retrieved" -ForegroundColor Green
    
    Write-Host "`nBy Status:" -ForegroundColor White
    foreach ($stat in $statsResponse.data.by_status) {
        Write-Host "     $($stat.status): $($stat.count) quotations, Total: $($stat.total_amount) TRY" -ForegroundColor Cyan
    }
    
    Write-Host "`nExpiring Soon:" -ForegroundColor White
    $expiringCount = $statsResponse.data.expiring_soon.Count
    Write-Host "     $expiringCount quotations expiring in next 7 days" -ForegroundColor Cyan
}
else {
    Write-Host "FAIL: Failed to get statistics" -ForegroundColor Red
}

# ============================================
# Summary
# ============================================

Write-Host "`n======================================" -ForegroundColor Magenta
Write-Host "TEST SUMMARY" -ForegroundColor Magenta
Write-Host "======================================`n" -ForegroundColor Magenta

Write-Host "Total Tests Run: $($testNumber - 1)" -ForegroundColor Cyan
Write-Host "Quotation Management API tests completed!" -ForegroundColor Green
Write-Host ""

# ============================================
# Quotation API Test Script
# Tests all quotation management endpoints
# ============================================

$baseUrl = "http://localhost/onmuhasebe/public/api"
$token = ""
$companyId = 0
$cariId = 0
$quotationId = 0
$testNumber = 1

# Colors for output
function Write-Success { param($msg) Write-Host "✓ $msg" -ForegroundColor Green }
function Write-Error { param($msg) Write-Host "✗ $msg" -ForegroundColor Red }
function Write-Info { param($msg) Write-Host "ℹ $msg" -ForegroundColor Cyan }
function Write-Test { param($msg) Write-Host "`n[$script:testNumber] $msg" -ForegroundColor Yellow; $script:testNumber++ }

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
    
    if ($UseAuth -and $token) {
        $headers["Authorization"] = "Bearer $token"
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
        Write-Error "Request failed: $_"
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

Write-Host "`n========================================" -ForegroundColor Magenta
Write-Host "QUOTATION API TEST SUITE" -ForegroundColor Magenta
Write-Host "========================================`n" -ForegroundColor Magenta

# Register new test user
Write-Test "Register Test User"
$timestamp = [int][double]::Parse((Get-Date -UFormat %s))
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
    Write-Success "Test user registered successfully"
    $token = $registerResponse.data.token
    $companyId = $registerResponse.data.user.company_id
    Write-Info "Email: $testEmail"
    Write-Info "Company ID: $companyId"
}
else {
    # Try to login with existing test user
    Write-Info "Registration failed, trying to login with testuser@example.com"
    
    $loginData = @{
        email = "testuser@example.com"
        password = "password123"
    }
    
    $loginResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/auth/login" -Body $loginData -UseAuth $false
    
    if ($loginResponse -and $loginResponse.success) {
        $token = $loginResponse.data.token
        $companyId = $loginResponse.data.user.company_id
        Write-Success "Logged in with existing user"
        Write-Info "Company ID: $companyId"
    }
    else {
        Write-Error "Both registration and login failed"
        exit
    }
}

# Get first cari for testing
Write-Test "Get Cari for Testing"
$cariResponse = Invoke-ApiRequest -Method "GET" -Endpoint "/cari?per_page=1"

if ($cariResponse -and $cariResponse.success -and $cariResponse.data.data.Count -gt 0) {
    $cariId = $cariResponse.data.data[0].id
    Write-Success "Got cari ID: $cariId"
}
else {
    Write-Info "No cari found, will use customer name instead"
}

# ============================================
# Quotation CRUD Tests
# ============================================

Write-Host "`n========================================" -ForegroundColor Magenta
Write-Host "QUOTATION CRUD OPERATIONS" -ForegroundColor Magenta
Write-Host "========================================`n" -ForegroundColor Magenta

# Create quotation
Write-Test "Create Quotation"
$quotationData = @{
    quotation_date = (Get-Date).ToString("yyyy-MM-dd")
    valid_until = (Get-Date).AddDays(30).ToString("yyyy-MM-dd")
    customer_name = "Test Müşteri A.Ş."
    customer_email = "test@example.com"
    customer_phone = "0555 123 4567"
    customer_tax_number = "1234567890"
    customer_tax_office = "İstanbul Vergi Dairesi"
    customer_address = "Test Mahallesi Test Sokak No:1"
    customer_city = "İstanbul"
    customer_district = "Kadıköy"
    discount_type = "percentage"
    discount_value = 10
    currency = "TRY"
    exchange_rate = 1.00
    notes = "Test teklifi"
    terms_conditions = "30 gün içinde ödeme yapılmalıdır"
    items = @(
        @{
            item_type = "product"
            item_code = "PRD001"
            item_name = "Test Ürün 1"
            description = "Test ürün açıklaması"
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
            description = "Test hizmet açıklaması"
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
            item_name = "Test Ürün 2"
            quantity = 10
            unit = "Adet"
            unit_price = 75.00
            tax_rate = 20
        }
    )
}

if ($cariId -gt 0) {
    $quotationData.cari_id = $cariId
}

$createResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/quotations" -Body $quotationData

if ($createResponse -and $createResponse.success) {
    $quotationId = $createResponse.data.id
    Write-Success "Quotation created: ID=$quotationId, Number=$($createResponse.data.quotation_number)"
    Write-Info "Total: $($createResponse.data.total) TRY"
    Write-Info "Items: $($createResponse.data.items.Count)"
}
else {
    Write-Error "Failed to create quotation"
}

# Get all quotations
Write-Test "Get All Quotations"
$listResponse = Invoke-ApiRequest -Method "GET" -Endpoint "/quotations?page=1&per_page=10"

if ($listResponse -and $listResponse.success) {
    Write-Success "Retrieved $($listResponse.data.data.Count) quotations"
    Write-Info "Total: $($listResponse.data.pagination.total)"
}
else {
    Write-Error "Failed to get quotations"
}

# Get single quotation
Write-Test "Get Single Quotation"
$showResponse = Invoke-ApiRequest -Method "GET" -Endpoint "/quotations/$quotationId"

if ($showResponse -and $showResponse.success) {
    Write-Success "Retrieved quotation: $($showResponse.data.quotation_number)"
    Write-Info "Status: $($showResponse.data.status)"
    Write-Info "Items: $($showResponse.data.items.Count)"
}
else {
    Write-Error "Failed to get quotation"
}

# Update quotation
Write-Test "Update Quotation"
$updateData = @{
    notes = "Updated test notes"
    internal_notes = "Internal note for team"
}

$updateResponse = Invoke-ApiRequest -Method "PUT" -Endpoint "/quotations/$quotationId" -Body $updateData

if ($updateResponse -and $updateResponse.success) {
    Write-Success "Quotation updated successfully"
}
else {
    Write-Error "Failed to update quotation"
}

# ============================================
# Workflow Tests
# ============================================

Write-Host "`n========================================" -ForegroundColor Magenta
Write-Host "QUOTATION WORKFLOW" -ForegroundColor Magenta
Write-Host "========================================`n" -ForegroundColor Magenta

# Send quotation
Write-Test "Send Quotation"
$sendResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/quotations/$quotationId/send"

if ($sendResponse -and $sendResponse.success) {
    Write-Success "Quotation sent successfully"
    Write-Info "Status: $($sendResponse.data.status)"
    Write-Info "Sent at: $($sendResponse.data.sent_at)"
}
else {
    Write-Error "Failed to send quotation"
}

# Try to update after sending (should fail)
Write-Test "Try Update After Send (Should Fail)"
$failUpdateResponse = Invoke-ApiRequest -Method "PUT" -Endpoint "/quotations/$quotationId" -Body @{ notes = "Try update" }

if ($failUpdateResponse -and -not $failUpdateResponse.success) {
    Write-Success "Update correctly blocked for sent quotation"
}
else {
    Write-Error "Update should have been blocked"
}

# Accept quotation
Write-Test "Accept Quotation"
$acceptResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/quotations/$quotationId/accept"

if ($acceptResponse -and $acceptResponse.success) {
    Write-Success "Quotation accepted successfully"
    Write-Info "Status: $($acceptResponse.data.status)"
    Write-Info "Accepted at: $($acceptResponse.data.accepted_at)"
}
else {
    Write-Error "Failed to accept quotation"
}

# ============================================
# Duplicate & Reject Tests
# ============================================

Write-Host "`n========================================" -ForegroundColor Magenta
Write-Host "DUPLICATE & REJECT" -ForegroundColor Magenta
Write-Host "========================================`n" -ForegroundColor Magenta

# Duplicate quotation
Write-Test "Duplicate Quotation"
$duplicateResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/quotations/$quotationId/duplicate"

if ($duplicateResponse -and $duplicateResponse.success) {
    $duplicateId = $duplicateResponse.data.id
    Write-Success "Quotation duplicated: ID=$duplicateId, Number=$($duplicateResponse.data.quotation_number)"
    Write-Info "Status: $($duplicateResponse.data.status) (should be draft)"
    Write-Info "Items: $($duplicateResponse.data.items.Count)"
    
    # Send duplicated quotation
    Write-Test "Send Duplicated Quotation"
    $sendDupResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/quotations/$duplicateId/send"
    
    if ($sendDupResponse -and $sendDupResponse.success) {
        Write-Success "Duplicated quotation sent"
        
        # Reject duplicated quotation
        Write-Test "Reject Duplicated Quotation"
        $rejectData = @{
            reason = "Fiyat yüksek bulundu"
        }
        
        $rejectResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/quotations/$duplicateId/reject" -Body $rejectData
        
        if ($rejectResponse -and $rejectResponse.success) {
            Write-Success "Quotation rejected successfully"
            Write-Info "Status: $($rejectResponse.data.status)"
            Write-Info "Reason: $($rejectResponse.data.rejection_reason)"
        }
        else {
            Write-Error "Failed to reject quotation"
        }
    }
}
else {
    Write-Error "Failed to duplicate quotation"
}

# ============================================
# Filter & Search Tests
# ============================================

Write-Host "`n========================================" -ForegroundColor Magenta
Write-Host "FILTERS & SEARCH" -ForegroundColor Magenta
Write-Host "========================================`n" -ForegroundColor Magenta

# Filter by status
Write-Test "Filter by Status (accepted)"
$filterResponse = Invoke-ApiRequest -Method "GET" -Endpoint "/quotations?status=accepted"

if ($filterResponse -and $filterResponse.success) {
    Write-Success "Filtered quotations: $($filterResponse.data.data.Count) accepted"
}
else {
    Write-Error "Failed to filter quotations"
}

# Get by status endpoint
Write-Test "Get By Status Endpoint (draft)"
$byStatusResponse = Invoke-ApiRequest -Method "GET" -Endpoint "/quotations/by-status/draft"

if ($byStatusResponse -and $byStatusResponse.success) {
    Write-Success "Draft quotations: $($byStatusResponse.data.data.Count)"
}
else {
    Write-Error "Failed to get quotations by status"
}

# Search quotations
Write-Test "Search Quotations"
$searchResponse = Invoke-ApiRequest -Method "GET" -Endpoint "/quotations?search=Test"

if ($searchResponse -and $searchResponse.success) {
    Write-Success "Search results: $($searchResponse.data.data.Count)"
}
else {
    Write-Error "Failed to search quotations"
}

# Filter by date range
Write-Test "Filter by Date Range"
$dateFrom = (Get-Date).AddDays(-7).ToString("yyyy-MM-dd")
$dateTo = (Get-Date).ToString("yyyy-MM-dd")
$dateFilterResponse = Invoke-ApiRequest -Method "GET" -Endpoint "/quotations?date_from=$dateFrom&date_to=$dateTo"

if ($dateFilterResponse -and $dateFilterResponse.success) {
    Write-Success "Date filtered quotations: $($dateFilterResponse.data.data.Count)"
}
else {
    Write-Error "Failed to filter by date"
}

# ============================================
# Statistics Tests
# ============================================

Write-Host "`n========================================" -ForegroundColor Magenta
Write-Host "STATISTICS" -ForegroundColor Magenta
Write-Host "========================================`n" -ForegroundColor Magenta

# Get statistics
Write-Test "Get Statistics"
$statsResponse = Invoke-ApiRequest -Method "GET" -Endpoint "/quotations/statistics"

if ($statsResponse -and $statsResponse.success) {
    Write-Success "Statistics retrieved"
    
    Write-Host "`nBy Status:" -ForegroundColor White
    foreach ($stat in $statsResponse.data.by_status) {
        Write-Info "  $($stat.status): $($stat.count) quotations, Total: $($stat.total_amount) TRY"
    }
    
    Write-Host "`nExpiring Soon:" -ForegroundColor White
    $expiringCount = $statsResponse.data.expiring_soon.Count
    Write-Info "  $expiringCount quotations expiring in next 7 days"
}
else {
    Write-Error "Failed to get statistics"
}

# ============================================
# Validation Tests
# ============================================

Write-Host "`n========================================" -ForegroundColor Magenta
Write-Host "VALIDATION TESTS" -ForegroundColor Magenta
Write-Host "========================================`n" -ForegroundColor Magenta

# Create without customer
Write-Test "Create Without Customer (Should Fail)"
$invalidData = @{
    items = @(
        @{
            item_name = "Test"
            quantity = 1
            unit_price = 100
        }
    )
}

$invalidResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/quotations" -Body $invalidData

if ($invalidResponse -and -not $invalidResponse.success) {
    Write-Success "Validation correctly failed for missing customer"
}
else {
    Write-Error "Should have failed validation"
}

# Create without items
Write-Test "Create Without Items (Should Fail)"
$noItemsData = @{
    customer_name = "Test"
    items = @()
}

$noItemsResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/quotations" -Body $noItemsData

if ($noItemsResponse -and -not $noItemsResponse.success) {
    Write-Success "Validation correctly failed for missing items"
}
else {
    Write-Error "Should have failed validation"
}

# ============================================
# Cleanup Tests
# ============================================

Write-Host "`n========================================" -ForegroundColor Magenta
Write-Host "CLEANUP" -ForegroundColor Magenta
Write-Host "========================================`n" -ForegroundColor Magenta

# Delete quotation (should fail for accepted)
Write-Test "Try Delete Accepted Quotation (Should Fail)"
$deleteAcceptedResponse = Invoke-ApiRequest -Method "DELETE" -Endpoint "/quotations/$quotationId"

if ($deleteAcceptedResponse -and -not $deleteAcceptedResponse.success) {
    Write-Success "Delete correctly blocked for accepted quotation"
}
else {
    Write-Error "Should not be able to delete accepted quotation"
}

# Delete draft quotation (should succeed)
if ($duplicateId -gt 0) {
    Write-Test "Delete Draft Quotation"
    # Get all drafts and delete the rejected one
    $draftsResponse = Invoke-ApiRequest -Method "GET" -Endpoint "/quotations/by-status/rejected"
    
    if ($draftsResponse -and $draftsResponse.success -and $draftsResponse.data.data.Count -gt 0) {
        $draftToDelete = $draftsResponse.data.data[0].id
        $deleteResponse = Invoke-ApiRequest -Method "DELETE" -Endpoint "/quotations/$draftToDelete"
        
        if ($deleteResponse -and $deleteResponse.success) {
            Write-Success "Draft quotation deleted successfully"
        }
        else {
            Write-Error "Failed to delete draft quotation"
        }
    }
}

# ============================================
# Summary
# ============================================

Write-Host "`n========================================" -ForegroundColor Magenta
Write-Host "TEST SUMMARY" -ForegroundColor Magenta
Write-Host "========================================`n" -ForegroundColor Magenta

Write-Info "Total Tests Run: $($testNumber - 1)"
Write-Success "Quotation Management API tests completed!"
Write-Host ""

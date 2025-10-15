# Simple API Test Script for On Muhasebe
# Tests: Auth, Company, Cari, Product modules

$baseUrl = "http://localhost/onmuhasebe/public/api"

# Global variables
$global:token = $null
$global:companyId = $null
$global:cariId = $null
$global:categoryId = $null
$global:productId = $null

function Invoke-Api {
    param(
        [string]$Method = "GET",
        [string]$Path,
        [hashtable]$Body = $null,
        [bool]$Auth = $false
    )
    
    $headers = @{ "Content-Type" = "application/json" }
    if ($Auth -and $global:token) {
        $headers["Authorization"] = "Bearer $global:token"
    }
    
    $params = @{
        Uri = "$baseUrl$Path"
        Method = $Method
        Headers = $headers
    }
    
    if ($Body) {
        $params.Body = ($Body | ConvertTo-Json -Depth 10)
    }
    
    try {
        $response = Invoke-RestMethod @params
        return $response
    } catch {
        Write-Host "ERROR: $($_.Exception.Message)" -ForegroundColor Red
        return $null
    }
}

Write-Host "`n=== ON MUHASEBE API TEST ===" -ForegroundColor Cyan
Write-Host ""

# 1. AUTH TEST
Write-Host "1. Testing Authentication..." -ForegroundColor Yellow
$random = Get-Random -Minimum 1000 -Maximum 9999
$testEmail = "test$random@example.com"
$testPassword = "Test123456!"

$registerData = @{
    full_name = "Test User $random"
    email = $testEmail
    password = $testPassword
    password_confirmation = $testPassword
}

$result = Invoke-Api -Method POST -Path "/auth/register" -Body $registerData
if ($result -and $result.success) {
    Write-Host "  [OK] User registered" -ForegroundColor Green
} else {
    Write-Host "  [FAIL] Registration failed" -ForegroundColor Red
    exit 1
}

# Login to get token
$loginData = @{
    email = $testEmail
    password = $testPassword
}

$result = Invoke-Api -Method POST -Path "/auth/login" -Body $loginData
if ($result -and $result.success) {
    Write-Host "  [OK] User logged in" -ForegroundColor Green
    $global:token = $result.data.tokens.access_token
} else {
    Write-Host "  [FAIL] Login failed" -ForegroundColor Red
    exit 1
}

$result = Invoke-Api -Method GET -Path "/auth/me" -Auth $true
if ($result -and $result.success) {
    Write-Host "  [OK] Token verified: $($result.data.email)" -ForegroundColor Green
} else {
    Write-Host "  [FAIL] Token verification failed" -ForegroundColor Red
}

# 2. COMPANY TEST
Write-Host "`n2. Testing Company Management..." -ForegroundColor Yellow

# List companies first (user already has one from registration)
$result = Invoke-Api -Method GET -Path "/companies" -Auth $true
if ($result -and $result.success -and $result.data.Count -gt 0) {
    Write-Host "  [OK] Companies listed: Count=$($result.data.Count)" -ForegroundColor Green
    $global:companyId = $result.data[0].id
    Write-Host "  [INFO] Using company ID: $global:companyId" -ForegroundColor Cyan
    
    # Get company details
    $result = Invoke-Api -Method GET -Path "/companies/$global:companyId" -Auth $true
    if ($result -and $result.success) {
        Write-Host "  [OK] Company details retrieved" -ForegroundColor Green
    } else {
        Write-Host "  [FAIL] Company details failed" -ForegroundColor Red
    }
} else {
    Write-Host "  [FAIL] Company listing failed" -ForegroundColor Red
}

# 3. CARI TEST
Write-Host "`n3. Testing Cari Management..." -ForegroundColor Yellow
$cariData = @{
    code = "CARI$(Get-Random -Minimum 1000 -Maximum 9999)"
    title = "ABC Trading Ltd"
    name = "ABC Trading"
    account_type = "customer"
    category = "general"
    tax_number = "9876543210"
    tax_office = "Besiktas"
    address = "Cari Address 456"
    city = "Istanbul"
    district = "Besiktas"
    phone = "+90 212 555 7777"
    email = "abc@trading.com"
    currency = "TRY"
    credit_limit = 50000.00
    payment_term_days = 30
}

$result = Invoke-Api -Method POST -Path "/cari" -Body $cariData -Auth $true
if ($result -and $result.success) {
    Write-Host "  [OK] Cari created: ID=$($result.data.id)" -ForegroundColor Green
    $global:cariId = $result.data.id
} else {
    Write-Host "  [FAIL] Cari creation failed" -ForegroundColor Red
}

$result = Invoke-Api -Method GET -Path "/cari" -Auth $true
if ($result -and $result.success) {
    Write-Host "  [OK] Cari accounts listed: Count=$($result.data.data.Count)" -ForegroundColor Green
} else {
    Write-Host "  [FAIL] Cari listing failed" -ForegroundColor Red
}

$transactionData = @{
    transaction_type = "tahsilat"
    transaction_date = (Get-Date -Format "yyyy-MM-dd")
    amount = 5000.00
    currency = "TRY"
    description = "Test payment"
    payment_method = "bank_transfer"
}

$result = Invoke-Api -Method POST -Path "/cari/$global:cariId/transactions" -Body $transactionData -Auth $true
if ($result -and $result.success) {
    Write-Host "  [OK] Transaction added" -ForegroundColor Green
} else {
    Write-Host "  [FAIL] Transaction add failed" -ForegroundColor Red
}

$result = Invoke-Api -Method GET -Path "/cari/$global:cariId/balance" -Auth $true
if ($result -and $result.success) {
    Write-Host "  [OK] Balance retrieved: $($result.data.balance) $($result.data.currency)" -ForegroundColor Green
} else {
    Write-Host "  [FAIL] Balance query failed" -ForegroundColor Red
}

# 4. PRODUCT TEST
Write-Host "`n4. Testing Product Management..." -ForegroundColor Yellow
$categoryData = @{
    category_name = "Electronics"
    code = "ELEC-$(Get-Random -Minimum 100 -Maximum 999)"
    description = "Electronics category"
}

$result = Invoke-Api -Method POST -Path "/product-categories" -Body $categoryData -Auth $true
if ($result -and $result.success) {
    Write-Host "  [OK] Category created: ID=$($result.data.id)" -ForegroundColor Green
    $global:categoryId = $result.data.id
} else {
    Write-Host "  [FAIL] Category creation failed" -ForegroundColor Red
}

$productData = @{
    product_code = "PRD$(Get-Random -Minimum 10000 -Maximum 99999)"
    product_name = "Test Laptop"
    category_id = $global:categoryId
    product_type = "standard"
    unit = "Adet"
    barcode = "1234567890123"
    purchase_price = 5000.00
    sale_price = 7500.00
    currency = "TRY"
    tax_rate = 18.00
    stock_tracking = $true
    min_stock_level = 5
    max_stock_level = 100
    description = "Test laptop product"
}

$result = Invoke-Api -Method POST -Path "/products" -Body $productData -Auth $true
if ($result -and $result.success) {
    Write-Host "  [OK] Product created: ID=$($result.data.id)" -ForegroundColor Green
    $global:productId = $result.data.id
} else {
    Write-Host "  [FAIL] Product creation failed" -ForegroundColor Red
}

$result = Invoke-Api -Method GET -Path "/products" -Auth $true
if ($result -and $result.success) {
    Write-Host "  [OK] Products listed: Total=$($result.data.total)" -ForegroundColor Green
} else {
    Write-Host "  [FAIL] Product listing failed" -ForegroundColor Red
}

$result = Invoke-Api -Method GET -Path "/products/barcode/1234567890123" -Auth $true
if ($result -and $result.success) {
    Write-Host "  [OK] Product found by barcode: $($result.data.name)" -ForegroundColor Green
} else {
    Write-Host "  [FAIL] Barcode search failed" -ForegroundColor Red
}

# SUMMARY
Write-Host "`n=== TEST SUMMARY ===" -ForegroundColor Cyan
Write-Host "Company ID:  $global:companyId"
Write-Host "Cari ID:     $global:cariId"
Write-Host "Category ID: $global:categoryId"
Write-Host "Product ID:  $global:productId"
Write-Host ""
Write-Host "Test completed!" -ForegroundColor Green

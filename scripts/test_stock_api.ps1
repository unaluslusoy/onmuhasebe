# Stock & Warehouse Management - API Test Script
# Created: October 3, 2025
# Purpose: Test all stock management endpoints

$baseUrl = "http://localhost/onmuhasebe/public/api"
$token = ""

# Colors for output
function Write-Success { param($msg) Write-Host "✓ $msg" -ForegroundColor Green }
function Write-Error { param($msg) Write-Host "✗ $msg" -ForegroundColor Red }
function Write-Info { param($msg) Write-Host "ℹ $msg" -ForegroundColor Cyan }
function Write-Section { param($msg) Write-Host "`n=== $msg ===" -ForegroundColor Yellow }

# Test counter
$script:passCount = 0
$script:failCount = 0

# Helper function for API calls
function Invoke-ApiTest {
    param(
        [string]$Method,
        [string]$Endpoint,
        [object]$Body = $null,
        [string]$Description
    )
    
    Write-Info "Testing: $Description"
    
    try {
        $headers = @{
            "Content-Type" = "application/json"
        }
        
        if ($script:token) {
            $headers["Authorization"] = "Bearer $($script:token)"
        }
        
        $params = @{
            Uri = "$baseUrl$Endpoint"
            Method = $Method
            Headers = $headers
            TimeoutSec = 30
        }
        
        if ($Body) {
            $params["Body"] = ($Body | ConvertTo-Json -Depth 10)
        }
        
        $response = Invoke-RestMethod @params
        
        if ($response.success) {
            Write-Success "$Description - Status: OK"
            $script:passCount++
            return $response
        } else {
            Write-Error "$Description - Failed: $($response.message)"
            $script:failCount++
            return $null
        }
    }
    catch {
        $statusCode = $_.Exception.Response.StatusCode.value__
        $errorBody = $_.ErrorDetails.Message | ConvertFrom-Json -ErrorAction SilentlyContinue
        $errorMsg = if ($errorBody) { $errorBody.message } else { $_.Exception.Message }
        
        Write-Error "$Description - HTTP $statusCode : $errorMsg"
        $script:failCount++
        return $null
    }
}

# ==============================================
# START TESTING
# ==============================================

Write-Host "`n" -NoNewline
Write-Host "╔════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║   STOCK & WAREHOUSE MANAGEMENT API TESTS      ║" -ForegroundColor Cyan
Write-Host "║            Sprint 1.5 - Full Test Suite       ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host "`n"

# ==============================================
# 1. AUTHENTICATION
# ==============================================
Write-Section "Authentication"

$loginData = @{
    email = "admin@test.com"
    password = "password123"
}

$authResponse = Invoke-ApiTest -Method POST -Endpoint "/auth/login" -Body $loginData -Description "User Login"

if ($authResponse) {
    $script:token = $authResponse.data.access_token
    Write-Success "Token obtained: $($token.Substring(0, 20))..."
} else {
    Write-Error "Authentication failed. Cannot proceed with tests."
    exit 1
}

# ==============================================
# 2. WAREHOUSE MANAGEMENT
# ==============================================
Write-Section "Warehouse Management"

# Create main warehouse
$warehouse1Data = @{
    warehouse_code = "MAIN-001"
    warehouse_name = "Ana Depo"
    warehouse_type = "main"
    city = "İstanbul"
    district = "Kadıköy"
    address = "Test Mahallesi, Test Sokak No:1"
    phone = "+90 555 111 2233"
    is_active = $true
    is_default = $true
    allow_negative_stock = $false
    auto_allocate = $true
    total_capacity = 10000
    description = "Ana merkez deposu"
}

$warehouse1 = Invoke-ApiTest -Method POST -Endpoint "/warehouses" -Body $warehouse1Data -Description "Create Main Warehouse"
$warehouse1Id = if ($warehouse1) { $warehouse1.data.id } else { $null }

# Create branch warehouse
$warehouse2Data = @{
    warehouse_code = "BRANCH-001"
    warehouse_name = "Şube Depo"
    warehouse_type = "branch"
    city = "Ankara"
    district = "Çankaya"
    is_active = $true
    allow_negative_stock = $false
    total_capacity = 5000
}

$warehouse2 = Invoke-ApiTest -Method POST -Endpoint "/warehouses" -Body $warehouse2Data -Description "Create Branch Warehouse"
$warehouse2Id = if ($warehouse2) { $warehouse2.data.id } else { $null }

# Get warehouses list
Invoke-ApiTest -Method GET -Endpoint "/warehouses?page=1`&per_page=10" -Description "Get Warehouses List"

# Get warehouse by ID
if ($warehouse1Id) {
    Invoke-ApiTest -Method GET -Endpoint "/warehouses/$warehouse1Id" -Description "Get Warehouse Details"
}

# Get warehouse statistics
Invoke-ApiTest -Method GET -Endpoint "/warehouses/statistics" -Description "Get Warehouse Statistics"

# Create warehouse locations
if ($warehouse1Id) {
    $location1Data = @{
        location_code = "A-01-01"
        location_name = "Koridor A, Raf 01, Kat 01"
        location_type = "storage"
        aisle = "A"
        rack = "01"
        shelf = "01"
        capacity = 100
        is_active = $true
    }
    
    Invoke-ApiTest -Method POST -Endpoint "/warehouses/$warehouse1Id/locations" -Body $location1Data -Description "Create Warehouse Location"
    
    # Get locations
    Invoke-ApiTest -Method GET -Endpoint "/warehouses/$warehouse1Id/locations" -Description "Get Warehouse Locations"
}

# Update warehouse
if ($warehouse1Id) {
    $updateData = @{
        manager_name = "Ahmet Yılmaz"
        phone = "+90 555 111 2244"
    }
    
    Invoke-ApiTest -Method PUT -Endpoint "/warehouses/$warehouse1Id" -Body $updateData -Description "Update Warehouse"
}

# ==============================================
# 3. STOCK MOVEMENTS
# ==============================================
Write-Section "Stock Movements"

# Note: We need product IDs from previous tests
# For now, using placeholder IDs (1, 2)
$productId1 = 1
$productId2 = 2

# Create initial stock movement (purchase in)
$movement1Data = @{
    warehouse_id = $warehouse1Id
    product_id = $productId1
    movement_type = "purchase_in"
    quantity = 100
    unit = "Adet"
    unit_cost = 50
    movement_date = (Get-Date).ToString("yyyy-MM-dd HH:mm:ss")
    reference_type = "purchase_order"
    reference_number = "PO-2025-001"
    notes = "İlk stok girişi"
}

$movement1 = Invoke-ApiTest -Method POST -Endpoint "/stock/movements" -Body $movement1Data -Description "Create Purchase In Movement"

# Create another purchase
$movement2Data = @{
    warehouse_id = $warehouse1Id
    product_id = $productId2
    movement_type = "purchase_in"
    quantity = 50
    unit = "Adet"
    unit_cost = 75
    movement_date = (Get-Date).ToString("yyyy-MM-dd HH:mm:ss")
    notes = "İkinci ürün stok girişi"
}

Invoke-ApiTest -Method POST -Endpoint "/stock/movements" -Body $movement2Data -Description "Create Second Purchase"

# Get movements list
Invoke-ApiTest -Method GET -Endpoint "/stock/movements?page=1`&per_page=20" -Description "Get Stock Movements List"

# Get current stock
Invoke-ApiTest -Method GET -Endpoint "/stock/movements/current-stock?product_id=$productId1`&warehouse_id=$warehouse1Id" -Description "Get Current Stock for Product"

# Get movement history
Invoke-ApiTest -Method GET -Endpoint "/stock/movements/history?product_id=$productId1" -Description "Get Product Movement History"

# Calculate FIFO cost
Invoke-ApiTest -Method GET -Endpoint "/stock/movements/fifo-cost?product_id=$productId1`&warehouse_id=$warehouse1Id`&quantity=30" -Description "Calculate FIFO Cost"

# Get stock value
Invoke-ApiTest -Method GET -Endpoint "/stock/movements/stock-value?warehouse_id=$warehouse1Id" -Description "Get Stock Value by Warehouse"

# Get movement statistics
Invoke-ApiTest -Method GET -Endpoint "/stock/movements/statistics" -Description "Get Movement Statistics"

# ==============================================
# 4. STOCK TRANSFERS
# ==============================================
Write-Section "Stock Transfers"

if ($warehouse1Id -and $warehouse2Id) {
    # Create transfer
    $transferData = @{
        from_warehouse_id = $warehouse1Id
        to_warehouse_id = $warehouse2Id
        transfer_date = (Get-Date).ToString("yyyy-MM-dd")
        notes = "Şube deposuna transfer"
        items = @(
            @{
                product_id = $productId1
                requested_quantity = 20
                unit = "Adet"
                unit_cost = 50
            }
        )
    }
    
    $transfer = Invoke-ApiTest -Method POST -Endpoint "/stock/transfers" -Body $transferData -Description "Create Stock Transfer"
    $transferId = if ($transfer) { $transfer.data.id } else { $null }
    
    # Get transfer details
    if ($transferId) {
        Invoke-ApiTest -Method GET -Endpoint "/stock/transfers/$transferId" -Description "Get Transfer Details"
        
        # Approve transfer
        Invoke-ApiTest -Method POST -Endpoint "/stock/transfers/$transferId/approve" -Description "Approve Transfer"
        
        # Ship transfer
        Invoke-ApiTest -Method POST -Endpoint "/stock/transfers/$transferId/ship" -Description "Ship Transfer"
        
        # Receive transfer
        Invoke-ApiTest -Method POST -Endpoint "/stock/transfers/$transferId/receive" -Description "Receive Transfer"
    }
    
    # Get transfers list
    Invoke-ApiTest -Method GET -Endpoint "/stock/transfers?page=1" -Description "Get Transfers List"
    
    # Get transfer statistics
    Invoke-ApiTest -Method GET -Endpoint "/stock/transfers/statistics" -Description "Get Transfer Statistics"
}

# ==============================================
# 5. STOCK COUNTS
# ==============================================
Write-Section "Stock Counts"

if ($warehouse1Id) {
    # Create stock count
    $countData = @{
        warehouse_id = $warehouse1Id
        count_type = "full"
        count_date = (Get-Date).ToString("yyyy-MM-dd")
        notes = "Aylık tam sayım"
    }
    
    $count = Invoke-ApiTest -Method POST -Endpoint "/stock/counts" -Body $countData -Description "Create Stock Count"
    $countId = if ($count) { $count.data.id } else { $null }
    
    if ($countId) {
        # Start count (load stock)
        Invoke-ApiTest -Method POST -Endpoint "/stock/counts/$countId/start" -Description "Start Stock Count"
        
        # Update counted quantities
        $countUpdateData = @{
            counts = @{
                "1" = 98  # Assume item ID 1, counted 98 instead of 100
                "2" = 52  # Assume item ID 2, counted 52 instead of 50
            }
        }
        
        Invoke-ApiTest -Method POST -Endpoint "/stock/counts/$countId/update-counts" -Body $countUpdateData -Description "Update Counted Quantities"
        
        # Complete count
        Invoke-ApiTest -Method POST -Endpoint "/stock/counts/$countId/complete" -Description "Complete Stock Count"
        
        # Verify count
        Invoke-ApiTest -Method POST -Endpoint "/stock/counts/$countId/verify" -Description "Verify Stock Count"
        
        # Get variance items
        Invoke-ApiTest -Method GET -Endpoint "/stock/counts/$countId/variances" -Description "Get Variance Items"
        
        # Approve count (creates adjustments)
        Invoke-ApiTest -Method POST -Endpoint "/stock/counts/$countId/approve" -Description "Approve Stock Count"
        
        # Get count details
        Invoke-ApiTest -Method GET -Endpoint "/stock/counts/$countId" -Description "Get Count Details"
    }
    
    # Get counts list
    Invoke-ApiTest -Method GET -Endpoint "/stock/counts?page=1" -Description "Get Counts List"
    
    # Get count statistics
    Invoke-ApiTest -Method GET -Endpoint "/stock/counts/statistics" -Description "Get Count Statistics"
}

# ==============================================
# 6. LOW STOCK ALERTS
# ==============================================
Write-Section "Stock Alerts `& Reports"

# Get low stock products
Invoke-ApiTest -Method GET -Endpoint "/stock/movements/low-stock" -Description "Get Low Stock Products"

# Get current stock for all warehouses
Invoke-ApiTest -Method GET -Endpoint "/stock/movements/current-stock?product_id=$productId1" -Description "Get Stock Across All Warehouses"

# ==============================================
# TEST SUMMARY
# ==============================================
Write-Host "`n"
Write-Host "╔════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║              TEST SUMMARY                      ║" -ForegroundColor Cyan
Write-Host "╠════════════════════════════════════════════════╣" -ForegroundColor Cyan
Write-Host "║  " -NoNewline -ForegroundColor Cyan
Write-Host "Total Tests: $($script:passCount + $script:failCount)" -NoNewline -ForegroundColor White
Write-Host (" " * (43 - ("Total Tests: $($script:passCount + $script:failCount)").Length)) -NoNewline
Write-Host "║" -ForegroundColor Cyan
Write-Host "║  " -NoNewline -ForegroundColor Cyan
Write-Host "Passed: " -NoNewline -ForegroundColor Green
Write-Host "$($script:passCount)" -NoNewline -ForegroundColor White
Write-Host (" " * (43 - ("Passed: $($script:passCount)").Length)) -NoNewline
Write-Host "║" -ForegroundColor Cyan
Write-Host "║  " -NoNewline -ForegroundColor Cyan
Write-Host "Failed: " -NoNewline -ForegroundColor Red
Write-Host "$($script:failCount)" -NoNewline -ForegroundColor White
Write-Host (" " * (43 - ("Failed: $($script:failCount)").Length)) -NoNewline
Write-Host "║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════╝" -ForegroundColor Cyan

if ($script:failCount -eq 0) {
    Write-Host "`n🎉 All tests passed successfully!" -ForegroundColor Green
} else {
    Write-Host "`n⚠️  Some tests failed. Please review the errors above." -ForegroundColor Yellow
}

Write-Host "`nTest completed at: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Gray
Write-Host ""

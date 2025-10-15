# ============================================
# Simple API Test (PowerShell)
# ============================================

$baseUrl = "http://localhost:8000/api"

Write-Host "`n============================================" -ForegroundColor Magenta
Write-Host "API Test - Ön Muhasebe Sistemi" -ForegroundColor Magenta
Write-Host "============================================`n" -ForegroundColor Magenta

# Test 1: Health Check
Write-Host "[1/7] Testing /api/health..." -ForegroundColor Cyan
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/health" -UseBasicParsing
    $data = $response.Content | ConvertFrom-Json
    Write-Host "✓ Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "  Version: $($data.data.version)" -ForegroundColor Gray
} catch {
    Write-Host "✗ Failed: $_" -ForegroundColor Red
}

# Test 2: Login
Write-Host "`n[2/7] Testing Login..." -ForegroundColor Cyan
try {
    $loginBody = @{
        email = "admin@onmuhasebe.com"
        password = "Admin123!"
    } | ConvertTo-Json
    
    $response = Invoke-WebRequest -Uri "$baseUrl/auth/login" -Method POST -Body $loginBody -ContentType "application/json" -UseBasicParsing
    $data = $response.Content | ConvertFrom-Json
    $tokens = $data.data.tokens
    
    Write-Host "✓ Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "  User: $($data.data.user.full_name) ($($data.data.user.email))" -ForegroundColor Gray
    Write-Host "  Access Token: $($tokens.access_token.Substring(0, 30))..." -ForegroundColor Gray
    Write-Host "  Refresh Token: $($tokens.refresh_token.Substring(0, 30))..." -ForegroundColor Gray
} catch {
    Write-Host "✗ Failed: $_" -ForegroundColor Red
    $tokens = $null
}

# Test 3: Get User Info (Protected)
if ($tokens) {
    Write-Host "`n[3/7] Testing /auth/me (Protected)..." -ForegroundColor Cyan
    try {
        $headers = @{
            "Authorization" = "Bearer $($tokens.access_token)"
        }
        $response = Invoke-WebRequest -Uri "$baseUrl/auth/me" -Headers $headers -UseBasicParsing
        $data = $response.Content | ConvertFrom-Json
        
        Write-Host "✓ Status: $($response.StatusCode)" -ForegroundColor Green
        Write-Host "  User ID: $($data.data.id)" -ForegroundColor Gray
        Write-Host "  Email: $($data.data.email)" -ForegroundColor Gray
        Write-Host "  Role: $($data.data.role)" -ForegroundColor Gray
    } catch {
        Write-Host "✗ Failed: $_" -ForegroundColor Red
    }
}

# Test 4: Register
Write-Host "`n[4/7] Testing Register..." -ForegroundColor Cyan
try {
    $timestamp = [DateTimeOffset]::UtcNow.ToUnixTimeSeconds()
    $registerBody = @{
        email = "test$timestamp@example.com"
        password = "Test123!"
        password_confirmation = "Test123!"
        full_name = "Test User"
        phone = "05551234567"
    } | ConvertTo-Json
    
    $response = Invoke-WebRequest -Uri "$baseUrl/auth/register" -Method POST -Body $registerBody -ContentType "application/json" -UseBasicParsing
    $data = $response.Content | ConvertFrom-Json
    
    Write-Host "✓ Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "  Created User: $($data.data.user.email)" -ForegroundColor Gray
} catch {
    Write-Host "✗ Failed: $_" -ForegroundColor Red
}

# Test 5: Refresh Token
if ($tokens) {
    Write-Host "`n[5/7] Testing Token Refresh..." -ForegroundColor Cyan
    try {
        $refreshBody = @{
            refresh_token = $tokens.refresh_token
        } | ConvertTo-Json
        
        $response = Invoke-WebRequest -Uri "$baseUrl/auth/refresh" -Method POST -Body $refreshBody -ContentType "application/json" -UseBasicParsing
        $data = $response.Content | ConvertFrom-Json
        
        Write-Host "✓ Status: $($response.StatusCode)" -ForegroundColor Green
        Write-Host "  New Access Token: $($data.data.access_token.Substring(0, 30))..." -ForegroundColor Gray
    } catch {
        Write-Host "✗ Failed: $_" -ForegroundColor Red
    }
}

# Test 6: Change Password (Expected to fail - wrong password)
if ($tokens) {
    Write-Host "`n[6/7] Testing Change Password (Expect Fail)..." -ForegroundColor Cyan
    try {
        $changeBody = @{
            current_password = "WrongPassword!"
            new_password = "NewPassword123!"
            new_password_confirmation = "NewPassword123!"
        } | ConvertTo-Json
        
        $headers = @{
            "Authorization" = "Bearer $($tokens.access_token)"
        }
        
        $response = Invoke-WebRequest -Uri "$baseUrl/auth/change-password" -Method POST -Body $changeBody -ContentType "application/json" -Headers $headers -UseBasicParsing
        Write-Host "✗ Should have failed!" -ForegroundColor Yellow
    } catch {
        Write-Host "✓ Correctly rejected (wrong password)" -ForegroundColor Green
    }
}

# Test 7: Logout
if ($tokens) {
    Write-Host "`n[7/7] Testing Logout..." -ForegroundColor Cyan
    try {
        $headers = @{
            "Authorization" = "Bearer $($tokens.access_token)"
        }
        
        $response = Invoke-WebRequest -Uri "$baseUrl/auth/logout" -Method POST -Headers $headers -UseBasicParsing
        $data = $response.Content | ConvertFrom-Json
        
        Write-Host "✓ Status: $($response.StatusCode)" -ForegroundColor Green
        Write-Host "  Message: $($data.message)" -ForegroundColor Gray
    } catch {
        Write-Host "✗ Failed: $_" -ForegroundColor Red
    }
}

Write-Host "`n============================================" -ForegroundColor Magenta
Write-Host "✅ All tests completed!" -ForegroundColor Green
Write-Host "============================================`n" -ForegroundColor Magenta

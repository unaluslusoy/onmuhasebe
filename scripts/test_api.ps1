# ============================================
# API Test Script (PowerShell)
# Test all authentication endpoints
# Usage: .\scripts\test_api.ps1
# ============================================

$baseUrl = "http://localhost:8000/api"
$testResults = @()

function Test-Endpoint {
    param(
        [string]$Name,
        [string]$Method,
        [string]$Url,
        [hashtable]$Body = $null,
        [hashtable]$Headers = @{}
    )
    
    Write-Host "`n[$Name]" -ForegroundColor Cyan
    Write-Host "$Method $Url" -ForegroundColor Gray
    
    try {
        $params = @{
            Uri = $Url
            Method = $Method
            UseBasicParsing = $true
            TimeoutSec = 10
        }
        
        if ($Body) {
            $params.Body = ($Body | ConvertTo-Json)
            $params.ContentType = "application/json"
        }
        
        if ($Headers.Count -gt 0) {
            $params.Headers = $Headers
        }
        
        $response = Invoke-WebRequest @params
        $content = $response.Content | ConvertFrom-Json
        
        Write-Host "✓ Status: $($response.StatusCode)" -ForegroundColor Green
        Write-Host "Response:" -ForegroundColor Gray
        Write-Host ($content | ConvertTo-Json -Depth 5) -ForegroundColor White
        
        return @{
            Success = $true
            StatusCode = $response.StatusCode
            Data = $content
        }
    }
    catch {
        Write-Host "✗ Error: $($_.Exception.Message)" -ForegroundColor Red
        if ($_.Exception.Response) {
            $statusCode = $_.Exception.Response.StatusCode.value__
            Write-Host "Status Code: $statusCode" -ForegroundColor Yellow
        }
        return @{
            Success = $false
            Error = $_.Exception.Message
        }
    }
}

Write-Host "============================================" -ForegroundColor Magenta
Write-Host "API Test Script" -ForegroundColor Magenta
Write-Host "============================================" -ForegroundColor Magenta

# Test 1: Health Check
$result = Test-Endpoint -Name "Health Check" -Method "GET" -Url "$baseUrl/health"

# Test 2: Login with Admin
$loginData = @{
    email = "admin@onmuhasebe.com"
    password = "Admin123!"
}
$result = Test-Endpoint -Name "Login (Admin)" -Method "POST" -Url "$baseUrl/auth/login" -Body $loginData

$tokens = $null
if ($result.Success -and $result.Data.data.tokens) {
    $tokens = $result.Data.data.tokens
    Write-Host "`n✓ Got Tokens:" -ForegroundColor Green
    Write-Host "  Access: $($tokens.access_token.Substring(0, 30))..." -ForegroundColor Gray
    Write-Host "  Refresh: $($tokens.refresh_token.Substring(0, 30))..." -ForegroundColor Gray
}

# Test 3: Get User Info (Protected)
if ($tokens) {
    $headers = @{
        "Authorization" = "Bearer $($tokens.access_token)"
    }
    $result = Test-Endpoint -Name "Get User Info (Protected)" -Method "GET" -Url "$baseUrl/auth/me" -Headers $headers
}

# Test 4: Register New User
$timestamp = [DateTimeOffset]::UtcNow.ToUnixTimeSeconds()
$registerData = @{
    email = "test$timestamp@example.com"
    password = "Test123!"
    password_confirmation = "Test123!"
    full_name = "Test User $timestamp"
    phone = "05551234567"
}
$result = Test-Endpoint -Name "Register New User" -Method "POST" -Url "$baseUrl/auth/register" -Body $registerData

# Test 5: Refresh Token
if ($tokens) {
    $refreshData = @{
        refresh_token = $tokens.refresh_token
    }
    $result = Test-Endpoint -Name "Refresh Access Token" -Method "POST" -Url "$baseUrl/auth/refresh" -Body $refreshData
}

# Test 6: Change Password (should fail - wrong current password)
if ($tokens) {
    $headers = @{
        "Authorization" = "Bearer $($tokens.access_token)"
    }
    $changePasswordData = @{
        current_password = "WrongPassword123!"
        new_password = "NewPassword123!"
        new_password_confirmation = "NewPassword123!"
    }
    $result = Test-Endpoint -Name "Change Password (Expect Fail)" -Method "POST" -Url "$baseUrl/auth/change-password" -Body $changePasswordData -Headers $headers
}

# Test 7: Logout
if ($tokens) {
    $headers = @{
        "Authorization" = "Bearer $($tokens.access_token)"
    }
    $result = Test-Endpoint -Name "Logout" -Method "POST" -Url "$baseUrl/auth/logout" -Headers $headers
}

# Test 8: Verify Token Invalid After Logout
if ($tokens) {
    $headers = @{
        "Authorization" = "Bearer $($tokens.access_token)"
    }
    $result = Test-Endpoint -Name "Get User Info After Logout (Expect Fail)" -Method "GET" -Url "$baseUrl/auth/me" -Headers $headers
}

Write-Host "`n============================================" -ForegroundColor Magenta
Write-Host "✅ All API tests completed!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Magenta

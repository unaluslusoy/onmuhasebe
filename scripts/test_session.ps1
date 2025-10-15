# Session Test Script
# Tests login, session creation, and web page access

$baseUrl = "http://localhost:8000"

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  SESSION TEST - Fatura Sayfası Erişimi" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# Step 1: Login
Write-Host "[1/4] Login yapılıyor..." -ForegroundColor Yellow
try {
    $loginResponse = Invoke-RestMethod -Uri "$baseUrl/api/auth/login" -Method POST -Body (@{
        email = "admin@example.com"
        password = "password123"
    } | ConvertTo-Json) -ContentType "application/json" -ErrorAction Stop
    
    if ($loginResponse.success) {
        $token = $loginResponse.data.access_token
        Write-Host "✓ Login başarılı!" -ForegroundColor Green
        Write-Host "  Token: $($token.Substring(0, 20))..." -ForegroundColor Gray
    } else {
        Write-Host "✗ Login başarısız: $($loginResponse.message)" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "✗ Login hatası: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Step 2: Create Session
Write-Host "`n[2/4] Session oluşturuluyor..." -ForegroundColor Yellow
try {
    $sessionResponse = Invoke-RestMethod -Uri "$baseUrl/api/auth/create-session" -Method POST -Headers @{
        "Authorization" = "Bearer $token"
    } -ErrorAction Stop
    
    if ($sessionResponse.success) {
        Write-Host "✓ Session başarıyla oluşturuldu!" -ForegroundColor Green
    } else {
        Write-Host "✗ Session oluşturulamadı: $($sessionResponse.message)" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "✗ Session hatası: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Step 3: Test Session Page
Write-Host "`n[3/4] Session kontrol ediliyor..." -ForegroundColor Yellow
try {
    # Use WebSession to persist cookies
    $session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    
    # First request to get session cookie
    $null = Invoke-WebRequest -Uri "$baseUrl/api/auth/create-session" -Method POST -Headers @{
        "Authorization" = "Bearer $token"
    } -WebSession $session -ErrorAction Stop
    
    # Now test the protected page with session
    $testResponse = Invoke-WebRequest -Uri "$baseUrl/test-session" -WebSession $session -ErrorAction Stop
    
    if ($testResponse.StatusCode -eq 200) {
        if ($testResponse.Content -match "Session Aktif") {
            Write-Host "✓ Session aktif - Web sayfası erişilebilir!" -ForegroundColor Green
        } else {
            Write-Host "⚠ Sayfa açıldı ama session bulunamadı" -ForegroundColor Yellow
        }
    }
} catch {
    Write-Host "✗ Session test hatası: $($_.Exception.Message)" -ForegroundColor Red
}

# Step 4: Test Invoice Page
Write-Host "`n[4/4] Fatura sayfası test ediliyor..." -ForegroundColor Yellow
try {
    $invoiceResponse = Invoke-WebRequest -Uri "$baseUrl/faturalar" -WebSession $session -ErrorAction Stop
    
    if ($invoiceResponse.StatusCode -eq 200) {
        if ($invoiceResponse.Content -match "Faturalar") {
            Write-Host "✓ Faturalar sayfası başarıyla yüklendi!" -ForegroundColor Green
        } else {
            Write-Host "⚠ Sayfa yüklendi ama içerik beklendiği gibi değil" -ForegroundColor Yellow
        }
    }
} catch {
    $statusCode = $_.Exception.Response.StatusCode.Value__
    if ($statusCode -eq 302) {
        Write-Host "✗ Redirect hatası - Session çalışmıyor olabilir" -ForegroundColor Red
    } else {
        Write-Host "✗ Fatura sayfası hatası: $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Summary
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  TEST TAMAMLANDI" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

Write-Host "📍 Test Sayfaları:" -ForegroundColor White
Write-Host "   • Session Debug: $baseUrl/test-session" -ForegroundColor Gray
Write-Host "   • Faturalar: $baseUrl/faturalar" -ForegroundColor Gray
Write-Host "   • Fatura Detay: $baseUrl/fatura/4" -ForegroundColor Gray
Write-Host "   • Dashboard: $baseUrl/" -ForegroundColor Gray

Write-Host "`n💡 Not: Tarayıcıda test etmek için önce /giris sayfasından giriş yapın" -ForegroundColor Yellow
Write-Host "   veya yukarıdaki script ile session oluşturun.`n" -ForegroundColor Yellow

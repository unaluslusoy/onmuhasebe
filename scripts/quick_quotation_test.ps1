# Quick Quotation Test
$baseUrl = "http://localhost/onmuhasebe/public/api"

# Register
$timestamp = [DateTimeOffset]::Now.ToUnixTimeSeconds()
$email = "quick_$timestamp@example.com"
$password = "Test123!@#"

$registerData = @{
    full_name = "Quick Test"
    email = $email
    password = $password
    password_confirmation = $password
    phone = "0555 000 0001"
    company_name = "Quick Test Company"
} | ConvertTo-Json

Write-Host "Registering..." -ForegroundColor Yellow
$registerResponse = Invoke-RestMethod -Uri "$baseUrl/auth/register" -Method POST -Body $registerData -ContentType "application/json"

if ($registerResponse.success) {
    $companyId = $registerResponse.data.user.company_id
    Write-Host "Registered OK - Company: $companyId" -ForegroundColor Green
    
    # Now login to get token
    Write-Host "Logging in..." -ForegroundColor Yellow
    $loginData = @{
        email = $email
        password = $password
    } | ConvertTo-Json
    
    $loginResponse = Invoke-RestMethod -Uri "$baseUrl/auth/login" -Method POST -Body $loginData -ContentType "application/json"
    
    if ($loginResponse.success) {
        $token = $loginResponse.data.tokens.access_token
        Write-Host "Login OK" -ForegroundColor Green
        Write-Host "Token (first 30): $($token.Substring(0, 30))..." -ForegroundColor Cyan
        
        # Create quotation
        Write-Host "`nCreating quotation..." -ForegroundColor Yellow
        $quotationData = @{
            quotation_date = (Get-Date).ToString("yyyy-MM-dd")
            valid_until = (Get-Date).AddDays(30).ToString("yyyy-MM-dd")
            customer_name = "Test Customer"
            customer_email = "customer@test.com"
            customer_phone = "0555 123 4567"
            currency = "TRY"
            exchange_rate = 1.00
            items = @(
                @{
                    item_type = "product"
                    item_name = "Test Product"
                    quantity = 5
                    unit = "Adet"
                    unit_price = 100.00
                    tax_rate = 20
                }
            )
        } | ConvertTo-Json -Depth 10
        
        $headers = @{
            "Authorization" = "Bearer $token"
            "Content-Type" = "application/json"
        }
        
        try {
            $quotationResponse = Invoke-RestMethod -Uri "$baseUrl/quotations" -Method POST -Body $quotationData -Headers $headers
            
            if ($quotationResponse.success) {
                Write-Host "Quotation created OK!" -ForegroundColor Green
                Write-Host "ID: $($quotationResponse.data.id)" -ForegroundColor Cyan
                Write-Host "Number: $($quotationResponse.data.quotation_number)" -ForegroundColor Cyan
                Write-Host "Total: $($quotationResponse.data.total) TRY" -ForegroundColor Cyan
            }
        }
        catch {
            Write-Host "Error: $_" -ForegroundColor Red
            if ($_.Exception.Response) {
                $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
                $responseBody = $reader.ReadToEnd()
                Write-Host "Response: $responseBody" -ForegroundColor Red
            }
        }
    }
    else {
        Write-Host "Login failed" -ForegroundColor Red
    }
}
else {
    Write-Host "Registration failed" -ForegroundColor Red
}

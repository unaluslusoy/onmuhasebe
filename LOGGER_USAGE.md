# Logger Kullanım Kılavuzu

## Kurulum

Logger otomatik olarak hazır. Sadece kullanmaya başlayın:

```php
use App\Helpers\Logger;
```

## Temel Kullanım

### 1. Error Logging
```php
try {
    // Risky operation
    $result = $db->query($sql);
} catch (Exception $e) {
    Logger::error('Database query failed', [
        'error' => $e->getMessage(),
        'query' => $sql,
        'line' => __LINE__,
        'file' => __FILE__
    ]);
}
```

### 2. Security Logging
```php
// Failed login
Logger::security('Failed login attempt', [
    'email' => $email,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'attempts' => $attemptCount
]);

// Successful login
Logger::security('User logged in', [
    'user_id' => $userId,
    'email' => $email
]);

// Password change
Logger::security('Password changed', [
    'user_id' => $userId,
    'ip' => $_SERVER['REMOTE_ADDR']
]);

// Suspicious activity
Logger::security('Suspicious activity detected', [
    'user_id' => $userId,
    'action' => 'multiple_failed_logins',
    'count' => 10
]);
```

### 3. API Logging
```php
// API request
Logger::api('Invoice created via API', [
    'endpoint' => '/api/invoices',
    'method' => 'POST',
    'invoice_id' => $invoiceId,
    'user_id' => $userId,
    'response_time' => $responseTime . 'ms'
]);

// API error
Logger::api('API request failed', [
    'endpoint' => $_SERVER['REQUEST_URI'],
    'method' => $_SERVER['REQUEST_METHOD'],
    'error' => $errorMessage,
    'status_code' => 500
]);
```

### 4. Info Logging
```php
// User registration
Logger::info('New user registered', [
    'user_id' => $userId,
    'email' => $email,
    'company_name' => $companyName
]);

// Invoice sent
Logger::info('Invoice sent to customer', [
    'invoice_id' => $invoiceId,
    'customer_email' => $customerEmail,
    'amount' => $amount
]);
```

### 5. Warning Logging
```php
// Low stock
Logger::warning('Product stock is low', [
    'product_id' => $productId,
    'product_name' => $productName,
    'current_stock' => $currentStock,
    'minimum_stock' => $minimumStock
]);

// Failed email
Logger::warning('Failed to send email', [
    'to' => $recipientEmail,
    'subject' => $subject,
    'error' => $error
]);
```

### 6. Debug Logging
```php
// Only logs in local environment with LOG_LEVEL=debug
Logger::debug('Cache hit', [
    'key' => $cacheKey,
    'ttl' => $ttl
]);

Logger::debug('Query executed', [
    'sql' => $sql,
    'execution_time' => $executionTime . 'ms'
]);
```

### 7. Database Logging
```php
// Slow query detection
Logger::database('Slow query detected', [
    'query' => $sql,
    'execution_time' => $executionTime . 'ms',
    'rows_affected' => $rowCount
]);

// Migration
Logger::database('Migration executed', [
    'migration' => $migrationName,
    'status' => 'success'
]);
```

### 8. e-Fatura Logging
```php
// e-Fatura gönderimi
Logger::efatura('Invoice sent to GIB', [
    'invoice_id' => $invoiceId,
    'invoice_number' => $invoiceNumber,
    'gib_ettn' => $ettn,
    'status' => 'success'
]);

// e-Fatura hatası
Logger::efatura('Failed to send invoice to GIB', [
    'invoice_id' => $invoiceId,
    'error_code' => $errorCode,
    'error_message' => $errorMessage
]);
```

## Otomatik Özellikler

### 1. Sensitive Data Sanitization
Şifreler, tokenlar ve hassas bilgiler otomatik olarak gizlenir:

```php
Logger::info('User data', [
    'email' => 'user@example.com',
    'password' => 'secret123',      // Otomatik ***REDACTED***
    'access_token' => 'xyz123'      // Otomatik ***REDACTED***
]);

// Log çıktısı:
// [2025-10-15 18:00:00] [INFO] User data | {"email":"user@example.com","password":"***REDACTED***","access_token":"***REDACTED***"}
```

### 2. Request Information (Security & API)
Security ve API loglarına otomatik olarak eklenir:
- IP adresi
- HTTP method ve URI
- User agent

```php
Logger::security('Login attempt', ['email' => $email]);

// Log çıktısı:
// [2025-10-15 18:00:00] [SECURITY] Login attempt | IP: 192.168.1.1 | POST /api/auth/login | UA: Mozilla/5.0... | {"email":"user@example.com"}
```

### 3. Automatic Cleanup
Eski log dosyaları otomatik temizlenir (varsayılan: 30 gün)

`.env` ile yapılandırma:
```env
LOG_RETENTION_DAYS=30
```

## Log Dosyaları

Loglar `storage/logs/` klasöründe saklanır:

```
storage/logs/
├── error_2025-10-15.log
├── warning_2025-10-15.log
├── info_2025-10-15.log
├── security_2025-10-15.log
├── api_2025-10-15.log
├── database_2025-10-15.log
├── efatura_2025-10-15.log
└── debug_2025-10-15.log
```

## İleri Kullanım

### Log Okuma
```php
// Son 10 log dosyasını al
$logFiles = Logger::getLogFiles('error', 10);

// Log dosyası oku (pagination)
$logs = Logger::readLog($filepath, $offset = 0, $limit = 100);

foreach ($logs['lines'] as $line) {
    echo $line . "\n";
}
```

### Log Arama
```php
// Tüm loglarda ara
$results = Logger::search('database connection');

// Belirli level'da ara
$results = Logger::search('failed', 'error');

// Belirli tarihte ara
$results = Logger::search('invoice', 'info', '2025-10-15');

foreach ($results as $result) {
    echo "{$result['file']} - Line {$result['line']}: {$result['content']}\n";
}
```

### İstatistikler
```php
// Bugünün istatistikleri
$stats = Logger::getStatistics();

// Belirli tarih
$stats = Logger::getStatistics('2025-10-14');

print_r($stats);
/*
Array (
    [date] => 2025-10-15
    [levels] => Array (
        [error] => Array (
            [count] => 5
            [size] => 2048
            [size_human] => 2.00 KB
        )
        [security] => Array (
            [count] => 12
            [size] => 4096
            [size_human] => 4.00 KB
        )
        ...
    )
)
*/
```

## Best Practices

### 1. Context İçin Relevant Data
```php
// ✓ İyi
Logger::error('Payment failed', [
    'user_id' => $userId,
    'payment_id' => $paymentId,
    'amount' => $amount,
    'error' => $errorMessage
]);

// ✗ Kötü
Logger::error('Payment failed');
```

### 2. Açıklayıcı Mesajlar
```php
// ✓ İyi
Logger::error('Failed to connect to database: connection timeout');

// ✗ Kötü
Logger::error('Error');
```

### 3. Doğru Level Kullanımı
- **error**: Hata - işlem başarısız
- **warning**: Uyarı - işlem başarılı ama dikkat edilmeli
- **info**: Bilgi - normal işlem
- **debug**: Debug - sadece development'ta
- **security**: Güvenlik olayları
- **api**: API istekleri
- **database**: Database işlemleri
- **efatura**: e-Fatura işlemleri

### 4. Performance
```php
// Debug logları sadece gerektiğinde
if ($_ENV['APP_ENV'] === 'local') {
    Logger::debug('Cache details', $cacheData);
}

// Çok büyük context kullanmayın
Logger::info('User data', [
    'user_id' => $userId,
    // 'full_user_object' => $user // ✗ Çok büyük
]);
```

## Monitoring & Alerts

Log dosyalarını düzenli kontrol edin:

```bash
# Error loglarını izle
tail -f storage/logs/error_2025-10-15.log

# Security olaylarını izle
tail -f storage/logs/security_*.log

# Son 50 error
tail -50 storage/logs/error_2025-10-15.log
```

## Production Önerileri

1. **Log Rotation**: Cron job ile eski logları temizle veya arşivle
2. **Monitoring**: Error logları için alert sistemi kur (email/SMS)
3. **Centralized Logging**: Sentry, LogRocket gibi servisler kullan
4. **Log Level**: Production'da LOG_LEVEL=error
5. **Storage**: Büyük sistemlerde log storage planla

## Örnek: Controller'da Kullanım

```php
<?php

namespace App\Controllers\Auth;

use App\Helpers\Logger;
use App\Helpers\Response;

class AuthController
{
    public function login(array $params = []): void
    {
        $input = $this->getJsonInput();

        Logger::info('Login attempt', [
            'email' => $input['email'] ?? 'unknown'
        ]);

        try {
            // Validate
            $errors = $this->validateLogin($input);
            if (!empty($errors)) {
                Logger::warning('Login validation failed', [
                    'email' => $input['email'],
                    'errors' => $errors
                ]);
                Response::validationError($errors);
            }

            // Check credentials
            $user = $this->userModel->findByEmail($input['email']);

            if (!$user || !password_verify($input['password'], $user['password'])) {
                Logger::security('Failed login attempt - invalid credentials', [
                    'email' => $input['email']
                ]);
                Response::error('Invalid credentials', 401);
            }

            // Generate tokens
            $tokens = $this->jwtService->generateTokens($user['id'], $user['email'], $user['role']);

            Logger::security('User logged in successfully', [
                'user_id' => $user['id'],
                'email' => $user['email']
            ]);

            Response::success([
                'user' => $user,
                'tokens' => $tokens
            ], 'Login successful');

        } catch (\Exception $e) {
            Logger::error('Login error', [
                'email' => $input['email'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Response::serverError('An error occurred during login');
        }
    }
}
```

---

**Dokümantasyon Tarihi:** 15 Ekim 2025
**Versiyon:** 1.0

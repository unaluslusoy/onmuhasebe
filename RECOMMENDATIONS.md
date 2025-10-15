# 📋 ÖN MUHASEBE SİSTEMİ - GELİŞTİRME ÖNERİLERİ

**Hazırlanma Tarihi:** 15 Ekim 2025
**Mevcut Tamamlanma:** %50-60
**Hedef:** Production-Ready Sistem

---

## 🎯 1. ÖNCELIK 1 - KRİTİK EKSIKLER (1-2 Hafta)

### 1.1 Git Versiyon Kontrolü - 🔴 ACİL
**Durum:** Proje henüz Git'te değil
**Risk:** Kod kaybı, rollback yapılamama, ekip çalışması zorlukları

**Yapılması Gerekenler:**
```bash
cd C:\xampp\htdocs\onmuhasebe

# Git başlat
git init

# .gitignore dosyası oluştur (zaten var, kontrol et)
# Aşağıdakilerin ignore edildiğinden emin ol:
# - vendor/
# - .env
# - storage/logs/
# - public/uploads/
# - node_modules/

# İlk commit
git add .
git commit -m "Initial commit: Ön Muhasebe Sistemi v1.0"

# GitHub/GitLab'a push
git remote add origin <repository-url>
git branch -M main
git push -u origin main
```

**Önerilen Branch Stratejisi:**
- `main` - Production-ready kod
- `develop` - Geliştirme branch'i
- `feature/module-name` - Yeni özellikler
- `hotfix/bug-description` - Acil düzeltmeler

**Tahmini Süre:** 2 saat

---

### 1.2 Veritabanı Backup Sistemi - 🔴 ACİL
**Durum:** Otomatik backup yok
**Risk:** Veri kaybı

**Yapılması Gerekenler:**

#### A) Otomatik Backup Script
```php
<?php
// scripts/backup-database.php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$backupDir = __DIR__ . '/../storage/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$filename = 'backup_' . date('Y-m-d_His') . '.sql';
$filepath = $backupDir . '/' . $filename;

$dbHost = $_ENV['DB_HOST'];
$dbName = $_ENV['DB_DATABASE'];
$dbUser = $_ENV['DB_USERNAME'];
$dbPass = $_ENV['DB_PASSWORD'];

$command = sprintf(
    'mysqldump -h%s -u%s -p%s %s > %s',
    escapeshellarg($dbHost),
    escapeshellarg($dbUser),
    escapeshellarg($dbPass),
    escapeshellarg($dbName),
    escapeshellarg($filepath)
);

exec($command, $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ Backup başarılı: {$filename}\n";

    // Eski backupları temizle (30 günden eski)
    $files = glob($backupDir . '/backup_*.sql');
    $now = time();
    foreach ($files as $file) {
        if ($now - filemtime($file) >= 30 * 24 * 3600) {
            unlink($file);
            echo "🗑️ Eski backup silindi: " . basename($file) . "\n";
        }
    }
} else {
    echo "❌ Backup başarısız!\n";
    exit(1);
}
```

#### B) Windows Task Scheduler ile Otomatik Çalıştırma
```batch
# backup-daily.bat
@echo off
cd C:\xampp\htdocs\onmuhasebe
php scripts\backup-database.php >> storage\logs\backup.log 2>&1
```

**Ayarlar:**
- Günlük: Her gün saat 02:00
- Haftalık: Her Pazar 03:00
- Aylık: Her ayın 1'i 04:00

**Tahmini Süre:** 3 saat

---

### 1.3 Error Logging & Monitoring - 🔴 ACİL
**Durum:** Temel logging var ama yetersiz
**Risk:** Hataları takip edememe

**Yapılması Gerekenler:**

#### A) Gelişmiş Logger Sınıfı
```php
<?php
// app/Helpers/Logger.php

namespace App\Helpers;

class Logger
{
    private static $logPath;

    public static function init()
    {
        self::$logPath = __DIR__ . '/../../storage/logs/';

        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }
    }

    public static function error($message, $context = [])
    {
        self::log('ERROR', $message, $context, 'error.log');
    }

    public static function warning($message, $context = [])
    {
        self::log('WARNING', $message, $context, 'app.log');
    }

    public static function info($message, $context = [])
    {
        self::log('INFO', $message, $context, 'app.log');
    }

    public static function api($endpoint, $method, $statusCode, $duration)
    {
        $message = sprintf(
            '%s %s - Status: %d - Duration: %.2fms',
            $method,
            $endpoint,
            $statusCode,
            $duration
        );
        self::log('API', $message, [], 'api.log');
    }

    public static function security($event, $context = [])
    {
        self::log('SECURITY', $event, $context, 'security.log');
    }

    private static function log($level, $message, $context, $filename)
    {
        self::init();

        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $user = $_SESSION['user']['email'] ?? 'guest';

        $logEntry = sprintf(
            "[%s] [%s] [IP: %s] [User: %s] %s",
            $timestamp,
            $level,
            $ip,
            $user,
            $message
        );

        if (!empty($context)) {
            $logEntry .= ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        $logEntry .= PHP_EOL;

        $filepath = self::$logPath . $filename;
        file_put_contents($filepath, $logEntry, FILE_APPEND | LOCK_EX);

        // Rotate logs if > 10MB
        if (filesize($filepath) > 10 * 1024 * 1024) {
            self::rotateLogs($filepath);
        }
    }

    private static function rotateLogs($filepath)
    {
        $rotatedPath = $filepath . '.' . date('Ymd_His');
        rename($filepath, $rotatedPath);

        // Gzip compress
        if (function_exists('gzencode')) {
            $data = file_get_contents($rotatedPath);
            file_put_contents($rotatedPath . '.gz', gzencode($data, 9));
            unlink($rotatedPath);
        }
    }
}
```

#### B) Error Handler Integration
```php
// public/index.php içine ekle

use App\Helpers\Logger;

// Set exception handler
set_exception_handler(function ($exception) {
    Logger::error('Exception: ' . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);

    if (config('app.debug')) {
        // Development
        echo "<pre>";
        echo "ERROR: " . $exception->getMessage() . "\n";
        echo "File: " . $exception->getFile() . ":" . $exception->getLine() . "\n";
        echo "Trace:\n" . $exception->getTraceAsString();
        echo "</pre>";
    } else {
        // Production
        \App\Helpers\Response::serverError('An error occurred');
    }
});
```

**Tahmini Süre:** 4 saat

---

## 🚀 2. ÖNCELIK 2 - TEMEL ÖZELLIKLER (2-4 Hafta)

### 2.1 e-Fatura Entegrasyonu - 🟠 ÇOK ÖNEMLİ
**Durum:** Henüz yapılmamış
**Önem:** Sistemin en kritik modülü

**Yapılması Gerekenler:**

#### Phase 1: Altyapı (1 hafta)
1. **GİB Test Ortamı Kurulumu**
   - Test kullanıcısı oluşturma
   - API credentials alma
   - Test firması bilgileri

2. **UBL-TR XML Generator**
   ```php
   // app/Services/EFatura/UBLGenerator.php

   namespace App\Services\EFatura;

   class UBLGenerator
   {
       public function generateInvoiceXML($invoice, $company)
       {
           // UBL-TR 2.1 standardına uygun XML oluştur
           // - Invoice Header
           // - Party bilgileri (Supplier/Customer)
           // - Invoice Lines
           // - Tax totals
           // - Legal Monetary Total
       }

       public function validateXML($xml)
       {
           // XML Schema validation
           // Business rules validation
       }
   }
   ```

3. **Mali Mühür Entegrasyonu**
   ```php
   // app/Services/EFatura/SignatureService.php

   namespace App\Services\EFatura;

   class SignatureService
   {
       public function signXML($xml, $certificate, $privateKey)
       {
           // XMLDSig implementation
           // Mali mühür ile imzalama
       }

       public function verifySignature($signedXml)
       {
           // İmza doğrulama
       }
   }
   ```

#### Phase 2: GİB API Entegrasyonu (1 hafta)
```php
// app/Services/EFatura/GIBApiClient.php

namespace App\Services\EFatura;

use GuzzleHttp\Client;

class GIBApiClient
{
    private $client;
    private $baseUrl;
    private $username;
    private $password;

    public function __construct()
    {
        $this->baseUrl = $_ENV['EFATURA_TEST_MODE']
            ? 'https://efaturatest.gib.gov.tr'
            : 'https://efatura.gib.gov.tr';

        $this->username = $_ENV['EFATURA_USERNAME'];
        $this->password = $_ENV['EFATURA_PASSWORD'];

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'verify' => true
        ]);
    }

    public function sendInvoice($signedXml)
    {
        // Fatura gönderimi
    }

    public function getInvoiceStatus($uuid)
    {
        // Fatura durum sorgulama
    }

    public function getInbox($startDate, $endDate)
    {
        // Gelen fatura sorgulama
    }

    public function sendInvoiceResponse($uuid, $status, $note = '')
    {
        // Kabul/Red yanıtı gönder
    }
}
```

#### Phase 3: UI ve İş Akışı (1 hafta)
- e-Fatura gönderme sayfası
- Gelen kutusu
- Giden kutusu
- Kabul/Red işlemleri
- Otomatik senkronizasyon (cron job)

**Kaynaklar:**
- [GİB e-Fatura Portal](https://efatura.gib.gov.tr)
- [UBL-TR Standart Dokümanları](https://efatura.gib.gov.tr/dosyalar/kilavuzlar/UBLTR_1.2.1_Kilavuzlar.zip)
- [e-Fatura Teknik Kılavuz](https://efatura.gib.gov.tr/teknik-kilavuz)

**Tahmini Süre:** 3-4 hafta

---

### 2.2 Dashboard ve Raporlama - 🟠 ÖNEMLİ
**Durum:** Temel dashboard var, detaylı raporlama yok

**Yapılması Gerekenler:**

#### A) Dashboard Widgets
```php
// app/Controllers/Web/DashboardController.php

public function index()
{
    $userId = $_SESSION['user']['id'];
    $companyId = $_SESSION['user']['company_id'];

    $stats = [
        // Finansal Özet
        'revenue' => [
            'this_month' => $this->getMonthlyRevenue($companyId),
            'last_month' => $this->getMonthlyRevenue($companyId, -1),
            'change_percent' => 0
        ],

        // Faturalar
        'invoices' => [
            'total' => $this->invoiceModel->countByCompany($companyId),
            'paid' => $this->invoiceModel->countByStatus($companyId, 'paid'),
            'pending' => $this->invoiceModel->countByStatus($companyId, 'pending'),
            'overdue' => $this->invoiceModel->countOverdue($companyId)
        ],

        // Cari Hesaplar
        'accounts' => [
            'total_receivable' => $this->cariModel->getTotalReceivable($companyId),
            'total_payable' => $this->cariModel->getTotalPayable($companyId),
            'overdue_count' => $this->cariModel->countOverdue($companyId)
        ],

        // Stok Durumu
        'stock' => [
            'low_stock_count' => $this->productModel->countLowStock($companyId),
            'out_of_stock_count' => $this->productModel->countOutOfStock($companyId),
            'total_value' => $this->stockModel->getTotalValue($companyId)
        ],

        // Grafikler için veri
        'charts' => [
            'monthly_revenue' => $this->getMonthlyRevenueChart($companyId, 12),
            'invoice_status_pie' => $this->getInvoiceStatusDistribution($companyId),
            'top_customers' => $this->getTopCustomers($companyId, 5)
        ]
    ];

    $this->render('dashboard/index', [
        'title' => 'Dashboard',
        'stats' => $stats
    ]);
}
```

#### B) Raporlama Modülü
```php
// app/Controllers/Web/ReportController.php

namespace App\Controllers\Web;

class ReportController
{
    // Gelir-Gider Raporu
    public function incomeExpense()
    {
        // Tarih aralığına göre gelir-gider raporu
    }

    // Cari Ekstre
    public function accountStatement($accountId)
    {
        // Cari hesap ekstresi
    }

    // KDV Beyannamesi
    public function vatDeclaration()
    {
        // Aylık KDV raporu
    }

    // Yaşlandırma Raporu
    public function agingReport()
    {
        // Alacak yaşlandırma analizi
    }

    // Kar-Zarar Tablosu
    public function profitLoss()
    {
        // P&L statement
    }

    // Stok Raporu
    public function stockReport()
    {
        // Stok hareket ve değer raporu
    }

    // Excel Export
    public function exportExcel($reportType, $params)
    {
        // CSV/Excel export
    }
}
```

#### C) Grafik Kütüphanesi Entegrasyonu
**Önerilen:** Chart.js veya ApexCharts

```html
<!-- app/Views/dashboard/index.php -->

<!-- Revenue Chart -->
<canvas id="revenueChart"></canvas>

<script>
const ctx = document.getElementById('revenueChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($stats['charts']['monthly_revenue']['labels']) ?>,
        datasets: [{
            label: 'Aylık Ciro',
            data: <?= json_encode($stats['charts']['monthly_revenue']['data']) ?>,
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }]
    }
});
</script>
```

**Tahmini Süre:** 1-2 hafta

---

### 2.3 Ödeme Takip Sistemi - 🟡 ORTA ÖNCELİK
**Durum:** Temel Payment modeli var, tam entegrasyon yok

**Yapılması Gerekenler:**

#### A) Ödeme Modülü Geliştirme
```php
// app/Controllers/Web/PaymentController.php

namespace App\Controllers\Web;

class PaymentController
{
    // Tahsilat kaydı
    public function recordCollection()
    {
        // Müşteriden alınan ödeme
        // - Nakit
        // - Banka transferi
        // - Kredi kartı
        // - Çek/Senet
    }

    // Ödeme kaydı
    public function recordPayment()
    {
        // Tedarikçiye yapılan ödeme
    }

    // Kısmi ödeme
    public function partialPayment($invoiceId, $amount)
    {
        // Faturanın bir kısmının ödenmesi
    }

    // Ödeme planı
    public function paymentSchedule($invoiceId)
    {
        // Taksitli ödeme planı
    }

    // Otomatik eşleştirme
    public function autoMatch()
    {
        // Banka hareketlerini faturalarla eşleştir
    }
}
```

#### B) Banka Entegrasyonu (Opsiyonel)
```php
// app/Services/Bank/BankApiClient.php

namespace App\Services\Bank;

class BankApiClient
{
    // Akbank, Garanti, İş Bankası API'leri
    public function getTransactions($startDate, $endDate)
    {
        // Banka hesap hareketlerini çek
    }

    public function getBalance()
    {
        // Güncel bakiye
    }
}
```

**Tahmini Süre:** 1 hafta

---

## 🔒 3. ÖNCELIK 3 - GÜVENLİK İYİLEŞTİRMELERİ (1 Hafta)

### 3.1 Rate Limiting - 🔴 KRİTİK
**Durum:** Altyapı var ama aktif değil

```php
// app/Middleware/RateLimitMiddleware.php

namespace App\Middleware;

use Predis\Client as Redis;

class RateLimitMiddleware
{
    private $redis;
    private $limit = 60; // 60 requests
    private $window = 60; // per 60 seconds

    public function __construct()
    {
        $this->redis = new Redis([
            'host' => $_ENV['REDIS_HOST'],
            'port' => $_ENV['REDIS_PORT']
        ]);
    }

    public function handle()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = "rate_limit:{$ip}";

        $current = $this->redis->get($key);

        if ($current === null) {
            $this->redis->setex($key, $this->window, 1);
            return;
        }

        if ($current >= $this->limit) {
            header('HTTP/1.1 429 Too Many Requests');
            header('Retry-After: ' . $this->window);
            \App\Helpers\Response::error('Too many requests. Please try again later.', 429);
        }

        $this->redis->incr($key);

        // Add headers
        header("X-RateLimit-Limit: {$this->limit}");
        header("X-RateLimit-Remaining: " . ($this->limit - $current));
    }
}
```

**Route'lara ekle:**
```php
// app/Config/routes.php

$router->group(['prefix' => '/api', 'middleware' => [RateLimitMiddleware::class]], function ($router) {
    // All API routes
});
```

---

### 3.2 CSRF Protection - 🟠 ÖNEMLİ
**Durum:** Middleware var ama tüm formlarda kullanılmıyor

```php
// app/Helpers/CSRF.php

namespace App\Helpers;

class CSRF
{
    public static function generateToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();

        return $token;
    }

    public static function validateToken($token)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        // Token timeout (1 saat)
        if (time() - $_SESSION['csrf_token_time'] > 3600) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function field()
    {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}
```

**Tüm formlara ekle:**
```php
<!-- app/Views/invoices/create.php -->

<form method="POST" action="/fatura/kaydet">
    <?= \App\Helpers\CSRF::field() ?>

    <!-- Form fields -->
</form>
```

---

### 3.3 SQL Injection Prevention Audit - 🟡 ORTA
**Durum:** PDO kullanılıyor ama kontrol edilmeli

**Yapılacaklar:**
1. Tüm raw SQL sorguları gözden geçir
2. Prepared statements kullanıldığından emin ol
3. User input'ları sanitize et

```php
// ❌ KÖTÜ
$sql = "SELECT * FROM users WHERE email = '{$email}'";

// ✅ İYİ
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$email]);
```

---

### 3.4 XSS Protection - 🟡 ORTA

```php
// app/Helpers/Security.php

namespace App\Helpers;

class Security
{
    public static function escape($value)
    {
        if (is_array($value)) {
            return array_map([self::class, 'escape'], $value);
        }

        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeFilename($filename)
    {
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename);
        return $filename;
    }

    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
```

**View'larda kullan:**
```php
<!-- ❌ KÖTÜ -->
<h1><?= $user['name'] ?></h1>

<!-- ✅ İYİ -->
<h1><?= \App\Helpers\Security::escape($user['name']) ?></h1>
```

---

## 🧪 4. ÖNCELIK 4 - TEST VE KALİTE (1-2 Hafta)

### 4.1 Unit Testing - 🟡 ORTA ÖNCELİK

```php
// tests/Unit/UserTest.php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    private $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
    }

    public function testUserCreation()
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'Test123!',
            'full_name' => 'Test User',
            'role' => 'user'
        ];

        $userId = $this->userModel->createUser($data);

        $this->assertNotNull($userId);
        $this->assertIsInt($userId);

        // Cleanup
        $this->userModel->delete($userId);
    }

    public function testEmailValidation()
    {
        $this->assertTrue($this->userModel->emailExists('admin@onmuhasebe.com'));
        $this->assertFalse($this->userModel->emailExists('nonexistent@test.com'));
    }

    public function testPasswordHashing()
    {
        $password = 'Test123!';
        $hash = password_hash($password, PASSWORD_ARGON2I);

        $this->assertTrue(password_verify($password, $hash));
        $this->assertFalse(password_verify('WrongPassword', $hash));
    }
}
```

**Çalıştırma:**
```bash
vendor/bin/phpunit tests/Unit/UserTest.php
```

---

### 4.2 Integration Testing

```php
// tests/Integration/AuthApiTest.php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class AuthApiTest extends TestCase
{
    private $client;
    private $baseUrl = 'http://localhost:8080';

    protected function setUp(): void
    {
        $this->client = new Client(['base_uri' => $this->baseUrl]);
    }

    public function testLoginSuccess()
    {
        $response = $this->client->post('/api/auth/login', [
            'json' => [
                'email' => 'admin@onmuhasebe.com',
                'password' => 'Admin123!'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('tokens', $data['data']);
        $this->assertArrayHasKey('access_token', $data['data']['tokens']);
    }

    public function testLoginFailure()
    {
        $response = $this->client->post('/api/auth/login', [
            'json' => [
                'email' => 'wrong@email.com',
                'password' => 'WrongPass'
            ],
            'http_errors' => false
        ]);

        $this->assertEquals(401, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertFalse($data['success']);
    }
}
```

---

### 4.3 API Dokümantasyonu - 🟠 ÖNEMLİ

**Postman Collection Oluştur:**

```json
{
  "info": {
    "name": "Ön Muhasebe API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Auth",
      "item": [
        {
          "name": "Register",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"full_name\": \"Test User\",\n  \"email\": \"test@example.com\",\n  \"password\": \"Test123!\",\n  \"password_confirmation\": \"Test123!\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/api/auth/register",
              "host": ["{{base_url}}"],
              "path": ["api", "auth", "register"]
            }
          }
        }
      ]
    }
  ]
}
```

**Veya Swagger/OpenAPI kullan:**

```yaml
# docs/api-spec.yaml

openapi: 3.0.0
info:
  title: Ön Muhasebe API
  version: 1.0.0
  description: Modern muhasebe sistemi API dokümantasyonu

servers:
  - url: http://localhost:8080/api
    description: Development server

paths:
  /auth/login:
    post:
      summary: User login
      tags:
        - Authentication
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                email:
                  type: string
                  format: email
                password:
                  type: string
                  format: password
      responses:
        '200':
          description: Login successful
          content:
            application/json:
              schema:
                type: object
                properties:
                  success:
                    type: boolean
                  data:
                    type: object
                    properties:
                      user:
                        $ref: '#/components/schemas/User'
                      tokens:
                        $ref: '#/components/schemas/Tokens'
```

---

## 📱 5. ÖNCELIK 5 - KULLANICI DENEYİMİ (2-3 Hafta)

### 5.1 Responsive Design İyileştirmeleri

**Kontrol Listesi:**
- [ ] Mobil menü çalışıyor mu?
- [ ] Tablolar mobilde scroll yapıyor mu?
- [ ] Formlar mobilde kullanılabilir mi?
- [ ] Dashboard kartları responsive mi?
- [ ] Modallar mobilde düzgün açılıyor mu?

```css
/* public/assets/css/custom.css */

/* Mobil iyileştirmeler */
@media (max-width: 768px) {
    .datatable-wrapper {
        overflow-x: auto;
    }

    .card {
        margin-bottom: 1rem;
    }

    .btn-group {
        flex-direction: column;
    }

    .modal-dialog {
        margin: 0.5rem;
    }
}
```

---

### 5.2 Kullanıcı Bildirimleri

```php
// app/Services/Notification/NotificationService.php

namespace App\Services\Notification;

class NotificationService
{
    // Vade yaklaşan faturalar
    public function sendDueInvoiceReminders()
    {
        $invoices = $this->invoiceModel->getDueSoon(3); // 3 gün içinde

        foreach ($invoices as $invoice) {
            $this->sendEmail(
                $invoice['customer_email'],
                'Vade Hatırlatması',
                "Fatura No: {$invoice['invoice_number']} - Vade: {$invoice['due_date']}"
            );
        }
    }

    // Düşük stok uyarısı
    public function sendLowStockAlerts()
    {
        $products = $this->productModel->getLowStock();

        foreach ($products as $product) {
            $this->sendNotification(
                'admin',
                'Düşük Stok',
                "{$product['name']} ürününde stok azaldı. Mevcut: {$product['stock']}"
            );
        }
    }

    // Email gönderimi
    private function sendEmail($to, $subject, $message)
    {
        // PHPMailer implementation
    }

    // In-app notification
    private function sendNotification($userId, $title, $message)
    {
        // Database'e notification kaydı
    }
}
```

**Cron job ile otomatik çalıştır:**
```php
// scripts/cron/daily-notifications.php

require_once __DIR__ . '/../../vendor/autoload.php';

$notificationService = new \App\Services\Notification\NotificationService();

// Her gün saat 09:00'da çalışsın
$notificationService->sendDueInvoiceReminders();
$notificationService->sendLowStockAlerts();
```

---

### 5.3 Arama ve Filtreleme

```javascript
// public/assets/js/pages/datatable-filter.js

// Advanced DataTable filtering
$(document).ready(function() {
    const table = $('#invoices-table').DataTable({
        ajax: '/api/invoices',
        columns: [
            { data: 'invoice_number' },
            { data: 'customer_name' },
            { data: 'total' },
            { data: 'status' },
            { data: 'due_date' }
        ],
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json'
        }
    });

    // Custom filters
    $('#filter-status').on('change', function() {
        table.column(3).search(this.value).draw();
    });

    $('#filter-date-range').on('change', function() {
        const [start, end] = this.value.split(' - ');
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                const date = new Date(data[4]);
                return date >= new Date(start) && date <= new Date(end);
            }
        );
        table.draw();
    });
});
```

---

## 🚢 6. ÖNCELIK 6 - DEPLOYMENT HAZIRLIĞI (1 Hafta)

### 6.1 Production Ortamı Kontrol Listesi

#### A) .env Configuration
```bash
# .env.production

APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Strong secret keys
JWT_SECRET=<generate-strong-random-key-64-chars>

# Production database
DB_HOST=localhost
DB_DATABASE=onmuhasebe_prod
DB_USERNAME=onmuhasebe_user
DB_PASSWORD=<strong-password>

# HTTPS enforced
SESSION_SECURE=true
SESSION_HTTPONLY=true
```

#### B) Apache VirtualHost
```apache
# /etc/apache2/sites-available/onmuhasebe.conf

<VirtualHost *:80>
    ServerName yourdomain.com

    # Redirect to HTTPS
    Redirect permanent / https://yourdomain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAdmin admin@yourdomain.com

    DocumentRoot /var/www/onmuhasebe/public

    <Directory /var/www/onmuhasebe/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem

    # Security Headers
    Header always set X-Frame-Options "DENY"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

    # Logging
    ErrorLog ${APACHE_LOG_DIR}/onmuhasebe_error.log
    CustomLog ${APACHE_LOG_DIR}/onmuhasebe_access.log combined
</VirtualHost>
```

#### C) Composer Production Install
```bash
composer install --no-dev --optimize-autoloader
```

#### D) File Permissions
```bash
# Owner: www-data (Apache user)
chown -R www-data:www-data /var/www/onmuhasebe

# Directories: 755
find /var/www/onmuhasebe -type d -exec chmod 755 {} \;

# Files: 644
find /var/www/onmuhasebe -type f -exec chmod 644 {} \;

# Writable directories: 775
chmod -R 775 /var/www/onmuhasebe/storage
chmod -R 775 /var/www/onmuhasebe/public/uploads
```

---

### 6.2 SSL Sertifikası (Let's Encrypt)

```bash
# Certbot kurulumu
sudo apt-get update
sudo apt-get install certbot python3-certbot-apache

# SSL sertifikası al
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Otomatik yenileme
sudo certbot renew --dry-run
```

---

### 6.3 Performance Optimization

#### A) PHP Optimization
```ini
; php.ini

; OPcache
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0

; Performance
max_execution_time=300
memory_limit=256M
post_max_size=50M
upload_max_filesize=50M

; Security
expose_php=Off
display_errors=Off
log_errors=On
```

#### B) MySQL Optimization
```sql
-- Slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- Query cache (MySQL 5.7)
SET GLOBAL query_cache_size = 64M;
SET GLOBAL query_cache_type = 1;
```

#### C) Redis Caching
```php
// app/Services/Cache/CacheService.php

namespace App\Services\Cache;

use Predis\Client;

class CacheService
{
    private $redis;
    private $ttl = 3600; // 1 hour

    public function __construct()
    {
        $this->redis = new Client([
            'host' => $_ENV['REDIS_HOST'],
            'port' => $_ENV['REDIS_PORT']
        ]);
    }

    public function get($key)
    {
        $value = $this->redis->get($key);
        return $value ? json_decode($value, true) : null;
    }

    public function set($key, $value, $ttl = null)
    {
        $ttl = $ttl ?? $this->ttl;
        $this->redis->setex($key, $ttl, json_encode($value));
    }

    public function remember($key, $callback, $ttl = null)
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    public function flush()
    {
        $this->redis->flushdb();
    }
}
```

**Kullanım:**
```php
// Dashboard stats caching
$cache = new CacheService();

$stats = $cache->remember('dashboard_stats_' . $companyId, function() use ($companyId) {
    return $this->calculateStats($companyId);
}, 300); // 5 dakika cache
```

---

## 📚 7. DOKÜMANTASYON VE EĞİTİM

### 7.1 Kullanıcı Kılavuzu

```markdown
# Ön Muhasebe Sistemi - Kullanıcı Kılavuzu

## İçindekiler
1. Giriş ve Kayıt
2. Dashboard Kullanımı
3. Cari Hesap Yönetimi
4. Fatura Oluşturma
5. Stok Takibi
6. e-Fatura Gönderimi
7. Raporlar
8. Ayarlar

## 1. Giriş ve Kayıt

### Yeni Hesap Oluşturma
1. [yourdomain.com/kayit](https://yourdomain.com/kayit) adresine gidin
2. Formları doldurun
3. "Kayıt Ol" butonuna tıklayın
4. 30 günlük ücretsiz deneme başlar

### Giriş Yapma
...
```

---

### 7.2 API Dokümantasyonu

**Swagger UI kurulumu:**

```bash
composer require zircote/swagger-php

# Generate API docs
vendor/bin/openapi app -o docs/api-docs.json
```

---

## 📊 8. SONUÇ VE ÖNCELIK SIRASI

### Kısa Vadeli (1-2 Hafta)
1. ✅ **Git Versiyon Kontrolü** - 2 saat
2. ✅ **Backup Sistemi** - 3 saat
3. ✅ **Error Logging** - 4 saat
4. ✅ **Rate Limiting** - 2 saat
5. ✅ **CSRF Protection** - 3 saat

**Toplam: 1 hafta**

### Orta Vadeli (2-4 Hafta)
6. ✅ **e-Fatura Entegrasyonu** - 3-4 hafta
7. ✅ **Dashboard & Raporlama** - 1-2 hafta
8. ✅ **Ödeme Takip** - 1 hafta

**Toplam: 3-4 hafta**

### Uzun Vadeli (1-2 Ay)
9. ✅ **Unit & Integration Tests** - 1 hafta
10. ✅ **UX İyileştirmeleri** - 2 hafta
11. ✅ **Deployment** - 1 hafta
12. ✅ **Dokümantasyon** - 1 hafta

**Toplam: 5-6 hafta**

---

## 🎯 ÖNERİLEN HIZLI AKSIYON PLANI

### Bugün Yapılabilecekler (2-4 Saat)
```bash
# 1. Git başlat
cd C:\xampp\htdocs\onmuhasebe
git init
git add .
git commit -m "Initial commit"

# 2. GitHub'a push
# (Önce GitHub'da repo oluştur)
git remote add origin <repo-url>
git push -u origin main

# 3. Backup script'i kur
php scripts/backup-database.php

# 4. Logger sınıfını implement et
# (Yukarıdaki kodu kopyala)
```

### Bu Hafta Yapılacaklar (5 Gün)
- ✅ Git workflow kurulumu
- ✅ Otomatik backup (daily)
- ✅ Enhanced logging
- ✅ Rate limiting aktif
- ✅ CSRF tüm formlarda

### Gelecek Ay
- ✅ e-Fatura Phase 1-3
- ✅ Dashboard geliştirme
- ✅ Raporlama modülü

---

## 💰 MALİYET TAHMİNİ

### Geliştirme Maliyeti
- **Kısa Vadeli:** 40 saat × ₺500/saat = ₺20,000
- **Orta Vadeli:** 160 saat × ₺500/saat = ₺80,000
- **Uzun Vadeli:** 200 saat × ₺500/saat = ₺100,000

**Toplam:** ₺200,000 (400 saat)

### Sunucu Maliyeti (Aylık)
- VPS (4 CPU, 8GB RAM): ₺1,500/ay
- SSL Sertifikası: Ücretsiz (Let's Encrypt)
- Domain: ₺100/yıl
- Backup Storage: ₺300/ay

**Toplam:** ~₺2,000/ay

---

## 📞 DESTEK VE KAYNAKLAR

### Resmi Dokümantasyonlar
- [PHP Manual](https://www.php.net/manual/tr/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Metronic 8 Docs](https://preview.keenthemes.com/metronic8/demo1/documentation/)
- [GİB e-Fatura](https://efatura.gib.gov.tr)

### Topluluklar
- [PHP Türkiye](https://www.facebook.com/groups/phpturkiye)
- [Stack Overflow](https://stackoverflow.com/questions/tagged/php)

---

**Son Güncelleme:** 15 Ekim 2025
**Hazırlayan:** Claude AI Assistant
**Versiyon:** 1.0

---

## ✅ HIZLI KONTROL LİSTESİ

### Bugün
- [ ] Git repository başlat
- [ ] İlk commit yap
- [ ] GitHub'a push et
- [ ] Backup script test et

### Bu Hafta
- [ ] Logger implement et
- [ ] Rate limiting aktif et
- [ ] CSRF tüm formlara ekle
- [ ] Otomatik backup kur

### Bu Ay
- [ ] e-Fatura Phase 1
- [ ] Dashboard geliştir
- [ ] Unit testler yaz

### Bu Çeyrek
- [ ] e-Fatura tamamla
- [ ] Tüm modüller test et
- [ ] Production'a deploy et
- [ ] Kullanıcı dokümantasyonu

---

**Not:** Bu öneriler proje durumuna göre önceliklendirilmiştir. İhtiyaçlarınıza göre sıralamayı değiştirebilirsiniz.

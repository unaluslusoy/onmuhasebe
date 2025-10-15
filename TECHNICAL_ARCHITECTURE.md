# 🏗️ TEKNİK MİMARİ DOKÜMANTASYON

**Proje:** Ön Muhasebe Sistemi  
**Mimari Pattern:** MVC + Service Layer  
**API Type:** RESTful  
**Auth:** JWT Bearer Token  

---

## 📐 SİSTEM MİMARİSİ

### Katmanlı Mimari (Layered Architecture)

```
┌─────────────────────────────────────────────────────┐
│         PRESENTATION LAYER (UI)                     │
│   Metronic 8 + Vanilla JS/jQuery + AJAX            │
│   - Forms, DataTables, Charts, Modals              │
└────────────────┬────────────────────────────────────┘
                 │ HTTP/AJAX
┌────────────────▼────────────────────────────────────┐
│         APPLICATION LAYER (API Gateway)             │
│   RESTful API Endpoints + Routing                  │
│   - JWT Middleware                                  │
│   - CSRF Middleware                                 │
│   - Rate Limiting                                   │
└────────────────┬────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────┐
│         BUSINESS LOGIC LAYER                        │
│   Controllers + Services                            │
│   - Validation                                      │
│   - Business Rules                                  │
│   - Transaction Management                          │
└────────────────┬────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────┐
│         DATA ACCESS LAYER                           │
│   Models (Repository Pattern)                       │
│   - PDO Prepared Statements                         │
│   - Query Builder                                   │
└────────────────┬────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────┐
│         DATABASE LAYER                              │
│   MySQL 8.0 (InnoDB)                               │
│   - Transactions (ACID)                             │
│   - Foreign Keys                                    │
│   - Indexes                                         │
└─────────────────────────────────────────────────────┘
```

---

## 🗂️ PROJE YAPISININ DETAYI

### Dizin Ağacı (Genişletilmiş)

```
/onmuhasebe/
│
├── /public/                          # Web root (document root)
│   ├── index.php                    # Ana router ve bootstrap
│   ├── .htaccess                    # Apache rewrite rules
│   │
│   ├── /assets/
│   │   ├── /admin/                  # Admin panel assets
│   │   │   ├── /css/
│   │   │   │   ├── style.bundle.css
│   │   │   │   └── custom.css
│   │   │   ├── /js/
│   │   │   │   ├── scripts.bundle.js
│   │   │   │   └── /pages/
│   │   │   │       ├── auth.js
│   │   │   │       ├── fatura.js
│   │   │   │       ├── cari.js
│   │   │   │       └── dashboard.js
│   │   │   └── /media/
│   │   │       ├── /logos/
│   │   │       └── /icons/
│   │   │
│   │   └── /plugins/                # Metronic plugins
│   │       ├── /global/
│   │       └── /custom/
│   │
│   └── /uploads/                    # User uploads
│       ├── /invoices/               # Fatura PDF'leri
│       ├── /logos/                  # Şirket logoları
│       ├── /certificates/           # Mali mühürler
│       └── /documents/              # Diğer belgeler
│
├── /app/
│   ├── /Controllers/                # İş mantığı kontrolörleri
│   │   ├── /Auth/
│   │   │   ├── AuthController.php
│   │   │   ├── PasswordController.php
│   │   │   └── TwoFactorController.php
│   │   │
│   │   ├── /Admin/                  # Admin panel controllers
│   │   │   ├── DashboardController.php
│   │   │   ├── CompanyController.php
│   │   │   ├── CariController.php
│   │   │   ├── ProductController.php
│   │   │   ├── StockController.php
│   │   │   ├── TeklifController.php
│   │   │   ├── FaturaController.php
│   │   │   ├── EFaturaController.php
│   │   │   ├── PaymentController.php
│   │   │   ├── BankController.php
│   │   │   ├── CheckController.php
│   │   │   ├── ExpenseController.php
│   │   │   ├── PersonnelController.php
│   │   │   ├── ReportController.php
│   │   │   └── NotificationController.php
│   │   │
│   │   └── /Api/                    # API controllers (v1)
│   │       ├── AuthApiController.php
│   │       └── ...
│   │
│   ├── /Models/                     # Veritabanı modelleri
│   │   ├── User.php
│   │   ├── Company.php
│   │   ├── CariAccount.php
│   │   ├── Product.php
│   │   ├── ProductCategory.php
│   │   ├── Warehouse.php
│   │   ├── WarehouseLocation.php
│   │   ├── StockMovement.php
│   │   ├── Teklif.php
│   │   ├── TeklifItem.php
│   │   ├── Fatura.php
│   │   ├── FaturaItem.php
│   │   ├── EFaturaSetting.php
│   │   ├── EFaturaInbox.php
│   │   ├── EFaturaOutbox.php
│   │   ├── Payment.php
│   │   ├── BankAccount.php
│   │   ├── BankTransaction.php
│   │   ├── Check.php
│   │   ├── PromissoryNote.php
│   │   ├── Expense.php
│   │   ├── Personnel.php
│   │   └── Notification.php
│   │
│   ├── /Services/                   # İş mantığı servisleri
│   │   ├── /Auth/
│   │   │   ├── JWTService.php
│   │   │   ├── PasswordService.php
│   │   │   └── TwoFactorService.php
│   │   │
│   │   ├── /Fatura/
│   │   │   ├── FaturaService.php
│   │   │   ├── FaturaCalculationService.php
│   │   │   └── FaturaPDFService.php
│   │   │
│   │   ├── /EFatura/
│   │   │   ├── EFaturaService.php
│   │   │   ├── UBLXMLGenerator.php
│   │   │   ├── SignatureService.php
│   │   │   └── GIBApiClient.php
│   │   │
│   │   ├── /Stock/
│   │   │   ├── StockService.php
│   │   │   ├── StockValuationService.php
│   │   │   └── StockTransferService.php
│   │   │
│   │   ├── /Report/
│   │   │   ├── ReportService.php
│   │   │   ├── DashboardService.php
│   │   │   └── ExportService.php
│   │   │
│   │   ├── /Notification/
│   │   │   ├── NotificationService.php
│   │   │   ├── EmailService.php
│   │   │   └── SMSService.php
│   │   │
│   │   └── /Payment/
│   │       ├── PaymentService.php
│   │       └── ReconciliationService.php
│   │
│   ├── /Repositories/               # Data access (opsiyonel)
│   │   ├── UserRepository.php
│   │   ├── FaturaRepository.php
│   │   └── ...
│   │
│   ├── /Middleware/                 # Request/Response middleware
│   │   ├── AuthMiddleware.php       # JWT doğrulama
│   │   ├── AdminAuthMiddleware.php  # Admin rol kontrolü
│   │   ├── CsrfMiddleware.php       # CSRF token
│   │   ├── RateLimitMiddleware.php  # Rate limiting
│   │   ├── LoggingMiddleware.php    # Request/response log
│   │   └── CorsMiddleware.php       # CORS headers
│   │
│   ├── /Helpers/                    # Yardımcı fonksiyonlar
│   │   ├── Validator.php            # Input validation
│   │   ├── DateHelper.php           # Tarih formatları
│   │   ├── NumberHelper.php         # Para ve sayı formatları
│   │   ├── FileUploader.php         # Dosya yükleme
│   │   ├── ImageResizer.php         # Resim işleme
│   │   ├── PdfGenerator.php         # PDF oluşturma
│   │   ├── ExcelExporter.php        # Excel export
│   │   ├── StringHelper.php         # String işlemleri
│   │   └── Response.php             # JSON response helper
│   │
│   ├── /Exceptions/                 # Custom exceptions
│   │   ├── ValidationException.php
│   │   ├── AuthenticationException.php
│   │   ├── AuthorizationException.php
│   │   └── BusinessLogicException.php
│   │
│   └── /Config/                     # Yapılandırma dosyaları
│       ├── database.php             # DB bağlantı
│       ├── app.php                  # Uygulama ayarları
│       ├── routes.php               # Route tanımları
│       ├── mail.php                 # Mail ayarları
│       ├── cache.php                # Cache ayarları
│       └── services.php             # Service bindings
│
├── /database/
│   ├── /migrations/                 # Veritabanı şema değişiklikleri
│   │   ├── 001_users_and_auth.sql
│   │   ├── 002_companies.sql
│   │   ├── 003_cari_accounts.sql
│   │   ├── 004_products.sql
│   │   ├── 005_warehouses_and_stock.sql
│   │   ├── 006_teklifler.sql
│   │   ├── 007_faturalar.sql
│   │   ├── 008_efatura.sql
│   │   ├── 009_payments.sql
│   │   ├── 010_bank_accounts.sql
│   │   ├── 011_checks_promissory.sql
│   │   ├── 012_expenses.sql
│   │   ├── 013_personnel.sql
│   │   ├── 014_notifications.sql
│   │   └── 015_indexes_and_optimization.sql
│   │
│   └── /seeds/                      # Örnek veri
│       ├── users_seed.sql
│       ├── companies_seed.sql
│       └── demo_data_seed.sql
│
├── /storage/                        # Depolama (write permission)
│   ├── /logs/                       # Log dosyaları
│   │   ├── app.log
│   │   ├── error.log
│   │   ├── api.log
│   │   └── efatura.log
│   │
│   ├── /cache/                      # File-based cache
│   │   ├── /views/
│   │   └── /data/
│   │
│   └── /temp/                       # Geçici dosyalar
│       ├── /uploads/
│       └── /exports/
│
├── /tests/                          # Test dosyaları
│   ├── /Unit/
│   │   ├── UserTest.php
│   │   ├── JWTServiceTest.php
│   │   └── ...
│   │
│   └── /Integration/
│       ├── AuthApiTest.php
│       ├── FaturaApiTest.php
│       └── ...
│
├── /scripts/                        # Maintenance ve cron scripts
│   ├── /cron/
│   │   ├── efatura-sync.php         # e-Fatura senkronizasyon
│   │   ├── notification-sender.php  # Bildirim gönderimi
│   │   └── backup-database.php      # Veritabanı yedekleme
│   │
│   └── /cli/
│       ├── migrate.php              # Migration çalıştırma
│       └── cache-clear.php          # Cache temizleme
│
├── /docs/                           # Dokümantasyon
│   ├── API_DOCUMENTATION.md
│   ├── USER_GUIDE.md
│   ├── INSTALLATION.md
│   └── DEPLOYMENT.md
│
├── /vendor/                         # Composer bağımlılıkları
│
├── composer.json                    # PHP bağımlılıkları
├── composer.lock
├── .env.example                     # Örnek environment dosyası
├── .env                             # Environment variables (gitignore)
├── .gitignore
├── .htaccess                        # Root htaccess
└── README.md                        # Proje açıklaması
```

---

## 🔧 CORE COMPONENT'LER

### 1. Router (index.php)

```php
<?php
// public/index.php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Helpers\Response;

// Environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Error handling
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// Database connection
$db = Database::getConnection();

// Router
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string
$uri = parse_url($requestUri, PHP_URL_PATH);

// Route matching
$routes = require __DIR__ . '/../app/Config/routes.php';

foreach ($routes as $route) {
    if ($route['method'] === $requestMethod && preg_match($route['pattern'], $uri, $matches)) {
        // Apply middleware
        foreach ($route['middleware'] ?? [] as $middleware) {
            $middlewareClass = "App\\Middleware\\{$middleware}";
            $middlewareInstance = new $middlewareClass();
            $middlewareInstance->handle();
        }
        
        // Execute controller
        [$controller, $method] = explode('@', $route['action']);
        $controllerClass = "App\\Controllers\\{$controller}";
        $controllerInstance = new $controllerClass($db);
        
        // Extract route parameters
        array_shift($matches); // Remove full match
        
        // Call controller method
        echo $controllerInstance->$method(...$matches);
        exit;
    }
}

// 404 Not Found
Response::json(['error' => 'Route not found'], 404);
```

### 2. Database Connection

```php
<?php
// app/Config/database.php

namespace App\Config;

class Database {
    private static $connection = null;
    
    public static function getConnection() {
        if (self::$connection === null) {
            $host = $_ENV['DB_HOST'];
            $dbname = $_ENV['DB_NAME'];
            $username = $_ENV['DB_USERNAME'];
            $password = $_ENV['DB_PASSWORD'];
            
            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
            
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            try {
                self::$connection = new \PDO($dsn, $username, $password, $options);
            } catch (\PDOException $e) {
                die('Database connection failed: ' . $e->getMessage());
            }
        }
        
        return self::$connection;
    }
    
    public static function beginTransaction() {
        return self::getConnection()->beginTransaction();
    }
    
    public static function commit() {
        return self::getConnection()->commit();
    }
    
    public static function rollback() {
        return self::getConnection()->rollBack();
    }
}
```

### 3. Base Model (Abstract)

```php
<?php
// app/Models/BaseModel.php

namespace App\Models;

abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Find by ID
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all records
     */
    public function all($orderBy = null) {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Insert record
     */
    public function insert($data) {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update record
     */
    public function update($id, $data) {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = ?";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " 
                WHERE {$this->primaryKey} = ?";
        
        $values = array_values($data);
        $values[] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Delete record
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Paginate
     */
    public function paginate($page = 1, $perPage = 20, $where = [], $orderBy = null) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table}";
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        
        $params = [];
        
        if (!empty($where)) {
            $whereParts = [];
            foreach ($where as $column => $value) {
                $whereParts[] = "{$column} = ?";
                $params[] = $value;
            }
            $whereClause = " WHERE " . implode(' AND ', $whereParts);
            $sql .= $whereClause;
            $countSql .= $whereClause;
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        $sql .= " LIMIT ? OFFSET ?";
        
        // Get total count
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        // Get paginated data
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage)
        ];
    }
}
```

### 4. JWT Service

```php
<?php
// app/Services/Auth/JWTService.php

namespace App\Services\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService {
    private $secretKey;
    private $algorithm = 'HS256';
    private $accessTokenExpiry = 3600; // 1 saat
    private $refreshTokenExpiry = 2592000; // 30 gün
    
    public function __construct() {
        $this->secretKey = $_ENV['JWT_SECRET_KEY'];
    }
    
    /**
     * Generate access token
     */
    public function generateAccessToken($userId, $email, $role) {
        $issuedAt = time();
        $expire = $issuedAt + $this->accessTokenExpiry;
        
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'sub' => $userId,
            'email' => $email,
            'role' => $role
        ];
        
        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }
    
    /**
     * Generate refresh token
     */
    public function generateRefreshToken($userId) {
        $issuedAt = time();
        $expire = $issuedAt + $this->refreshTokenExpiry;
        
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'sub' => $userId,
            'type' => 'refresh'
        ];
        
        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }
    
    /**
     * Verify and decode token
     */
    public function verifyToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return (array) $decoded;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get token from header
     */
    public function getTokenFromHeader() {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];
            
            if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
}
```

### 5. Response Helper

```php
<?php
// app/Helpers/Response.php

namespace App\Helpers;

class Response {
    /**
     * JSON response
     */
    public static function json($data, $statusCode = 200, $headers = []) {
        http_response_code($statusCode);
        
        // Default headers
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        
        // Custom headers
        foreach ($headers as $key => $value) {
            header("{$key}: {$value}");
        }
        
        // Add metadata
        if (!isset($data['meta'])) {
            $data['meta'] = [
                'timestamp' => date('c'),
                'version' => '1.0.0'
            ];
        }
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Success response
     */
    public static function success($data = null, $message = 'İşlem başarılı', $statusCode = 200) {
        return self::json([
            'success' => true,
            'data' => $data,
            'message' => $message
        ], $statusCode);
    }
    
    /**
     * Error response
     */
    public static function error($message, $errors = [], $statusCode = 400) {
        return self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
}
```

---

## 📝 SONUÇ

Bu dokümantasyon:
- ✅ Katmanlı mimari yapısı
- ✅ Tam dizin ağacı (100+ dosya)
- ✅ Core component'lerin implementasyonu
- ✅ BaseModel abstract class
- ✅ JWT Service
- ✅ Response Helper

**Sonraki:** Database migration dosyalarını oluşturalım mı? 🗄️

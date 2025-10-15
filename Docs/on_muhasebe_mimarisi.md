# Ön Muhasebe Sistemi - Mimari Dokümantasyon

## 1. Sistem Genel Bakış

Modern, ölçeklenebilir ve güvenli bir ön muhasebe sistemi mimarisi. Bu sistem, KOBİ'lerin temel muhasebe ihtiyaçlarını karşılamak üzere tasarlanmıştır.

### Temel Özellikler
- **Çoklu Kullanıcı Desteği** (Rol bazlı yetkilendirme)
- **Fatura ve Makbuz Yönetimi**
- **Cari Hesap Takibi**
- **Gelir-Gider Yönetimi**
- **Banka Hesap Entegrasyonu**
- **Stok Takibi**
- **Raporlama ve Analiz**
- **e-Fatura/e-Arşiv Entegrasyonu**

---

## 2. Teknoloji Stack

### Backend
- **PHP 8.2+** (OOP, PDO, Namespace kullanımı)
- **MySQL 8.0** / MariaDB 10.6+
- **RESTful API** mimarisi
- **JWT Authentication** (Token bazlı kimlik doğrulama)
- **Composer** (Bağımlılık yönetimi)

### Frontend
- **HTML5 & CSS3**
- **JavaScript (ES6+)**
- **AJAX** (Asenkron işlemler)
- **Bootstrap 5** (Responsive tasarım)
- **Chart.js** (Grafik ve raporlama)
- **jQuery** (DOM manipülasyonu - opsiyonel)

### Güvenlik
- **HTTPS/SSL** (Zorunlu)
- **Password Hashing** (bcrypt/Argon2)
- **CSRF Token Protection**
- **SQL Injection Prevention** (Prepared Statements)
- **XSS Protection**
- **Rate Limiting**

---

## 3. Mimari Yapı

### 3.1 Katmanlı Mimari (Layered Architecture)

```
┌─────────────────────────────────────┐
│     Presentation Layer (UI)         │
│   (HTML, CSS, JS, AJAX)             │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│     Application Layer (API)         │
│   (RESTful API Endpoints)           │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│     Business Logic Layer            │
│   (Controllers & Services)          │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│     Data Access Layer               │
│   (Models & Database)               │
└─────────────────────────────────────┘
```

### 3.2 Proje Dizin Yapısı

```
/on-muhasebe/
│
├── /public/                    # Web root (Erişilebilir dizin)
│   ├── index.php              # Ana giriş noktası
│   ├── .htaccess              # URL rewriting
│   ├── /assets/
│   │   ├── /css/
│   │   ├── /js/
│   │   └── /images/
│   └── /uploads/              # Fatura/makbuz yüklemeleri
│
├── /app/
│   ├── /Controllers/          # İş mantığı kontrolörleri
│   │   ├── AuthController.php
│   │   ├── FaturaController.php
│   │   ├── CariController.php
│   │   ├── GelirGiderController.php
│   │   └── RaporController.php
│   │
│   ├── /Models/               # Veritabanı modelleri
│   │   ├── User.php
│   │   ├── Fatura.php
│   │   ├── Cari.php
│   │   ├── Gelir.php
│   │   └── Gider.php
│   │
│   ├── /Services/             # İş mantığı servisleri
│   │   ├── FaturaService.php
│   │   ├── CariService.php
│   │   └── RaporService.php
│   │
│   ├── /Middleware/           # Güvenlik ve doğrulama
│   │   ├── AuthMiddleware.php
│   │   └── CsrfMiddleware.php
│   │
│   ├── /Helpers/              # Yardımcı fonksiyonlar
│   │   ├── Validator.php
│   │   ├── DateHelper.php
│   │   └── NumberHelper.php
│   │
│   └── /Config/               # Yapılandırma dosyaları
│       ├── database.php
│       ├── app.php
│       └── routes.php
│
├── /database/
│   ├── /migrations/           # Veritabanı şema değişiklikleri
│   └── /seeds/                # Örnek veri
│
├── /vendor/                   # Composer bağımlılıkları
├── composer.json
└── .env                       # Çevre değişkenleri
```

---

## 4. Veritabanı Şeması

### 4.1 Temel Tablolar

#### users (Kullanıcılar)
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'muhasebeci', 'kullanici') DEFAULT 'kullanici',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
);
```

#### companies (Şirket Bilgileri)
```sql
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(200) NOT NULL,
    tax_office VARCHAR(100),
    tax_number VARCHAR(20),
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### cari_accounts (Cari Hesaplar)
```sql
CREATE TABLE cari_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cari_code VARCHAR(50) UNIQUE NOT NULL,
    cari_name VARCHAR(200) NOT NULL,
    cari_type ENUM('musteri', 'tedarikci', 'her_ikisi') NOT NULL,
    tax_office VARCHAR(100),
    tax_number VARCHAR(20),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    opening_balance DECIMAL(15,2) DEFAULT 0.00,
    current_balance DECIMAL(15,2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_cari_code (cari_code),
    INDEX idx_cari_type (cari_type)
);
```

#### faturalar (Faturalar)
```sql
CREATE TABLE faturalar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cari_id INT NOT NULL,
    fatura_no VARCHAR(50) NOT NULL,
    fatura_type ENUM('alis', 'satis') NOT NULL,
    fatura_date DATE NOT NULL,
    due_date DATE,
    total_amount DECIMAL(15,2) NOT NULL,
    kdv_amount DECIMAL(15,2) DEFAULT 0.00,
    grand_total DECIMAL(15,2) NOT NULL,
    currency ENUM('TRY', 'USD', 'EUR') DEFAULT 'TRY',
    exchange_rate DECIMAL(10,4) DEFAULT 1.0000,
    payment_status ENUM('odenmedi', 'kismi', 'odendi') DEFAULT 'odenmedi',
    paid_amount DECIMAL(15,2) DEFAULT 0.00,
    notes TEXT,
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (cari_id) REFERENCES cari_accounts(id) ON DELETE RESTRICT,
    INDEX idx_fatura_no (fatura_no),
    INDEX idx_fatura_date (fatura_date),
    INDEX idx_payment_status (payment_status)
);
```

#### fatura_items (Fatura Kalemleri)
```sql
CREATE TABLE fatura_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fatura_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    kdv_rate DECIMAL(5,2) DEFAULT 18.00,
    kdv_amount DECIMAL(15,2) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (fatura_id) REFERENCES faturalar(id) ON DELETE CASCADE
);
```

#### gelir_gider (Gelir-Gider)
```sql
CREATE TABLE gelir_gider (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    transaction_type ENUM('gelir', 'gider') NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    transaction_date DATE NOT NULL,
    payment_method ENUM('nakit', 'banka', 'kredi_karti', 'cek') NOT NULL,
    cari_id INT NULL,
    fatura_id INT NULL,
    receipt_no VARCHAR(50),
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (cari_id) REFERENCES cari_accounts(id) ON DELETE SET NULL,
    FOREIGN KEY (fatura_id) REFERENCES faturalar(id) ON DELETE SET NULL,
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_category (category)
);
```

#### bank_accounts (Banka Hesapları)
```sql
CREATE TABLE bank_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bank_name VARCHAR(100) NOT NULL,
    account_name VARCHAR(100) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    iban VARCHAR(34),
    currency ENUM('TRY', 'USD', 'EUR') DEFAULT 'TRY',
    balance DECIMAL(15,2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### payments (Ödemeler)
```sql
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    fatura_id INT NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_method ENUM('nakit', 'banka', 'kredi_karti', 'cek') NOT NULL,
    bank_account_id INT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (fatura_id) REFERENCES faturalar(id) ON DELETE CASCADE,
    FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id) ON DELETE SET NULL
);
```

---

## 5. API Endpoints

### 5.1 Kimlik Doğrulama (Authentication)

```
POST   /api/auth/register     - Kullanıcı kaydı
POST   /api/auth/login        - Giriş yapma (JWT token)
POST   /api/auth/logout       - Çıkış yapma
GET    /api/auth/profile      - Kullanıcı profili
PUT    /api/auth/profile      - Profil güncelleme
POST   /api/auth/change-password - Şifre değiştirme
```

### 5.2 Cari Hesaplar

```
GET    /api/cari              - Tüm cariler
GET    /api/cari/{id}         - Tek cari detayı
POST   /api/cari              - Yeni cari ekleme
PUT    /api/cari/{id}         - Cari güncelleme
DELETE /api/cari/{id}         - Cari silme
GET    /api/cari/{id}/bakiye  - Cari bakiye
GET    /api/cari/{id}/ekstre  - Cari ekstre
```

### 5.3 Faturalar

```
GET    /api/fatura            - Tüm faturalar (filtreleme ile)
GET    /api/fatura/{id}       - Fatura detayı
POST   /api/fatura            - Yeni fatura
PUT    /api/fatura/{id}       - Fatura güncelleme
DELETE /api/fatura/{id}       - Fatura silme
POST   /api/fatura/{id}/upload - Fatura dosya yükleme
GET    /api/fatura/bekleyen   - Ödenmemiş faturalar
```

### 5.4 Gelir-Gider

```
GET    /api/gelir-gider       - Gelir-gider listesi
GET    /api/gelir-gider/{id}  - Detay
POST   /api/gelir-gider       - Yeni kayıt
PUT    /api/gelir-gider/{id}  - Güncelleme
DELETE /api/gelir-gider/{id}  - Silme
```

### 5.5 Raporlar

```
GET    /api/rapor/gelir-gider-ozet   - Gelir-gider özeti
GET    /api/rapor/cari-bakiye        - Cari bakiye raporu
GET    /api/rapor/fatura-durum       - Fatura durum raporu
GET    /api/rapor/aylik-kar-zarar    - Aylık kar-zarar
GET    /api/rapor/kdv-beyan          - KDV beyan raporu
```

---

## 6. Güvenlik Önlemleri

### 6.1 Kimlik Doğrulama
```php
// JWT Token örneği
class AuthMiddleware {
    public function validate() {
        $token = $this->getBearerToken();
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Token gerekli']);
            exit;
        }
        
        try {
            $decoded = JWT::decode($token, SECRET_KEY);
            return $decoded->user_id;
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Geçersiz token']);
            exit;
        }
    }
}
```

### 6.2 SQL Injection Koruması
```php
// PDO Prepared Statements
class Database {
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

// Kullanım
$db->query(
    "SELECT * FROM faturalar WHERE user_id = ? AND fatura_date >= ?",
    [$userId, $date]
);
```

### 6.3 CSRF Koruması
```php
// CSRF Token oluşturma
class CsrfMiddleware {
    public static function generateToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function validateToken($token) {
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
```

---

## 7. Frontend - Backend İletişimi (AJAX)

### 7.1 Fatura Ekleme Örneği

```javascript
// JavaScript (Frontend)
async function faturaEkle(faturaData) {
    try {
        const response = await fetch('/api/fatura', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'X-CSRF-Token': getCsrfToken()
            },
            body: JSON.stringify(faturaData)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            showSuccess('Fatura başarıyla eklendi!');
            loadFaturalar();
        } else {
            showError(result.error || 'Bir hata oluştu');
        }
    } catch (error) {
        showError('Sunucu hatası: ' + error.message);
    }
}
```

```php
// PHP (Backend)
class FaturaController {
    public function create() {
        // Kimlik doğrulama
        $userId = AuthMiddleware::validate();
        
        // Veri alma ve validasyon
        $data = json_decode(file_get_contents('php://input'), true);
        $validator = new Validator($data, [
            'cari_id' => 'required|integer',
            'fatura_no' => 'required|unique:faturalar',
            'fatura_type' => 'required|in:alis,satis',
            'grand_total' => 'required|numeric|min:0'
        ]);
        
        if ($validator->fails()) {
            http_response_code(400);
            echo json_encode(['error' => $validator->errors()]);
            return;
        }
        
        // Fatura kaydetme
        $faturaService = new FaturaService();
        $fatura = $faturaService->create($userId, $data);
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'data' => $fatura
        ]);
    }
}
```

---

## 8. Performans Optimizasyonu

### 8.1 Veritabanı İndeksleme
- Sık sorgulanan alanlar için index
- Foreign key ilişkileri optimize edilmeli
- Composite index kullanımı (çoklu kolon sorguları için)

### 8.2 Caching Stratejisi
```php
// Redis/Memcached kullanımı
class CacheService {
    public function remember($key, $ttl, $callback) {
        $cached = $this->get($key);
        if ($cached !== null) {
            return $cached;
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }
}

// Kullanım
$raporlar = $cache->remember('aylik_rapor_' . $month, 3600, function() {
    return $this->raporService->getAylikRapor($month);
});
```

### 8.3 Lazy Loading
```javascript
// Sayfalama ve lazy loading
const loadMoreFaturalar = async (page = 1) => {
    const response = await fetch(`/api/fatura?page=${page}&limit=20`);
    const data = await response.json();
    appendFaturaTable(data.items);
};
```

---

## 9. Dağıtım ve DevOps

### 9.1 Sunucu Gereksinimleri
- **PHP:** 8.2+
- **MySQL:** 8.0+
- **Web Server:** Apache 2.4+ / Nginx 1.18+
- **SSL Sertifikası:** Let's Encrypt (Ücretsiz)
- **RAM:** Minimum 2GB
- **Disk:** 20GB+ (Log ve yedekler için)

### 9.2 Yedekleme Stratejisi
```bash
# Günlük otomatik yedek (cron job)
0 2 * * * mysqldump -u user -p database > /backups/db_$(date +\%Y\%m\%d).sql
0 3 * * * tar -czf /backups/files_$(date +\%Y\%m\%d).tar.gz /var/www/html/uploads/
```

### 9.3 Monitoring
- **Error Logging:** PHP error_log + custom logger
- **Performance Monitoring:** New Relic / Datadog (opsiyonel)
- **Uptime Monitoring:** UptimeRobot / Pingdom

---

## 10. Gelecek Geliştirmeler

### Faz 2
- **e-Fatura/e-Arşiv entegrasyonu** (GİB API)
- **Mobil uygulama** (React Native / Flutter)
- **Çoklu dil desteği**
- **Gelişmiş raporlama** (PDF/Excel export)

### Faz 3
- **Stok yönetim modülü**
- **Bordro sistemi entegrasyonu**
- **Muhasebe entegrasyonu** (XML export)
- **Çoklu şirket desteği**

---

## Sonuç

Bu mimari, **ölçeklenebilir, güvenli ve modern** bir ön muhasebe sistemi için sağlam bir temel oluşturur. PHP 8+ özellikleri, RESTful API tasarımı ve katmanlı mimari sayesinde bakımı kolay ve genişletilebilir bir yapı sunar.

**Önemli:** Gerçek üretim ortamına geçmeden önce:
- ✅ Güvenlik testleri yapılmalı
- ✅ Performans testleri gerçekleştirilmeli
- ✅ Yedekleme stratejisi oluşturulmalı
- ✅ SSL sertifikası kurulmalı
- ✅ Yasal uyumluluk kontrol edilmeli

---

**Mimari Versiyonu:** 1.0  
**Son Güncelleme:** Eylül 2025  
**Hazırlayan:** Claude & Ünal
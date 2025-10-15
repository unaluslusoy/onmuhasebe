# Modül 2: Company Management (Şirket Yönetimi)

## 📋 Modül Özeti

Şirket bilgilerini yöneten ve e-fatura için gerekli tüm kurumsal bilgileri tutan modül. Her kullanıcı bir veya birden fazla şirket bilgisi kaydedebilir.

### Özellikler
- ✅ Şirket bilgileri yönetimi
- ✅ Logo yükleme
- ✅ Vergi dairesi ve vergi numarası
- ✅ e-Fatura kullanıcı bilgileri
- ✅ Şirket adresi ve iletişim bilgileri
- ✅ Mersis ve ticaret sicil numarası
- ✅ Çoklu şirket desteği (gelecek için)

---

## 🗄️ Veritabanı Tablosu

```sql
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    
    -- Temel bilgiler
    company_name VARCHAR(200) NOT NULL,
    company_title VARCHAR(250) COMMENT 'Tam ticari ünvan',
    company_type ENUM('sahis', 'limited', 'anonim', 'komandit', 'kollektif') DEFAULT 'sahis',
    
    -- Yasal bilgiler
    tax_office VARCHAR(100),
    tax_number VARCHAR(20),
    trade_registry_no VARCHAR(50),
    mersis_no VARCHAR(16),
    
    -- İletişim bilgileri
    email VARCHAR(100),
    phone VARCHAR(20),
    fax VARCHAR(20),
    website VARCHAR(200),
    
    -- Adres bilgileri
    address TEXT,
    district VARCHAR(50),
    city VARCHAR(50),
    postal_code VARCHAR(10),
    country VARCHAR(50) DEFAULT 'Türkiye',
    
    -- Logo
    logo_path VARCHAR(255),
    logo_updated_at TIMESTAMP NULL,
    
    -- e-Fatura bilgileri
    is_efatura_user BOOLEAN DEFAULT FALSE,
    efatura_gb_username VARCHAR(100) COMMENT 'GİB entegratör kullanıcı adı',
    efatura_gb_password VARCHAR(255) COMMENT 'Encrypted',
    efatura_alias VARCHAR(100) COMMENT 'e-Fatura alias',
    efatura_activation_date DATE,
    
    -- Mali mühür bilgileri
    tax_certificate_path VARCHAR(255),
    tax_certificate_password VARCHAR(255) COMMENT 'Encrypted',
    tax_certificate_expires_at DATE,
    
    -- Banka bilgileri (varsayılan)
    default_bank_name VARCHAR(100),
    default_iban VARCHAR(34),
    
    -- Fatura ayarları
    invoice_prefix VARCHAR(10) DEFAULT 'FAT',
    invoice_start_number INT DEFAULT 1,
    invoice_footer TEXT COMMENT 'Fatura alt bilgi',
    
    -- Durum
    is_active BOOLEAN DEFAULT TRUE,
    is_default BOOLEAN DEFAULT FALSE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_tax_number (tax_number),
    INDEX idx_mersis_no (mersis_no),
    UNIQUE KEY unique_tax_number (tax_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🔌 API Endpoints

```
GET    /api/company                    - Kullanıcının şirket bilgileri
POST   /api/company                    - Yeni şirket oluştur
PUT    /api/company/{id}               - Şirket bilgilerini güncelle
DELETE /api/company/{id}               - Şirketi sil
POST   /api/company/{id}/logo          - Logo yükle
DELETE /api/company/{id}/logo          - Logo sil
PUT    /api/company/{id}/set-default   - Varsayılan şirket olarak ayarla

# e-Fatura
POST   /api/company/{id}/efatura/activate    - e-Fatura aktivasyonu
PUT    /api/company/{id}/efatura/settings    - e-Fatura ayarları
POST   /api/company/{id}/certificate/upload  - Mali mühür yükle
```

---

## 💻 Backend Implementasyonu

### 1. Company Model

```php
<?php
namespace App\Models;

class Company {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Kullanıcının şirketlerini getir
     */
    public function getByUserId($userId) {
        $sql = "SELECT * FROM companies 
                WHERE user_id = ? 
                ORDER BY is_default DESC, created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * ID ile şirket getir
     */
    public function findById($id, $userId) {
        $sql = "SELECT * FROM companies 
                WHERE id = ? AND user_id = ?
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $userId]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Varsayılan şirketi getir
     */
    public function getDefault($userId) {
        $sql = "SELECT * FROM companies 
                WHERE user_id = ? AND is_default = TRUE
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        $company = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Varsayılan yoksa ilk şirketi döndür
        if (!$company) {
            $sql = "SELECT * FROM companies 
                    WHERE user_id = ?
                    ORDER BY created_at ASC
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $company = $stmt->fetch(\PDO::FETCH_ASSOC);
        }
        
        return $company;
    }
    
    /**
     * Yeni şirket oluştur
     */
    public function create($userId, $data) {
        // İlk şirket ise varsayılan olarak işaretle
        $isFirstCompany = $this->countByUser($userId) === 0;
        
        $sql = "INSERT INTO companies (
            user_id, company_name, company_title, company_type,
            tax_office, tax_number, trade_registry_no, mersis_no,
            email, phone, fax, website,
            address, district, city, postal_code, country,
            is_default
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $userId,
            $data['company_name'],
            $data['company_title'] ?? $data['company_name'],
            $data['company_type'] ?? 'sahis',
            $data['tax_office'] ?? null,
            $data['tax_number'] ?? null,
            $data['trade_registry_no'] ?? null,
            $data['mersis_no'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['fax'] ?? null,
            $data['website'] ?? null,
            $data['address'] ?? null,
            $data['district'] ?? null,
            $data['city'] ?? null,
            $data['postal_code'] ?? null,
            $data['country'] ?? 'Türkiye',
            $isFirstCompany
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Şirket bilgilerini güncelle
     */
    public function update($id, $userId, $data) {
        $sql = "UPDATE companies SET
                company_name = ?,
                company_title = ?,
                company_type = ?,
                tax_office = ?,
                tax_number = ?,
                trade_registry_no = ?,
                mersis_no = ?,
                email = ?,
                phone = ?,
                fax = ?,
                website = ?,
                address = ?,
                district = ?,
                city = ?,
                postal_code = ?,
                country = ?,
                default_bank_name = ?,
                default_iban = ?,
                invoice_prefix = ?,
                invoice_footer = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['company_name'],
            $data['company_title'] ?? $data['company_name'],
            $data['company_type'] ?? 'sahis',
            $data['tax_office'] ?? null,
            $data['tax_number'] ?? null,
            $data['trade_registry_no'] ?? null,
            $data['mersis_no'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['fax'] ?? null,
            $data['website'] ?? null,
            $data['address'] ?? null,
            $data['district'] ?? null,
            $data['city'] ?? null,
            $data['postal_code'] ?? null,
            $data['country'] ?? 'Türkiye',
            $data['default_bank_name'] ?? null,
            $data['default_iban'] ?? null,
            $data['invoice_prefix'] ?? 'FAT',
            $data['invoice_footer'] ?? null,
            $id,
            $userId
        ]);
    }
    
    /**
     * Logo yolu güncelle
     */
    public function updateLogo($id, $userId, $logoPath) {
        $sql = "UPDATE companies SET
                logo_path = ?,
                logo_updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$logoPath, $id, $userId]);
    }
    
    /**
     * Varsayılan şirketi ayarla
     */
    public function setDefault($id, $userId) {
        // Önce tüm şirketlerin varsayılan işaretini kaldır
        $sql = "UPDATE companies SET is_default = FALSE WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        // Seçili şirketi varsayılan yap
        $sql = "UPDATE companies SET is_default = TRUE 
                WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id, $userId]);
    }
    
    /**
     * e-Fatura aktivasyonu
     */
    public function activateEFatura($id, $userId, $gbUsername, $gbPassword, $alias) {
        $sql = "UPDATE companies SET
                is_efatura_user = TRUE,
                efatura_gb_username = ?,
                efatura_gb_password = ?,
                efatura_alias = ?,
                efatura_activation_date = CURDATE()
                WHERE id = ? AND user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        
        // Şifreyi encrypt et
        $encryptedPassword = $this->encryptPassword($gbPassword);
        
        return $stmt->execute([
            $gbUsername,
            $encryptedPassword,
            $alias,
            $id,
            $userId
        ]);
    }
    
    /**
     * Mali mühür bilgilerini kaydet
     */
    public function saveCertificate($id, $userId, $certPath, $password, $expiresAt) {
        $sql = "UPDATE companies SET
                tax_certificate_path = ?,
                tax_certificate_password = ?,
                tax_certificate_expires_at = ?
                WHERE id = ? AND user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        
        $encryptedPassword = $this->encryptPassword($password);
        
        return $stmt->execute([
            $certPath,
            $encryptedPassword,
            $expiresAt,
            $id,
            $userId
        ]);
    }
    
    /**
     * Kullanıcının şirket sayısı
     */
    private function countByUser($userId) {
        $sql = "SELECT COUNT(*) as count FROM companies WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    /**
     * Şifreyi encrypt et
     */
    private function encryptPassword($password) {
        $key = $_ENV['ENCRYPTION_KEY'];
        $iv = random_bytes(16);
        
        $encrypted = openssl_encrypt(
            $password,
            'AES-256-CBC',
            $key,
            0,
            $iv
        );
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Şifreyi decrypt et
     */
    public function decryptPassword($encryptedPassword) {
        $key = $_ENV['ENCRYPTION_KEY'];
        $data = base64_decode($encryptedPassword);
        
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        return openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $key,
            0,
            $iv
        );
    }
}
```

### 2. Company Controller

```php
<?php
namespace App\Controllers;

use App\Models\Company;
use App\Helpers\Validator;
use App\Middleware\AuthMiddleware;

class CompanyController {
    private $companyModel;
    private $authMiddleware;
    
    public function __construct($db) {
        $this->companyModel = new Company($db);
        $this->authMiddleware = new AuthMiddleware();
    }
    
    /**
     * Şirket bilgilerini getir
     * GET /api/company
     */
    public function index() {
        $userId = $this->authMiddleware->getUserId();
        
        $companies = $this->companyModel->getByUserId($userId);
        
        // Hassas bilgileri kaldır
        foreach ($companies as &$company) {
            unset($company['efatura_gb_password']);
            unset($company['tax_certificate_password']);
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $companies
        ]);
    }
    
    /**
     * Yeni şirket oluştur
     * POST /api/company
     */
    public function create() {
        $userId = $this->authMiddleware->getUserId();
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validasyon
        $validator = new Validator($data, [
            'company_name' => 'required|min:2|max:200',
            'tax_office' => 'nullable|max:100',
            'tax_number' => 'nullable|numeric|digits:10|unique:companies,tax_number',
            'mersis_no' => 'nullable|numeric|digits:16',
            'email' => 'nullable|email',
            'phone' => 'nullable|phone',
            'website' => 'nullable|url'
        ]);
        
        if ($validator->fails()) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'errors' => $validator->errors()
            ]);
            return;
        }
        
        try {
            $companyId = $this->companyModel->create($userId, $data);
            
            $company = $this->companyModel->findById($companyId, $userId);
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Şirket başarıyla oluşturuldu.',
                'data' => $company
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Şirket oluşturulurken bir hata oluştu.'
            ]);
        }
    }
    
    /**
     * Şirket bilgilerini güncelle
     * PUT /api/company/{id}
     */
    public function update($id) {
        $userId = $this->authMiddleware->getUserId();
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Şirket mevcut mu ve kullanıcıya ait mi kontrol et
        $company = $this->companyModel->findById($id, $userId);
        
        if (!$company) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Şirket bulunamadı.'
            ]);
            return;
        }
        
        // Validasyon
        $validator = new Validator($data, [
            'company_name' => 'required|min:2|max:200',
            'tax_office' => 'nullable|max:100',
            'tax_number' => 'nullable|numeric|digits:10',
            'email' => 'nullable|email',
            'phone' => 'nullable|phone'
        ]);
        
        if ($validator->fails()) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'errors' => $validator->errors()
            ]);
            return;
        }
        
        try {
            $this->companyModel->update($id, $userId, $data);
            
            $updatedCompany = $this->companyModel->findById($id, $userId);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Şirket bilgileri güncellendi.',
                'data' => $updatedCompany
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Güncelleme sırasında bir hata oluştu.'
            ]);
        }
    }
    
    /**
     * Logo yükle
     * POST /api/company/{id}/logo
     */
    public function uploadLogo($id) {
        $userId = $this->authMiddleware->getUserId();
        
        // Şirket kontrolü
        $company = $this->companyModel->findById($id, $userId);
        
        if (!$company) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Şirket bulunamadı.'
            ]);
            return;
        }
        
        // Dosya yükleme kontrolü
        if (!isset($_FILES['logo'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Logo dosyası bulunamadı.'
            ]);
            return;
        }
        
        $file = $_FILES['logo'];
        
        // Dosya doğrulama
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Geçersiz dosya tipi. Sadece JPG, PNG ve GIF desteklenir.'
            ]);
            return;
        }
        
        if ($file['size'] > $maxSize) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Dosya boyutu 2MB\'dan küçük olmalıdır.'
            ]);
            return;
        }
        
        // Dosya adı oluştur
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'company_' . $id . '_' . time() . '.' . $extension;
        $uploadPath = __DIR__ . '/../../public/uploads/logos/' . $filename;
        
        // Dosyayı kaydet
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Eski logoyu sil
            if ($company['logo_path']) {
                $oldPath = __DIR__ . '/../../public' . $company['logo_path'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            $logoPath = '/uploads/logos/' . $filename;
            $this->companyModel->updateLogo($id, $userId, $logoPath);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Logo başarıyla yüklendi.',
                'logo_url' => $logoPath
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Logo yüklenirken bir hata oluştu.'
            ]);
        }
    }
    
    /**
     * Varsayılan şirketi ayarla
     * PUT /api/company/{id}/set-default
     */
    public function setDefault($id) {
        $userId = $this->authMiddleware->getUserId();
        
        // Şirket kontrolü
        $company = $this->companyModel->findById($id, $userId);
        
        if (!$company) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Şirket bulunamadı.'
            ]);
            return;
        }
        
        $this->companyModel->setDefault($id, $userId);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Varsayılan şirket güncellendi.'
        ]);
    }
    
    /**
     * e-Fatura aktivasyonu
     * POST /api/company/{id}/efatura/activate
     */
    public function activateEFatura($id) {
        $userId = $this->authMiddleware->getUserId();
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Şirket kontrolü
        $company = $this->companyModel->findById($id, $userId);
        
        if (!$company) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Şirket bulunamadı.'
            ]);
            return;
        }
        
        // Validasyon
        $validator = new Validator($data, [
            'gb_username' => 'required',
            'gb_password' => 'required',
            'alias' => 'required|alpha_dash'
        ]);
        
        if ($validator->fails()) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'errors' => $validator->errors()
            ]);
            return;
        }
        
        try {
            $this->companyModel->activateEFatura(
                $id,
                $userId,
                $data['gb_username'],
                $data['gb_password'],
                $data['alias']
            );
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'e-Fatura başarıyla aktifleştirildi.'
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'e-Fatura aktivasyonu sırasında bir hata oluştu.'
            ]);
        }
    }
}
```

---

## 🎨 Frontend Implementasyonu

### Company Form (HTML)

```html
<!-- company-form.html -->
<div class="max-w-4xl mx-auto p-6">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-6">Şirket Bilgileri</h2>
        
        <form id="companyForm">
            <!-- Logo Upload -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Şirket Logosu
                </label>
                <div class="flex items-center space-x-4">
                    <img 
                        id="logoPreview" 
                        src="/assets/images/default-logo.png" 
                        alt="Logo" 
                        class="w-32 h-32 object-contain border rounded"
                    >
                    <input 
                        type="file" 
                        id="logoFile" 
                        accept="image/*"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                    >
                </div>
            </div>
            
            <!-- Şirket Adı -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Şirket Adı *
                    </label>
                    <input 
                        type="text" 
                        name="company_name" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Ticari Ünvan
                    </label>
                    <input 
                        type="text" 
                        name="company_title"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
            </div>
            
            <!-- Şirket Tipi -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Şirket Tipi
                </label>
                <select 
                    name="company_type"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="sahis">Şahıs Şirketi</option>
                    <option value="limited">Limited Şirket</option>
                    <option value="anonim">Anonim Şirket</option>
                    <option value="komandit">Komandit Şirket</option>
                    <option value="kollektif">Kollektif Şirket</option>
                </select>
            </div>
            
            <!-- Vergi Bilgileri -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Vergi Dairesi
                    </label>
                    <input 
                        type="text" 
                        name="tax_office"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Vergi Numarası
                    </label>
                    <input 
                        type="text" 
                        name="tax_number"
                        maxlength="10"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
            </div>
            
            <!-- Ticaret Sicil ve Mersis -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Ticaret Sicil No
                    </label>
                    <input 
                        type="text" 
                        name="trade_registry_no"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Mersis No
                    </label>
                    <input 
                        type="text" 
                        name="mersis_no"
                        maxlength="16"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
            </div>
            
            <!-- İletişim Bilgileri -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        E-posta
                    </label>
                    <input 
                        type="email" 
                        name="email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Telefon
                    </label>
                    <input 
                        type="tel" 
                        name="phone"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Web Sitesi
                    </label>
                    <input 
                        type="url" 
                        name="website"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
            </div>
            
            <!-- Adres -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Adres
                </label>
                <textarea 
                    name="address"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                ></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        İlçe
                    </label>
                    <input 
                        type="text" 
                        name="district"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        İl
                    </label>
                    <input 
                        type="text" 
                        name="city"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Posta Kodu
                    </label>
                    <input 
                        type="text" 
                        name="postal_code"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
            </div>
            
            <!-- Fatura Ayarları -->
            <div class="bg-gray-50 p-4 rounded-md mb-4">
                <h3 class="font-semibold mb-3">Fatura Ayarları</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Fatura Ön Eki
                        </label>
                        <input 
                            type="text" 
                            name="invoice_prefix"
                            value="FAT"
                            maxlength="10"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Varsayılan IBAN
                        </label>
                        <input 
                            type="text" 
                            name="default_iban"
                            maxlength="34"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>
                </div>
                
                <div class="mt-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Fatura Alt Bilgi
                    </label>
                    <textarea 
                        name="invoice_footer"
                        rows="2"
                        placeholder="Faturalarınızın alt kısmında görünecek bilgi"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    ></textarea>
                </div>
            </div>
            
            <!-- Buttons -->
            <div class="flex justify-end space-x-3">
                <button 
                    type="button"
                    onclick="window.history.back()"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                    İptal
                </button>
                <button 
                    type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                >
                    Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<script src="/assets/js/company.js"></script>
```

### company.js

```javascript
// Company Management
class CompanyManager {
    constructor() {
        this.apiClient = window.apiClient;
        this.currentCompanyId = null;
        this.init();
    }
    
    init() {
        this.loadCompanies();
        this.attachEventListeners();
    }
    
    async loadCompanies() {
        try {
            const response = await this.apiClient.get('/company');
            
            if (response.success) {
                this.renderCompanyList(response.data);
                
                // Varsayılan şirketi form'a yükle
                const defaultCompany = response.data.find(c => c.is_default);
                if (defaultCompany) {
                    this.loadCompanyToForm(defaultCompany);
                }
            }
        } catch (error) {
            console.error('Şirketler yüklenemedi:', error);
        }
    }
    
    loadCompanyToForm(company) {
        this.currentCompanyId = company.id;
        
        const form = document.getElementById('companyForm');
        if (!form) return;
        
        // Form alanlarını doldur
        Object.keys(company).forEach(key => {
            const input = form.querySelector(`[name="${key}"]`);
            if (input && company[key]) {
                input.value = company[key];
            }
        });
        
        // Logo'yu göster
        if (company.logo_path) {
            document.getElementById('logoPreview').src = company.logo_path;
        }
    }
    
    attachEventListeners() {
        // Form submit
        const form = document.getElementById('companyForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
        
        // Logo upload preview
        const logoInput = document.getElementById('logoFile');
        if (logoInput) {
            logoInput.addEventListener('change', (e) => this.previewLogo(e));
        }
    }
    
    async handleSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        // Form verilerini object'e çevir
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });
        
        try {
            let response;
            
            if (this.currentCompanyId) {
                // Güncelleme
                response = await this.apiClient.put(
                    `/company/${this.currentCompanyId}`,
                    data
                );
            } else {
                // Yeni oluşturma
                response = await this.apiClient.post('/company', data);
            }
            
            if (response.success) {
                // Logo yükleme
                const logoFile = document.getElementById('logoFile').files[0];
                if (logoFile) {
                    await this.uploadLogo(
                        response.data.id || this.currentCompanyId,
                        logoFile
                    );
                }
                
                this.showNotification('Şirket bilgileri kaydedildi', 'success');
                this.loadCompanies();
            } else {
                this.showNotification(response.error, 'error');
            }
            
        } catch (error) {
            this.showNotification('Bir hata oluştu', 'error');
        }
    }
    
    async uploadLogo(companyId, file) {
        const formData = new FormData();
        formData.append('logo', file);
        
        try {
            const response = await fetch(`/api/company/${companyId}/logo`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.apiClient.authAPI.getAccessToken()}`
                },
                body: formData
            });
            
            return await response.json();
        } catch (error) {
            console.error('Logo yüklenemedi:', error);
        }
    }
    
    previewLogo(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                document.getElementById('logoPreview').src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
    
    renderCompanyList(companies) {
        // Şirket listesi dropdown veya card'ları render et
        // İhtiyaca göre implement edilecek
    }
    
    showNotification(message, type = 'info') {
        // Basit notification göster
        alert(message);
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    new CompanyManager();
});
```

---

## 📝 Özet

Bu modül ile:
- ✅ Kapsamlı şirket bilgisi yönetimi
- ✅ Logo yükleme ve görüntüleme
- ✅ e-Fatura için gerekli tüm bilgiler
- ✅ Güvenli şifre saklama (encryption)
- ✅ Çoklu şirket desteği altyapısı
- ✅ Fatura ön eki ve numaralandırma ayarları

**Sonraki Modül:** Cari Hesaplar (Customer/Supplier Management)
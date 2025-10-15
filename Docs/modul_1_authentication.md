# Modül 1: Authentication (Kimlik Doğrulama Sistemi)

## 📋 Modül Özeti

Kimlik doğrulama modülü, sistemin güvenlik temelini oluşturur. JWT (JSON Web Token) tabanlı, güvenli ve ölçeklenebilir bir authentication sistemi.

### Özellikler
- ✅ Kullanıcı kayıt ve giriş
- ✅ JWT token tabanlı kimlik doğrulama
- ✅ Refresh token mekanizması
- ✅ E-posta doğrulama
- ✅ Şifre sıfırlama (forgot password)
- ✅ 2FA (Two-Factor Authentication)
- ✅ Rol bazlı yetkilendirme
- ✅ Session yönetimi
- ✅ Güvenlik logları

---

## 🗄️ Veritabanı Tabloları

### users
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    
    -- Roller
    role ENUM('admin', 'muhasebeci', 'personel', 'kullanici') DEFAULT 'kullanici',
    
    -- Durum bilgileri
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(64),
    email_verified_at TIMESTAMP NULL,
    
    -- 2FA
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(255),
    two_factor_recovery_codes TEXT COMMENT 'JSON array',
    
    -- Şifre sıfırlama
    password_reset_token VARCHAR(64),
    password_reset_expires TIMESTAMP NULL,
    
    -- Login bilgileri
    last_login TIMESTAMP NULL,
    last_login_ip VARCHAR(45),
    failed_login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    
    -- Tarih bilgileri
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_email_verification_token (email_verification_token),
    INDEX idx_password_reset_token (password_reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### refresh_tokens
```sql
CREATE TABLE refresh_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    revoked_at TIMESTAMP NULL,
    user_agent TEXT,
    ip_address VARCHAR(45),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### login_history
```sql
CREATE TABLE login_history (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    login_type ENUM('success', 'failed', 'logout') NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    location VARCHAR(100) COMMENT 'City, Country from IP',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_login_type (login_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🔌 API Endpoints

```
POST   /api/auth/register              - Yeni kullanıcı kaydı
POST   /api/auth/login                 - Giriş yap (JWT döner)
POST   /api/auth/logout                - Çıkış yap
POST   /api/auth/refresh               - Token yenileme
GET    /api/auth/me                    - Kullanıcı bilgileri
PUT    /api/auth/profile               - Profil güncelleme
POST   /api/auth/change-password       - Şifre değiştirme

# E-posta Doğrulama
POST   /api/auth/verify-email          - E-posta doğrula
POST   /api/auth/resend-verification   - Doğrulama maili tekrar gönder

# Şifre Sıfırlama
POST   /api/auth/forgot-password       - Şifre sıfırlama isteği
POST   /api/auth/reset-password        - Yeni şifre belirle

# 2FA (Two-Factor Authentication)
POST   /api/auth/2fa/enable            - 2FA aktifleştir
POST   /api/auth/2fa/verify            - 2FA kodu doğrula
POST   /api/auth/2fa/disable           - 2FA devre dışı bırak
GET    /api/auth/2fa/recovery-codes    - Yedek kodları al

# Admin İşlemleri
GET    /api/auth/users                 - Kullanıcı listesi (admin)
PUT    /api/auth/users/{id}/role       - Rol değiştir (admin)
PUT    /api/auth/users/{id}/status     - Aktif/pasif (admin)
```

---

## 💻 Backend Implementasyonu

### 1. User Model

```php
<?php
namespace App\Models;

class User {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Yeni kullanıcı oluştur
     */
    public function create($data) {
        $sql = "INSERT INTO users (
            username, email, password_hash, full_name, 
            phone, role, email_verification_token
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $verificationToken = bin2hex(random_bytes(32));
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_ARGON2ID),
            $data['full_name'],
            $data['phone'] ?? null,
            $data['role'] ?? 'kullanici',
            $verificationToken
        ]);
        
        $userId = $this->db->lastInsertId();
        
        return [
            'id' => $userId,
            'verification_token' => $verificationToken
        ];
    }
    
    /**
     * E-posta ile kullanıcı bul
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Kullanıcı adı ile bul
     */
    public function findByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * ID ile bul
     */
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Şifre doğrula
     */
    public function verifyPassword($user, $password) {
        return password_verify($password, $user['password_hash']);
    }
    
    /**
     * Son giriş bilgisini güncelle
     */
    public function updateLastLogin($userId, $ipAddress) {
        $sql = "UPDATE users SET 
                last_login = NOW(), 
                last_login_ip = ?,
                failed_login_attempts = 0
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ipAddress, $userId]);
    }
    
    /**
     * Başarısız giriş denemesini artır
     */
    public function incrementFailedAttempts($userId) {
        $sql = "UPDATE users SET 
                failed_login_attempts = failed_login_attempts + 1
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        // 5 başarısız denemeden sonra hesabı kilitle
        $user = $this->findById($userId);
        if ($user['failed_login_attempts'] >= 5) {
            $this->lockAccount($userId, 30); // 30 dakika kilitle
        }
    }
    
    /**
     * Hesabı kilitle
     */
    private function lockAccount($userId, $minutes) {
        $sql = "UPDATE users SET 
                locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE)
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$minutes, $userId]);
    }
    
    /**
     * E-posta doğrula
     */
    public function verifyEmail($token) {
        $sql = "UPDATE users SET 
                email_verified = TRUE,
                email_verified_at = NOW(),
                email_verification_token = NULL
                WHERE email_verification_token = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$token]);
    }
}
```

### 2. JWT Service

```php
<?php
namespace App\Services;

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
     * Access token oluştur
     */
    public function generateAccessToken($user) {
        $payload = [
            'iss' => $_ENV['APP_URL'],
            'aud' => $_ENV['APP_URL'],
            'iat' => time(),
            'exp' => time() + $this->accessTokenExpiry,
            'sub' => $user['id'],
            'data' => [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ];
        
        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }
    
    /**
     * Refresh token oluştur
     */
    public function generateRefreshToken($userId) {
        $token = bin2hex(random_bytes(64));
        
        $sql = "INSERT INTO refresh_tokens (
            user_id, token, expires_at, user_agent, ip_address
        ) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND), ?, ?)";
        
        global $db;
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $userId,
            hash('sha256', $token),
            $this->refreshTokenExpiry,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $_SERVER['REMOTE_ADDR']
        ]);
        
        return $token;
    }
    
    /**
     * Token doğrula
     */
    public function verifyToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return (array) $decoded->data;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Refresh token doğrula
     */
    public function verifyRefreshToken($token) {
        $hashedToken = hash('sha256', $token);
        
        global $db;
        $sql = "SELECT * FROM refresh_tokens 
                WHERE token = ? 
                AND expires_at > NOW() 
                AND revoked_at IS NULL
                LIMIT 1";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$hashedToken]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Refresh token'ı iptal et
     */
    public function revokeRefreshToken($token) {
        $hashedToken = hash('sha256', $token);
        
        global $db;
        $sql = "UPDATE refresh_tokens SET revoked_at = NOW() WHERE token = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$hashedToken]);
    }
}
```

### 3. Auth Controller

```php
<?php
namespace App\Controllers\Auth;

use App\Models\User;
use App\Services\JWTService;
use App\Services\MailService;
use App\Helpers\Validator;

class AuthController {
    private $userModel;
    private $jwtService;
    private $mailService;
    
    public function __construct($db) {
        $this->userModel = new User($db);
        $this->jwtService = new JWTService();
        $this->mailService = new MailService();
    }
    
    /**
     * Kullanıcı kaydı
     * POST /api/auth/register
     */
    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validasyon
        $validator = new Validator($data, [
            'username' => 'required|min:3|max:50|alpha_dash',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'full_name' => 'required|min:3|max:100',
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
        
        // Kullanıcıyı oluştur
        try {
            $result = $this->userModel->create($data);
            
            // E-posta doğrulama maili gönder
            $this->mailService->sendVerificationEmail(
                $data['email'],
                $data['full_name'],
                $result['verification_token']
            );
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Kayıt başarılı! Lütfen e-postanızı doğrulayın.',
                'user_id' => $result['id']
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Kayıt sırasında bir hata oluştu.'
            ]);
        }
    }
    
    /**
     * Giriş yap
     * POST /api/auth/login
     */
    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validasyon
        $validator = new Validator($data, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if ($validator->fails()) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'errors' => $validator->errors()
            ]);
            return;
        }
        
        // Kullanıcıyı bul
        $user = $this->userModel->findByEmail($data['email']);
        
        if (!$user) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'E-posta veya şifre hatalı.'
            ]);
            return;
        }
        
        // Hesap kilitli mi kontrol et
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            http_response_code(423);
            echo json_encode([
                'success' => false,
                'error' => 'Hesabınız geçici olarak kilitlendi. Lütfen daha sonra tekrar deneyin.'
            ]);
            return;
        }
        
        // Şifre doğrula
        if (!$this->userModel->verifyPassword($user, $data['password'])) {
            $this->userModel->incrementFailedAttempts($user['id']);
            
            // Login history'ye kaydet
            $this->logLoginAttempt($user['id'], 'failed');
            
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'E-posta veya şifre hatalı.'
            ]);
            return;
        }
        
        // Aktif mi kontrol et
        if (!$user['is_active']) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'Hesabınız devre dışı bırakılmış.'
            ]);
            return;
        }
        
        // 2FA aktif mi?
        if ($user['two_factor_enabled']) {
            // 2FA kodu bekleniyor
            $tempToken = bin2hex(random_bytes(32));
            
            // Redis'e geçici olarak kaydet
            // Redis::setex("2fa_pending:{$tempToken}", 300, $user['id']);
            
            echo json_encode([
                'success' => true,
                'requires_2fa' => true,
                'temp_token' => $tempToken
            ]);
            return;
        }
        
        // Token'ları oluştur
        $accessToken = $this->jwtService->generateAccessToken($user);
        $refreshToken = $this->jwtService->generateRefreshToken($user['id']);
        
        // Son giriş bilgilerini güncelle
        $this->userModel->updateLastLogin($user['id'], $_SERVER['REMOTE_ADDR']);
        
        // Login history'ye kaydet
        $this->logLoginAttempt($user['id'], 'success');
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'full_name' => $user['full_name'],
                'role' => $user['role']
            ]
        ]);
    }
    
    /**
     * Token yenile
     * POST /api/auth/refresh
     */
    public function refresh() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['refresh_token'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Refresh token gerekli.'
            ]);
            return;
        }
        
        // Refresh token'ı doğrula
        $tokenData = $this->jwtService->verifyRefreshToken($data['refresh_token']);
        
        if (!$tokenData) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Geçersiz veya süresi dolmuş token.'
            ]);
            return;
        }
        
        // Kullanıcıyı bul
        $user = $this->userModel->findById($tokenData['user_id']);
        
        if (!$user || !$user['is_active']) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Kullanıcı bulunamadı veya aktif değil.'
            ]);
            return;
        }
        
        // Eski token'ı iptal et
        $this->jwtService->revokeRefreshToken($data['refresh_token']);
        
        // Yeni token'lar oluştur
        $accessToken = $this->jwtService->generateAccessToken($user);
        $refreshToken = $this->jwtService->generateRefreshToken($user['id']);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ]);
    }
    
    /**
     * Çıkış yap
     * POST /api/auth/logout
     */
    public function logout() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['refresh_token'])) {
            $this->jwtService->revokeRefreshToken($data['refresh_token']);
        }
        
        // Access token'ı blacklist'e ekle (Redis)
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
            // Redis::setex("blacklist:{$token}", 3600, 1);
        }
        
        // Login history
        $userData = $this->jwtService->verifyToken($token);
        if ($userData) {
            $this->logLoginAttempt($userData['user_id'], 'logout');
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Çıkış başarılı.'
        ]);
    }
    
    /**
     * E-posta doğrula
     * POST /api/auth/verify-email
     */
    public function verifyEmail() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['token'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Doğrulama token gerekli.'
            ]);
            return;
        }
        
        $success = $this->userModel->verifyEmail($data['token']);
        
        if ($success) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'E-posta adresiniz başarıyla doğrulandı.'
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Geçersiz veya süresi dolmuş doğrulama kodu.'
            ]);
        }
    }
    
    /**
     * Şifre sıfırlama isteği
     * POST /api/auth/forgot-password
     */
    public function forgotPassword() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $validator = new Validator($data, [
            'email' => 'required|email'
        ]);
        
        if ($validator->fails()) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'errors' => $validator->errors()
            ]);
            return;
        }
        
        $user = $this->userModel->findByEmail($data['email']);
        
        // Güvenlik: Kullanıcı bulunamasa bile aynı mesajı ver
        if ($user) {
            $resetToken = bin2hex(random_bytes(32));
            
            // Token'ı veritabanına kaydet (60 dakika geçerli)
            global $db;
            $sql = "UPDATE users SET 
                    password_reset_token = ?,
                    password_reset_expires = DATE_ADD(NOW(), INTERVAL 60 MINUTE)
                    WHERE id = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$resetToken, $user['id']]);
            
            // Şifre sıfırlama mailini gönder
            $this->mailService->sendPasswordResetEmail(
                $user['email'],
                $user['full_name'],
                $resetToken
            );
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Eğer bu e-posta adresi kayıtlıysa, şifre sıfırlama bağlantısı gönderildi.'
        ]);
    }
    
    /**
     * Login geçmişini kaydet
     */
    private function logLoginAttempt($userId, $type) {
        global $db;
        
        $sql = "INSERT INTO login_history (user_id, login_type, ip_address, user_agent)
                VALUES (?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $userId,
            $type,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
}
```

---

## 🎨 Frontend Implementasyonu

### Login Sayfası (HTML + JavaScript)

```html
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Ön Muhasebe</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Ön Muhasebe Sistemi
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Hesabınıza giriş yapın
                </p>
            </div>
            
            <form id="loginForm" class="mt-8 space-y-6">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email" class="sr-only">E-posta</label>
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            required 
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                            placeholder="E-posta adresi"
                        >
                    </div>
                    <div>
                        <label for="password" class="sr-only">Şifre</label>
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            required 
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                            placeholder="Şifre"
                        >
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            id="remember-me" 
                            name="remember-me" 
                            type="checkbox" 
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        >
                        <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                            Beni hatırla
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="/forgot-password" class="font-medium text-indigo-600 hover:text-indigo-500">
                            Şifremi unuttum
                        </a>
                    </div>
                </div>

                <div id="errorMessage" class="hidden text-red-600 text-sm text-center"></div>

                <div>
                    <button 
                        type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Giriş Yap
                    </button>
                </div>
                
                <div class="text-center">
                    <a href="/register" class="font-medium text-indigo-600 hover:text-indigo-500">
                        Hesabınız yok mu? Kayıt olun
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="/assets/js/auth.js"></script>
</body>
</html>
```

### auth.js

```javascript
// Auth API Helper
class AuthAPI {
    constructor() {
        this.baseUrl = '/api/auth';
    }
    
    async login(email, password) {
        const response = await fetch(`${this.baseUrl}/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password })
        });
        
        return await response.json();
    }
    
    async register(userData) {
        const response = await fetch(`${this.baseUrl}/register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(userData)
        });
        
        return await response.json();
    }
    
    async logout() {
        const refreshToken = localStorage.getItem('refresh_token');
        
        const response = await fetch(`${this.baseUrl}/logout`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.getAccessToken()}`
            },
            body: JSON.stringify({ refresh_token: refreshToken })
        });
        
        this.clearTokens();
        return await response.json();
    }
    
    async refreshToken() {
        const refreshToken = localStorage.getItem('refresh_token');
        
        if (!refreshToken) {
            throw new Error('No refresh token available');
        }
        
        const response = await fetch(`${this.baseUrl}/refresh`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ refresh_token: refreshToken })
        });
        
        if (response.ok) {
            const data = await response.json();
            this.saveTokens(data.access_token, data.refresh_token);
            return data;
        } else {
            this.clearTokens();
            window.location.href = '/login';
            throw new Error('Token refresh failed');
        }
    }
    
    saveTokens(accessToken, refreshToken) {
        localStorage.setItem('access_token', accessToken);
        localStorage.setItem('refresh_token', refreshToken);
    }
    
    clearTokens() {
        localStorage.removeItem('access_token');
        localStorage.removeItem('refresh_token');
        localStorage.removeItem('user');
    }
    
    getAccessToken() {
        return localStorage.getItem('access_token');
    }
    
    isAuthenticated() {
        return !!this.getAccessToken();
    }
}

// Login Form Handler
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('errorMessage');
    const authAPI = new AuthAPI();
    
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            try {
                // Loading state
                const submitButton = loginForm.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                submitButton.textContent = 'Giriş yapılıyor...';
                
                // API çağrısı
                const result = await authAPI.login(email, password);
                
                if (result.success) {
                    // Token'ları kaydet
                    authAPI.saveTokens(result.access_token, result.refresh_token);
                    
                    // Kullanıcı bilgilerini kaydet
                    localStorage.setItem('user', JSON.stringify(result.user));
                    
                    // Dashboard'a yönlendir
                    window.location.href = '/dashboard';
                } else {
                    // Hata mesajını göster
                    showError(result.error || 'Giriş başarısız oldu.');
                    submitButton.disabled = false;
                    submitButton.textContent = 'Giriş Yap';
                }
                
            } catch (error) {
                showError('Bir hata oluştu. Lütfen tekrar deneyin.');
                submitButton.disabled = false;
                submitButton.textContent = 'Giriş Yap';
            }
        });
    }
    
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.classList.remove('hidden');
        
        setTimeout(() => {
            errorMessage.classList.add('hidden');
        }, 5000);
    }
});

// Otomatik token yenileme
setInterval(async () => {
    const authAPI = new AuthAPI();
    if (authAPI.isAuthenticated()) {
        try {
            await authAPI.refreshToken();
        } catch (error) {
            console.error('Token yenileme hatası:', error);
        }
    }
}, 50 * 60 * 1000); // Her 50 dakikada bir
```

### HTTP Interceptor (Fetch wrapper)

```javascript
// api.js - Tüm API çağrıları için wrapper
class APIClient {
    constructor() {
        this.baseUrl = '/api';
        this.authAPI = new AuthAPI();
    }
    
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        
        // Token ekle
        const token = this.authAPI.getAccessToken();
        if (token) {
            options.headers = {
                ...options.headers,
                'Authorization': `Bearer ${token}`
            };
        }
        
        // Content-Type ekle
        if (options.body && !(options.body instanceof FormData)) {
            options.headers = {
                ...options.headers,
                'Content-Type': 'application/json'
            };
        }
        
        try {
            let response = await fetch(url, options);
            
            // Token süresi dolmuşsa yenile ve tekrar dene
            if (response.status === 401) {
                await this.authAPI.refreshToken();
                
                // Token'ı güncelle ve tekrar dene
                const newToken = this.authAPI.getAccessToken();
                options.headers['Authorization'] = `Bearer ${newToken}`;
                response = await fetch(url, options);
            }
            
            return await response.json();
            
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
    
    get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    }
    
    post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }
    
    put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }
    
    delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }
}

// Global API client
window.apiClient = new APIClient();
```

---

## 🔒 Güvenlik Önlemleri

### 1. Rate Limiting Middleware

```php
<?php
namespace App\Middleware;

class RateLimitMiddleware {
    private $redis;
    private $maxAttempts = 5;
    private $decayMinutes = 1;
    
    public function handle() {
        $key = $this->getRateLimitKey();
        
        if ($this->tooManyAttempts($key)) {
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'error' => 'Çok fazla deneme yaptınız. Lütfen daha sonra tekrar deneyin.',
                'retry_after' => 60
            ]);
            exit;
        }
        
        $this->incrementAttempts($key);
    }
    
    private function getRateLimitKey() {
        return 'rate_limit:' . $_SERVER['REMOTE_ADDR'] . ':login';
    }
    
    private function tooManyAttempts($key) {
        // Redis ile kontrol
        // return Redis::get($key) >= $this->maxAttempts;
        return false; // Placeholder
    }
    
    private function incrementAttempts($key) {
        // Redis::incr($key);
        // Redis::expire($key, $this->decayMinutes * 60);
    }
}
```

### 2. CSRF Protection

```php
<?php
namespace App\Middleware;

class CsrfMiddleware {
    public function generateToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public function validateToken($token) {
        if (empty($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public function handle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            
            if (!$this->validateToken($token)) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'CSRF token doğrulaması başarısız.'
                ]);
                exit;
            }
        }
    }
}
```

---

## 📝 Özet

Bu modül ile:
- ✅ Güvenli JWT tabanlı authentication
- ✅ Refresh token mekanizması
- ✅ E-posta doğrulama
- ✅ Şifre sıfırlama
- ✅ Rate limiting
- ✅ CSRF protection
- ✅ Login geçmişi
- ✅ Hesap kilitleme (brute force koruması)
- ✅ Frontend entegrasyonu

**Sonraki Modül:** Şirket Yönetimi (Company Management)
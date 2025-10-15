# 🔐 SECURITY GUIDE - Ön Muhasebe Sistemi

## Güvenlik Yapılandırması

### JWT Secret Güncelleme

**ÖNEMLİ:** Production ortamında mutlaka güçlü bir JWT secret kullanın!

```bash
# Yeni secret oluştur
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"

# .env dosyasında günc elle
JWT_SECRET=<yukarıdaki-komuttan-gelen-64-karakter>
```

### Environment Değişkenleri

#### Local Development
```env
APP_ENV=local
APP_DEBUG=true
FORCE_HTTPS=false
SESSION_SECURE=false
```

#### Production
```env
APP_ENV=production
APP_DEBUG=false
FORCE_HTTPS=true
SESSION_SECURE=true
LOG_LEVEL=error
```

## Güvenlik Kontrol Listesi

### Deployment Öncesi

- [ ] JWT_SECRET güçlü ve unique
- [ ] APP_DEBUG=false
- [ ] FORCE_HTTPS=true
- [ ] SESSION_SECURE=true
- [ ] Database şifreleri güçlü
- [ ] .env dosyası git'de yok (.gitignore'da)
- [ ] Hassas bilgiler loglanmıyor
- [ ] Rate limiting aktif
- [ ] CSRF protection aktif

### Düzenli Kontroller

- [ ] Dependency güncellemeleri (`composer update`)
- [ ] Security scan (`composer audit`)
- [ ] Log dosyaları kontrol
- [ ] Başarısız login denemelerini izle
- [ ] Database backup test

## Güvenlik Özellikleri

### 1. Authentication
- JWT token tabanlı
- Access token: 1 saat
- Refresh token: 30 gün
- Argon2 password hashing

### 2. Input Validation
- Tüm user input'ları validate edilir
- XSS koruması (htmlspecialchars)
- SQL Injection koruması (PDO prepared statements)

### 3. CSRF Protection
- Form submission'larda CSRF token kontrolü
- API endpoint'lerinde JWT yeterli

### 4. Rate Limiting
- API: 60 request/dakika
- Login: 5 deneme/15 dakika
- IP bazlı tracking

### 5. Session Security
```env
SESSION_HTTPONLY=true    # JavaScript erişemez
SESSION_SECURE=true      # Sadece HTTPS
SESSION_SAMESITE=strict  # Cross-site koruması
```

### 6. Headers
```php
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
```

## Güvenlik İhlali Durumunda

### Immediate Actions

1. **JWT Secret Değiştir**
   ```bash
   php -r "echo bin2hex(random_bytes(32));"
   # .env'de güncelleyin
   # Tüm kullanıcılar logout olur
   ```

2. **Database Backup Al**
   ```bash
   php scripts/backup-database.php
   ```

3. **Şüpheli Aktiviteleri Logla**
   ```bash
   grep "Failed login" storage/logs/security_*.log
   ```

4. **Etkilenen Kullanıcıları Bilgilendir**

### Investigation

1. Error logları incele: `storage/logs/error_*.log`
2. Security logları incele: `storage/logs/security_*.log`
3. Access logları incele (web server logs)
4. Database anomalileri kontrol et

## Güvenlik En İyi Uygulamalar

### Şifre Politikası
- Minimum 8 karakter
- En az 1 büyük harf
- En az 1 küçük harf
- En az 1 rakam
- En az 1 özel karakter

### API Güvenliği
- Her zaman JWT token zorunlu
- Sensitive data loglama
- Response'larda gereksiz bilgi verme
- Error mesajlarında detay verme

### Database Güvenliği
- Hazır statements kullan (SQL injection önleme)
- Minimum privilege principle
- Regular backup
- Encrypted connection (SSL/TLS)

### File Upload Güvenliği
- Allowed file types kontrolü
- File size limit (10MB)
- Virus scan (optional)
- Unique filename (hash)
- Storage outside web root

## Monitoring

### Log Files
```
storage/logs/
├── error_2025-10-15.log          # PHP errors
├── security_2025-10-15.log       # Security events
├── api_2025-10-15.log            # API requests
└── efatura_2025-10-15.log        # e-Fatura operations
```

### Security Events
- Failed login attempts
- Password changes
- Email changes
- Permission changes
- Suspicious activity

## Contact

Güvenlik sorunları için:
- Email: security@onmuhasebe.com
- Responsible disclosure policy

**Son Güncelleme:** 15 Ekim 2025

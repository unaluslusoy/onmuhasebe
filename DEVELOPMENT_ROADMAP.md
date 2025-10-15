# 📊 ÖN MUHASEBE SİSTEMİ - GELİŞTİRME YOLU HARİTASI

**Proje:** Parasut Benzeri Ön Muhasebe Yazılımı  
**Teknoloji:** PHP 8.2+, MySQL 8.0, Metronic 8, JWT Auth  
**Başlangıç:** 02 Ocak 2025  
**Hedef:** 30 Nisan 2025 (16 hafta)  
**Yaklaşım:** Yazılım Mühendisliği Prensipleri

---

## 🎯 PROJE ANALİZİ

### Modül İncelemesi Sonuçları

#### ✅ İncelenen Modüller (8/15):
1. **Authentication** (Modül 1) - JWT, 2FA, RBAC
2. **Company Management** (Modül 2) - Şirket, logo, e-fatura ayarları
3. **Cari Hesaplar** (Modül 3) - Müşteri/tedarikçi, bakiye, ekstre
4. **Ürün/Hizmet** (Modül 4) - *Dosya workspace dışında, manuel inceleme gerekli*
5. **Stok Yönetimi** (Modül 5) - Çoklu depo, lot/seri, transfer
6. **Teklif/Proforma** (Modül 6) - Kar marjı, faturaya dönüşüm
7. **Fatura** (Modül 7) - Satış/alış, vade, ödeme planı
8. **e-Fatura (GİB)** (Modül 8) - UBL-TR XML, mali mühür, API

#### 📊 Karmaşıklık Analizi:

| Modül | Tablo Sayısı | API Endpoint | Karmaşıklık | Tahmini Süre |
|-------|-------------|--------------|-------------|--------------|
| Authentication | 3 | 15 | Yüksek | 3 gün |
| Company | 1 | 8 | Orta | 2 gün |
| Cari Hesaplar | 2 | 12 | Yüksek | 4 gün |
| Ürün/Hizmet | 4 | 15 | Orta | 3 gün |
| Stok | 6 | 20+ | Çok Yüksek | 6 gün |
| Teklif | 3 | 12 | Orta | 3 gün |
| Fatura | 5 | 18 | Çok Yüksek | 7 gün |
| e-Fatura | 3 | 10 | Kritik | 8 gün |
| Ödeme | 3 | 12 | Yüksek | 4 gün |
| Banka | 4 | 15 | Yüksek | 5 gün |
| Çek/Senet | 3 | 10 | Orta | 3 gün |
| Gider | 2 | 10 | Düşük | 2 gün |
| Personel | 4 | 12 | Orta | 3 gün |
| Raporlama | 0 | 15 | Yüksek | 5 gün |
| Bildirim | 2 | 8 | Düşük | 2 gün |

**Toplam Backend Geliştirme:** ~60 gün (sadece backend)

---

## 🏗️ MİMARİ KARARLAR

### 1. Temel Mimari
- **Pattern:** MVC (Model-View-Controller)
- **API:** RESTful (JSON response)
- **Auth:** JWT (Access: 1h, Refresh: 30 gün)
- **Database:** MySQL 8.0 (InnoDB, utf8mb4_unicode_ci)
- **Frontend:** Metronic 8 + Vanilla JS/jQuery

### 2. Klasör Yapısı Kararı
```
/onmuhasebe/
├── /public/                  # Web root
│   ├── index.php            # Router
│   ├── /assets/             # CSS, JS, images
│   └── /uploads/            # Faturalar, logolar
├── /app/
│   ├── /Controllers/        # Business logic
│   ├── /Models/             # Database models
│   ├── /Services/           # Complex operations
│   ├── /Middleware/         # Auth, CSRF, Rate Limit
│   ├── /Helpers/            # Utilities
│   └── /Config/             # Database, app, routes
├── /database/
│   ├── /migrations/         # Schema changes
│   └── /seeds/              # Sample data
├── /vendor/                 # Composer
├── composer.json
└── .env                     # Environment variables
```

### 3. Güvenlik Katmanları
1. **Input Validation** (Tüm POST/PUT/DELETE)
2. **CSRF Token** (Form koruma)
3. **JWT Verification** (Tüm API endpoint'ler)
4. **Rate Limiting** (100 req/min/IP)
5. **SQL Injection Prevention** (PDO Prepared Statements)
6. **XSS Protection** (htmlspecialchars, CSP headers)
7. **Encryption** (Şifreler: Argon2, Hassas bilgi: OpenSSL AES-256)

### 4. Bağımlılık Stratejisi

#### Kritik Bağımlılıklar:
```json
{
  "require": {
    "php": ">=8.2",
    "firebase/php-jwt": "^6.0",           // JWT Auth
    "phpmailer/phpmailer": "^6.9",        // Email
    "league/csv": "^9.0",                 // Import/Export
    "mpdf/mpdf": "^8.2",                  // PDF oluşturma
    "guzzlehttp/guzzle": "^7.8",          // HTTP client (GİB API)
    "symfony/dotenv": "^6.4",             // .env parser
    "vlucas/phpdotenv": "^5.6",           // Alternative .env
    "predis/predis": "^2.2"               // Redis (cache, queue)
  }
}
```

### 5. Veritabanı Stratejisi

#### Migration Sırası:
1. `001_users_and_auth.sql` (Authentication)
2. `002_companies.sql` (Company Management)
3. `003_cari_accounts.sql` (Cari)
4. `004_products.sql` (Ürünler)
5. `005_warehouses_and_stock.sql` (Stok)
6. `006_teklifler.sql` (Teklif)
7. `007_faturalar.sql` (Fatura)
8. `008_efatura.sql` (e-Fatura)
9. `009_payments.sql` (Ödemeler)
10. `010_bank_accounts.sql` (Banka)
11. `011_checks_promissory.sql` (Çek/Senet)
12. `012_expenses.sql` (Giderler)
13. `013_personnel.sql` (Personel)
14. `014_notifications.sql` (Bildirimler)
15. `015_indexes_and_optimization.sql` (Optimizasyon)

#### İndeks Stratejisi:
- **Primary Keys:** AUTO_INCREMENT INT
- **Foreign Keys:** ON DELETE CASCADE/RESTRICT
- **Indexes:** Sık sorgulanan kolonlar (email, tax_number, invoice_date, status)
- **Fulltext:** Arama (ürün, cari)
- **Composite:** Çoklu sorgular (user_id + date, user_id + status)

---

## 📅 4 FAZLI GELİŞTİRME PLANI

### 🔵 FAZ 1: TEMEL ALT YAPI (Hafta 1-3) - 18 gün

#### Hedef: Sistemi çalıştırabilir hale getirmek

**Sprint 1.1 - Proje Kurulumu (3 gün)**
- [x] Workspace oluşturuldu ✅
- [ ] Veritabanı şeması tasarımı
- [ ] MySQL database oluşturma
- [ ] Composer kurulumu ve bağımlılıklar
- [ ] .env yapılandırması
- [ ] Autoloader (PSR-4) kurulumu
- [ ] Routing sistemi (index.php)
- [ ] Error handling ve logging

**Sprint 1.2 - Authentication (5 gün)**
- [ ] User modeli ve migration
- [ ] JWT Service (token generate/verify)
- [ ] AuthController (register, login, logout)
- [ ] AuthMiddleware (JWT verification)
- [ ] Refresh token mekanizması
- [ ] Email verification
- [ ] Password reset
- [ ] 2FA (opsiyonel)
- [ ] Login/Register UI (Metronic)

**Sprint 1.3 - Company & Cari (5 gün)**
- [ ] Company modeli ve CRUD
- [ ] Logo upload fonksiyonu
- [ ] CariAccount modeli
- [ ] Cari CRUD API
- [ ] Cari bakiye hesaplama
- [ ] Cari ekstre oluşturma
- [ ] Company UI (Metronic form)
- [ ] Cari listesi UI (DataTable)

**Sprint 1.4 - Ürün Yönetimi (5 gün)**
- [ ] Product modeli
- [ ] Kategori sistemi
- [ ] Barkod yönetimi
- [ ] Varyant desteği
- [ ] Fiyat geçmişi
- [ ] Ürün CRUD API
- [ ] Ürün listesi UI
- [ ] Toplu import (CSV/Excel)

**Faz 1 Çıktıları:**
✅ Çalışan authentication sistemi  
✅ Şirket ve cari yönetimi  
✅ Ürün kataloğu  
✅ Temel admin panel (Metronic)  

---

### 🟢 FAZ 2: İLERİ MODÜLLER (Hafta 4-8) - 30 gün

#### Hedef: Temel iş akışlarını tamamlamak

**Sprint 2.1 - Stok Yönetimi (6 gün)**
- [ ] Warehouse modeli ve lokasyonlar
- [ ] StockMovement modeli
- [ ] Depo transfer işlemleri
- [ ] Stok sayımı
- [ ] Lot/seri no takibi
- [ ] FIFO/LIFO değerleme
- [ ] Stok raporları
- [ ] Stok UI (çoklu depo görünümü)

**Sprint 2.2 - Teklif Sistemi (3 gün)**
- [ ] Teklif modeli
- [ ] Teklif items (kalemler)
- [ ] Kar marjı hesaplama
- [ ] Durum yönetimi (taslak, gönderildi, kabul, red)
- [ ] PDF export
- [ ] E-posta gönderimi
- [ ] Faturaya dönüştürme
- [ ] Teklif formu UI

**Sprint 2.3 - Fatura Sistemi (7 gün)**
- [ ] Fatura modeli (satış/alış)
- [ ] Fatura items
- [ ] Vade takibi
- [ ] Ödeme planı
- [ ] Stok entegrasyonu (otomatik düşme)
- [ ] Cari hareket kaydı
- [ ] Fatura iptal/düzeltme
- [ ] Tekrar eden faturalar
- [ ] Fatura CRUD UI
- [ ] Fatura detay/print sayfası

**Sprint 2.4 - e-Fatura (GİB) (8 gün)**
- [ ] e-Fatura settings
- [ ] UBL-TR XML generator
- [ ] Mali mühür entegrasyonu (OpenSSL)
- [ ] GİB API client
- [ ] Outbox (giden e-fatura)
- [ ] Inbox (gelen e-fatura)
- [ ] Kabul/red işlemleri
- [ ] Otomatik senkronizasyon (cron)
- [ ] e-Arşiv desteği
- [ ] e-Fatura UI (gönder/al)

**Sprint 2.5 - Ödeme Sistemi (4 gün)**
- [ ] Payment modeli
- [ ] Tahsilat/ödeme kaydı
- [ ] Çoklu ödeme yöntemi (nakit, banka, kredi kartı)
- [ ] Fatura ile eşleştirme
- [ ] Kısmi ödeme desteği
- [ ] Ödeme planı
- [ ] Ödeme UI

**Sprint 2.6 - Test ve Stabilizasyon (2 gün)**
- [ ] Integration testleri
- [ ] Bug fixes
- [ ] Performance optimization
- [ ] Code review

**Faz 2 Çıktıları:**
✅ Tam özellikli fatura sistemi  
✅ e-Fatura entegrasyonu  
✅ Stok yönetimi  
✅ Ödeme takibi  

---

### 🟡 FAZ 3: FİNANSAL MODÜLLER (Hafta 9-11) - 15 gün

#### Hedef: Mali takip sistemlerini eklemek

**Sprint 3.1 - Banka Yönetimi (5 gün)**
- [ ] BankAccount modeli
- [ ] Havale işlemleri
- [ ] Banka ekstreleri
- [ ] Banka API entegrasyonu (opsiyonel)
- [ ] Otomatik mutabakat
- [ ] Banka UI

**Sprint 3.2 - Çek/Senet (3 gün)**
- [ ] Check/Promissory modeli
- [ ] Portföy yönetimi
- [ ] Ciro işlemleri
- [ ] Vade takvimi
- [ ] Protestolu çek
- [ ] Çek/senet UI

**Sprint 3.3 - Gider Yönetimi (2 gün)**
- [ ] Expense modeli
- [ ] Kategori sistemi
- [ ] Personel giderleri
- [ ] Düzenli giderler
- [ ] Makbuz yönetimi
- [ ] Gider UI

**Sprint 3.4 - Personel (3 gün)**
- [ ] Personnel modeli
- [ ] Maaş bordrosu
- [ ] Avans/prim
- [ ] İzin takibi
- [ ] SGK entegrasyonu (opsiyonel)
- [ ] Personel UI

**Sprint 3.5 - Bildirim Sistemi (2 gün)**
- [ ] Notification modeli
- [ ] E-posta servisi (PHPMailer)
- [ ] SMS servisi (API)
- [ ] Vade hatırlatmaları
- [ ] Stok uyarıları
- [ ] Push notification (opsiyonel)
- [ ] Bildirim UI

**Faz 3 Çıktıları:**
✅ Banka ve çek/senet takibi  
✅ Gider yönetimi  
✅ Personel sistemi  
✅ Otomatik bildirimler  

---

### 🔴 FAZ 4: RAPORLAMA & DEPLOYMENT (Hafta 12-16) - 25 gün

#### Hedef: Sistem tamamlama ve yayınlama

**Sprint 4.1 - Raporlama (5 gün)**
- [ ] Dashboard (Chart.js/ApexCharts)
- [ ] Gelir-gider raporu
- [ ] Cari bakiye raporu
- [ ] KDV beyanı
- [ ] Kar-zarar raporu
- [ ] Nakit akış
- [ ] Yaşlandırma raporu
- [ ] Excel export (League/CSV)
- [ ] Rapor UI (grafikler)

**Sprint 4.2 - Frontend Tamamlama (8 gün)**
- [ ] Tüm sayfalar için Metronic entegrasyonu
- [ ] Responsive design kontrolü
- [ ] DataTables (tüm listeler)
- [ ] Form validation (frontend)
- [ ] AJAX error handling
- [ ] Loading states
- [ ] Toast notifications
- [ ] Modal'lar
- [ ] Print layouts

**Sprint 4.3 - Güvenlik & Performans (4 gün)**
- [ ] Rate limiting (Redis)
- [ ] CSRF token tüm formlarda
- [ ] XSS protection
- [ ] SQL injection kontrol
- [ ] Query optimization
- [ ] Database indexing
- [ ] Redis cache implementasyonu
- [ ] CDN hazırlığı

**Sprint 4.4 - Test & Dokümantasyon (4 gün)**
- [ ] Unit testler (PHPUnit)
- [ ] Integration testler
- [ ] API dokümantasyonu (Postman/Swagger)
- [ ] Kullanıcı kılavuzu
- [ ] Kurulum kılavuzu
- [ ] Deployment guide
- [ ] Video tutorials (opsiyonel)

**Sprint 4.5 - Deployment (4 gün)**
- [ ] Production server setup
- [ ] SSL kurulumu (Let's Encrypt)
- [ ] Backup sistemi (günlük)
- [ ] Monitoring (error tracking)
- [ ] Cron jobs (e-fatura sync, bildirimler)
- [ ] Performance tuning
- [ ] Beta testing
- [ ] Production launch 🚀

**Faz 4 Çıktıları:**
✅ Kapsamlı raporlama sistemi  
✅ Tam Metronic entegrasyonu  
✅ Production-ready sistem  
✅ Dokümantasyon tamamlandı  

---

## 🔧 TEKNİK YAPI DETAYLARI

### API Response Standardı
```json
{
  "success": true,
  "data": { },
  "message": "İşlem başarılı",
  "errors": [],
  "meta": {
    "timestamp": "2025-01-02T10:30:00Z",
    "version": "1.0.0"
  }
}
```

### Error Handling
```php
try {
    // Business logic
} catch (ValidationException $e) {
    return Response::json([
        'success' => false,
        'errors' => $e->getErrors(),
        'message' => 'Validasyon hatası'
    ], 400);
} catch (Exception $e) {
    Log::error($e->getMessage());
    return Response::json([
        'success' => false,
        'message' => 'Sunucu hatası'
    ], 500);
}
```

### JWT Token Yapısı
```json
{
  "sub": 123,
  "email": "user@example.com",
  "role": "admin",
  "iat": 1704189000,
  "exp": 1704192600
}
```

### Naming Conventions
- **Klasörler:** PascalCase (Controllers, Models)
- **Dosyalar:** PascalCase (UserController.php)
- **Sınıflar:** PascalCase (UserService)
- **Metodlar:** camelCase (getUserById)
- **Değişkenler:** camelCase ($userId)
- **Sabitler:** UPPER_SNAKE_CASE (MAX_LOGIN_ATTEMPTS)
- **Tablolar:** snake_case (cari_accounts)
- **Kolonlar:** snake_case (created_at)

---

## 📈 İLERLEME TAKİP METRİKLERİ

### Kod Metrikleri
- **Code Coverage:** Minimum %70
- **Cyclomatic Complexity:** Maximum 10
- **Lines per File:** Maximum 500
- **Methods per Class:** Maximum 20

### Performans Hedefleri
- **API Response Time:** < 200ms (ortalama)
- **Page Load Time:** < 2s
- **Database Query:** < 100ms
- **File Upload:** < 5s (10MB)

### Güvenlik Kontrolleri
- [ ] OWASP Top 10 kontrolleri
- [ ] SQL Injection testleri
- [ ] XSS testleri
- [ ] CSRF testleri
- [ ] Authentication bypass testleri
- [ ] Authorization testleri

---

## 🎯 KRİTİK BAŞARI FAKTÖRLERİ

### Teknik
1. **Modüler Mimari:** Her modül bağımsız çalışabilmeli
2. **API First:** Frontend backend'den bağımsız
3. **Güvenlik:** Her katmanda kontrol
4. **Performance:** Optimize edilmiş sorgular
5. **Scalability:** Çoklu kullanıcı desteği

### İş Süreci
1. **Sprint Planning:** Her sprint başında hedef belirleme
2. **Daily Progress:** Günlük ilerleme takibi
3. **Code Review:** Her önemli değişiklik review
4. **Testing:** Her modül tamamlandığında test
5. **Documentation:** Kod yazılırken dokümantasyon

### Kullanıcı Deneyimi
1. **Sezgisel Arayüz:** Metronic tema standardı
2. **Responsive:** Mobil uyumlu
3. **Hızlı:** Loading states, optimistic UI
4. **Hata Yönetimi:** Anlaşılır hata mesajları
5. **Yardım:** Tooltip, dokümantasyon

---

## 🚨 RİSK YÖNETİMİ

### Yüksek Riskler
1. **e-Fatura Entegrasyonu** (Teknik karmaşıklık)
   - Mitigation: Erken başlama, test ortamı
2. **Stok Yönetimi** (İş mantığı karmaşıklığı)
   - Mitigation: Detaylı analiz, incremental development
3. **Performans** (Büyük veri)
   - Mitigation: Indexing, caching, pagination
4. **Güvenlik** (Kritik)
   - Mitigation: Security audit, penetration testing

### Orta Riskler
1. **Zaman Aşımı**
   - Mitigation: Buffer time, MVP approach
2. **Scope Creep**
   - Mitigation: Strict requirement control
3. **Bağımlılık Problemleri**
   - Mitigation: Composer lock, vendor backup

---

## 📞 EKİP ROLLER & SORUMLULUKLAR

### Geliştirici (Ünal)
- Backend development
- Frontend integration
- Database design
- API development
- Testing
- Documentation
- Deployment

### Test Rolü
- Manual testing
- Bug reporting
- User acceptance testing

### Kullanıcı Rolü
- Requirement feedback
- Beta testing
- Feature prioritization

---

## 🎉 SONUÇ

Bu planlama ile:
- ✅ **16 haftalık** detaylı yol haritası
- ✅ **4 fazlı** aşamalı geliştirme
- ✅ **Sprint bazlı** ilerleme takibi
- ✅ **Risk yönetimi** stratejisi
- ✅ **Kalite metrikleri** belirlendi

**Sonraki Adım:** Faz 1, Sprint 1.1'i başlatmak için onay bekliyorum! 🚀

---

**Hazırlayan:** GitHub Copilot & Ünal  
**Tarih:** 02 Ocak 2025  
**Versiyon:** 1.0

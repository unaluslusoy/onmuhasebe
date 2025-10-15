# Sprint 1.3 - Company & Cari Modülleri Tamamlandı ✅

**Tarih:** 3 Ekim 2025  
**Durum:** TAMAMLANDI  
**Süre:** ~1 saat

---

## 📊 Yapılan İşler

### 1. Database Migrations ✅

#### 005_create_cari_accounts_table.sql
- ✅ `cari_accounts` tablosu oluşturuldu
- ✅ 40+ kolon (temel bilgiler, vergi, iletişim, adres, banka, e-fatura)
- ✅ Account types: customer, supplier, both
- ✅ Fulltext index (arama için)
- ✅ Otomatik bakiye takibi (current_balance)
- ✅ Soft delete desteği

**Kolonlar:**
- Kimlik: id, company_id, code, account_type
- Bilgi: title, name, surname, tax_office, tax_number, vkn, tckn
- İletişim: email, phone, mobile, fax, website
- Adres: billing_address, shipping_address (+ district, city, country, postal_code)
- Mali: currency, payment_term, credit_limit, current_balance
- Banka: bank_name, bank_branch, bank_account_no, iban
- Diğer: notes, tags, risk_group, customer_group
- e-Fatura: efatura_enabled, efatura_alias

#### 006_create_cari_transactions_table.sql
- ✅ `cari_transactions` tablosu oluşturuldu
- ✅ Transaction types: invoice_sale, invoice_purchase, payment_received, payment_made, opening_balance, adjustment, other
- ✅ Borç/Alacak takibi (debit/credit)
- ✅ Çoklu para birimi desteği
- ✅ Referans sistemi (fatura, ödeme vb.)
- ✅ **3 Trigger oluşturuldu:**
  - `update_cari_balance_after_insert` → Yeni işlemde bakiye güncelle
  - `update_cari_balance_after_update` → İşlem güncellemede bakiye düzelt
  - `update_cari_balance_after_delete` → İşlem silinince bakiye geri al

### 2. Models ✅

#### CariAccount.php (500+ satır)
**Public Methods:**
- `create($data)` → Yeni cari hesap oluştur, otomatik kod üret
- `find($id, $companyId)` → ID ile bul
- `findByCode($code, $companyId)` → Cari kodu ile bul
- `update($id, $data, $companyId)` → Güncelle
- `delete($id, $companyId)` → Soft delete
- `getAll($companyId, $filters, $page, $perPage)` → Filtreleme + pagination
- `getBalance($id)` → Güncel bakiye
- `getStatement($id, $startDate, $endDate)` → Cari ekstre (tarih aralıklı)
- `getOverdue($companyId)` → Vadesi geçmiş hesaplar
- `getStats($companyId)` → İstatistikler (toplam müşteri, tedarikçi, alacak, borç)

**Private Methods:**
- `generateAccountCode($companyId, $type)` → Otomatik cari kodu (C000001, S000001, B000001)

**Özellikler:**
- ✅ Otomatik cari kodu oluşturma (C: Customer, S: Supplier, B: Both)
- ✅ Company-level security (her kullanıcı sadece kendi şirketini görür)
- ✅ Soft delete desteği
- ✅ Advanced filtering (tip, arama, aktif/pasif)
- ✅ Bakiye hesaplama
- ✅ Ekstre oluşturma
- ✅ Vadesi geçmiş hesaplar raporu

### 3. Controllers ✅

#### CompanyController.php
**Endpoints:**
- `GET /api/companies` → Tüm şirketler (admin only)
- `GET /api/company/me` → Kullanıcının şirketi
- `GET /api/companies/{id}` → Şirket detayı
- `POST /api/companies` → Yeni şirket oluştur
- `PUT /api/companies/{id}` → Şirket güncelle (sadece kendi şirketi veya super admin)
- `DELETE /api/companies/{id}` → Şirket sil (sadece super admin)
- `POST /api/companies/{id}/logo` → Logo yükle (max 2MB, JPG/PNG/GIF)

**Güvenlik:**
- ✅ Sadece kendi şirketini düzenleyebilir
- ✅ Super admin tüm şirketleri yönetebilir
- ✅ Logo upload validasyonu (tip, boyut)

#### CariController.php
**Endpoints:**
- `GET /api/cari` → Cari listesi (pagination + filters)
- `GET /api/cari/stats` → İstatistikler
- `GET /api/cari/overdue` → Vadesi geçenler
- `GET /api/cari/{id}` → Cari detayı
- `GET /api/cari/code/{code}` → Cari kodu ile arama
- `POST /api/cari` → Yeni cari oluştur (otomatik kod)
- `PUT /api/cari/{id}` → Cari güncelle
- `DELETE /api/cari/{id}` → Cari sil (soft delete)
- `GET /api/cari/{id}/balance` → Bakiye sorgula
- `GET /api/cari/{id}/statement` → Ekstre (tarih aralıklı)

**Filters:**
- `account_type`: customer / supplier / both
- `search`: Kod, ünvan, ad, email, vergi no
- `is_active`: 0 / 1

### 4. Routes ✅

routes.php güncellendi:
```php
// Company routes (7 endpoint)
$router->get('/companies', [CompanyController::class, 'index']);
$router->get('/company/me', [CompanyController::class, 'me']);
$router->get('/companies/{id}', [CompanyController::class, 'show']);
$router->post('/companies', [CompanyController::class, 'store']);
$router->put('/companies/{id}', [CompanyController::class, 'update']);
$router->delete('/companies/{id}', [CompanyController::class, 'delete']);
$router->post('/companies/{id}/logo', [CompanyController::class, 'uploadLogo']);

// Cari routes (10 endpoint)
$router->get('/cari', [CariController::class, 'index']);
$router->get('/cari/stats', [CariController::class, 'stats']);
$router->get('/cari/overdue', [CariController::class, 'overdue']);
$router->get('/cari/{id}', [CariController::class, 'show']);
$router->get('/cari/code/{code}', [CariController::class, 'showByCode']);
$router->post('/cari', [CariController::class, 'store']);
$router->put('/cari/{id}', [CariController::class, 'update']);
$router->delete('/cari/{id}', [CariController::class, 'delete']);
$router->get('/cari/{id}/balance', [CariController::class, 'balance']);
$router->get('/cari/{id}/statement', [CariController::class, 'statement']);
```

---

## 📈 Proje Durumu

### Database Tabloları (8/15)
- ✅ users
- ✅ refresh_tokens
- ✅ companies
- ✅ subscriptions
- ✅ subscription_plans
- ✅ payments
- ✅ **cari_accounts** (YENİ)
- ✅ **cari_transactions** (YENİ)

### Modüller (2/15)
- ✅ Authentication (Login, Register, JWT)
- ✅ Subscription (Trial, Plans, Upgrade)
- ✅ **Company Management** (YENİ)
- ✅ **Cari Hesaplar** (YENİ)

### API Endpoints (42+ toplam)
- ✅ Auth: 7 endpoint
- ✅ Subscription: 4 endpoint
- ✅ **Company: 7 endpoint** (YENİ)
- ✅ **Cari: 10 endpoint** (YENİ)

---

## 🧪 Test Önerileri

### 1. Company API Test
```bash
# Login (get token)
POST http://localhost:8000/api/auth/login
{
  "email": "admin@onmuhasebe.com",
  "password": "Admin123!"
}

# Kendi şirketini getir
GET http://localhost:8000/api/company/me
Authorization: Bearer <token>

# Şirket güncelle
PUT http://localhost:8000/api/companies/1
Authorization: Bearer <token>
{
  "name": "Test Şirketi A.Ş.",
  "tax_office": "İstanbul Vergi Dairesi",
  "tax_number": "1234567890"
}

# Logo yükle
POST http://localhost:8000/api/companies/1/logo
Authorization: Bearer <token>
Content-Type: multipart/form-data
logo: [file]
```

### 2. Cari API Test
```bash
# Yeni müşteri oluştur
POST http://localhost:8000/api/cari
Authorization: Bearer <token>
{
  "account_type": "customer",
  "title": "ABC Ticaret Ltd. Şti.",
  "name": "Ahmet",
  "surname": "Yılmaz",
  "email": "info@abc.com",
  "phone": "0212 555 1234",
  "tax_office": "Kadıköy",
  "tax_number": "1234567890",
  "payment_term": 30,
  "credit_limit": 50000.00
}

# Cari listesi (filtre ile)
GET http://localhost:8000/api/cari?account_type=customer&search=ABC&page=1&per_page=25
Authorization: Bearer <token>

# Cari bakiye
GET http://localhost:8000/api/cari/1/balance
Authorization: Bearer <token>

# Cari ekstre (tarih aralıklı)
GET http://localhost:8000/api/cari/1/statement?start_date=2025-01-01&end_date=2025-12-31
Authorization: Bearer <token>

# İstatistikler
GET http://localhost:8000/api/cari/stats
Authorization: Bearer <token>

# Vadesi geçenler
GET http://localhost:8000/api/cari/overdue
Authorization: Bearer <token>
```

### 3. Trigger Test (Database)
```sql
-- Test carisi oluştur
INSERT INTO cari_accounts (company_id, account_type, code, title, name) 
VALUES (1, 'customer', 'C000001', 'Test Müşteri', 'Test');

-- İşlem ekle (alacak)
INSERT INTO cari_transactions (company_id, cari_account_id, transaction_type, transaction_date, amount, debit, credit)
VALUES (1, 1, 'invoice_sale', '2025-10-03', 1000.00, 1000.00, 0.00);

-- Bakiye kontrol et (1000.00 olmalı)
SELECT current_balance FROM cari_accounts WHERE id = 1;

-- Ödeme ekle (borç)
INSERT INTO cari_transactions (company_id, cari_account_id, transaction_type, transaction_date, amount, debit, credit)
VALUES (1, 1, 'payment_received', '2025-10-03', 500.00, 0.00, 500.00);

-- Bakiye kontrol et (500.00 olmalı)
SELECT current_balance FROM cari_accounts WHERE id = 1;
```

---

## 🎯 Sonraki Adımlar (Sprint 1.4)

### Modül 4: Ürün/Hizmet Yönetimi (5 gün)
- [ ] products tablosu migration
- [ ] product_categories tablosu
- [ ] product_variants tablosu (beden, renk vb.)
- [ ] product_price_history tablosu
- [ ] Product modeli (CRUD, barkod, kategori, varyant)
- [ ] ProductController (15 endpoint)
- [ ] Toplu import/export (CSV/Excel)
- [ ] Barkod arama sistemi

**Tahmin:** 3-5 gün (orta karmaşıklık)

---

## 🚀 Sprint 1.3 Özeti

✅ **Tamamlandı:**
- 2 yeni migration dosyası
- 2 yeni tablo (cari_accounts, cari_transactions)
- 3 database trigger (otomatik bakiye güncelleme)
- 1 yeni model (CariAccount - 500+ satır)
- 2 yeni controller (Company, Cari)
- 17 yeni API endpoint
- Route tanımları

✅ **Özellikler:**
- Otomatik cari kodu oluşturma
- Bakiye otomatik güncelleme (trigger)
- Cari ekstre sistemi
- Vadesi geçmiş hesaplar raporu
- Filtreleme ve pagination
- Soft delete desteği
- Multi-currency (TRY, USD, EUR)
- e-Fatura hazırlığı

✅ **Güvenlik:**
- Company-level data isolation
- Super admin bypass
- Input validation
- SQL injection koruması (PDO)

---

**Hazırlayan:** GitHub Copilot  
**Süre:** ~1 saat  
**Status:** ✅ BAŞARILI

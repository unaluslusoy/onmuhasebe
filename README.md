# Ön Muhasebe Sistemi - Todo ve Proje Yönetimi

## 🎯 Proje Hakkında
Modern, bulut tabanlı ve tam entegre bir ön muhasebe sistemi. **Parasut.com'un tüm özelliklerini** içeren, KOBİ'ler ve girişimciler için kapsamlı bir finansal yönetim platformu.

## 🏗️ Mimari
- **Backend:** PHP 8.2+, MySQL 8.0+, Redis
- **Frontend:** HTML5, JavaScript ES6+, TailwindCSS
- **Tema:** Metronic 8 (Bootstrap 5)
- **API:** RESTful API
- **Güvenlik:** JWT Authentication, CSRF Protection, Rate Limiting

## 📦 Kurulum

### Gereksinimler
- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js 18+ (Frontend build için)

### Adımlar

1. **Repository'yi klonlayın:**
```bash
cd c:\xampp\htdocs
git clone [repo-url] onmuhasebe
cd onmuhasebe
```

2. **Composer bağımlılıklarını yükleyin:**
```bash
composer install
```

3. **Veritabanını oluşturun:**
```bash
mysql -u root -p
CREATE DATABASE on_muhasebe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. **Veritabanı tablolarını oluşturun:**
```bash
php scripts/install_database.php
```

5. **`.env` dosyasını yapılandırın:**
```bash
cp .env.example .env
# .env dosyasını düzenleyin
```

6. **Frontend asset'lerini build edin:**
```bash
npm install
npm run build
```

7. **Apache'yi başlatın ve tarayıcıda açın:**
```
http://localhost/onmuhasebe
```

## 📋 Modül Listesi (15 Modül)

### ✅ Tamamlanacak Modüller
1. **Authentication** (Kimlik Doğrulama)
2. **Company Management** (Şirket Yönetimi)
3. **Cari Hesaplar** (Müşteri/Tedarikçi)
4. **Ürün/Hizmet Yönetimi**
5. **Stok Yönetimi** (Çoklu Depo)
6. **Teklif Yönetimi**
7. **Fatura Yönetimi**
8. **e-Fatura Entegrasyonu** (GİB)
9. **Ödemeler** (Tahsilat/Ödeme)
10. **Banka Yönetimi**
11. **Çek/Senet Takibi**
12. **Gider Yönetimi**
13. **Personel Yönetimi**
14. **Raporlama ve Analizler**
15. **Bildirimler** (E-posta, SMS)

## 🚀 Geliştirme Planı

### Faz 1: Temel Modüller (1-4 Hafta)
- Authentication sistemi
- Şirket ve cari yönetimi
- Ürün/hizmet kataloğu
- Temel fatura oluşturma

### Faz 2: İleri Özellikler (4-6 Hafta)
- Stok yönetimi ve çoklu depo
- Teklif ve proforma
- e-Fatura entegrasyonu
- Ödeme sistemi

### Faz 3: Finansal Modüller (6-8 Hafta)
- Banka entegrasyonu
- Çek/senet takibi
- Gider yönetimi
- Personel bordrosu

### Faz 4: Raporlama (8-10 Hafta)
- Gelir-gider raporları
- Kar-zarar analizi
- KDV beyanı
- Dashboard ve grafikler

## 📚 Dokümantasyon
- [API Dokümantasyonu](docs/API.md)
- [Veritabanı Şeması](docs/DATABASE.md)
- [Güvenlik Kılavuzu](docs/SECURITY.md)
- [e-Fatura Entegrasyonu](docs/EFATURA.md)

## 🔐 Güvenlik
- JWT token tabanlı kimlik doğrulama
- Şifreler Argon2 ile hashlenir
- SQL Injection koruması (Prepared Statements)
- XSS ve CSRF koruması
- Rate limiting (100 req/min)
- HTTPS zorunlu (production)

## 🤝 Katkıda Bulunma
Pull request'ler memnuniyetle karşılanır. Büyük değişiklikler için lütfen önce bir issue açın.

## 📝 Lisans
[MIT License](LICENSE)

## 👨‍💻 Geliştirici
**Ünal**
- Email: [your-email]
- GitHub: [your-github]

---
**Son Güncelleme:** 2025-01-02

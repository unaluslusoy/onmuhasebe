# Master Layout Dönüşüm Kontrol Listesi

## 📊 Genel Durum

### ✅ Tamamlanan Sayfalar
- [x] Dashboard (`/`) - ✅ Test edildi
- [x] Faturalar Listesi (`/faturalar`) - ✅ Test edildi

### ⏳ Dönüştürülecek Sayfalar
- [ ] Fatura Detay (`/fatura/{id}`)
- [ ] Fatura Oluştur (`/fatura/yeni`)
- [ ] Fatura Düzenle (`/fatura/{id}/duzenle`)
- [ ] Diğer modül sayfaları

---

## 🔄 Sayfa Dönüşüm Süreci

### Adım 1: Mevcut Sayfayı Analiz Et

```bash
# Örnek: detail.php
cat app/Views/invoices/detail.php
```

**Kontrol Listesi:**
- [ ] Full HTML yapısı var mı? (`<html>`, `<body>` vs.)
- [ ] Header/Sidebar/Footer include'ları var mı?
- [ ] Container yapısı nasıl?
- [ ] JavaScript/CSS dosyaları neler?

### Adım 2: Content Dosyası Oluştur

```bash
# Yeni content dosyası
touch app/Views/invoices/detail-content.php
```

**İçerik Dosyasında:**
- [ ] Sadece sayfa içeriği (row, card, table vs.)
- [ ] HTML, head, body YOK
- [ ] Layout wrapper'ları YOK
- [ ] Sadece business content

**Örnek:**
```php
<!--begin::Row-->
<div class="row">
    <!-- Card'lar, tablolar, formlar -->
</div>
<!--end::Row-->
```

### Adım 3: Ana Sayfa Dosyasını Dönüştür

**ESKİ YAPIYI SİL:**
```php
// ❌ Bunları SİL
<!DOCTYPE html>
<html>
<head>...</head>
<body id="kt_body">
    <?php require_once 'sidebar.php'; ?>
    <div class="wrapper">
        <?php require_once 'header.php'; ?>
        ...
    </div>
</body>
</html>
```

**YENİ YAPIYI EKLE:**
```php
// ✅ Bu şekilde YAZ
<?php
$pageTitle = 'Sayfa Başlığı';
$pageIcon = 'ki-duotone ki-icon-name';
$breadcrumbs = [...];

ob_start();
?>
<!-- Toolbar actions -->
<?php
$toolbarActions = ob_get_clean();

ob_start();
?>
<!-- Additional CSS -->
<?php
$additionalCSS = ob_get_clean();

ob_start();
?>
<!-- Additional JS -->
<?php
$additionalJS = ob_get_clean();

$contentFile = __DIR__ . '/detail-content.php';
require_once __DIR__ . '/../layouts/master.php';
?>
```

### Adım 4: Test Et

```bash
# Tarayıcıda aç
http://localhost:8000/fatura/1
```

**Kontrol Listesi:**
- [ ] Sayfa yükleniyor mu?
- [ ] Header doğru mu?
- [ ] Sidebar doğru mu?
- [ ] Breadcrumb doğru mu?
- [ ] İçerik doğru görünüyor mu?
- [ ] Footer doğru mu?
- [ ] Console'da hata yok mu?
- [ ] Network'te 404 yok mu?

### Adım 5: Responsive Test

**Kontrol Listesi:**
- [ ] Desktop (1920px) ✅
- [ ] Laptop (1366px) ✅
- [ ] Tablet (768px) ✅
- [ ] Mobile (375px) ✅

---

## 📋 Sayfa-Sayfa Dönüşüm

### 1. Fatura Detay (`invoices/detail.php`)

**Dosyalar:**
- `detail.php` (ana dosya - master layout kullanır)
- `detail-content.php` (içerik - oluşturulacak)

**İçerik:**
```php
// detail.php
$pageTitle = 'Fatura Detay';
$pageIcon = 'ki-duotone ki-file fs-2';
$breadcrumbs = [
    ['text' => 'Ana Sayfa', 'url' => '/'],
    ['text' => 'Faturalar', 'url' => '/faturalar'],
    ['text' => 'Fatura #' . $invoice['invoice_number']]
];

// Toolbar actions
ob_start();
?>
<button class="btn btn-light-primary" data-bs-toggle="modal" data-bs-target="#payment-modal">
    <i class="ki-duotone ki-dollar fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
    Ödeme Al
</button>
<a href="/fatura/<?= $invoice['id'] ?>/pdf" class="btn btn-light-warning">
    <i class="ki-duotone ki-file-down fs-2"><span class="path1"></span><span class="path2"></span></i>
    PDF İndir
</a>
<a href="/fatura/<?= $invoice['id'] ?>/duzenle" class="btn btn-primary">
    <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
    Düzenle
</a>
<?php
$toolbarActions = ob_get_clean();

$contentFile = __DIR__ . '/detail-content.php';
require_once __DIR__ . '/../layouts/master.php';
```

**Content Structure:**
- [ ] Fatura bilgi kartı (müşteri, tarih, vade, tutar)
- [ ] Kalemler tablosu
- [ ] Ödeme geçmişi
- [ ] İşlem logları
- [ ] Ödeme modal

**Status:** ⏳ Yapılacak

---

### 2. Fatura Formu (`invoices/form.php`)

**Dosyalar:**
- `form.php` (ana dosya)
- `form-content.php` (içerik)

**İçerik:**
```php
// form.php
$isEdit = isset($invoice);
$pageTitle = $isEdit ? 'Fatura Düzenle' : 'Yeni Fatura';
$pageIcon = $isEdit ? 'ki-duotone ki-pencil fs-2' : 'ki-duotone ki-plus fs-2';
$breadcrumbs = [
    ['text' => 'Ana Sayfa', 'url' => '/'],
    ['text' => 'Faturalar', 'url' => '/faturalar'],
    ['text' => $pageTitle]
];

// Toolbar actions
ob_start();
?>
<a href="/faturalar" class="btn btn-light">
    <i class="ki-duotone ki-cross fs-2"></i>
    İptal
</a>
<button type="submit" form="invoice-form" class="btn btn-primary">
    <i class="ki-duotone ki-check fs-2"></i>
    Kaydet
</button>
<?php
$toolbarActions = ob_get_clean();

// Additional CSS
ob_start();
?>
<link href="/assets/plugins/custom/select2/select2.min.css" rel="stylesheet">
<link href="/assets/plugins/custom/flatpickr/flatpickr.min.css" rel="stylesheet">
<?php
$additionalCSS = ob_get_clean();

// Additional JS
ob_start();
?>
<script src="/assets/plugins/custom/select2/select2.min.js"></script>
<script src="/assets/plugins/custom/flatpickr/flatpickr.min.js"></script>
<script src="/assets/plugins/custom/flatpickr/l10n/tr.js"></script>
<script src="/assets/js/pages/invoice-form.js"></script>
<?php
$additionalJS = ob_get_clean();

$contentFile = __DIR__ . '/form-content.php';
require_once __DIR__ . '/../layouts/master.php';
```

**Content Structure:**
- [ ] Müşteri seçimi (Select2)
- [ ] Tarih seçimi (Flatpickr)
- [ ] Vade tarihi
- [ ] Kalemler (dinamik satır ekleme/silme)
- [ ] Tutar hesaplama (KDV, toplam)
- [ ] Not alanı

**Status:** ⏳ Yapılacak

---

## 🎯 Öncelik Sırası

### Yüksek Öncelik (Şimdi)
1. ✅ Dashboard - YAPILDI
2. ✅ Faturalar Listesi - YAPILDI
3. ⏳ Fatura Detay - ŞİMDİ
4. ⏳ Fatura Formu - SONRA

### Orta Öncelik (Sonra)
5. ⏳ Ödeme modal
6. ⏳ Müşteri sayfaları
7. ⏳ Ürün sayfaları
8. ⏳ Diğer modüller

### Düşük Öncelik (En Son)
9. ⏳ Ayarlar sayfaları
10. ⏳ Profil sayfaları
11. ⏳ Yardım sayfaları

---

## 🐛 Sık Karşılaşılan Sorunlar

### 1. Çift Header Görünüyor
**Neden:** Controller'da render() metoduyla sarmalama  
**Çözüm:** Controller'da direkt `require_once` kullan

```php
// ❌ YANLIŞ
return $this->render('view.php');

// ✅ DOĞRU
require_once __DIR__ . '/../../Views/view.php';
```

### 2. CSS/JS Yüklenmiyor
**Neden:** Path hatası veya master layout'ta eksik  
**Çözüm:** `$additionalCSS` ve `$additionalJS` kullan

### 3. Breadcrumb Görünmüyor
**Neden:** `$breadcrumbs` dizisi eksik  
**Çözüm:** Her sayfada breadcrumb tanımla

### 4. Container Genişliği Yanlış
**Neden:** `container-xxl` kullanımı  
**Çözüm:** Master layout'ta `container-fluid` kullanılıyor, değişiklik gereksiz

### 5. Card Body Bozuk
**Neden:** Yanlış padding sınıfları  
**Çözüm:** `px-0 pb-0` veya Metronic default'ları kullan

---

## ✅ Kontrol Listesi (Her Sayfa İçin)

### Dosya Yapısı
- [ ] Ana dosya oluşturuldu (örn: `detail.php`)
- [ ] Content dosyası oluşturuldu (örn: `detail-content.php`)
- [ ] Eski dosya yedeklendi (`.old` uzantısı)

### Layout Değişkenleri
- [ ] `$pageTitle` tanımlandı
- [ ] `$pageIcon` tanımlandı (varsa)
- [ ] `$breadcrumbs` tanımlandı
- [ ] `$toolbarActions` tanımlandı (varsa)
- [ ] `$additionalCSS` tanımlandı (varsa)
- [ ] `$additionalJS` tanımlandı (varsa)
- [ ] `$contentFile` tanımlandı
- [ ] `master.php` include edildi

### İçerik Dosyası
- [ ] Sadece business content var
- [ ] HTML/head/body YOK
- [ ] Layout wrapper'ları YOK
- [ ] Metronic component'leri kullanılmış

### Test
- [ ] Sayfa yükleniyor
- [ ] Console'da hata yok
- [ ] Network'te 404 yok
- [ ] Responsive çalışıyor
- [ ] JavaScript çalışıyor
- [ ] Form submit/AJAX çalışıyor

### Kod Kalitesi
- [ ] PHP hataları yok
- [ ] Türkçe karakter sorunları yok
- [ ] Number format düzgün (₺1.234,56)
- [ ] Tarih format düzgün (dd.MM.yyyy)
- [ ] Yorum satırları temiz

---

## 📊 İlerleme Takibi

### Güncel Durum
- **Tamamlanan:** 2/10 (20%)
- **Devam Eden:** 0/10 (0%)
- **Bekleyen:** 8/10 (80%)

### Hedefler
- **Bugün:** Fatura Detay + Fatura Formu (4/10 - 40%)
- **Bu Hafta:** Tüm fatura sayfaları (6/10 - 60%)
- **Önümüzdeki Hafta:** Diğer modüller (10/10 - 100%)

---

**SON GÜNCELLEª:** <?= date('d.m.Y H:i') ?>

# Metronic 8 Demo 1 - Resmi HTML Yapısı

## 🎯 Doğru Yapı (Metronic 8 Demo 1 Official)

Bu proje **https://preview.keenthemes.com/metronic8/demo1/** temasının resmi HTML yapısını kullanır.

### 📋 HTML Hiyerarşisi

```html
<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-*>
  
  <!--begin::App-->
  <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    
    <!--begin::Page-->
    <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
      
      <!--begin::Header-->
      <div id="kt_app_header" class="app-header">
        <div class="app-container container-fluid">
          <!-- Mobile toggle, logo, menu, navbar -->
        </div>
      </div>
      <!--end::Header-->
      
      <!--begin::Wrapper-->
      <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
        
        <!--begin::Sidebar-->
        <div id="kt_app_sidebar" class="app-sidebar">
          <!-- Logo, menu -->
        </div>
        <!--end::Sidebar-->
        
        <!--begin::Main-->
        <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
          
          <!--begin::Content wrapper-->
          <div class="d-flex flex-column flex-column-fluid">
            
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar">
              <div class="app-container container-fluid">
                <!-- Page title, breadcrumbs, actions -->
              </div>
            </div>
            <!--end::Toolbar-->
            
            <!--begin::Content-->
            <div id="kt_app_content" class="app-content">
              <div class="app-container container-fluid">
                <!-- PAGE CONTENT -->
              </div>
            </div>
            <!--end::Content-->
            
          </div>
          <!--end::Content wrapper-->
          
          <!--begin::Footer-->
          <div id="kt_app_footer" class="app-footer">
            <div class="app-container container-fluid">
              <!-- Footer content -->
            </div>
          </div>
          <!--end::Footer-->
          
        </div>
        <!--end:::Main-->
        
      </div>
      <!--end::Wrapper-->
      
    </div>
    <!--end::Page-->
    
  </div>
  <!--end::App-->
  
</body>
```

### 🔑 Önemli ID ve Class'lar

| Element | ID | Class | Açıklama |
|---------|----|----- --|----------|
| Body | `kt_app_body` | `app-default` | Ana body, data attributes ile yapılandırma |
| Root | `kt_app_root` | `app-root` | Ana wrapper |
| Page | `kt_app_page` | `app-page` | Sayfa container |
| Header | `kt_app_header` | `app-header` | Üst menü |
| Wrapper | `kt_app_wrapper` | `app-wrapper` | Sidebar + Main wrapper |
| Sidebar | `kt_app_sidebar` | `app-sidebar` | Sol menü |
| Main | `kt_app_main` | `app-main` | Ana içerik alanı |
| Toolbar | `kt_app_toolbar` | `app-toolbar` | Sayfa başlığı bölgesi |
| Content | `kt_app_content` | `app-content` | Sayfa içeriği |
| Footer | `kt_app_footer` | `app-footer` | Alt bilgi |

### 📦 Container Yapısı

Her bölümde **container** kullanımı:

```html
<!-- Header Container -->
<div class="app-container container-fluid">

<!-- Toolbar Container -->
<div class="app-container container-fluid">

<!-- Content Container -->
<div class="app-container container-fluid">
  <!-- Burada sayfa içeriği -->
</div>

<!-- Footer Container -->
<div class="app-container container-fluid">
```

### 📁 Dosya Yapısı

```
app/Views/
├── layouts/
│   ├── master.php              # Ana layout (tüm sayfalar bunu kullanır)
│   └── partials/
│       ├── header.php          # Header (app-header)
│       ├── sidebar.php         # Sidebar (app-sidebar)
│       └── footer.php          # Footer (app-footer)
├── dashboard/
│   ├── index.php               # Master layout kullanır
│   └── dashboard-content.php   # Sadece içerik
├── invoices/
│   ├── list.php                # Master layout kullanır
│   ├── list-content.php        # Sadece içerik
│   ├── detail.php              # Master layout kullanır
│   └── detail-content.php      # Sadece içerik
└── ...
```

## 🚀 Yeni Sayfa Oluşturma

### 1. İçerik Dosyası (örn: example-content.php)

Sadece sayfa içeriğini içerir (card'lar, tablolar, formlar vs.):

```php
<!--begin::Row-->
<div class="row">
    <!--begin::Col-->
    <div class="col-12">
        <!--begin::Card-->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Başlık</h3>
            </div>
            <div class="card-body">
                <!-- İçerik -->
            </div>
        </div>
        <!--end::Card-->
    </div>
    <!--end::Col-->
</div>
<!--end::Row-->
```

### 2. Ana Sayfa Dosyası (örn: example.php)

Master layout'u kullanır ve değişkenleri ayarlar:

```php
<?php
/**
 * Örnek Sayfa
 */

// Sayfa değişkenleri
$pageTitle = 'Sayfa Başlığı';
$pageIcon = 'ki-duotone ki-element-11';
$breadcrumbs = [
    ['text' => 'Ana Sayfa', 'url' => '/'],
    ['text' => 'Kategorî, 'url' => '/kategori'],
    ['text' => 'Sayfa']
];

// Toolbar aksiyonları (opsiyonel)
ob_start();
?>
<a href="/yeni" class="btn btn-primary">
    <i class="ki-duotone ki-plus fs-2"></i>
    Yeni Ekle
</a>
<?php
$toolbarActions = ob_get_clean();

// Ek CSS (opsiyonel)
ob_start();
?>
<link href="/assets/css/custom.css" rel="stylesheet">
<?php
$additionalCSS = ob_get_clean();

// Ek JavaScript (opsiyonel)
ob_start();
?>
<script src="/assets/js/pages/example.js"></script>
<?php
$additionalJS = ob_get_clean();

// Toolbar'ı gizlemek için (opsiyonel)
// $hideToolbar = true;

// İçerik dosyası
$contentFile = __DIR__ . '/example-content.php';

// Master layout'u include et
require_once __DIR__ . '/../layouts/master.php';
?>
```

### 3. Controller'dan Kullanım

```php
public function index() {
    // Session kontrolü yapıldıysa direkt view'i include et
    require_once __DIR__ . '/../../Views/example/index.php';
}
```

## ⚙️ Master Layout Değişkenleri

| Değişken | Tip | Zorunlu | Açıklama |
|----------|-----|---------|----------|
| `$pageTitle` | string | Evet | Sayfa başlığı (title ve h1) |
| `$pageIcon` | string | Hayır | Başlık ikonu (ki-duotone sınıfları) |
| `$breadcrumbs` | array | Hayır | Breadcrumb dizisi `[['text'=>'','url'=>'']]` |
| `$toolbarActions` | string | Hayır | Toolbar sağ taraf butonları (HTML) |
| `$additionalCSS` | string | Hayır | Ek CSS (link veya style) |
| `$additionalJS` | string | Hayır | Ek JavaScript (script) |
| `$contentFile` | string | Evet* | İçerik dosyası yolu |
| `$content` | string | Evet* | Direkt HTML içerik |
| `$hideToolbar` | bool | Hayır | Toolbar'ı gizle (varsayılan: false) |

*Not: `$contentFile` VEYA `$content` kullanılabilir.

## ❌ YAPMAYACAKLARIMIZ

1. ❌ Kendi layout yapımızı oluşturmak
2. ❌ Metronic class'larını değiştirmek
3. ❌ Custom CSS ile temanın yapısını bozmak
4. ❌ Eski `kt_body`, `page`, `wrapper` gibi class'ları kullanmak
5. ❌ Full HTML sayfaları oluşturmak (master layout kullan)

## ✅ YAPACAKLARIMIZ

1. ✅ Her zaman `master.php` layout'unu kullan
2. ✅ Metronic 8'in resmi component'lerini kullan
3. ✅ Resmi demodan component kopyala/yapıştır
4. ✅ İçerik ve layout'u ayır (separation of concerns)
5. ✅ Tüm sayfalarda aynı yapıyı koru

## 🎨 Metronic 8 Component Kaynakları

- **Resmi Demo**: https://preview.keenthemes.com/metronic8/demo1/
- **Components**: https://preview.keenthemes.com/metronic8/demo1/utilities/
- **Documentation**: https://preview.keenthemes.com/html/metronic/docs/
- **Local Theme**: `/public/lisanstema/demo/`

## 📝 Örnek Sayfalar

Proje içinde örnek olarak:

1. **Dashboard** (`app/Views/dashboard/index.php`)
   - Master layout kullanımı
   - Toolbar aksiyonları
   - Widget'lar

2. **Faturalar Listesi** (`app/Views/invoices/list.php`)
   - Master layout kullanımı
   - DataTable entegrasyonu
   - Filtreleme

3. **Fatura Detay** (yapılacak)
   - Modal kullanımı
   - Form işlemleri

## 🔧 Bakım ve Güncelleme

- **Tema Güncellemesi**: `/public/lisanstema/demo/` klasörünü güncelle
- **Global CSS/JS**: `master.php` içinde `plugins.bundle.*` ve `style.bundle.*`
- **Component Değişikliği**: Resmi demodan kopyala, özelleştirme yapma
- **Layout Değişikliği**: Sadece `master.php` üzerinden yapılandır

---

**NOT**: Bu yapı Metronic 8 Demo 1'in resmi HTML yapısıdır. Herhangi bir değişiklik yapmadan kullanılmalıdır.

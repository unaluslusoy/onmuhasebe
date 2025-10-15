<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura Detay - Ön Muhasebe</title>
    
    <!-- Theme CSS -->
    <link href="/lisanstema/demo/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/lisanstema/demo/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    
    <!-- Print Styles -->
    <link href="/assets/css/invoice-print.css" rel="stylesheet" type="text/css" media="print" />
</head>
<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed aside-enabled aside-fixed">
    
    <div class="d-flex flex-column flex-root">
        <div class="page d-flex flex-row flex-column-fluid">
            
            <!-- Aside (Sidebar) -->
            <?php include __DIR__ . '/../layouts/partials/sidebar.php'; ?>
            
            <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                
                <!-- Header -->
                <?php include __DIR__ . '/../layouts/partials/header.php'; ?>
                
                <!-- Content -->
                <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                    
                    <!-- Toolbar -->
                    <div class="toolbar" id="kt_toolbar">
                        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
                            <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                                <h1 class="d-flex text-dark fw-bold fs-3 align-items-center my-1">
                                    Fatura Detay
                                </h1>
                                <span class="h-20px border-gray-300 border-start mx-4"></span>
                                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
                                    <li class="breadcrumb-item text-muted">
                                        <a href="/" class="text-muted text-hover-primary">Anasayfa</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                                    </li>
                                    <li class="breadcrumb-item text-muted">
                                        <a href="/faturalar" class="text-muted text-hover-primary">Faturalar</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                                    </li>
                                    <li class="breadcrumb-item text-dark" id="invoice-number-breadcrumb">Fatura</li>
                                </ul>
                            </div>
                            
                            <div class="d-flex align-items-center gap-2 gap-lg-3">
                                <a href="/faturalar" class="btn btn-sm btn-light">
                                    <i class="ki-duotone ki-left fs-2"></i>
                                    Geri Dön
                                </a>
                                <button type="button" class="btn btn-sm btn-light-primary" id="btn-print">
                                    <i class="ki-duotone ki-printer fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                        <span class="path5"></span>
                                    </i>
                                    Yazdır
                                </button>
                                <button type="button" class="btn btn-sm btn-light-danger" id="btn-pdf">
                                    <i class="ki-duotone ki-file-down fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    PDF
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="dropdown">
                                    İşlemler
                                    <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-6 w-200px py-4">
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3" id="btn-approve">
                                            <i class="ki-duotone ki-check-circle fs-2 me-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            Onayla
                                        </a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3" id="btn-payment" data-bs-toggle="modal" data-bs-target="#payment-modal">
                                            <i class="ki-duotone ki-dollar fs-2 me-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                            Ödeme Kaydet
                                        </a>
                                    </div>
                                    <div class="separator my-2"></div>
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3" id="btn-email">
                                            <i class="ki-duotone ki-send fs-2 me-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            E-posta Gönder
                                        </a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="/fatura/duzenle/<?php echo $invoiceId ?? ''; ?>" class="menu-link px-3" id="btn-edit">
                                            <i class="ki-duotone ki-pencil fs-2 me-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            Düzenle
                                        </a>
                                    </div>
                                    <div class="separator my-2"></div>
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3 text-danger" id="btn-cancel">
                                            <i class="ki-duotone ki-cross-circle fs-2 me-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            İptal Et
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Post -->
                    <div class="post d-flex flex-column-fluid" id="kt_post">
                        <div id="kt_content_container" class="container-xxl">
                            
                            <!-- Invoice Card -->
                            <div class="card">
                                <div class="card-body p-lg-20">
                                    
                                    <!-- Invoice Header -->
                                    <div class="d-flex flex-column flex-xl-row">
                                        <div class="flex-lg-row-fluid me-xl-18 mb-10 mb-xl-0">
                                            <div class="mt-n1">
                                                <!-- Logo and Invoice Number -->
                                                <div class="d-flex flex-stack pb-10">
                                                    <a href="/">
                                                        <img alt="Logo" src="/lisanstema/assets/media/logos/default-dark.svg" style="height: 50px;" />
                                                    </a>
                                                    <div class="text-end">
                                                        <h3 class="fw-bold text-gray-800 mb-2" id="invoice-number">FATURA</h3>
                                                        <span class="text-muted fw-semibold fs-7" id="invoice-dates"></span>
                                                    </div>
                                                </div>
                                                
                                                <div class="m-0">
                                                    <!-- Status Badges -->
                                                    <div class="mb-8">
                                                        <div id="invoice-badges"></div>
                                                    </div>
                                                    
                                                    <!-- Customer & Company Info -->
                                                    <div class="row g-5 mb-12">
                                                        <div class="col-sm-6">
                                                            <div class="fw-semibold fs-7 text-gray-600 mb-1">Düzenleyen:</div>
                                                            <div class="fw-bold fs-6 text-gray-800" id="company-name">-</div>
                                                            <div class="fw-semibold fs-7 text-gray-600" id="company-address">-</div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="fw-semibold fs-7 text-gray-600 mb-1">Müşteri:</div>
                                                            <div class="fw-bold fs-6 text-gray-800" id="customer-name">-</div>
                                                            <div class="fw-semibold fs-7 text-gray-600" id="customer-address">-</div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Invoice Items Table -->
                                                    <div class="table-responsive mb-12">
                                                        <table class="table g-5 gs-0 mb-0 fw-bold text-gray-700">
                                                            <thead>
                                                                <tr class="border-bottom fs-7 fw-bold text-gray-700 text-uppercase">
                                                                    <th class="min-w-300px w-475px">Ürün/Hizmet</th>
                                                                    <th class="min-w-100px w-100px text-end">Miktar</th>
                                                                    <th class="min-w-100px w-150px text-end">Birim Fiyat</th>
                                                                    <th class="min-w-100px w-100px text-end">Tutar</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="invoice-items">
                                                                <tr>
                                                                    <td colspan="4" class="text-center py-10">
                                                                        <div class="spinner-border text-primary" role="status">
                                                                            <span class="visually-hidden">Yükleniyor...</span>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    
                                                    <!-- Totals -->
                                                    <div class="d-flex justify-content-end">
                                                        <div class="mw-300px">
                                                            <div class="d-flex flex-stack mb-3">
                                                                <div class="fw-semibold pe-10 text-gray-600 fs-7">Ara Toplam:</div>
                                                                <div class="text-end fw-bold fs-6 text-gray-800" id="subtotal">₺0.00</div>
                                                            </div>
                                                            <div class="d-flex flex-stack mb-3">
                                                                <div class="fw-semibold pe-10 text-gray-600 fs-7">İndirim:</div>
                                                                <div class="text-end fw-bold fs-6 text-gray-800" id="discount">₺0.00</div>
                                                            </div>
                                                            <div class="d-flex flex-stack mb-3">
                                                                <div class="fw-semibold pe-10 text-gray-600 fs-7">KDV:</div>
                                                                <div class="text-end fw-bold fs-6 text-gray-800" id="tax">₺0.00</div>
                                                            </div>
                                                            <div class="separator separator-dashed my-4"></div>
                                                            <div class="d-flex flex-stack">
                                                                <div class="fw-bold pe-10 text-gray-800 fs-4">TOPLAM:</div>
                                                                <div class="text-end fw-boldest fs-2x text-primary" id="total">₺0.00</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Notes -->
                                                    <div class="mt-15" id="notes-section" style="display: none;">
                                                        <div class="mb-0">
                                                            <span class="fw-bold text-gray-800 fs-6">Notlar:</span>
                                                            <span class="fw-semibold text-gray-600 fs-6" id="invoice-notes"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Sidebar Info Cards -->
                                        <div class="m-0">
                                            
                                            <!-- Payment Details Card -->
                                            <div class="card card-flush bg-light-primary mb-8">
                                                <div class="card-header">
                                                    <div class="card-title">
                                                        <h3 class="fw-bold m-0">Ödeme Bilgileri</h3>
                                                    </div>
                                                </div>
                                                <div class="card-body pt-0">
                                                    <div class="mb-5">
                                                        <div class="fw-semibold text-gray-600 fs-7">Durum:</div>
                                                        <div class="fw-bold fs-6" id="payment-status-text">-</div>
                                                    </div>
                                                    <div class="mb-5">
                                                        <div class="fw-semibold text-gray-600 fs-7">Toplam Tutar:</div>
                                                        <div class="fw-bold fs-6 text-gray-800" id="sidebar-total">₺0.00</div>
                                                    </div>
                                                    <div class="mb-5">
                                                        <div class="fw-semibold text-gray-600 fs-7">Ödenen:</div>
                                                        <div class="fw-bold fs-6 text-success" id="paid-amount">₺0.00</div>
                                                    </div>
                                                    <div class="mb-0">
                                                        <div class="fw-semibold text-gray-600 fs-7">Kalan:</div>
                                                        <div class="fw-bold fs-6 text-danger" id="remaining-amount">₺0.00</div>
                                                    </div>
                                                    <div class="separator separator-dashed my-5"></div>
                                                    <div class="mb-0">
                                                        <div class="fw-semibold text-gray-600 fs-7 mb-1">Vade Tarihi:</div>
                                                        <div class="fw-bold fs-6 text-gray-800" id="due-date">-</div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Payment History -->
                                            <div class="card card-flush mb-8">
                                                <div class="card-header">
                                                    <div class="card-title">
                                                        <h3 class="fw-bold m-0">Ödeme Geçmişi</h3>
                                                    </div>
                                                </div>
                                                <div class="card-body pt-0" id="payment-history">
                                                    <div class="text-gray-600 text-center py-5">
                                                        Henüz ödeme kaydı bulunmuyor.
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <?php include __DIR__ . '/../layouts/partials/footer.php'; ?>
                
            </div>
        </div>
    </div>
    
    <!-- Payment Modal (will be included separately) -->
    <div id="payment-modal-container"></div>
    
    <!-- Scripts -->
    <script src="/lisanstema/demo/assets/plugins/global/plugins.bundle.js"></script>
    <script src="/lisanstema/demo/assets/js/scripts.bundle.js"></script>
    
    <!-- Invoice Detail JS -->
    <script>
        // Pass invoice ID to JavaScript
        window.INVOICE_ID = <?php echo $invoiceId ?? 'null'; ?>;
    </script>
    <script src="/assets/js/pages/invoices-detail.js"></script>
    
</body>
</html>

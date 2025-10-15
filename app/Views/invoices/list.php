<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faturalar - Ön Muhasebe</title>
    
    <!-- Theme CSS -->
    <link href="/lisanstema/demo/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/lisanstema/demo/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        .badge-light-success { background-color: #e8fff3; color: #50cd89; }
        .badge-light-warning { background-color: #fff8dd; color: #ffc700; }
        .badge-light-danger { background-color: #ffe2e5; color: #f1416c; }
        .badge-light-info { background-color: #e1f0ff; color: #009ef7; }
        .badge-light-primary { background-color: #e1f0ff; color: #009ef7; }
        .invoice-actions { white-space: nowrap; }
    </style>
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
                                    <i class="ki-duotone ki-file fs-2 text-primary me-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Faturalar
                                </h1>
                                <span class="h-20px border-gray-300 border-start mx-4"></span>
                                <small class="text-muted fs-7 fw-semibold my-1">Tüm faturalarınızı yönetin</small>
                            </div>
                            
                            <div class="d-flex align-items-center gap-2 gap-lg-3">
                                <a href="/fatura/olustur" class="btn btn-sm btn-primary">
                                    <i class="ki-duotone ki-plus fs-2"></i>
                                    Yeni Fatura
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Post -->
                    <div class="post d-flex flex-column-fluid" id="kt_post">
                        <div id="kt_content_container" class="container-xxl">
                            
                            <!-- Stats Cards -->
                            <div class="row g-5 g-xl-8 mb-5">
                                <div class="col-xl-3">
                                    <div class="card card-flush h-xl-100">
                                        <div class="card-body d-flex flex-column justify-content-between pb-0">
                                            <div class="d-flex flex-stack">
                                                <div class="text-gray-700 fw-semibold fs-6 me-2">Toplam Fatura</div>
                                                <div class="symbol symbol-30px">
                                                    <span class="symbol-label bg-light-primary">
                                                        <i class="ki-duotone ki-file-up fs-3 text-primary">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center pt-3">
                                                <div class="fs-2hx fw-bold text-dark me-2" id="total-invoices">0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-xl-3">
                                    <div class="card card-flush h-xl-100">
                                        <div class="card-body d-flex flex-column justify-content-between pb-0">
                                            <div class="d-flex flex-stack">
                                                <div class="text-gray-700 fw-semibold fs-6 me-2">Ödenmemiş</div>
                                                <div class="symbol symbol-30px">
                                                    <span class="symbol-label bg-light-danger">
                                                        <i class="ki-duotone ki-cross-circle fs-3 text-danger">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center pt-3">
                                                <div class="fs-2hx fw-bold text-danger me-2" id="unpaid-amount">₺0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-xl-3">
                                    <div class="card card-flush h-xl-100">
                                        <div class="card-body d-flex flex-column justify-content-between pb-0">
                                            <div class="d-flex flex-stack">
                                                <div class="text-gray-700 fw-semibold fs-6 me-2">Tahsil Edilen</div>
                                                <div class="symbol symbol-30px">
                                                    <span class="symbol-label bg-light-success">
                                                        <i class="ki-duotone ki-check-circle fs-3 text-success">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center pt-3">
                                                <div class="fs-2hx fw-bold text-success me-2" id="paid-amount">₺0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-xl-3">
                                    <div class="card card-flush h-xl-100">
                                        <div class="card-body d-flex flex-column justify-content-between pb-0">
                                            <div class="d-flex flex-stack">
                                                <div class="text-gray-700 fw-semibold fs-6 me-2">Vadesi Geçmiş</div>
                                                <div class="symbol symbol-30px">
                                                    <span class="symbol-label bg-light-warning">
                                                        <i class="ki-duotone ki-time fs-3 text-warning">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center pt-3">
                                                <div class="fs-2hx fw-bold text-warning me-2" id="overdue-count">0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Invoices Table Card -->
                            <div class="card">
                                <div class="card-header border-0 pt-6">
                                    <div class="card-title">
                                        <div class="d-flex align-items-center position-relative my-1">
                                            <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            <input type="text" id="search-input" class="form-control form-control-solid w-250px ps-13" placeholder="Fatura ara..." />
                                        </div>
                                    </div>
                                    
                                    <div class="card-toolbar">
                                        <div class="d-flex justify-content-end gap-2" data-kt-invoice-table-toolbar="base">
                                            <!-- Filter -->
                                            <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                <i class="ki-duotone ki-filter fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                                Filtrele
                                            </button>
                                            <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                                                <div class="px-7 py-5">
                                                    <div class="fs-5 text-dark fw-bold">Filtrele</div>
                                                </div>
                                                <div class="separator border-gray-200"></div>
                                                <div class="px-7 py-5" data-kt-invoice-table-filter="form">
                                                    <div class="mb-5">
                                                        <label class="form-label fs-6 fw-semibold">Fatura Tipi:</label>
                                                        <select class="form-select form-select-solid" id="filter-type">
                                                            <option value="">Tümü</option>
                                                            <option value="sales">Satış Faturası</option>
                                                            <option value="purchase">Alış Faturası</option>
                                                            <option value="sales_return">Satış İade</option>
                                                            <option value="purchase_return">Alış İade</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-5">
                                                        <label class="form-label fs-6 fw-semibold">Ödeme Durumu:</label>
                                                        <select class="form-select form-select-solid" id="filter-status">
                                                            <option value="">Tümü</option>
                                                            <option value="unpaid">Ödenmemiş</option>
                                                            <option value="partial">Kısmi Ödendi</option>
                                                            <option value="paid">Ödendi</option>
                                                            <option value="overdue">Vadesi Geçmiş</option>
                                                        </select>
                                                    </div>
                                                    <div class="d-flex justify-content-end">
                                                        <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6" id="filter-reset">Sıfırla</button>
                                                        <button type="submit" class="btn btn-primary fw-semibold px-6" id="filter-apply">Uygula</button>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Export -->
                                            <button type="button" class="btn btn-light-primary" id="export-excel">
                                                <i class="ki-duotone ki-exit-up fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                                Dışa Aktar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body py-4">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="invoices-table">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                                <th class="min-w-125px">Fatura No</th>
                                                <th class="min-w-150px">Müşteri</th>
                                                <th class="min-w-100px">Tarih</th>
                                                <th class="min-w-100px">Vade</th>
                                                <th class="min-w-100px">Tutar</th>
                                                <th class="min-w-100px">Durum</th>
                                                <th class="text-end min-w-100px">İşlemler</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold"></tbody>
                                    </table>
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
    
    <!-- Scripts -->
    <script src="/lisanstema/demo/assets/plugins/global/plugins.bundle.js"></script>
    <script src="/lisanstema/demo/assets/js/scripts.bundle.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/assets/js/pages/invoices-list.js"></script>
    
</body>
</html>

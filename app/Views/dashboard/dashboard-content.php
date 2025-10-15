<!--begin::Row - Üst İstatistikler-->
<div class="row g-5 gx-xl-10">
    <!--begin::Col - Sol Taraf (4 Widget)-->
    <div class="col-xxl-6 mb-md-5 mb-xl-10">
        <!--begin::Row-->
        <div class="row g-5 g-xl-10">
            <!--begin::Col-->
            <div class="col-md-6 col-xl-6 mb-xxl-10">
                <!--begin::Card widget - Toplam Satış-->
                <div class="card overflow-hidden h-md-50 mb-5 mb-xl-10">
                    <!--begin::Card body-->
                    <div class="card-body d-flex justify-content-between flex-column px-0 pb-0">
                        <!--begin::Statistics-->
                        <div class="mb-4 px-9">
                            <!--begin::Info-->
                            <div class="d-flex align-items-center mb-2">
                                <!--begin::Currency-->
                                <span class="fs-4 fw-semibold text-gray-500 align-self-start me-1">₺</span>
                                <!--end::Currency-->
                                <!--begin::Value-->
                                <span class="fs-2hx fw-bold text-gray-800 me-2 lh-1 ls-n2" id="total-sales">0</span>
                                <!--end::Value-->
                                <!--begin::Label-->
                                <span class="badge badge-light-success fs-base">
                                    <i class="ki-duotone ki-arrow-up fs-5 text-success ms-n1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i><span id="sales-change">0%</span>
                                </span>
                                <!--end::Label-->
                            </div>
                            <!--end::Info-->
                            <!--begin::Description-->
                            <span class="fs-6 fw-semibold text-gray-500">Toplam Satış (Bu Ay)</span>
                            <!--end::Description-->
                        </div>
                        <!--end::Statistics-->
                        <!--begin::Chart-->
                        <div id="chart_sales_widget" class="min-h-auto" style="height: 125px"></div>
                        <!--end::Chart-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget-->
                
                <!--begin::Card widget - Fatura Sayısı-->
                <div class="card card-flush h-md-50 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Info-->
                            <div class="d-flex align-items-center">
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2" id="invoice-count">0</span>
                                <!--end::Amount-->
                                <!--begin::Badge-->
                                <span class="badge badge-light-primary fs-base">
                                    <i class="ki-duotone ki-arrow-up fs-5 text-primary ms-n1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i><span id="invoice-change">0%</span>
                                </span>
                                <!--end::Badge-->
                            </div>
                            <!--end::Info-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">Toplam Fatura</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body d-flex align-items-end pt-0">
                        <!--begin::Progress-->
                        <div class="d-flex align-items-center flex-column mt-3 w-100">
                            <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                                <span class="fw-bolder fs-6 text-gray-900">Aylık Hedefe</span>
                                <span class="fw-bold fs-6 text-gray-500"><span id="goal-percentage">0</span>%</span>
                            </div>
                            <div class="h-8px mx-3 w-100 bg-light-success rounded">
                                <div class="bg-success rounded h-8px" role="progressbar" style="width: 0%;" id="goal-progress" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <!--end::Progress-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget-->
            </div>
            <!--end::Col-->
            
            <!--begin::Col-->
            <div class="col-md-6 col-xl-6 mb-xxl-10">
                <!--begin::Card widget - Alışlar-->
                <div class="card overflow-hidden h-md-50 mb-5 mb-xl-10">
                    <!--begin::Card body-->
                    <div class="card-body d-flex justify-content-between flex-column px-0 pb-0">
                        <!--begin::Statistics-->
                        <div class="mb-4 px-9">
                            <!--begin::Info-->
                            <div class="d-flex align-items-center mb-2">
                                <!--begin::Currency-->
                                <span class="fs-4 fw-semibold text-gray-500 align-self-start me-1">₺</span>
                                <!--end::Currency-->
                                <!--begin::Value-->
                                <span class="fs-2hx fw-bold text-gray-800 me-2 lh-1 ls-n2" id="total-purchases">0</span>
                                <!--end::Value-->
                                <!--begin::Label-->
                                <span class="badge badge-light-danger fs-base">
                                    <i class="ki-duotone ki-arrow-down fs-5 text-danger ms-n1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i><span id="purchase-change">0%</span>
                                </span>
                                <!--end::Label-->
                            </div>
                            <!--end::Info-->
                            <!--begin::Description-->
                            <span class="fs-6 fw-semibold text-gray-500">Toplam Alış (Bu Ay)</span>
                            <!--end::Description-->
                        </div>
                        <!--end::Statistics-->
                        <!--begin::Chart-->
                        <div id="chart_purchases_widget" class="min-h-auto" style="height: 125px"></div>
                        <!--end::Chart-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget-->
                
                <!--begin::Card widget - Yeni Müşteriler-->
                <div class="card card-flush h-md-50 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Amount-->
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2" id="new-customers">0</span>
                            <!--end::Amount-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-500 pt-1 fw-semibold fs-6">Yeni Müşteriler (Bu Ay)</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body d-flex flex-column justify-content-end pe-0">
                        <!--begin::Title-->
                        <span class="fs-6 fw-bolder text-gray-800 d-block mb-2">En Aktif Müşteriler</span>
                        <!--end::Title-->
                        <!--begin::Users group-->
                        <div class="symbol-group symbol-hover flex-nowrap" id="top-customers">
                            <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" title="Müşteri 1">
                                <span class="symbol-label bg-warning text-inverse-warning fw-bold">A</span>
                            </div>
                            <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" title="Müşteri 2">
                                <span class="symbol-label bg-primary text-inverse-primary fw-bold">B</span>
                            </div>
                            <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" title="Müşteri 3">
                                <span class="symbol-label bg-success text-inverse-success fw-bold">C</span>
                            </div>
                            <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" title="Müşteri 4">
                                <span class="symbol-label bg-danger text-inverse-danger fw-bold">D</span>
                            </div>
                            <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" title="Müşteri 5">
                                <span class="symbol-label bg-info text-inverse-info fw-bold">E</span>
                            </div>
                            <a href="/cariler" class="symbol symbol-35px symbol-circle">
                                <span class="symbol-label bg-light text-gray-400 fs-8 fw-bold">+12</span>
                            </a>
                        </div>
                        <!--end::Users group-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget-->
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
    </div>
    <!--end::Col-->
    
    <!--begin::Col - Sağ Taraf (Aylık Satış Grafiği)-->
    <div class="col-xxl-6 mb-5 mb-xl-10">
        <!--begin::Chart widget - Aylık Satış Trend-->
        <div class="card card-flush h-md-100">
            <!--begin::Header-->
            <div class="card-header pt-7">
                <!--begin::Title-->
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900">Aylık Satış Trendi</span>
                    <span class="text-gray-500 pt-2 fw-semibold fs-6">Son 12 Ay</span>
                </h3>
                <!--end::Title-->
                <!--begin::Toolbar-->
                <div class="card-toolbar">
                    <button class="btn btn-sm btn-icon btn-color-primary btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                        <i class="ki-duotone ki-category fs-6">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                    </button>
                    <!--begin::Menu-->
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-3" data-kt-menu="true">
                        <div class="menu-item px-3">
                            <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">Dönem</div>
                        </div>
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link px-3 active">Son 12 Ay</a>
                        </div>
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link px-3">Bu Yıl</a>
                        </div>
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link px-3">Geçen Yıl</a>
                        </div>
                    </div>
                    <!--end::Menu-->
                </div>
                <!--end::Toolbar-->
            </div>
            <!--end::Header-->
            <!--begin::Body-->
            <div class="card-body d-flex flex-center">
                <!--begin::Chart container-->
                <div id="chart_monthly_sales" class="w-100" style="height: 350px"></div>
                <!--end::Chart container-->
            </div>
            <!--end::Body-->
        </div>
        <!--end::Chart widget-->
    </div>
    <!--end::Col-->
</div>
<!--end::Row-->

<!--begin::Row - Orta Kısım-->
<div class="row g-5 g-xl-10 g-xl-10">
    <!--begin::Col - Ödeme Durumu-->
    <div class="col-xl-4 mb-xl-10">
        <!--begin::Card widget - Ödeme Durumu Pasta Grafik-->
        <div class="card card-flush h-md-100">
            <!--begin::Header-->
            <div class="card-header flex-nowrap pt-5">
                <!--begin::Title-->
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900">Ödeme Durumu</span>
                    <span class="text-gray-500 pt-2 fw-semibold fs-6">Fatura bazlı</span>
                </h3>
                <!--end::Title-->
            </div>
            <!--end::Header-->
            <!--begin::Body-->
            <div class="card-body pt-5 ps-6">
                <div id="chart_payment_status" class="min-h-auto" style="height: 250px"></div>
            </div>
            <!--end::Body-->
        </div>
        <!--end::Card widget-->
    </div>
    <!--end::Col-->
    
    <!--begin::Col - Kategori Dağılımı-->
    <div class="col-xl-4 mb-xl-10">
        <!--begin::Chart widget - Kategori Donut-->
        <div class="card card-flush h-md-100">
            <!--begin::Header-->
            <div class="card-header flex-nowrap pt-5">
                <!--begin::Title-->
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900">Kategori Dağılımı</span>
                    <span class="text-gray-500 pt-2 fw-semibold fs-6">En çok satanlar</span>
                </h3>
                <!--end::Title-->
                <!--begin::Toolbar-->
                <div class="card-toolbar">
                    <a href="/urunler" class="btn btn-sm btn-light">Tümünü Gör</a>
                </div>
                <!--end::Toolbar-->
            </div>
            <!--end::Header-->
            <!--begin::Body-->
            <div class="card-body pt-5 ps-6">
                <div id="chart_category_distribution" class="min-h-auto" style="height: 250px"></div>
            </div>
            <!--end::Body-->
        </div>
        <!--end::Chart widget-->
    </div>
    <!--end::Col-->
    
    <!--begin::Col - En Çok Satan Ürünler-->
    <div class="col-xl-4 mb-5 mb-xl-10">
        <!--begin::List widget - Top Products-->
        <div class="card card-flush h-md-100">
            <!--begin::Header-->
            <div class="card-header pt-7">
                <!--begin::Title-->
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">En Çok Satanlar</span>
                    <span class="text-gray-500 mt-1 fw-semibold fs-6">Bu ay</span>
                </h3>
                <!--end::Title-->
                <!--begin::Toolbar-->
                <div class="card-toolbar">
                    <a href="/urunler" class="btn btn-sm btn-light">Tümü</a>
                </div>
                <!--end::Toolbar-->
            </div>
            <!--end::Header-->
            <!--begin::Body-->
            <div class="card-body pt-4">
                <!--begin::Table container-->
                <div class="table-responsive">
                    <!--begin::Table-->
                    <table class="table table-row-dashed align-middle gs-0 gy-4 my-0" id="top-products-table">
                        <!--begin::Table head-->
                        <thead>
                            <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                                <th class="p-0 w-50px pb-1">SIRA</th>
                                <th class="ps-0 min-w-140px">ÜRÜN ADI</th>
                                <th class="text-end min-w-80px p-0 pb-1">ADET</th>
                            </tr>
                        </thead>
                        <!--end::Table head-->
                        <!--begin::Table body-->
                        <tbody>
                            <!-- JavaScript ile doldurulacak -->
                        </tbody>
                        <!--end::Table body-->
                    </table>
                </div>
                <!--end::Table-->
            </div>
            <!--end::Body-->
        </div>
        <!--end::List widget-->
    </div>
    <!--end::Col-->
</div>
<!--end::Row-->

<!--begin::Row - Alt Kısım-->
<div class="row g-5 g-xl-10">
    <!--begin::Col - Ödenmemiş Faturalar-->
    <div class="col-xxl-6 mb-xxl-10">
        <!--begin::List widget - Unpaid Invoices-->
        <div class="card card-flush h-md-100">
            <!--begin::Header-->
            <div class="card-header py-7">
                <!--begin::Statistics-->
                <div class="m-0">
                    <!--begin::Heading-->
                    <div class="d-flex align-items-center mb-2">
                        <!--begin::Title-->
                        <span class="fs-2hx fw-bold text-gray-800 me-2 lh-1 ls-n2" id="unpaid-amount">₺0</span>
                        <!--end::Title-->
                        <!--begin::Badge-->
                        <span class="badge badge-light-warning fs-base">
                            <i class="ki-duotone ki-information-5 fs-5 text-warning ms-n1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i><span id="unpaid-count">0</span> Adet
                        </span>
                        <!--end::Badge-->
                    </div>
                    <!--end::Heading-->
                    <!--begin::Description-->
                    <span class="fs-6 fw-semibold text-gray-500">Ödenmemiş Faturalar</span>
                    <!--end::Description-->
                </div>
                <!--end::Statistics-->
                <!--begin::Toolbar-->
                <div class="card-toolbar">
                    <a href="/faturalar?durum=odenmemis" class="btn btn-sm btn-light">Tümünü Gör</a>
                </div>
                <!--end::Toolbar-->
            </div>
            <!--end::Header-->
            <!--begin::Body-->
            <div class="card-body pt-0">
                <!--begin::Items-->
                <div class="mb-0" id="unpaid-invoices-list">
                    <!-- JavaScript ile doldurulacak -->
                </div>
                <!--end::Items-->
            </div>
            <!--end::Body-->
        </div>
        <!--end::List widget-->
    </div>
    <!--end::Col-->
    
    <!--begin::Col - Vadesi Geçmiş Faturalar-->
    <div class="col-xxl-6 mb-5 mb-xl-10">
        <!--begin::List widget - Overdue Invoices-->
        <div class="card card-flush h-md-100">
            <!--begin::Header-->
            <div class="card-header py-7">
                <!--begin::Statistics-->
                <div class="m-0">
                    <!--begin::Heading-->
                    <div class="d-flex align-items-center mb-2">
                        <!--begin::Title-->
                        <span class="fs-2hx fw-bold text-gray-800 me-2 lh-1 ls-n2" id="overdue-amount">₺0</span>
                        <!--end::Title-->
                        <!--begin::Badge-->
                        <span class="badge badge-light-danger fs-base">
                            <i class="ki-duotone ki-cross-circle fs-5 text-danger ms-n1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i><span id="overdue-count">0</span> Adet
                        </span>
                        <!--end::Badge-->
                    </div>
                    <!--end::Heading-->
                    <!--begin::Description-->
                    <span class="fs-6 fw-semibold text-gray-500">Vadesi Geçmiş Faturalar</span>
                    <!--end::Description-->
                </div>
                <!--end::Statistics-->
                <!--begin::Toolbar-->
                <div class="card-toolbar">
                    <a href="/faturalar?durum=vadesi-gecmis" class="btn btn-sm btn-light">Tümünü Gör</a>
                </div>
                <!--end::Toolbar-->
            </div>
            <!--end::Header-->
            <!--begin::Body-->
            <div class="card-body pt-0">
                <!--begin::Items-->
                <div class="mb-0" id="overdue-invoices-list">
                    <!-- JavaScript ile doldurulacak -->
                </div>
                <!--end::Items-->
            </div>
            <!--end::Body-->
        </div>
        <!--end::List widget-->
    </div>
    <!--end::Col-->
</div>
<!--end::Row-->

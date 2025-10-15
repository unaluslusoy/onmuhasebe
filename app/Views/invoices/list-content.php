<!--begin::Row - Statistics Cards-->
<div class="row g-5 g-xl-10 mb-5 mb-xl-10">
    
    <!--begin::Col-->
    <div class="col-sm-6 col-xl-3 mb-xl-10">
        <!--begin::Card widget-->
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <div class="d-flex align-items-center">
                        <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2" id="stat-total-invoices">0</span>
                        <span class="badge badge-light-success fs-base">
                            <i class="ki-duotone ki-arrow-up fs-5 text-success ms-n1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            2.2%
                        </span>
                    </div>
                    <span class="text-gray-400 pt-1 fw-semibold fs-6">Toplam Fatura</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="d-flex justify-content-between fw-bold fs-6 text-gray-800 w-100 mt-auto mb-2">
                        <span>Bu Ay</span>
                        <span id="stat-month-invoices">0</span>
                    </div>
                    <div class="h-8px mx-3 w-100 bg-light-success rounded">
                        <div class="bg-success rounded h-8px" role="progressbar" style="width: 50%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Card widget-->
    </div>
    <!--end::Col-->
    
    <!--begin::Col-->
    <div class="col-sm-6 col-xl-3 mb-xl-10">
        <!--begin::Card widget-->
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <div class="d-flex align-items-center">
                        <span class="fs-4 fw-semibold text-gray-400 me-1 align-self-start">₺</span>
                        <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2" id="stat-total-amount">0</span>
                    </div>
                    <span class="text-gray-400 pt-1 fw-semibold fs-6">Toplam Tutar</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="d-flex justify-content-between fw-bold fs-6 text-gray-800 w-100 mt-auto mb-2">
                        <span>Tahsilat</span>
                        <span>75%</span>
                    </div>
                    <div class="h-8px mx-3 w-100 bg-light-primary rounded">
                        <div class="bg-primary rounded h-8px" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Card widget-->
    </div>
    <!--end::Col-->
    
    <!--begin::Col-->
    <div class="col-sm-6 col-xl-3 mb-xl-10">
        <!--begin::Card widget-->
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <div class="d-flex align-items-center">
                        <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2" id="stat-unpaid-count">0</span>
                        <span class="badge badge-light-warning fs-base">
                            <i class="ki-duotone ki-information fs-5 text-warning ms-n1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </span>
                    </div>
                    <span class="text-gray-400 pt-1 fw-semibold fs-6">Ödenmemiş</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="d-flex justify-content-between fw-bold fs-6 text-gray-800 w-100 mt-auto mb-2">
                        <span>Tutar</span>
                        <span id="stat-unpaid-amount">₺0</span>
                    </div>
                    <div class="h-8px mx-3 w-100 bg-light-warning rounded">
                        <div class="bg-warning rounded h-8px" role="progressbar" style="width: 35%;" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Card widget-->
    </div>
    <!--end::Col-->
    
    <!--begin::Col-->
    <div class="col-sm-6 col-xl-3 mb-xl-10">
        <!--begin::Card widget-->
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <div class="d-flex align-items-center">
                        <span class="fs-2hx fw-bold text-danger me-2 lh-1 ls-n2" id="stat-overdue-count">0</span>
                        <span class="badge badge-light-danger fs-base">
                            <i class="ki-duotone ki-calendar-remove fs-5 text-danger ms-n1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </span>
                    </div>
                    <span class="text-gray-400 pt-1 fw-semibold fs-6">Vadesi Geçmiş</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="d-flex justify-content-between fw-bold fs-6 text-gray-800 w-100 mt-auto mb-2">
                        <span>Tutar</span>
                        <span id="stat-overdue-amount">₺0</span>
                    </div>
                    <div class="h-8px mx-3 w-100 bg-light-danger rounded">
                        <div class="bg-danger rounded h-8px" role="progressbar" style="width: 15%;" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Card widget-->
    </div>
    <!--end::Col-->
    
</div>
<!--end::Row-->

<!--begin::Card - Invoice List-->
<div class="card">
    <!--begin::Card header-->
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                <input type="text" id="invoice-search" class="form-control form-control-solid w-250px ps-13" placeholder="Fatura Ara..." />
            </div>
        </div>
        
        <div class="card-toolbar">
            <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
                <button type="button" class="btn btn-light-primary me-3" data-bs-toggle="modal" data-bs-target="#kt_modal_filter_invoices">
                    <i class="ki-duotone ki-filter fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Filtrele
                </button>
                
                <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-exit-up fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Export
                </button>
                
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                    <div class="menu-item px-3">
                        <a href="#" class="menu-link px-3" id="export-excel">Excel</a>
                    </div>
                    <div class="menu-item px-3">
                        <a href="#" class="menu-link px-3" id="export-pdf">PDF</a>
                    </div>
                </div>
                
                <a href="/fatura/olustur" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i>
                    Yeni Fatura
                </a>
            </div>
        </div>
    </div>
    <!--end::Card header-->
    
    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Table-->
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="invoices-table">
            <thead>
                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-125px">Fatura No</th>
                    <th class="min-w-125px">Müşteri</th>
                    <th class="min-w-125px">Tarih</th>
                    <th class="min-w-100px">Vade</th>
                    <th class="min-w-100px">Tutar</th>
                    <th class="min-w-100px">Durum</th>
                    <th class="min-w-100px">Ödeme</th>
                    <th class="text-end min-w-70px">İşlemler</th>
                </tr>
            </thead>
            <tbody class="fw-semibold text-gray-600">
                <!-- DataTable will populate this -->
            </tbody>
        </table>
        <!--end::Table-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->

<!--begin::Modal - Filter-->
<div class="modal fade" id="kt_modal_filter_invoices" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Fatura Filtrele</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="filter-form">
                    
                    <div class="mb-10">
                        <label class="form-label fs-6 fw-semibold">Durum:</label>
                        <select class="form-select form-select-solid" name="status">
                            <option value="">Tümü</option>
                            <option value="draft">Taslak</option>
                            <option value="sent">Gönderildi</option>
                            <option value="paid">Ödendi</option>
                            <option value="partial">Kısmi Ödendi</option>
                            <option value="overdue">Vadesi Geçti</option>
                            <option value="cancelled">İptal</option>
                        </select>
                    </div>
                    
                    <div class="mb-10">
                        <label class="form-label fs-6 fw-semibold">Tarih Aralığı:</label>
                        <input type="text" class="form-control form-control-solid" name="date_range" placeholder="Başlangıç - Bitiş" />
                    </div>
                    
                    <div class="mb-10">
                        <label class="form-label fs-6 fw-semibold">Tutar Aralığı:</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="number" class="form-control form-control-solid" name="amount_min" placeholder="Min" />
                            </div>
                            <div class="col-md-6">
                                <input type="number" class="form-control form-control-solid" name="amount_max" placeholder="Max" />
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="reset" class="btn btn-light btn-active-light-primary me-2" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Uygula</button>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
</div>
<!--end::Modal-->

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

<!-- Payment Modal (will be included separately) -->
<div id="payment-modal-container"></div>

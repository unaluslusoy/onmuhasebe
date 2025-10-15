<!--begin::Content-->
<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">
        
        <!--begin::Navbar-->
        <div class="card mb-5 mb-xl-10">
            <div class="card-body pt-9 pb-0">
                <!--begin::Details-->
                <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                    <!--begin::Info-->
                    <div class="flex-grow-1">
                        <!--begin::Title-->
                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="text-gray-900 fs-2 fw-bold me-1">Ayarlar</span>
                                </div>
                                <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                                    <span class="d-flex align-items-center text-gray-500 mb-2">
                                        Sistem ayarlarÄ±nÄ±zÄ± bu sayfadan yÃ¶netebilirsiniz
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Info-->
                </div>
                <!--end::Details-->
                
                <!--begin::Navs-->
                <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5 <?= ($activeTab ?? '') === 'genel' ? 'active' : '' ?>" href="/ayarlar/genel">
                            <i class="ki-duotone ki-user fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Genel
                        </a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5 <?= ($activeTab ?? '') === 'sirket' ? 'active' : '' ?>" href="/ayarlar/sirket">
                            <i class="ki-duotone ki-office-bag fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                            </i>
                            Åirket
                        </a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5" href="/ayarlar/guvenlik">
                            <i class="ki-duotone ki-shield-tick fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            GÃ¼venlik
                        </a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5 active" href="/ayarlar/firma">
                            <i class="ki-duotone ki-briefcase fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Firma Bilgileri
                        </a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5 <?= ($activeTab ?? '') === 'kullanicilar' ? 'active' : '' ?>" href="/ayarlar/kullanicilar">
                            <i class="ki-duotone ki-people fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                                <span class="path5"></span>
                            </i>
                            KullanÄ±cÄ±lar
                        </a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5 <?= ($activeTab ?? '') === 'kategoriler' ? 'active' : '' ?>" href="/ayarlar/kategoriler">
                            <i class="ki-duotone ki-element-11 fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                            </i>
                            Kategoriler
                        </a>
                    </li>
                </ul>
                <!--end::Navs-->
            </div>
        </div>
        <!--end::Navbar-->
        
        <?php if (empty($user['company_id'])): ?>
        <div class="card">
            <div class="card-body p-9">
                <div class="alert alert-warning">
                    <strong>HenÃ¼z bir ÅŸirket tanÄ±mlanmamÄ±ÅŸ</strong><br>
                    Firma bilgilerini dÃ¼zenleyebilmek iÃ§in Ã¶nce bir ÅŸirket oluÅŸturmanÄ±z gerekmektedir.
                </div>
            </div>
        </div>
        <?php else: ?>
        
        <!--begin::Firma Bilgileri-->
        <div class="card mb-5 mb-xl-10">
            <div class="card-header border-0 cursor-pointer">
                <div class="card-title m-0">
                    <h3 class="fw-bold m-0">Firma Bilgileri</h3>
                </div>
            </div>
            <div id="kt_firma_settings">
                <form id="kt_firma_form" class="form" method="POST" enctype="multipart/form-data">
                    <div class="card-body border-top p-9">
                        
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Evrak TÃ¼rÃ¼</label>
                            <div class="col-lg-8">
                                <select name="document_type" class="form-select">
                                    <option value="invoice" <?= (isset($company['document_type']) && $company['document_type'] === 'invoice') ? 'selected' : '' ?>>Fatura</option>
                                    <option value="waybill" <?= (isset($company['document_type']) && $company['document_type'] === 'waybill') ? 'selected' : '' ?>>Ä°rsaliye</option>
                                    <option value="receipt" <?= (isset($company['document_type']) && $company['document_type'] === 'receipt') ? 'selected' : '' ?>>Makbuz</option>
                                    <option value="other" <?= (isset($company['document_type']) && $company['document_type'] === 'other') ? 'selected' : '' ?>>DiÄŸer</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">SektÃ¶r</label>
                            <div class="col-lg-8">
                                <select name="sector" class="form-select">
                                    <option value="">SeÃ§iniz...</option>
                                    <option value="technology" <?= (isset($company['sector']) && $company['sector'] === 'technology') ? 'selected' : '' ?>>Teknoloji</option>
                                    <option value="construction" <?= (isset($company['sector']) && $company['sector'] === 'construction') ? 'selected' : '' ?>>Ä°nÅŸaat</option>
                                    <option value="food" <?= (isset($company['sector']) && $company['sector'] === 'food') ? 'selected' : '' ?>>GÄ±da</option>
                                    <option value="other" <?= (isset($company['sector']) && $company['sector'] === 'other') ? 'selected' : '' ?>>DiÄŸer</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">YÄ±llÄ±k Ciro (â‚º)</label>
                            <div class="col-lg-8">
                                <input type="number" name="annual_revenue" class="form-control" value="<?= htmlspecialchars($company['annual_revenue'] ?? '') ?>" step="0.01" />
                            </div>
                        </div>
                        
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Ã‡alÄ±ÅŸan SayÄ±sÄ±</label>
                            <div class="col-lg-8">
                                <input type="number" name="employee_count" class="form-control" value="<?= htmlspecialchars($company['employee_count'] ?? '') ?>" />
                            </div>
                        </div>
                        
                    </div>
                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                        <button type="submit" class="btn btn-primary" id="kt_firma_submit">
                            <span class="indicator-label">Kaydet</span>
                            <span class="indicator-progress">LÃ¼tfen bekleyin... 
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!--end::Firma Bilgileri-->
        
        <?php endif; ?>
                            <div class="col-lg-8 fv-row">
                                <input type="password" name="confirm_password" class="form-control form-control-lg form-control-solid" placeholder="Yeni ÅŸifrenizi tekrar girin" autocomplete="off" />
                                <div class="fv-plugins-message-container invalid-feedback"></div>
                            </div>
                        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('kt_firma_form');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('kt_firma_submit');
            submitBtn.disabled = true;
            
            try {
                const formData = new FormData(form);
                const response = await fetch('/ayarlar/firma/guncelle', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Başarılı!',
                        text: result.message
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hata!',
                        text: result.message || 'Bir hata oluştu'
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Bir hata oluştu'
                });
            } finally {
                submitBtn.disabled = false;
            }
        });
    }
});
</script>

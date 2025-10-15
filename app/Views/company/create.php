<!--begin::Content-->
<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">
        
        <?php if (isset($_SESSION['error'])): ?>
        <!--begin::Alert-->
        <div class="alert alert-dismissible bg-light-danger d-flex flex-column flex-sm-row p-5 mb-10">
            <i class="ki-duotone ki-information fs-2hx text-danger me-4 mb-5 mb-sm-0">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            <div class="d-flex flex-column pe-0 pe-sm-10">
                <span><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-1 text-danger"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
        <!--end::Alert-->
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <h2 class="fw-bold">Yeni Şirket Oluştur</h2>
                </div>
                <div class="card-toolbar">
                    <span class="badge badge-light-primary">Zorunlu alanlar (*) işaretlidir</span>
                </div>
            </div>
            <!--end::Card header-->
            
            <!--begin::Card body-->
            <div class="card-body">
                <!--begin::Form-->
                <form id="kt_company_create_form" class="form">
                    
                    <!--begin::Heading-->
                    <div class="mb-10">
                        <h3 class="text-dark fw-bold mb-2">Temel Bilgiler</h3>
                        <div class="text-muted fs-6">Şirketinizin temel bilgilerini giriniz</div>
                    </div>
                    <!--end::Heading-->
                    
                    <!--begin::Input group - Company Name-->
                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label required fw-semibold fs-6">Şirket Adı</label>
                        <div class="col-lg-9 fv-row">
                            <input type="text" name="name" class="form-control form-control-lg form-control-solid" placeholder="Örn: ABC Teknoloji Ltd. Şti." />
                            <div class="fv-plugins-message-container invalid-feedback"></div>
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group - Trade Name-->
                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label fw-semibold fs-6">Ticari Ünvan</label>
                        <div class="col-lg-9 fv-row">
                            <input type="text" name="trade_name" class="form-control form-control-lg form-control-solid" placeholder="Ticari ünvanınız" />
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group - Company Type-->
                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label required fw-semibold fs-6">Şirket Tipi</label>
                        <div class="col-lg-9 fv-row">
                            <select name="company_type" class="form-select form-select-lg form-select-solid">
                                <option value="limited">Limited Şirket</option>
                                <option value="corporation">Anonim Şirket</option>
                                <option value="individual">Şahıs Şirketi</option>
                                <option value="other">Diğer</option>
                            </select>
                            <div class="fv-plugins-message-container invalid-feedback"></div>
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-10"></div>
                    <!--end::Separator-->
                    
                    <!--begin::Heading-->
                    <div class="mb-10">
                        <h3 class="text-dark fw-bold mb-2">Vergi ve Sicil Bilgileri</h3>
                        <div class="text-muted fs-6">Vergi dairesi ve sicil numaralarınızı giriniz</div>
                    </div>
                    <!--end::Heading-->
                    
                    <!--begin::Input group - Tax Office-->
                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label fw-semibold fs-6">Vergi Dairesi</label>
                        <div class="col-lg-9 fv-row">
                            <input type="text" name="tax_office" class="form-control form-control-lg form-control-solid" placeholder="Vergi dairesi adı" />
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group - Tax Number-->
                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label fw-semibold fs-6">Vergi Numarası</label>
                        <div class="col-lg-9 fv-row">
                            <input type="text" name="tax_number" class="form-control form-control-lg form-control-solid" placeholder="Vergi numarası" />
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group - VKN & TCKN-->
                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label fw-semibold fs-6">VKN / TCKN</label>
                        <div class="col-lg-9">
                            <div class="row">
                                <div class="col-lg-6 fv-row mb-3 mb-lg-0">
                                    <input type="text" name="vkn" class="form-control form-control-lg form-control-solid" placeholder="VKN (10 haneli)" maxlength="10" />
                                    <div class="fv-plugins-message-container invalid-feedback"></div>
                                    <div class="form-text">Vergi Kimlik Numarası</div>
                                </div>
                                <div class="col-lg-6 fv-row">
                                    <input type="text" name="tckn" class="form-control form-control-lg form-control-solid" placeholder="TCKN (11 haneli)" maxlength="11" />
                                    <div class="fv-plugins-message-container invalid-feedback"></div>
                                    <div class="form-text">T.C. Kimlik Numarası</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group - MERSİS-->
                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label fw-semibold fs-6">MERSİS No</label>
                        <div class="col-lg-9 fv-row">
                            <input type="text" name="mersis_no" class="form-control form-control-lg form-control-solid" placeholder="MERSİS numarası (16 haneli)" maxlength="16" />
                            <div class="fv-plugins-message-container invalid-feedback"></div>
                            <div class="form-text">Merkezi Sicil Kayıt Sistemi Numarası</div>
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-10"></div>
                    <!--end::Separator-->
                    
                    <!--begin::Heading-->
                    <div class="mb-10">
                        <h3 class="text-dark fw-bold mb-2">Adres Bilgileri</h3>
                        <div class="text-muted fs-6">Şirket adres bilgilerinizi giriniz</div>
                    </div>
                    <!--end::Heading-->
                    
                    <!--begin::Input group - Address-->
                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label fw-semibold fs-6">Adres</label>
                        <div class="col-lg-9 fv-row">
                            <textarea name="address" class="form-control form-control-lg form-control-solid" rows="3" placeholder="Tam adres"></textarea>
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group - District & City-->
                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label fw-semibold fs-6">İlçe / İl</label>
                        <div class="col-lg-9">
                            <div class="row">
                                <div class="col-lg-6 fv-row mb-3 mb-lg-0">
                                    <input type="text" name="district" class="form-control form-control-lg form-control-solid" placeholder="İlçe" />
                                </div>
                                <div class="col-lg-6 fv-row">
                                    <input type="text" name="city" class="form-control form-control-lg form-control-solid" placeholder="İl" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group - Country & Postal Code-->
                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label fw-semibold fs-6">Ülke / Posta Kodu</label>
                        <div class="col-lg-9">
                            <div class="row">
                                <div class="col-lg-6 fv-row mb-3 mb-lg-0">
                                    <input type="text" name="country" class="form-control form-control-lg form-control-solid" placeholder="Ülke" value="Türkiye" />
                                </div>
                                <div class="col-lg-6 fv-row">
                                    <input type="text" name="postal_code" class="form-control form-control-lg form-control-solid" placeholder="Posta kodu" maxlength="10" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-10"></div>
                    <!--end::Separator-->
                    
                    <!--begin::Heading-->
                    <div class="mb-10">
                        <h3 class="text-dark fw-bold mb-2">İletişim Bilgileri</h3>
                        <div class="text-muted fs-6">Şirket iletişim bilgilerinizi giriniz</div>
                    </div>
                    <!--end::Heading-->
                    
                    <!--begin::Input group - Phone & Fax-->
                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label fw-semibold fs-6">Telefon / Faks</label>
                        <div class="col-lg-9">
                            <div class="row">
                                <div class="col-lg-6 fv-row mb-3 mb-lg-0">
                                    <input type="text" name="phone" class="form-control form-control-lg form-control-solid" placeholder="Telefon" />
                                    <div class="fv-plugins-message-container invalid-feedback"></div>
                                </div>
                                <div class="col-lg-6 fv-row">
                                    <input type="text" name="fax" class="form-control form-control-lg form-control-solid" placeholder="Faks" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group - Email-->
                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label fw-semibold fs-6">E-posta</label>
                        <div class="col-lg-9 fv-row">
                            <input type="email" name="email" class="form-control form-control-lg form-control-solid" placeholder="sirket@example.com" />
                            <div class="fv-plugins-message-container invalid-feedback"></div>
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group - Website-->
                    <div class="row mb-6">
                        <label class="col-lg-3 col-form-label fw-semibold fs-6">Website</label>
                        <div class="col-lg-9 fv-row">
                            <input type="text" name="website" class="form-control form-control-lg form-control-solid" placeholder="www.sirketiniz.com" />
                        </div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Actions-->
                    <div class="row">
                        <div class="col-lg-9 offset-lg-3">
                            <button type="submit" class="btn btn-primary" id="kt_company_create_submit">
                                <span class="indicator-label">Şirketi Oluştur</span>
                                <span class="indicator-progress">Lütfen bekleyin...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                            <a href="/ayarlar/sirket" class="btn btn-light-primary ms-2">İptal</a>
                        </div>
                    </div>
                    <!--end::Actions-->
                    
                </form>
                <!--end::Form-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
        
    </div>
    <!--end::Content container-->
</div>
<!--end::Content-->

<script>
// Company Create Form Handler
document.getElementById('kt_company_create_form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitButton = document.getElementById('kt_company_create_submit');
    const form = e.target;
    const formData = new FormData(form);
    
    // Clear previous errors
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    
    // Show loading
    submitButton.setAttribute('data-kt-indicator', 'on');
    submitButton.disabled = true;
    
    fetch('/sirket/kaydet', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        submitButton.removeAttribute('data-kt-indicator');
        submitButton.disabled = false;
        
        if (data.success) {
            Swal.fire({
                title: 'Başarılı!',
                text: data.message,
                icon: 'success',
                buttonsStyling: false,
                confirmButtonText: 'Tamam',
                customClass: {
                    confirmButton: 'btn btn-primary'
                }
            }).then(() => {
                if (data.data && data.data.redirect) {
                    window.location.href = data.data.redirect;
                } else {
                    window.location.href = '/';
                }
            });
        } else {
            // Show validation errors
            if (data.errors) {
                for (const [field, message] of Object.entries(data.errors)) {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = input.closest('.fv-row')?.querySelector('.invalid-feedback');
                        if (feedback) {
                            feedback.textContent = message;
                        }
                    }
                }
            }
            
            Swal.fire({
                title: 'Hata!',
                text: data.message || 'Bir hata oluştu',
                icon: 'error',
                buttonsStyling: false,
                confirmButtonText: 'Tamam',
                customClass: {
                    confirmButton: 'btn btn-primary'
                }
            });
        }
    })
    .catch(error => {
        submitButton.removeAttribute('data-kt-indicator');
        submitButton.disabled = false;
        
        Swal.fire({
            title: 'Hata!',
            text: 'Bir hata oluştu. Lütfen tekrar deneyin.',
            icon: 'error',
            buttonsStyling: false,
            confirmButtonText: 'Tamam',
            customClass: {
                confirmButton: 'btn btn-primary'
            }
        });
    });
});

// VKN input - only numbers
document.querySelector('input[name="vkn"]')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '');
});

// TCKN input - only numbers
document.querySelector('input[name="tckn"]')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '');
});

// MERSİS input - only numbers
document.querySelector('input[name="mersis_no"]')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '');
});
</script>

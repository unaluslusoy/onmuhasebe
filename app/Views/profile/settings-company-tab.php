<!--begin::Company Info Card-->
<div class="card mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_company_info" aria-expanded="true">
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">Şirket Bilgileri</h3>
        </div>
    </div>
    <!--end::Card header-->
    <!--begin::Content-->
    <div id="kt_account_company_info" class="collapse show">
        <!--begin::Form-->
        <form id="kt_account_company_form" class="form">
            <!--begin::Card body-->
            <div class="card-body border-top p-9">
                <!--begin::Input group - Company Name-->
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Şirket Adı</label>
                    <div class="col-lg-8 fv-row">
                        <input type="text" name="company_name" class="form-control form-control-lg form-control-solid" placeholder="Şirket adı" value="<?= htmlspecialchars($company['name'] ?? '') ?>" />
                        <div class="fv-plugins-message-container invalid-feedback"></div>
                    </div>
                </div>
                <!--end::Input group-->
                
                <!--begin::Input group - Tax Info-->
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Vergi Dairesi</label>
                    <div class="col-lg-8">
                        <div class="row">
                            <div class="col-lg-6 fv-row mb-3 mb-lg-0">
                                <input type="text" name="tax_office" class="form-control form-control-lg form-control-solid" placeholder="Vergi dairesi" value="<?= htmlspecialchars($company['tax_office'] ?? '') ?>" />
                            </div>
                            <div class="col-lg-6 fv-row">
                                <input type="text" name="tax_number" class="form-control form-control-lg form-control-solid" placeholder="Vergi no" value="<?= htmlspecialchars($company['tax_number'] ?? '') ?>" />
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Input group-->
                
                <!--begin::Input group - Address-->
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Adres</label>
                    <div class="col-lg-8 fv-row">
                        <textarea name="address" class="form-control form-control-lg form-control-solid" rows="3" placeholder="Adres"><?= htmlspecialchars($company['address'] ?? '') ?></textarea>
                    </div>
                </div>
                <!--end::Input group-->
                
                <!--begin::Input group - District & City-->
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">İlçe / İl</label>
                    <div class="col-lg-8">
                        <div class="row">
                            <div class="col-lg-6 fv-row mb-3 mb-lg-0">
                                <input type="text" name="district" class="form-control form-control-lg form-control-solid" placeholder="İlçe" value="<?= htmlspecialchars($company['district'] ?? '') ?>" />
                            </div>
                            <div class="col-lg-6 fv-row">
                                <input type="text" name="city" class="form-control form-control-lg form-control-solid" placeholder="İl" value="<?= htmlspecialchars($company['city'] ?? '') ?>" />
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Input group-->
                
                <!--begin::Input group - Postal Code-->
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Posta Kodu</label>
                    <div class="col-lg-8 fv-row">
                        <input type="text" name="postal_code" class="form-control form-control-lg form-control-solid" placeholder="Posta kodu" value="<?= htmlspecialchars($company['postal_code'] ?? '') ?>" />
                    </div>
                </div>
                <!--end::Input group-->
                
                <!--begin::Input group - Company Phone-->
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">
                        <span>Şirket Telefonu</span>
                        <span class="ms-1" data-bs-toggle="tooltip" title="Şirket iletişim telefonu">
                            <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </span>
                    </label>
                    <div class="col-lg-8 fv-row">
                        <input type="tel" name="company_phone" class="form-control form-control-lg form-control-solid" placeholder="Şirket telefonu" value="<?= htmlspecialchars($company['phone'] ?? '') ?>" />
                    </div>
                </div>
                <!--end::Input group-->
                
                <!--begin::Input group - Company Email-->
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Şirket E-postası</label>
                    <div class="col-lg-8 fv-row">
                        <input type="email" name="company_email" class="form-control form-control-lg form-control-solid" placeholder="Şirket e-postası" value="<?= htmlspecialchars($company['email'] ?? '') ?>" />
                    </div>
                </div>
                <!--end::Input group-->
                
                <!--begin::Input group - Website-->
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Website</label>
                    <div class="col-lg-8 fv-row">
                        <input type="text" name="website" class="form-control form-control-lg form-control-solid" placeholder="www.sirket.com" value="<?= htmlspecialchars($company['website'] ?? '') ?>" />
                    </div>
                </div>
                <!--end::Input group-->
            </div>
            <!--end::Card body-->
            <!--begin::Actions-->
            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <button type="reset" class="btn btn-light btn-active-light-primary me-2">İptal</button>
                <button type="submit" class="btn btn-primary" id="kt_account_company_submit">
                    <span class="indicator-label">Değişiklikleri Kaydet</span>
                    <span class="indicator-progress">Lütfen bekleyin...
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
            <!--end::Actions-->
        </form>
        <!--end::Form-->
    </div>
    <!--end::Content-->
</div>
<!--end::Company Info Card-->

<script>
// Company Update Form
document.getElementById('kt_account_company_form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitButton = document.getElementById('kt_account_company_submit');
    const form = e.target;
    const formData = new FormData(form);
    
    submitButton.setAttribute('data-kt-indicator', 'on');
    submitButton.disabled = true;
    
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    
    fetch('/profil/sirket-guncelle', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        submitButton.removeAttribute('data-kt-indicator');
        submitButton.disabled = false;
        
        if (data.success) {
            Swal.fire({
                text: data.message,
                icon: 'success',
                buttonsStyling: false,
                confirmButtonText: 'Tamam',
                customClass: {
                    confirmButton: 'btn btn-primary'
                }
            }).then(() => {
                window.location.reload();
            });
        } else {
            if (data.errors) {
                for (const [field, message] of Object.entries(data.errors)) {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = input.nextElementSibling;
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.textContent = message;
                        }
                    }
                }
            }
            
            Swal.fire({
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
</script>

<!--begin::Profile Details Card-->
<div class="card mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true">
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">Profil Bilgileri</h3>
        </div>
    </div>
    <!--end::Card header-->
    <!--begin::Content-->
    <div id="kt_account_profile_details" class="collapse show">
        <!--begin::Form-->
        <form id="kt_account_profile_form" class="form">
            <!--begin::Card body-->
            <div class="card-body border-top p-9">
                <!--begin::Input group - Avatar-->
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Profil Resmi</label>
                    <div class="col-lg-8">
                        <!--begin::Image input-->
                        <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url('/lisanstema/demo/assets/media/svg/avatars/blank.svg')">
                            <!--begin::Preview existing avatar-->
                            <div class="image-input-wrapper w-125px h-125px" style="background-image: url('/lisanstema/demo/assets/media/avatars/blank.png')">
                                <div class="symbol-label fs-2 fw-semibold text-primary bg-light-primary d-flex align-items-center justify-content-center" style="width: 125px; height: 125px;">
                                    <?= strtoupper(mb_substr($user['full_name'] ?? 'U', 0, 2)) ?>
                                </div>
                            </div>
                            <!--end::Preview existing avatar-->
                            <!--begin::Label-->
                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Resim değiştir">
                                <i class="ki-duotone ki-pencil fs-7">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <input type="file" name="avatar" accept=".png, .jpg, .jpeg" />
                                <input type="hidden" name="avatar_remove" />
                            </label>
                            <!--end::Label-->
                            <!--begin::Cancel-->
                            <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="İptal">
                                <i class="ki-duotone ki-cross fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <!--end::Cancel-->
                            <!--begin::Remove-->
                            <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Kaldır">
                                <i class="ki-duotone ki-cross fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <!--end::Remove-->
                        </div>
                        <!--end::Image input-->
                        <div class="form-text">İzin verilen dosya türleri: png, jpg, jpeg.</div>
                    </div>
                </div>
                <!--end::Input group-->
                
                <!--begin::Input group - Full Name-->
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Ad Soyad</label>
                    <div class="col-lg-8 fv-row">
                        <input type="text" name="full_name" class="form-control form-control-lg form-control-solid" placeholder="Ad Soyad" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" />
                        <div class="fv-plugins-message-container invalid-feedback"></div>
                    </div>
                </div>
                <!--end::Input group-->
                
                <!--begin::Input group - Email-->
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">E-posta</label>
                    <div class="col-lg-8 fv-row">
                        <input type="email" name="email" class="form-control form-control-lg form-control-solid" placeholder="E-posta" value="<?= htmlspecialchars($user['email'] ?? '') ?>" />
                        <div class="fv-plugins-message-container invalid-feedback"></div>
                    </div>
                </div>
                <!--end::Input group-->
                
                <!--begin::Input group - Phone-->
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-semibold fs-6">
                        <span>Telefon</span>
                        <span class="ms-1" data-bs-toggle="tooltip" title="Telefon numarası">
                            <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </span>
                    </label>
                    <div class="col-lg-8 fv-row">
                        <input type="tel" name="phone" class="form-control form-control-lg form-control-solid" placeholder="Telefon" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" />
                    </div>
                </div>
                <!--end::Input group-->
            </div>
            <!--end::Card body-->
            <!--begin::Actions-->
            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <button type="reset" class="btn btn-light btn-active-light-primary me-2">İptal</button>
                <button type="submit" class="btn btn-primary" id="kt_account_profile_submit">
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
<!--end::Profile Details Card-->

<script>
// Profile Update Form
document.getElementById('kt_account_profile_form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitButton = document.getElementById('kt_account_profile_submit');
    const form = e.target;
    const formData = new FormData(form);
    
    submitButton.setAttribute('data-kt-indicator', 'on');
    submitButton.disabled = true;
    
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    
    fetch('/profil/guncelle', {
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

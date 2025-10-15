<!--begin::Content-->
<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">
        <!--begin::Navbar-->
        <div class="card mb-5 mb-xl-10">
            <div class="card-body pt-9 pb-0">
                <!--begin::Details-->
                <div class="d-flex flex-wrap flex-sm-nowrap">
                    <!--begin: Avatar-->
                    <div class="me-7 mb-4">
                        <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                            <div class="symbol-label fs-2 fw-semibold text-primary bg-light-primary">
                                <?= strtoupper(mb_substr($user['full_name'] ?? 'U', 0, 2)) ?>
                            </div>
                            <div class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-body h-20px w-20px"></div>
                        </div>
                    </div>
                    <!--end::Avatar-->
                    <!--begin::Info-->
                    <div class="flex-grow-1">
                        <!--begin::Title-->
                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                            <!--begin::User-->
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center mb-2">
                                    <a href="#" class="text-gray-900 text-hover-primary fs-2 fw-bold me-1">
                                        <?= htmlspecialchars($user['full_name'] ?? 'Kullanıcı') ?>
                                    </a>
                                </div>
                                <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                                    <span class="d-flex align-items-center text-gray-500 me-5 mb-2">
                                        <i class="ki-duotone ki-profile-circle fs-4 me-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                        <?php
                                        $roleLabels = [
                                            'admin' => 'Yönetici',
                                            'manager' => 'Müdür',
                                            'accountant' => 'Muhasebeci',
                                            'user' => 'Kullanıcı'
                                        ];
                                        echo $roleLabels[$user['role']] ?? $user['role'];
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <!--end::User-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Info-->
                </div>
                <!--end::Details-->
                <!--begin::Navs-->
                <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5" href="/profil">Genel Bakış</a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5 active" href="/profil/duzenle">Ayarlar</a>
                    </li>
                </ul>
                <!--end::Navs-->
            </div>
        </div>
        <!--end::Navbar-->
        
        <div class="row g-5 g-xl-10">
            <!--begin::Col - Profile Details-->
            <div class="col-xl-6">
                <!--begin::Card-->
                <div class="card mb-5 mb-xl-10">
                    <!--begin::Card header-->
                    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
                        <div class="card-title m-0">
                            <h3 class="fw-bold m-0">Profil Bilgileri</h3>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Content-->
                    <div id="kt_account_profile_details" class="collapse show">
                        <!--begin::Form-->
                        <form id="kt_account_profile_details_form" class="form">
                            <!--begin::Card body-->
                            <div class="card-body border-top p-9">
                                <!--begin::Input group-->
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Ad Soyad</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="full_name" class="form-control form-control-lg form-control-solid" placeholder="Ad Soyad" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" />
                                        <div class="fv-plugins-message-container invalid-feedback"></div>
                                    </div>
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">E-posta</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="email" name="email" class="form-control form-control-lg form-control-solid" placeholder="E-posta" value="<?= htmlspecialchars($user['email'] ?? '') ?>" />
                                        <div class="fv-plugins-message-container invalid-feedback"></div>
                                    </div>
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
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
                                <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit">
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
                <!--end::Card-->
            </div>
            <!--end::Col-->
            
            <!--begin::Col - Change Password-->
            <div class="col-xl-6">
                <!--begin::Card-->
                <div class="card mb-5 mb-xl-10">
                    <!--begin::Card header-->
                    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_signin_method">
                        <div class="card-title m-0">
                            <h3 class="fw-bold m-0">Şifre Değiştir</h3>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Content-->
                    <div id="kt_account_signin_method" class="collapse show">
                        <!--begin::Card body-->
                        <div class="card-body border-top p-9">
                            <!--begin::Form-->
                            <form id="kt_password_change_form" class="form">
                                <!--begin::Input group-->
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6 required">Mevcut Şifre</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="password" name="current_password" class="form-control form-control-lg form-control-solid" placeholder="Mevcut şifreniz" />
                                        <div class="fv-plugins-message-container invalid-feedback"></div>
                                    </div>
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6 required">Yeni Şifre</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="password" name="new_password" class="form-control form-control-lg form-control-solid" placeholder="Yeni şifre (en az 8 karakter)" />
                                        <div class="fv-plugins-message-container invalid-feedback"></div>
                                    </div>
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6 required">Yeni Şifre (Tekrar)</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="password" name="confirm_password" class="form-control form-control-lg form-control-solid" placeholder="Yeni şifrenizi tekrar girin" />
                                        <div class="fv-plugins-message-container invalid-feedback"></div>
                                    </div>
                                </div>
                                <!--end::Input group-->
                                <!--begin::Actions-->
                                <div class="d-flex justify-content-end">
                                    <button type="reset" class="btn btn-light btn-active-light-primary me-2">İptal</button>
                                    <button type="submit" class="btn btn-primary" id="kt_password_change_submit">
                                        <span class="indicator-label">Şifreyi Değiştir</span>
                                        <span class="indicator-progress">Lütfen bekleyin...
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                </div>
                                <!--end::Actions-->
                            </form>
                            <!--end::Form-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Content-->
                </div>
                <!--end::Card-->
                
                <!--begin::Notice-->
                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                    <i class="ki-duotone ki-shield-search fs-2tx text-warning me-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">Güvenlik İpuçları</h4>
                            <div class="fs-6 text-gray-700">
                                • Şifreniz en az 8 karakter uzunluğunda olmalıdır<br>
                                • Büyük ve küçük harf, rakam ve özel karakter kullanın<br>
                                • Kolay tahmin edilebilir şifreler kullanmayın<br>
                                • Şifrenizi düzenli olarak değiştirin
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Notice-->
            </div>
            <!--end::Col-->
        </div>
    </div>
    <!--end::Content container-->
</div>
<!--end::Content-->

<script>
"use strict";

// Profile Update Form
document.getElementById('kt_account_profile_details_form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitButton = document.getElementById('kt_account_profile_details_submit');
    const form = e.target;
    const formData = new FormData(form);
    
    // Show loading
    submitButton.setAttribute('data-kt-indicator', 'on');
    submitButton.disabled = true;
    
    // Clear previous errors
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    
    // Send request
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
                window.location.href = '/profil';
            });
        } else {
            // Show errors
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

// Password Change Form
document.getElementById('kt_password_change_form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitButton = document.getElementById('kt_password_change_submit');
    const form = e.target;
    const formData = new FormData(form);
    
    // Show loading
    submitButton.setAttribute('data-kt-indicator', 'on');
    submitButton.disabled = true;
    
    // Clear previous errors
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    
    // Send request
    fetch('/profil/sifre-degistir', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        submitButton.removeAttribute('data-kt-indicator');
        submitButton.disabled = false;
        
        if (data.success) {
            form.reset();
            Swal.fire({
                text: data.message,
                icon: 'success',
                buttonsStyling: false,
                confirmButtonText: 'Tamam',
                customClass: {
                    confirmButton: 'btn btn-primary'
                }
            });
        } else {
            // Show errors
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

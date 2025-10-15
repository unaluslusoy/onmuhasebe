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
                                        Sistem ayarlarınızı bu sayfadan yönetebilirsiniz
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
                            Şirket
                        </a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5 <?= ($activeTab ?? 'guvenlik') === 'guvenlik' ? 'active' : '' ?>" href="/ayarlar/guvenlik">
                            <i class="ki-duotone ki-shield-tick fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Güvenlik
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
                            Kullanıcılar
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
        
        <!--begin::Şifre Değiştirme-->
        <div class="card mb-5 mb-xl-10">
            <!--begin::Card header-->
            <div class="card-header border-0 cursor-pointer">
                <div class="card-title m-0">
                    <h3 class="fw-bold m-0">Şifre Değiştir</h3>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Content-->
            <div id="kt_account_settings_password">
                <!--begin::Form-->
                <form id="kt_account_password_form" class="form">
                    <!--begin::Card body-->
                    <div class="card-body border-top p-9">
                        
                        <!--begin::Input group - Current Password-->
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Mevcut Şifre</label>
                            <div class="col-lg-8 fv-row">
                                <input type="password" name="current_password" class="form-control form-control-lg form-control-solid" placeholder="Mevcut şifrenizi girin" autocomplete="off" />
                                <div class="fv-plugins-message-container invalid-feedback"></div>
                            </div>
                        </div>
                        <!--end::Input group-->
                        
                        <!--begin::Input group - New Password-->
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Yeni Şifre</label>
                            <div class="col-lg-8 fv-row">
                                <input type="password" name="new_password" class="form-control form-control-lg form-control-solid" placeholder="Yeni şifrenizi girin" autocomplete="off" />
                                <div class="fv-plugins-message-container invalid-feedback"></div>
                                <div class="form-text">Şifreniz en az 8 karakter uzunluğunda olmalıdır.</div>
                            </div>
                        </div>
                        <!--end::Input group-->
                        
                        <!--begin::Input group - Confirm Password-->
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Yeni Şifre (Tekrar)</label>
                            <div class="col-lg-8 fv-row">
                                <input type="password" name="confirm_password" class="form-control form-control-lg form-control-solid" placeholder="Yeni şifrenizi tekrar girin" autocomplete="off" />
                                <div class="fv-plugins-message-container invalid-feedback"></div>
                            </div>
                        </div>
                        <!--end::Input group-->
                        
                    </div>
                    <!--end::Card body-->
                    <!--begin::Actions-->
                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                        <button type="reset" class="btn btn-light btn-active-light-primary me-2">Temizle</button>
                        <button type="submit" class="btn btn-primary" id="kt_account_password_submit">
                            <span class="indicator-label">Şifreyi Değiştir</span>
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
        <!--end::Şifre Değiştirme-->
        
        <!--begin::Hesabı Kapat-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 cursor-pointer">
                <div class="card-title m-0">
                    <h3 class="fw-bold m-0">Hesabı Kapat</h3>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Content-->
            <div id="kt_account_settings_deactivate">
                <!--begin::Form-->
                <form id="kt_account_deactivate_form" class="form">
                    <!--begin::Card body-->
                    <div class="card-body border-top p-9">
                        
                        <!--begin::Notice-->
                        <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed mb-9 p-6">
                            <i class="ki-duotone ki-information fs-2tx text-warning me-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <h4 class="text-gray-900 fw-bold">Hesabınızı kapatmak üzeresiniz</h4>
                                    <div class="fs-6 text-gray-700">
                                        Hesabınızı kapattığınızda:
                                        <ul class="mt-3">
                                            <li>Sisteme giriş yapamayacaksınız</li>
                                            <li>Verileriniz korunacak ancak erişemeyeceksiniz</li>
                                            <li>Hesabınızı yeniden aktifleştirmek için yönetici ile iletişime geçmeniz gerekecek</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Notice-->
                        
                        <!--begin::Checkbox-->
                        <div class="form-check form-check-solid fv-row">
                            <input name="deactivate_confirm" class="form-check-input" type="checkbox" value="1" id="deactivate_confirm" />
                            <label class="form-check-label fw-semibold ps-2 fs-6" for="deactivate_confirm">
                                Hesabımı kapatmak istediğimi onaylıyorum
                            </label>
                        </div>
                        <!--end::Checkbox-->
                        
                    </div>
                    <!--end::Card body-->
                    <!--begin::Actions-->
                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                        <button type="submit" class="btn btn-danger" id="kt_account_deactivate_submit">
                            <span class="indicator-label">Hesabı Kapat</span>
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
        <!--end::Hesabı Kapat-->
        
    </div>
    <!--end::Content container-->
</div>
<!--end::Content-->

<script>
// Password Change Form
document.getElementById('kt_account_password_form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitButton = document.getElementById('kt_account_password_submit');
    const form = e.target;
    const formData = new FormData(form);
    
    submitButton.setAttribute('data-kt-indicator', 'on');
    submitButton.disabled = true;
    
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    
    fetch('/ayarlar/sifre-degistir', {
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
                form.reset();
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

// Account Deactivate Form
document.getElementById('kt_account_deactivate_form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const checkbox = document.getElementById('deactivate_confirm');
    
    if (!checkbox.checked) {
        Swal.fire({
            text: 'Lütfen onay kutusunu işaretleyin',
            icon: 'warning',
            buttonsStyling: false,
            confirmButtonText: 'Tamam',
            customClass: {
                confirmButton: 'btn btn-primary'
            }
        });
        return;
    }
    
    Swal.fire({
        title: 'Hesabınızı kapatmak istediğinize emin misiniz?',
        html: 'Bu işlemi onaylamak için lütfen şifrenizi girin:<br><br>' +
              '<input type="password" id="swal-password-input" class="form-control" placeholder="Şifreniz">',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Evet, Kapat',
        cancelButtonText: 'İptal',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary'
        },
        preConfirm: () => {
            const password = document.getElementById('swal-password-input').value;
            if (!password) {
                Swal.showValidationMessage('Lütfen şifrenizi girin');
                return false;
            }
            return password;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const submitButton = document.getElementById('kt_account_deactivate_submit');
            submitButton.setAttribute('data-kt-indicator', 'on');
            submitButton.disabled = true;
            
            const formData = new FormData();
            formData.append('password', result.value);
            
            fetch('/ayarlar/hesap-kapat', {
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
                        window.location.href = '/giris';
                    });
                } else {
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
        }
    });
});
</script>

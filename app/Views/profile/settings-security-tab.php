<!--begin::Sign-in Method Card-->
<div class="card mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_signin_method">
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">Giriş Bilgileri</h3>
        </div>
    </div>
    <!--end::Card header-->
    <!--begin::Content-->
    <div id="kt_account_signin_method" class="collapse show">
        <div class="card-body border-top p-9">
            <!--begin::Email Address-->
            <div class="d-flex flex-wrap align-items-center">
                <!--begin::Label-->
                <div id="kt_signin_email">
                    <div class="fs-6 fw-bold mb-1">E-posta Adresi</div>
                    <div class="fw-semibold text-gray-600"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                </div>
                <!--end::Label-->
                <!--begin::Edit-->
                <div id="kt_signin_email_edit" class="flex-row-fluid d-none">
                    <form id="kt_signin_change_email" class="form">
                        <div class="row mb-6">
                            <div class="col-lg-6 mb-4 mb-lg-0">
                                <div class="fv-row mb-0">
                                    <label for="emailaddress" class="form-label fs-6 fw-bold mb-3">Yeni E-posta Adresi</label>
                                    <input type="email" class="form-control form-control-lg form-control-solid" id="emailaddress" placeholder="E-posta adresi" name="emailaddress" value="<?= htmlspecialchars($user['email'] ?? '') ?>" />
                                    <div class="fv-plugins-message-container invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="fv-row mb-0">
                                    <label for="confirmemailpassword" class="form-label fs-6 fw-bold mb-3">Mevcut Şifre</label>
                                    <input type="password" class="form-control form-control-lg form-control-solid" name="confirmemailpassword" id="confirmemailpassword" placeholder="Mevcut şifreniz" />
                                    <div class="fv-plugins-message-container invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary me-2 px-6" id="kt_signin_email_submit">
                                <span class="indicator-label">E-postayı Güncelle</span>
                                <span class="indicator-progress">Lütfen bekleyin...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                            <button type="button" class="btn btn-color-gray-500 btn-active-light-primary px-6" id="kt_signin_cancel_email">İptal</button>
                        </div>
                    </form>
                </div>
                <!--end::Edit-->
                <!--begin::Action-->
                <div id="kt_signin_email_button" class="ms-auto">
                    <button class="btn btn-light btn-active-light-primary">E-postayı Değiştir</button>
                </div>
                <!--end::Action-->
            </div>
            <!--end::Email Address-->
            <!--begin::Separator-->
            <div class="separator separator-dashed my-6"></div>
            <!--end::Separator-->
            <!--begin::Password-->
            <div class="d-flex flex-wrap align-items-center mb-10">
                <!--begin::Label-->
                <div id="kt_signin_password">
                    <div class="fs-6 fw-bold mb-1">Şifre</div>
                    <div class="fw-semibold text-gray-600">************</div>
                </div>
                <!--end::Label-->
                <!--begin::Edit-->
                <div id="kt_signin_password_edit" class="flex-row-fluid d-none">
                    <form id="kt_signin_change_password" class="form">
                        <div class="row mb-1">
                            <div class="col-lg-4">
                                <div class="fv-row mb-0">
                                    <label for="currentpassword" class="form-label fs-6 fw-bold mb-3">Mevcut Şifre</label>
                                    <input type="password" class="form-control form-control-lg form-control-solid" name="currentpassword" id="currentpassword" placeholder="Mevcut şifre" />
                                    <div class="fv-plugins-message-container invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="fv-row mb-0">
                                    <label for="newpassword" class="form-label fs-6 fw-bold mb-3">Yeni Şifre</label>
                                    <input type="password" class="form-control form-control-lg form-control-solid" name="newpassword" id="newpassword" placeholder="Yeni şifre" />
                                    <div class="fv-plugins-message-container invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="fv-row mb-0">
                                    <label for="confirmpassword" class="form-label fs-6 fw-bold mb-3">Yeni Şifre (Tekrar)</label>
                                    <input type="password" class="form-control form-control-lg form-control-solid" name="confirmpassword" id="confirmpassword" placeholder="Şifre tekrarı" />
                                    <div class="fv-plugins-message-container invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-text mb-5">Şifreniz en az 8 karakter uzunluğunda olmalıdır</div>
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary me-2 px-6" id="kt_signin_password_submit">
                                <span class="indicator-label">Şifreyi Güncelle</span>
                                <span class="indicator-progress">Lütfen bekleyin...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                            <button type="button" class="btn btn-color-gray-500 btn-active-light-primary px-6" id="kt_signin_cancel_password">İptal</button>
                        </div>
                    </form>
                </div>
                <!--end::Edit-->
                <!--begin::Action-->
                <div id="kt_signin_password_button" class="ms-auto">
                    <button class="btn btn-light btn-active-light-primary">Şifreyi Değiştir</button>
                </div>
                <!--end::Action-->
            </div>
            <!--end::Password-->
        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Sign-in Method Card-->

<!--begin::Deactivate Account Card-->
<div class="card">
    <!--begin::Card header-->
    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_deactivate" aria-expanded="true">
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">Hesabı Devre Dışı Bırak</h3>
        </div>
    </div>
    <!--end::Card header-->
    <!--begin::Content-->
    <div id="kt_account_deactivate" class="collapse show">
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
                            <h4 class="text-gray-900 fw-bold">Hesabınızı devre dışı bırakmak üzeresiniz</h4>
                            <div class="fs-6 text-gray-700">
                                Hesabınızı devre dışı bıraktığınızda:
                                <br>• Sisteme giriş yapamayacaksınız
                                <br>• Verileriniz korunacak ancak erişemeyeceksiniz
                                <br>• Yönetici ile iletişime geçerek hesabınızı tekrar aktif edebilirsiniz
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Notice-->
                <!--begin::Form input row-->
                <div class="form-check form-check-solid fv-row">
                    <input name="deactivate" class="form-check-input" type="checkbox" value="1" id="deactivate" />
                    <label class="form-check-label fw-semibold ps-2 fs-6" for="deactivate">Hesabımı devre dışı bırakmak istediğimi onaylıyorum</label>
                </div>
                <!--end::Form input row-->
            </div>
            <!--end::Card body-->
            <!--begin::Card footer-->
            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <button type="submit" class="btn btn-danger fw-semibold" id="kt_account_deactivate_submit">
                    <span class="indicator-label">Hesabı Devre Dışı Bırak</span>
                    <span class="indicator-progress">Lütfen bekleyin...
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
            <!--end::Card footer-->
        </form>
        <!--end::Form-->
    </div>
    <!--end::Content-->
</div>
<!--end::Deactivate Account Card-->

<script>
// Email Change Toggle
document.getElementById('kt_signin_email_button').querySelector('button').addEventListener('click', function() {
    document.getElementById('kt_signin_email').classList.add('d-none');
    document.getElementById('kt_signin_email_button').classList.add('d-none');
    document.getElementById('kt_signin_email_edit').classList.remove('d-none');
});

document.getElementById('kt_signin_cancel_email').addEventListener('click', function() {
    document.getElementById('kt_signin_email').classList.remove('d-none');
    document.getElementById('kt_signin_email_button').classList.remove('d-none');
    document.getElementById('kt_signin_email_edit').classList.add('d-none');
});

// Password Change Toggle
document.getElementById('kt_signin_password_button').querySelector('button').addEventListener('click', function() {
    document.getElementById('kt_signin_password').classList.add('d-none');
    document.getElementById('kt_signin_password_button').classList.add('d-none');
    document.getElementById('kt_signin_password_edit').classList.remove('d-none');
});

document.getElementById('kt_signin_cancel_password').addEventListener('click', function() {
    document.getElementById('kt_signin_password').classList.remove('d-none');
    document.getElementById('kt_signin_password_button').classList.remove('d-none');
    document.getElementById('kt_signin_password_edit').classList.add('d-none');
});

// Email Change Form
document.getElementById('kt_signin_change_email').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitButton = document.getElementById('kt_signin_email_submit');
    const form = e.target;
    const formData = new FormData();
    formData.append('email', form.emailaddress.value);
    formData.append('current_password', form.confirmemailpassword.value);
    
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
    });
});

// Password Change Form
document.getElementById('kt_signin_change_password').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitButton = document.getElementById('kt_signin_password_submit');
    const form = e.target;
    const formData = new FormData();
    formData.append('current_password', form.currentpassword.value);
    formData.append('new_password', form.newpassword.value);
    formData.append('confirm_password', form.confirmpassword.value);
    
    submitButton.setAttribute('data-kt-indicator', 'on');
    submitButton.disabled = true;
    
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    
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
            document.getElementById('kt_signin_cancel_password').click();
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
            if (data.errors) {
                for (const [field, message] of Object.entries(data.errors)) {
                    let inputName = field;
                    if (field === 'current_password') inputName = 'currentpassword';
                    else if (field === 'new_password') inputName = 'newpassword';
                    else if (field === 'confirm_password') inputName = 'confirmpassword';
                    
                    const input = form.querySelector(`[name="${inputName}"]`);
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
    });
});

// Deactivate Account Form
document.getElementById('kt_account_deactivate_form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const checkbox = document.getElementById('deactivate');
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
        text: 'Hesabınızı devre dışı bırakmak için lütfen şifrenizi girin',
        icon: 'warning',
        input: 'password',
        inputPlaceholder: 'Şifreniz',
        showCancelButton: true,
        confirmButtonText: 'Devre Dışı Bırak',
        cancelButtonText: 'İptal',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-light'
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            const submitButton = document.getElementById('kt_account_deactivate_submit');
            submitButton.setAttribute('data-kt-indicator', 'on');
            submitButton.disabled = true;
            
            const formData = new FormData();
            formData.append('confirm_password', result.value);
            
            fetch('/profil/hesap-kapat', {
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
            });
        }
    });
});
</script>

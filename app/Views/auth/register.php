<!--begin::Authentication - Sign-up -->
<div class="d-flex flex-column flex-lg-row flex-column-fluid">
    <!--begin::Body-->
    <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-2 order-lg-1">
        <!--begin::Form-->
        <div class="d-flex flex-center flex-column flex-lg-row-fluid">
            <!--begin::Wrapper-->
            <div class="w-lg-600px p-10">
                
                <!--begin::Form-->
                <form class="form w-100" id="kt_sign_up_form">
                    <!--begin::Heading-->
                    <div class="text-center mb-11">
                        <!--begin::Title-->
                        <h1 class="text-gray-900 fw-bolder mb-3">Kayıt Ol</h1>
                        <!--end::Title-->
                        <!--begin::Subtitle-->
                        <div class="text-gray-500 fw-semibold fs-6">Ön Muhasebe Sistemi</div>
                        <!--end::Subtitle-->
                    </div>
                    <!--begin::Heading-->
                    
                    <!--begin::Alert-->
                    <div id="alert-container"></div>
                    <!--end::Alert-->
                    
                    <!--begin::Input group - Name-->
                    <div class="fv-row mb-8">
                        <input type="text" placeholder="Ad Soyad" name="name" autocomplete="off" class="form-control bg-transparent" />
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group - Company Name-->
                    <div class="fv-row mb-8">
                        <input type="text" placeholder="Firma Adı (Opsiyonel)" name="company_name" autocomplete="off" class="form-control bg-transparent" />
                        <div class="text-muted fs-7 mt-2">Belirtmezseniz, adınız otomatik olarak firma adı olarak kullanılacaktır.</div>
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group - Email-->
                    <div class="fv-row mb-8">
                        <input type="text" placeholder="Email" name="email" autocomplete="off" class="form-control bg-transparent" />
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group - Password-->
                    <div class="fv-row mb-8" data-kt-password-meter="true">
                        <!--begin::Wrapper-->
                        <div class="mb-1">
                            <!--begin::Input wrapper-->
                            <div class="position-relative mb-3">
                                <input class="form-control bg-transparent" type="password" placeholder="Şifre" name="password" autocomplete="off" />
                                <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" data-kt-password-meter-control="visibility">
                                    <i class="ki-duotone ki-eye-slash fs-2"></i>
                                    <i class="ki-duotone ki-eye fs-2 d-none"></i>
                                </span>
                            </div>
                            <!--end::Input wrapper-->
                            <!--begin::Meter-->
                            <div class="d-flex align-items-center mb-3" data-kt-password-meter-control="highlight">
                                <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
                                <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
                                <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
                                <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px"></div>
                            </div>
                            <!--end::Meter-->
                        </div>
                        <!--end::Wrapper-->
                        <!--begin::Hint-->
                        <div class="text-muted">En az 8 karakter, büyük harf, küçük harf ve rakam kullanın.</div>
                        <!--end::Hint-->
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Input group - Confirm password-->
                    <div class="fv-row mb-8">
                        <input type="password" placeholder="Şifre Tekrar" name="password_confirmation" autocomplete="off" class="form-control bg-transparent" />
                    </div>
                    <!--end::Input group-->
                    
                    <!--begin::Accept-->
                    <div class="fv-row mb-8">
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="toc" value="1" />
                            <span class="form-check-label fw-semibold text-gray-700 fs-base ms-1">
                                <a href="#" class="ms-1 link-primary">Kullanım Şartlarını</a> kabul ediyorum
                            </span>
                        </label>
                    </div>
                    <!--end::Accept-->
                    
                    <!--begin::Submit button-->
                    <div class="d-grid mb-10">
                        <button type="submit" id="kt_sign_up_submit" class="btn btn-primary">
                            <!--begin::Indicator label-->
                            <span class="indicator-label">Kayıt Ol</span>
                            <!--end::Indicator label-->
                            <!--begin::Indicator progress-->
                            <span class="indicator-progress">Lütfen bekleyin...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            <!--end::Indicator progress-->
                        </button>
                    </div>
                    <!--end::Submit button-->
                    
                    <!--begin::Sign in-->
                    <div class="text-gray-500 text-center fw-semibold fs-6">
                        Zaten hesabınız var mı?
                        <a href="/login" class="link-primary">Giriş Yap</a>
                    </div>
                    <!--end::Sign in-->
                </form>
                <!--end::Form-->
                
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Form-->
        
        <!--begin::Footer-->
        <div class="d-flex flex-center flex-wrap px-5">
            <!--begin::Links-->
            <div class="d-flex fw-semibold text-primary fs-base">
                <a href="/about" class="px-5" target="_blank">Hakkımızda</a>
                <a href="/contact" class="px-5" target="_blank">İletişim</a>
                <a href="/support" class="px-5" target="_blank">Destek</a>
            </div>
            <!--end::Links-->
        </div>
        <!--end::Footer-->
    </div>
    <!--end::Body-->
    
    <!--begin::Aside-->
    <div class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-center order-1 order-lg-2" style="background-image: url(/lisanstema/demo/assets/media/misc/auth-bg.png)">
        <!--begin::Content-->
        <div class="d-flex flex-column flex-center py-7 py-lg-15 px-5 px-md-15 w-100">
            <!--begin::Logo-->
            <a href="/" class="mb-0 mb-lg-12">
                <img alt="Logo" src="/lisanstema/demo/assets/media/logos/custom-1.png" class="h-60px h-lg-75px" />
            </a>
            <!--end::Logo-->
            
            <!--begin::Image-->
            <img class="d-none d-lg-block mx-auto w-275px w-md-50 w-xl-500px mb-10 mb-lg-20" src="/lisanstema/demo/assets/media/misc/auth-screens.png" alt="" />
            <!--end::Image-->
            
            <!--begin::Title-->
            <h1 class="d-none d-lg-block text-white fs-2qx fw-bolder text-center mb-7">
                Hızlı, Güvenli, Kolay
            </h1>
            <!--end::Title-->
            
            <!--begin::Text-->
            <div class="d-none d-lg-block text-white fs-base text-center">
                Modern muhasebe yönetimi için <br />
                ihtiyacınız olan her şey burada
            </div>
            <!--end::Text-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Aside-->
</div>
<!--end::Authentication - Sign-up-->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('kt_sign_up_form');
    const submitButton = document.getElementById('kt_sign_up_submit');
    const alertContainer = document.getElementById('alert-container');
    
    // Show alert function
    function showAlert(message, type = 'danger') {
        alertContainer.innerHTML = `
            <div class="alert alert-${type} d-flex align-items-center p-5 mb-10">
                <i class="ki-duotone ki-shield-tick fs-2hx text-${type} me-4">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                <div class="d-flex flex-column">
                    <span>${message}</span>
                </div>
            </div>
        `;
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            alertContainer.innerHTML = '';
        }, 5000);
    }
    
    // Password visibility toggle
    const toggleButtons = document.querySelectorAll('[data-kt-password-meter-control="visibility"]');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.closest('.position-relative').querySelector('input');
            const eyeSlash = this.querySelector('.ki-eye-slash');
            const eye = this.querySelector('.ki-eye');
            
            if (input.type === 'password') {
                input.type = 'text';
                eyeSlash.classList.add('d-none');
                eye.classList.remove('d-none');
            } else {
                input.type = 'password';
                eyeSlash.classList.remove('d-none');
                eye.classList.add('d-none');
            }
        });
    });
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validation
        const name = form.querySelector('[name="name"]').value.trim();
        const companyName = form.querySelector('[name="company_name"]').value.trim();
        const email = form.querySelector('[name="email"]').value.trim();
        const password = form.querySelector('[name="password"]').value;
        const passwordConfirm = form.querySelector('[name="password_confirmation"]').value;
        const toc = form.querySelector('[name="toc"]').checked;
        
        if (!name) {
            showAlert('Lütfen adınızı giriniz.', 'warning');
            return;
        }
        
        if (!email) {
            showAlert('Lütfen email adresinizi giriniz.', 'warning');
            return;
        }
        
        if (password.length < 8) {
            showAlert('Şifre en az 8 karakter olmalıdır.', 'warning');
            return;
        }
        
        if (password !== passwordConfirm) {
            showAlert('Şifreler eşleşmiyor.', 'warning');
            return;
        }
        
        if (!toc) {
            showAlert('Kullanım şartlarını kabul etmelisiniz.', 'warning');
            return;
        }
        
        // Show loading
        submitButton.setAttribute('data-kt-indicator', 'on');
        submitButton.disabled = true;
        
        const formData = {
            full_name: name,
            company_name: companyName || null,
            email: email,
            password: password,
            password_confirmation: passwordConfirm
        };
        
        try {
            const response = await fetch('/api/auth/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                // Show success message with trial info
                const message = data.data?.message || 'Kayıt başarılı! 30 günlük ücretsiz deneme süresi başladı. Giriş sayfasına yönlendiriliyorsunuz...';
                showAlert(message, 'success');
                
                // Redirect to login
                setTimeout(() => {
                    window.location.href = '/login';
                }, 3000);
            } else {
                showAlert(data.message || 'Kayıt başarısız. Lütfen bilgilerinizi kontrol edin.', 'danger');
                submitButton.removeAttribute('data-kt-indicator');
                submitButton.disabled = false;
            }
        } catch (error) {
            console.error('Register error:', error);
            showAlert('Bir hata oluştu. Lütfen tekrar deneyin.', 'danger');
            submitButton.removeAttribute('data-kt-indicator');
            submitButton.disabled = false;
        }
    });
});
</script>

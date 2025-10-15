<!--begin::Authentication - Sign-in -->
<div class="d-flex flex-column flex-lg-row flex-column-fluid">
    <!--begin::Body-->
    <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-2 order-lg-1">
        <!--begin::Form-->
        <div class="d-flex flex-center flex-column flex-lg-row-fluid">
            <!--begin::Wrapper-->
            <div class="w-lg-500px p-10">
                
                <!--begin::Form-->
                <form class="form w-100" id="kt_sign_in_form">
                    <!--begin::Heading-->
                    <div class="text-center mb-11">
                        <!--begin::Title-->
                        <h1 class="text-gray-900 fw-bolder mb-3">Giriş Yap</h1>
                        <!--end::Title-->
                        <!--begin::Subtitle-->
                        <div class="text-gray-500 fw-semibold fs-6">Ön Muhasebe Sistemi</div>
                        <!--end::Subtitle-->
                    </div>
                    <!--begin::Heading-->
                    
                    <!--begin::Alert-->
                    <div id="alert-container"></div>
                    <!--end::Alert-->
                    
                    <!--begin::Input group=-->
                    <div class="fv-row mb-8">
                        <!--begin::Email-->
                        <input type="text" placeholder="Email" name="email" autocomplete="off" class="form-control bg-transparent" value="admin@onmuhasebe.com" />
                        <!--end::Email-->
                    </div>
                    
                    <!--begin::Input group-->
                    <div class="fv-row mb-3">
                        <!--begin::Password-->
                        <input type="password" placeholder="Şifre" name="password" autocomplete="off" class="form-control bg-transparent" value="Admin123!" />
                        <!--end::Password-->
                    </div>
                    <!--end::Input group=-->
                    
                    <!--begin::Wrapper-->
                    <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                        <div></div>
                        <!--begin::Link-->
                        <a href="/forgot-password" class="link-primary">Şifremi Unuttum?</a>
                        <!--end::Link-->
                    </div>
                    <!--end::Wrapper-->
                    
                    <!--begin::Submit button-->
                    <div class="d-grid mb-10">
                        <button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
                            <!--begin::Indicator label-->
                            <span class="indicator-label">Giriş Yap</span>
                            <!--end::Indicator label-->
                            <!--begin::Indicator progress-->
                            <span class="indicator-progress">Lütfen bekleyin...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            <!--end::Indicator progress-->
                        </button>
                    </div>
                    <!--end::Submit button-->
                    
                    <!--begin::Sign up-->
                    <div class="text-gray-500 text-center fw-semibold fs-6">
                        Hesabınız yok mu?
                        <a href="/register" class="link-primary">Kayıt Ol</a>
                    </div>
                    <!--end::Sign up-->
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
<!--end::Authentication - Sign-in-->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('kt_sign_in_form');
    const submitButton = document.getElementById('kt_sign_in_submit');
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
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Show loading
        submitButton.setAttribute('data-kt-indicator', 'on');
        submitButton.disabled = true;
        
        const formData = {
            email: form.querySelector('[name="email"]').value,
            password: form.querySelector('[name="password"]').value
        };
        
        try {
            const response = await fetch('/api/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                // Store tokens
                localStorage.setItem('access_token', data.data.tokens.access_token);
                localStorage.setItem('refresh_token', data.data.tokens.refresh_token);
                
                // Create session via hidden endpoint
                const sessionResponse = await fetch('/api/auth/create-session', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${data.data.tokens.access_token}`,
                        'Content-Type': 'application/json'
                    }
                });
                
                const sessionData = await sessionResponse.json();
                
                if (sessionResponse.ok && sessionData.success) {
                    // Show success message
                    showAlert('Giriş başarılı! Yönlendiriliyorsunuz...', 'success');
                    
                    // Redirect to dashboard (root path)
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 1000);
                } else {
                    const errorMsg = sessionData.message || 'Session oluşturulamadı. Lütfen tekrar deneyin.';
                    console.error('Session error:', sessionData);
                    showAlert(errorMsg, 'warning');
                    submitButton.removeAttribute('data-kt-indicator');
                    submitButton.disabled = false;
                }
            } else {
                showAlert(data.message || 'Giriş başarısız. Lütfen bilgilerinizi kontrol edin.', 'danger');
                submitButton.removeAttribute('data-kt-indicator');
                submitButton.disabled = false;
            }
        } catch (error) {
            console.error('Login error:', error);
            showAlert('Bir hata oluştu. Lütfen tekrar deneyin.', 'danger');
            submitButton.removeAttribute('data-kt-indicator');
            submitButton.disabled = false;
        }
    });
});
</script>

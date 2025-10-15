<!--begin::Header-->
<div id="kt_app_header" class="app-header" data-kt-sticky="true" data-kt-sticky-activate="{default: true, lg: true}" data-kt-sticky-name="app-header-minimize" data-kt-sticky-offset="{default: '200px', lg: '0'}" data-kt-sticky-animation="false">
    <!--begin::Header container-->
    <div class="app-container container-fluid d-flex align-items-stretch justify-content-between" id="kt_app_header_container">
        
        <!--begin::Sidebar mobile toggle-->
        <div class="d-flex align-items-center d-lg-none ms-n3 me-1 me-md-2" title="Show sidebar menu">
            <div class="btn btn-icon btn-active-color-primary w-35px h-35px" id="kt_app_sidebar_mobile_toggle">
                <i class="ki-duotone ki-abstract-14 fs-2 fs-md-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </div>
        </div>
        <!--end::Sidebar mobile toggle-->
        
        <!--begin::Mobile logo-->
        <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
            <a href="/" class="d-lg-none">
                <img alt="Logo" src="/lisanstema/demo/assets/media/logos/default-small.svg" class="h-30px" />
            </a>
        </div>
        <!--end::Mobile logo-->
        
        <!--begin::Header wrapper-->
        <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1" id="kt_app_header_wrapper">
            
            <!--begin::Menu wrapper-->
            <div class="app-header-menu app-header-mobile-drawer align-items-stretch" data-kt-drawer="true" data-kt-drawer-name="app-header-menu" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_app_header_menu_toggle" data-kt-swapper="true" data-kt-swapper-mode="{default: 'append', lg: 'prepend'}" data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_wrapper'}">
                <!--begin::Menu-->
                <div class="menu menu-rounded menu-column menu-lg-row my-5 my-lg-0 align-items-stretch fw-semibold px-2 px-lg-0" id="kt_app_header_menu" data-kt-menu="true">
                    <!--begin:Menu item-->
                    <div class="menu-item me-0 me-lg-2">
                        <a class="menu-link" href="/">
                            <span class="menu-title">Dashboard</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                </div>
                <!--end::Menu-->
            </div>
            <!--end::Menu wrapper-->
            
            <!--begin::Navbar-->
            <div class="app-navbar flex-shrink-0">
                
                <!--begin::Search-->
                <div class="app-navbar-item align-items-stretch ms-1 ms-lg-3">
                    <div id="kt_header_search" class="header-search d-flex align-items-stretch" data-kt-search-keypress="true" data-kt-search-min-length="2" data-kt-search-enter="enter" data-kt-search-layout="menu" data-kt-menu-trigger="auto" data-kt-menu-overflow="false" data-kt-menu-permanent="true" data-kt-menu-placement="bottom-end">
                        <div class="d-flex align-items-center" data-kt-search-element="toggle" id="kt_header_search_toggle">
                            <div class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-35px h-35px w-md-40px h-md-40px">
                                <i class="ki-duotone ki-magnifier fs-2 fs-lg-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                        </div>
                        <div data-kt-search-element="content" class="menu menu-sub menu-sub-dropdown p-7 w-325px w-md-375px">
                            <div data-kt-search-element="wrapper">
                                <form data-kt-search-element="form" class="w-100 position-relative mb-3" autocomplete="off">
                                    <i class="ki-duotone ki-magnifier fs-2 text-gray-500 position-absolute top-50 translate-middle-y ms-4">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <input type="text" class="form-control form-control-flush ps-10" name="search" placeholder="Fatura, Cari, Ürün ara..." data-kt-search-element="input" />
                                    <span class="position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-1" data-kt-search-element="spinner">
                                        <span class="spinner-border h-15px w-15px align-middle text-gray-400"></span>
                                    </span>
                                    <span class="btn btn-flush btn-active-color-primary position-absolute top-50 end-0 translate-middle-y lh-0 d-none" data-kt-search-element="clear">
                                        <i class="ki-duotone ki-cross fs-2 fs-lg-1 me-0">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                </form>
                                <div data-kt-search-element="results" class="d-none">
                                    <div class="scroll-y mh-200px mh-lg-350px">
                                        <h3 class="fs-5 text-muted m-0 pb-5" data-kt-search-element="category-title">Sonuçlar</h3>
                                        <div data-kt-search-element="suggestions">
                                            <div class="text-center text-muted py-10">Arama sonucu bulunamadı</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="" data-kt-search-element="main">
                                    <div class="d-flex flex-stack fw-semibold mb-4">
                                        <span class="text-muted fs-6 me-2">Son Aramalar:</span>
                                    </div>
                                    <div class="scroll-y mh-200px mh-lg-325px">
                                        <div class="d-flex align-items-center mb-5">
                                            <div class="symbol symbol-40px me-4">
                                                <span class="symbol-label bg-light">
                                                    <i class="ki-duotone ki-file fs-2 text-primary">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </span>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <a href="#" class="fs-6 text-gray-800 text-hover-primary fw-semibold">Faturalar</a>
                                                <span class="fs-7 text-muted fw-semibold">#12345</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Search-->
                
                <!--begin::Notifications-->
                <div class="app-navbar-item ms-1 ms-lg-3">
                    <div class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-35px h-35px w-md-40px h-md-40px position-relative" id="kt_drawer_chat_toggle">
                        <i class="ki-duotone ki-notification-on fs-2 fs-lg-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                            <span class="path5"></span>
                        </i>
                        <span class="bullet bullet-dot bg-success h-6px w-6px position-absolute translate-middle top-0 start-50 animation-blink"></span>
                    </div>
                </div>
                <!--end::Notifications-->
                
                <!--begin::Quick links-->
                <div class="app-navbar-item ms-1 ms-lg-3">
                    <div class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-35px h-35px w-md-40px h-md-40px" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                        <i class="ki-duotone ki-element-11 fs-2 fs-lg-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                    </div>
                    <div class="menu menu-sub menu-sub-dropdown menu-column w-250px w-lg-325px" data-kt-menu="true">
                        <div class="d-flex flex-column flex-center bgi-no-repeat rounded-top px-9 py-10" style="background-image:url('/lisanstema/demo/assets/media/misc/menu-header-bg.jpg')">
                            <h3 class="text-white fw-semibold mb-3">Hızlı Erişim</h3>
                            <span class="badge bg-primary py-2 px-3">Sık Kullanılanlar</span>
                        </div>
                        <div class="row g-0">
                            <div class="col-6">
                                <a href="/fatura/olustur" class="d-flex flex-column flex-center h-100 p-6 bg-hover-light border-end border-bottom">
                                    <i class="ki-duotone ki-file-added fs-3x text-primary mb-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <span class="fs-5 fw-semibold text-gray-800 mb-0">Yeni Fatura</span>
                                    <span class="fs-7 text-gray-400">Fatura Oluştur</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="/faturalar" class="d-flex flex-column flex-center h-100 p-6 bg-hover-light border-bottom">
                                    <i class="ki-duotone ki-abstract-41 fs-3x text-primary mb-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <span class="fs-5 fw-semibold text-gray-800 mb-0">Faturalar</span>
                                    <span class="fs-7 text-gray-400">Tümünü Gör</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="/cari/liste" class="d-flex flex-column flex-center h-100 p-6 bg-hover-light border-end">
                                    <i class="ki-duotone ki-profile-user fs-3x text-primary mb-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                    </i>
                                    <span class="fs-5 fw-semibold text-gray-800 mb-0">Cariler</span>
                                    <span class="fs-7 text-gray-400">Müşteri/Tedarikçi</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="/raporlar" class="d-flex flex-column flex-center h-100 p-6 bg-hover-light">
                                    <i class="ki-duotone ki-chart-simple fs-3x text-primary mb-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                    </i>
                                    <span class="fs-5 fw-semibold text-gray-800 mb-0">Raporlar</span>
                                    <span class="fs-7 text-gray-400">Analizler</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Quick links-->
                
                <!--begin::User menu-->
                <div class="app-navbar-item ms-1 ms-lg-3" id="kt_header_user_menu_toggle">
                    <!--begin::Menu wrapper-->
                    <?php
                    // Session'dan kullanıcı bilgisini al
                    $userId = $_SESSION['user_id'] ?? null;
                    $headerAvatarUrl = '/lisanstema/demo/assets/media/avatars/300-1.jpg'; // default
                    
                    if ($userId) {
                        $userModel = new \App\Models\User();
                        $headerUser = $userModel->find($userId);
                        if ($headerUser && !empty($headerUser['avatar'])) {
                            $headerAvatarUrl = '/storage/uploads/avatars/' . htmlspecialchars($headerUser['avatar']);
                        }
                    }
                    ?>
                    <div class="cursor-pointer symbol symbol-35px symbol-md-40px" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                        <img src="<?php echo $headerAvatarUrl; ?>" alt="user" class="user-avatar-header" />
                    </div>
                    
                    <!--begin::User account menu-->
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px" data-kt-menu="true">
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <div class="menu-content d-flex align-items-center px-3">
                                <div class="symbol symbol-50px me-5">
                                    <img alt="Logo" src="<?php echo $headerAvatarUrl; ?>" class="user-avatar-menu" />
                                </div>
                                <div class="d-flex flex-column">
                                    <div class="fw-bold d-flex align-items-center fs-5" id="header-user-name">
                                        Kullanıcı
                                    </div>
                                    <a href="#" class="fw-semibold text-muted text-hover-primary fs-7" id="header-user-email">
                                        user@example.com
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!--end::Menu item-->
                        
                        <!--begin::Menu separator-->
                        <div class="separator my-2"></div>
                        <!--end::Menu separator-->
                        
                        <!--begin::Menu item-->
                        <div class="menu-item px-5">
                            <a href="/profil" class="menu-link px-5">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-profile-circle fs-4 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </span>
                                <span class="menu-text">Profilim</span>
                            </a>
                        </div>
                        <!--end::Menu item-->
                        
                        <!--begin::Menu item-->
                        <div class="menu-item px-5">
                            <a href="/" class="menu-link px-5">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-chart-simple fs-4 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                    </i>
                                </span>
                                <span class="menu-text">Dashboard</span>
                            </a>
                        </div>
                        <!--end::Menu item-->
                        
                        <!--begin::Menu item-->
                        <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="left-start" data-kt-menu-offset="-15px, 0">
                            <a href="#" class="menu-link px-5">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-setting-2 fs-4 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Ayarlar</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="menu-sub menu-sub-dropdown w-175px py-4">
                                <div class="menu-item px-3">
                                    <a href="/ayarlar/genel" class="menu-link px-5">Genel Ayarlar</a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="/ayarlar/sirket" class="menu-link px-5">Şirket Bilgileri</a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="/ayarlar/guvenlik" class="menu-link px-5">Güvenlik</a>
                                </div>
                            </div>
                        </div>
                        <!--end::Menu item-->
                        
                        <!--begin::Menu separator-->
                        <div class="separator my-2"></div>
                        <!--end::Menu separator-->
                        
                        <!--begin::Menu item-->
                        <div class="menu-item px-5 my-1">
                            <a href="/ayarlar/hesap" class="menu-link px-5">Hesap Ayarları</a>
                        </div>
                        <!--end::Menu item-->
                        
                        <!--begin::Menu item-->
                        <div class="menu-item px-5">
                            <a href="#" id="kt_sign_out" class="menu-link px-5">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-exit-left fs-4 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-text">Çıkış Yap</span>
                            </a>
                        </div>
                        <!--end::Menu item-->
                    </div>
                    <!--end::User account menu-->
                </div>
                <!--end::User menu-->
                
                <!--begin::Header menu toggle-->
                <div class="app-navbar-item d-lg-none ms-2 me-n2" title="Show header menu">
                    <div class="btn btn-flex btn-icon btn-active-color-primary w-30px h-30px" id="kt_app_header_menu_toggle">
                        <i class="ki-duotone ki-element-4 fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                </div>
                <!--end::Header menu toggle-->
                
            </div>
            <!--end::Navbar-->
            
        </div>
        <!--end::Header wrapper-->
        
    </div>
    <!--end::Header container-->
</div>
<!--end::Header-->

<script>
// Load user info and handle logout
document.addEventListener('DOMContentLoaded', function() {
    // Load user info from API
    loadUserInfo();
    
    // Setup logout
    setupLogout();
});

/**
 * Load user information
 */
async function loadUserInfo() {
    const token = localStorage.getItem('access_token');
    if (!token) return;
    
    try {
        const response = await fetch('/api/auth/me', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.success && data.data) {
                const user = data.data;
                
                // Update header display
                const nameEl = document.getElementById('header-user-name');
                const emailEl = document.getElementById('header-user-email');
                const avatarHeader = document.querySelector('.user-avatar-header');
                const avatarMenu = document.querySelector('.user-avatar-menu');
                
                if (nameEl) {
                    nameEl.textContent = user.full_name || user.name || 'Kullanıcı';
                }
                if (emailEl) {
                    emailEl.textContent = user.email || '';
                }
                
                // Update avatar images
                if (user.avatar) {
                    const avatarUrl = '/storage/uploads/avatars/' + user.avatar;
                    if (avatarHeader) avatarHeader.src = avatarUrl;
                    if (avatarMenu) avatarMenu.src = avatarUrl;
                }
            }
        }
    } catch (error) {
        console.error('Error loading user info:', error);
    }
}

/**
 * Setup logout functionality
 */
function setupLogout() {
    const signOutBtn = document.getElementById('kt_sign_out');
    if (!signOutBtn) return;
    
    signOutBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        
        // Show confirmation
        const result = await Swal.fire({
            title: 'Çıkış Yap',
            text: 'Çıkış yapmak istediğinizden emin misiniz?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Evet, Çıkış Yap',
            cancelButtonText: 'İptal',
            confirmButtonColor: '#f1416c',
            cancelButtonColor: '#a1a5b7'
        });
        
        if (!result.isConfirmed) return;
        
        // Call API logout
        const token = localStorage.getItem('access_token');
        if (token) {
            try {
                await fetch('/api/auth/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });
            } catch (error) {
                console.error('Logout API error:', error);
            }
        }
        
        // Clear all tokens
        localStorage.removeItem('access_token');
        localStorage.removeItem('refresh_token');
        
        // Show success message
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Başarıyla çıkış yapıldı',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
        });
        
        // Redirect to web logout (clears session)
        setTimeout(() => {
            window.location.href = '/cikis';
        }, 1000);
    });
}
</script>

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
                        <a class="nav-link text-active-primary ms-0 me-10 py-5 active" href="/profil/ayarlar">Ayarlar</a>
                    </li>
                </ul>
                <!--end::Navs-->
            </div>
        </div>
        <!--end::Navbar-->
        
        <!--begin::Tabs-->
        <div class="row g-5 g-xl-10">
            <div class="col-xl-12">
                <!--begin::Tab nav-->
                <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_pane_profile">
                            <i class="ki-duotone ki-user fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Genel Ayarlar
                        </a>
                    </li>
                    <?php if (!empty($user['company_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_pane_company">
                            <i class="ki-duotone ki-office-bag fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                            </i>
                            Şirket Bilgileri
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_pane_security">
                            <i class="ki-duotone ki-shield-tick fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Güvenlik
                        </a>
                    </li>
                </ul>
                <!--end::Tab nav-->
                
                <!--begin::Tab content-->
                <div class="tab-content" id="myTabContent">
                    <!--begin::Tab pane - Profile-->
                    <div class="tab-pane fade show active" id="kt_tab_pane_profile" role="tabpanel">
                        <?php include __DIR__ . '/settings-profile-tab.php'; ?>
                    </div>
                    <!--end::Tab pane-->
                    
                    <?php if (!empty($user['company_id'])): ?>
                    <!--begin::Tab pane - Company-->
                    <div class="tab-pane fade" id="kt_tab_pane_company" role="tabpanel">
                        <?php include __DIR__ . '/settings-company-tab.php'; ?>
                    </div>
                    <!--end::Tab pane-->
                    <?php endif; ?>
                    
                    <!--begin::Tab pane - Security-->
                    <div class="tab-pane fade" id="kt_tab_pane_security" role="tabpanel">
                        <?php include __DIR__ . '/settings-security-tab.php'; ?>
                    </div>
                    <!--end::Tab pane-->
                </div>
                <!--end::Tab content-->
            </div>
        </div>
        <!--end::Tabs-->
    </div>
    <!--end::Content container-->
</div>
<!--end::Content-->

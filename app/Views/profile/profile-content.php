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
                            <?php
                            // Avatar URL oluştur
                            $avatarUrl = null;
                            if (isset($user) && !empty($user['avatar'])) {
                                // Ensure forward slashes and proper encoding
                                $avatarFilename = str_replace('\\', '/', $user['avatar']);
                                // Avatar dosyası avatars klasöründe
                                $avatarUrl = '/storage/uploads/avatars/' . htmlspecialchars($avatarFilename, ENT_QUOTES, 'UTF-8');
                            }
                            ?>
                            <?php if ($avatarUrl): ?>
                                <img src="<?php echo $avatarUrl; ?>" alt="Avatar" class="symbol-label" style="object-fit: cover;" />
                            <?php else: ?>
                                <div class="symbol-label fs-2 fw-semibold text-primary bg-light-primary">
                                    <?= strtoupper(mb_substr($user['full_name'] ?? 'U', 0, 2)) ?>
                                </div>
                            <?php endif; ?>
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
                                    <?php if (!empty($user['email_verified_at'])): ?>
                                        <i class="ki-duotone ki-verify fs-1 text-primary">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    <?php endif; ?>
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
                                    <?php if (!empty($company)): ?>
                                        <span class="d-flex align-items-center text-gray-500 me-5 mb-2">
                                            <i class="ki-duotone ki-sms fs-4 me-1">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            <?= htmlspecialchars($company['name'] ?? '') ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="d-flex align-items-center text-gray-500 mb-2">
                                        <i class="ki-duotone ki-geolocation fs-4 me-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <?= htmlspecialchars($company['city'] ?? 'Türkiye') ?>
                                    </span>
                                </div>
                            </div>
                            <!--end::User-->
                            <!--begin::Actions-->
                            <div class="d-flex my-4">
                                <a href="/profil/duzenle" class="btn btn-sm btn-primary me-3">Profili Düzenle</a>
                            </div>
                            <!--end::Actions-->
                        </div>
                        <!--end::Title-->
                        <!--begin::Stats-->
                        <div class="d-flex flex-wrap flex-stack">
                            <div class="d-flex flex-column flex-grow-1 pe-8">
                                <div class="d-flex flex-wrap">
                                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="ki-duotone ki-calendar fs-3 text-success me-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            <div class="fs-2 fw-bold"><?= date('d.m.Y', strtotime($user['created_at'])) ?></div>
                                        </div>
                                        <div class="fw-semibold fs-6 text-gray-500">Kayıt Tarihi</div>
                                    </div>
                                    <?php if (!empty($user['last_login_at'])): ?>
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="d-flex align-items-center">
                                                <i class="ki-duotone ki-arrow-right fs-3 text-primary me-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                                <div class="fs-2 fw-bold"><?= date('d.m.Y H:i', strtotime($user['last_login_at'])) ?></div>
                                            </div>
                                            <div class="fw-semibold fs-6 text-gray-500">Son Giriş</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <!--end::Stats-->
                    </div>
                    <!--end::Info-->
                </div>
                <!--end::Details-->
                <!--begin::Navs-->
                <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5 active" href="/profil">Genel Bakış</a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5" href="/profil/duzenle">Ayarlar</a>
                    </li>
                </ul>
                <!--end::Navs-->
            </div>
        </div>
        <!--end::Navbar-->
        
        <!--begin::details View-->
        <div class="card mb-5 mb-xl-10" id="kt_profile_details_view">
            <!--begin::Card header-->
            <div class="card-header cursor-pointer">
                <div class="card-title m-0">
                    <h3 class="fw-bold m-0">Profil Detayları</h3>
                </div>
                <a href="/profil/duzenle" class="btn btn-sm btn-primary align-self-center">Profili Düzenle</a>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body p-9">
                <!--begin::Row-->
                <div class="row mb-7">
                    <label class="col-lg-4 fw-semibold text-muted">Ad Soyad</label>
                    <div class="col-lg-8">
                        <span class="fw-bold fs-6 text-gray-800"><?= htmlspecialchars($user['full_name'] ?? '-') ?></span>
                    </div>
                </div>
                <!--end::Row-->
                <!--begin::Row-->
                <div class="row mb-7">
                    <label class="col-lg-4 fw-semibold text-muted">E-posta</label>
                    <div class="col-lg-8 d-flex align-items-center">
                        <span class="fw-bold fs-6 text-gray-800 me-2"><?= htmlspecialchars($user['email'] ?? '-') ?></span>
                        <?php if (!empty($user['email_verified_at'])): ?>
                            <span class="badge badge-success">Doğrulandı</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Doğrulanmadı</span>
                        <?php endif; ?>
                    </div>
                </div>
                <!--end::Row-->
                <!--begin::Row-->
                <div class="row mb-7">
                    <label class="col-lg-4 fw-semibold text-muted">
                        Telefon
                        <span class="ms-1" data-bs-toggle="tooltip" title="Telefon numarası">
                            <i class="ki-duotone ki-information fs-7">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </span>
                    </label>
                    <div class="col-lg-8">
                        <span class="fw-bold fs-6 text-gray-800"><?= htmlspecialchars($user['phone'] ?? '-') ?></span>
                    </div>
                </div>
                <!--end::Row-->
                <!--begin::Row-->
                <div class="row mb-7">
                    <label class="col-lg-4 fw-semibold text-muted">Rol</label>
                    <div class="col-lg-8">
                        <span class="fw-bold fs-6 text-gray-800">
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
                <!--end::Row-->
                
                <?php if (!empty($company)): ?>
                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-6"></div>
                    <!--end::Separator-->
                    
                    <h4 class="fw-bold mb-7">Şirket Bilgileri</h4>
                    
                    <!--begin::Row-->
                    <div class="row mb-7">
                        <label class="col-lg-4 fw-semibold text-muted">Şirket Adı</label>
                        <div class="col-lg-8">
                            <span class="fw-semibold text-gray-800 fs-6"><?= htmlspecialchars($company['name'] ?? '-') ?></span>
                        </div>
                    </div>
                    <!--end::Row-->
                    <!--begin::Row-->
                    <?php if (!empty($company['tax_number'])): ?>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">Vergi No</label>
                            <div class="col-lg-8">
                                <span class="fw-semibold text-gray-800 fs-6"><?= htmlspecialchars($company['tax_number']) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!--end::Row-->
                    <!--begin::Row-->
                    <?php if (!empty($company['tax_office'])): ?>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">Vergi Dairesi</label>
                            <div class="col-lg-8">
                                <span class="fw-semibold text-gray-800 fs-6"><?= htmlspecialchars($company['tax_office']) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!--end::Row-->
                    <!--begin::Row-->
                    <?php if (!empty($company['address'])): ?>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">Adres</label>
                            <div class="col-lg-8">
                                <span class="fw-semibold text-gray-800 fs-6">
                                    <?= htmlspecialchars($company['address']) ?>
                                    <?php if (!empty($company['district']) || !empty($company['city'])): ?>
                                        <br>
                                        <?= htmlspecialchars($company['district'] ?? '') ?>
                                        <?= !empty($company['district']) && !empty($company['city']) ? '/' : '' ?>
                                        <?= htmlspecialchars($company['city'] ?? '') ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!--end::Row-->
                    <!--begin::Row-->
                    <?php if (!empty($company['phone'])): ?>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">Şirket Telefon</label>
                            <div class="col-lg-8">
                                <span class="fw-semibold text-gray-800 fs-6"><?= htmlspecialchars($company['phone']) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!--end::Row-->
                    <!--begin::Row-->
                    <?php if (!empty($company['email'])): ?>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">Şirket E-posta</label>
                            <div class="col-lg-8">
                                <a href="mailto:<?= htmlspecialchars($company['email']) ?>" class="fw-semibold fs-6 text-gray-800 text-hover-primary">
                                    <?= htmlspecialchars($company['email']) ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!--end::Row-->
                    <!--begin::Row-->
                    <?php if (!empty($company['website'])): ?>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">Website</label>
                            <div class="col-lg-8">
                                <a href="<?= htmlspecialchars($company['website']) ?>" target="_blank" class="fw-semibold fs-6 text-gray-800 text-hover-primary">
                                    <?= htmlspecialchars($company['website']) ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!--end::Row-->
                <?php endif; ?>
                
                <!--begin::Notice-->
                <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6 mt-10">
                    <i class="ki-duotone ki-shield-tick fs-2tx text-primary me-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">Hesap Güvenliği</h4>
                            <div class="fs-6 text-gray-700">
                                Hesap güvenliğiniz için düzenli olarak şifrenizi değiştirmenizi öneririz.
                                <a class="fw-bold" href="/profil/duzenle">Şifrenizi değiştirmek için tıklayın</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Notice-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::details View-->
    </div>
    <!--end::Content container-->
</div>
<!--end::Content-->

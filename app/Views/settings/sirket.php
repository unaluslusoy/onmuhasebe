<!--begin::Content-->
<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">
        
        <?php if (!isset($company) || empty($company)): ?>
        <div class="alert alert-warning">
            <strong>Uyarı:</strong> Şirket bilgisi bulunamadı. Lütfen önce şirket oluşturun.
        </div>
        <?php endif; ?>
        
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
                        <a class="nav-link text-active-primary ms-0 me-10 py-5 <?= ($activeTab ?? 'sirket') === 'sirket' ? 'active' : '' ?>" href="/ayarlar/sirket">
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
                        <a class="nav-link text-active-primary ms-0 me-10 py-5 <?= ($activeTab ?? '') === 'guvenlik' ? 'active' : '' ?>" href="/ayarlar/guvenlik">
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
        
        <?php if (empty($user['company_id'])): ?>
        <!--begin::No Company Notice-->
        <div class="card">
            <div class="card-body p-9">
                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                    <i class="ki-duotone ki-information fs-2tx text-warning me-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">Henüz bir şirket tanımlanmamış</h4>
                            <div class="fs-6 text-gray-700 mb-5">
                                Şirket ayarlarını düzenleyebilmek için önce bir şirket oluşturmanız gerekmektedir.
                            </div>
                            <a href="/sirket/olustur" class="btn btn-warning">
                                <i class="ki-duotone ki-plus fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Şirket Oluştur
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::No Company Notice-->
        <?php else: ?>
        <!--begin::Şirket Ayarları-->
        <div class="card mb-5 mb-xl-10">
            <!--begin::Card header-->
            <div class="card-header border-0 cursor-pointer">
                <div class="card-title m-0">
                    <h3 class="fw-bold m-0">Şirket Bilgileri</h3>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Content-->
            <div id="kt_account_settings_company">
                <!--begin::Form-->
                <form id="kt_account_company_form" class="form">
                    <!--begin::Card body-->
                    <div class="card-body border-top p-9">
                        
                        <!--begin::Section Title-->
                        <div class="row mb-8">
                            <div class="col-lg-12">
                                <h3 class="fw-bold text-dark mb-1">Temel Bilgiler ve Görseller</h3>
                                <div class="text-muted fs-7">Şirket logo, kaşe, imza ve iletişim bilgilerini yönetin</div>
                            </div>
                        </div>
                        <!--end::Section Title-->
                        
                        <!--begin::Input group - Logo-->
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Şirket Logosu</label>
                            <div class="col-lg-8">
                                <?php
                                $logoUrl = '/lisanstema/demo/assets/media/svg/brand-logos/plurk.svg';
                                if (isset($company) && $company && !empty($company['company_logo'])) {
                                    // Ensure forward slashes and proper encoding
                                    $companyId = (int)$company['id'];
                                    $logoFilename = str_replace('\\', '/', $company['company_logo']);
                                    $logoUrl = '/storage/uploads/companies/' . $companyId . '/' . htmlspecialchars($logoFilename, ENT_QUOTES, 'UTF-8');
                                }
                                // Debug: Echo URL to console
                                if (!empty($company['company_logo'])) {
                                    echo '<script>console.log("Logo URL:", ' . json_encode($logoUrl) . ');</script>';
                                }
                                ?>
                                <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url('/lisanstema/demo/assets/media/svg/files/blank-image.svg')">
                                    <div class="image-input-wrapper w-125px h-125px" style="background-image: url('<?php echo $logoUrl; ?>')"></div>
                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Logo değiştir">
                                        <i class="ki-duotone ki-pencil fs-7">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <input type="file" name="company_logo" accept=".png, .jpg, .jpeg" />
                                        <input type="hidden" name="company_logo_remove" />
                                    </label>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Değişikliği iptal et">
                                        <i class="ki-duotone ki-cross fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Logo sil">
                                        <i class="ki-duotone ki-cross fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                </div>
                                <div class="form-text">İzin verilen dosya türleri: png, jpg, jpeg. Önerilen boyut: 400x400px</div>
                            </div>
                        </div>
                        <!--end::Input group-->
                        
                        <!--begin::Input group - Kaşe-->
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Şirket Kaşesi</label>
                            <div class="col-lg-8">
                                <?php
                                $stampUrl = '/lisanstema/demo/assets/media/svg/files/blank-image.svg';
                                if (isset($company) && $company && !empty($company['company_stamp'])) {
                                    // Ensure forward slashes and proper encoding
                                    $companyId = (int)$company['id'];
                                    $stampFilename = str_replace('\\', '/', $company['company_stamp']);
                                    $stampUrl = '/storage/uploads/companies/' . $companyId . '/' . htmlspecialchars($stampFilename, ENT_QUOTES, 'UTF-8');
                                }
                                if (!empty($company['company_stamp'])) {
                                    echo '<script>console.log("Stamp URL:", ' . json_encode($stampUrl) . ');</script>';
                                }
                                ?>
                                <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url('/lisanstema/demo/assets/media/svg/files/blank-image.svg')">
                                    <div class="image-input-wrapper w-125px h-125px" style="background-image: url('<?php echo $stampUrl; ?>')"></div>
                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Kaşe değiştir">
                                        <i class="ki-duotone ki-pencil fs-7">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <input type="file" name="company_stamp" accept=".png, .jpg, .jpeg" />
                                        <input type="hidden" name="company_stamp_remove" />
                                    </label>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Değişikliği iptal et">
                                        <i class="ki-duotone ki-cross fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Kaşe sil">
                                        <i class="ki-duotone ki-cross fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                </div>
                                <div class="form-text">Fatura ve belgelerde kullanılacak şirket kaşesi. PNG formatı (şeffaf arka plan) önerilir.</div>
                            </div>
                        </div>
                        <!--end::Input group-->
                        
                        <!--begin::Input group - İmza-->
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Yetkili İmzası</label>
                            <div class="col-lg-8">
                                <?php
                                $signatureUrl = '/lisanstema/demo/assets/media/svg/files/blank-image.svg';
                                if (isset($company) && $company && !empty($company['company_signature'])) {
                                    $signatureUrl = '/storage/uploads/companies/' . $company['id'] . '/' . htmlspecialchars($company['company_signature']);
                                }
                                ?>
                                <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url('/lisanstema/demo/assets/media/svg/files/blank-image.svg')">
                                    <div class="image-input-wrapper w-200px h-125px" style="background-image: url('<?php echo $signatureUrl; ?>')"></div>
                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="İmza değiştir">
                                        <i class="ki-duotone ki-pencil fs-7">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <input type="file" name="company_signature" accept=".png, .jpg, .jpeg" />
                                        <input type="hidden" name="company_signature_remove" />
                                    </label>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Değişikliği iptal et">
                                        <i class="ki-duotone ki-cross fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="İmza sil">
                                        <i class="ki-duotone ki-cross fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                </div>
                                <div class="form-text">Fatura ve belgelerde kullanılacak yetkili imzası. PNG formatı (şeffaf arka plan) önerilir.</div>
                            </div>
                        </div>
                        <!--end::Input group-->
                        
                        <!--begin::Separator-->
                        <div class="separator separator-dashed my-10"></div>
                        <!--end::Separator-->
                        
                        <!--begin::Input group - Company Name-->
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Şirket Adı</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="company_name" class="form-control form-control-lg form-control-solid" placeholder="Şirket adı" value="<?= htmlspecialchars($company['name'] ?? '') ?>" />
                                <div class="fv-plugins-message-container invalid-feedback"></div>
                            </div>
                        </div>
                        <!--end::Input group-->
                        
                        <!--begin::Input group - Trade Name-->
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Ticari Ünvan</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="trade_name" class="form-control form-control-lg form-control-solid" placeholder="Ticari ünvan" value="<?= htmlspecialchars($company['trade_name'] ?? '') ?>" />
                            </div>
                        </div>
                        <!--end::Input group-->
                        
                        <!--begin::Input group - Tax Info-->
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Vergi Bilgileri</label>
                            <div class="col-lg-8">
                                <div class="row">
                                    <div class="col-lg-6 fv-row mb-3">
                                        <input type="text" name="tax_office" class="form-control form-control-lg form-control-solid" placeholder="Vergi dairesi" value="<?= htmlspecialchars($company['tax_office'] ?? '') ?>" />
                                    </div>
                                    <div class="col-lg-6 fv-row mb-3">
                                        <input type="text" name="tax_number" class="form-control form-control-lg form-control-solid" placeholder="Vergi no" value="<?= htmlspecialchars($company['tax_number'] ?? '') ?>" />
                                    </div>
                                    <div class="col-lg-12 fv-row">
                                        <input type="text" name="vkn" class="form-control form-control-lg form-control-solid" placeholder="VKN (10 haneli)" value="<?= htmlspecialchars($company['vkn'] ?? '') ?>" maxlength="10" />
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
                        
                        <!--begin::Separator-->
                        <div class="separator separator-dashed my-10"></div>
                        <!--end::Separator-->
                        
                        <!--begin::Section Title-->
                        <div class="row mb-6">
                            <div class="col-lg-12">
                                <h3 class="fw-bold text-dark mb-0">İş Bilgileri</h3>
                                <div class="text-muted fs-7">Şirketinizin iş alanı ve faaliyet bilgilerini yönetin</div>
                            </div>
                        </div>
                        <!--end::Section Title-->
                        
                        <!--begin::Input group - Document Type-->
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                <span class="required">Evrak Türü</span>
                                <span class="ms-1" data-bs-toggle="tooltip" title="Şirketinizin kullandığı ana evrak türü">
                                    <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </span>
                            </label>
                            <div class="col-lg-8 fv-row">
                                <select name="document_type" class="form-select form-select-solid form-select-lg" data-control="select2" data-placeholder="Evrak türü seçin" data-hide-search="true">
                                    <option value="">Seçiniz...</option>
                                    <option value="invoice" <?= ($company['document_type'] ?? '') === 'invoice' ? 'selected' : '' ?>>Fatura</option>
                                    <option value="waybill" <?= ($company['document_type'] ?? '') === 'waybill' ? 'selected' : '' ?>>İrsaliye</option>
                                    <option value="receipt" <?= ($company['document_type'] ?? '') === 'receipt' ? 'selected' : '' ?>>Makbuz</option>
                                    <option value="other" <?= ($company['document_type'] ?? '') === 'other' ? 'selected' : '' ?>>Diğer</option>
                                </select>
                            </div>
                        </div>
                        <!--end::Input group-->
                        
                        <!--begin::Input group - Sector-->
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                <span>Sektör</span>
                                <span class="ms-1" data-bs-toggle="tooltip" title="Şirketinizin faaliyet gösterdiği ana sektör">
                                    <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </span>
                            </label>
                            <div class="col-lg-8 fv-row">
                                <select name="sector" class="form-select form-select-solid form-select-lg" data-control="select2" data-placeholder="Sektör seçin">
                                    <option value="">Seçiniz...</option>
                                    <option value="technology" <?= ($company['sector'] ?? '') === 'technology' ? 'selected' : '' ?>>Teknoloji</option>
                                    <option value="construction" <?= ($company['sector'] ?? '') === 'construction' ? 'selected' : '' ?>>İnşaat</option>
                                    <option value="food" <?= ($company['sector'] ?? '') === 'food' ? 'selected' : '' ?>>Gıda</option>
                                    <option value="textile" <?= ($company['sector'] ?? '') === 'textile' ? 'selected' : '' ?>>Tekstil</option>
                                    <option value="automotive" <?= ($company['sector'] ?? '') === 'automotive' ? 'selected' : '' ?>>Otomotiv</option>
                                    <option value="healthcare" <?= ($company['sector'] ?? '') === 'healthcare' ? 'selected' : '' ?>>Sağlık</option>
                                    <option value="education" <?= ($company['sector'] ?? '') === 'education' ? 'selected' : '' ?>>Eğitim</option>
                                    <option value="retail" <?= ($company['sector'] ?? '') === 'retail' ? 'selected' : '' ?>>Perakende</option>
                                    <option value="finance" <?= ($company['sector'] ?? '') === 'finance' ? 'selected' : '' ?>>Finans</option>
                                    <option value="logistics" <?= ($company['sector'] ?? '') === 'logistics' ? 'selected' : '' ?>>Lojistik</option>
                                    <option value="tourism" <?= ($company['sector'] ?? '') === 'tourism' ? 'selected' : '' ?>>Turizm</option>
                                    <option value="manufacturing" <?= ($company['sector'] ?? '') === 'manufacturing' ? 'selected' : '' ?>>İmalat</option>
                                    <option value="other" <?= ($company['sector'] ?? '') === 'other' ? 'selected' : '' ?>>Diğer</option>
                                </select>
                            </div>
                        </div>
                        <!--end::Input group-->
                        
                        <!--begin::Separator-->
                        <div class="separator separator-dashed my-10"></div>
                        <!--end::Separator-->
                        
                        <!--begin::Section Title-->
                        <div class="row mb-6">
                            <div class="col-lg-12">
                                <h3 class="fw-bold text-dark mb-0">Finansal Bilgiler</h3>
                                <div class="text-muted fs-7">Şirketinizin finansal verilerini kaydedin</div>
                            </div>
                        </div>
                        <!--end::Section Title-->
                        
                        <!--begin::Input group - Annual Revenue-->
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                <span>Yıllık Ciro</span>
                                <span class="ms-1" data-bs-toggle="tooltip" title="Şirketinizin yıllık cirosu (₺)">
                                    <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </span>
                            </label>
                            <div class="col-lg-8 fv-row">
                                <div class="input-group input-group-solid">
                                    <input type="number" name="annual_revenue" class="form-control form-control-lg" placeholder="0.00" step="0.01" min="0" value="<?= htmlspecialchars($company['annual_revenue'] ?? '') ?>" />
                                    <span class="input-group-text">₺</span>
                                </div>
                            </div>
                        </div>
                        <!--end::Input group-->
                        
                        <!--begin::Input group - Employee Count-->
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                <span>Çalışan Sayısı</span>
                                <span class="ms-1" data-bs-toggle="tooltip" title="Toplam çalışan sayınız">
                                    <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </span>
                            </label>
                            <div class="col-lg-8 fv-row">
                                <input type="number" name="employee_count" class="form-control form-control-lg form-control-solid" placeholder="0" min="0" step="1" value="<?= htmlspecialchars($company['employee_count'] ?? '') ?>" />
                            </div>
                        </div>
                        <!--end::Input group-->
                        
                        <!--begin::Input group - Foundation Year-->
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                <span>Kuruluş Yılı</span>
                                <span class="ms-1" data-bs-toggle="tooltip" title="Şirketinizin kuruluş yılı">
                                    <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </span>
                            </label>
                            <div class="col-lg-8 fv-row">
                                <input type="number" name="foundation_year" class="form-control form-control-lg form-control-solid" placeholder="<?= date('Y') ?>" min="1900" max="<?= date('Y') ?>" step="1" value="<?= htmlspecialchars($company['foundation_year'] ?? '') ?>" />
                            </div>
                        </div>
                        <!--end::Input group-->
                        
                        <!--begin::Separator-->
                        <div class="separator separator-dashed my-10"></div>
                        <!--end::Separator-->
                        
                        <!--begin::Section Title-->
                        <div class="row mb-6">
                            <div class="col-lg-12">
                                <h3 class="fw-bold text-dark mb-0">İş Tanımı</h3>
                                <div class="text-muted fs-7">Şirketinizin iş alanı ve faaliyetlerini açıklayın</div>
                            </div>
                        </div>
                        <!--end::Section Title-->
                        
                        <!--begin::Input group - Business Description-->
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                <span>Faaliyet Alanı</span>
                                <span class="ms-1" data-bs-toggle="tooltip" title="Şirketinizin detaylı faaliyet açıklaması">
                                    <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </span>
                            </label>
                            <div class="col-lg-8 fv-row">
                                <textarea name="business_description" class="form-control form-control-lg form-control-solid" rows="5" placeholder="Şirketinizin faaliyet alanını ve iş kapsamını detaylı olarak açıklayın..."><?= htmlspecialchars($company['business_description'] ?? '') ?></textarea>
                                <div class="form-text">Şirketinizin ne yaptığını, hangi ürün/hizmetleri sunduğunu açıklayın</div>
                            </div>
                        </div>
                        <!--end::Input group-->
                        
                    </div>
                    <!--end::Card body-->
                    <!--begin::Actions-->
                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                        <button type="reset" class="btn btn-light btn-active-light-primary me-2">Sıfırla</button>
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
        <!--end::Şirket Ayarları-->
        <?php endif; ?>
        
    </div>
    <!--end::Content container-->
</div>
<!--end::Content-->

<?php if (!empty($user['company_id'])): ?>
<script>
// Image Upload Handler - Monitors file input changes and uploads immediately
document.addEventListener('DOMContentLoaded', function() {
    console.log('Company images script loaded');
    const imageTypes = ['company_logo', 'company_stamp', 'company_signature'];
    
    imageTypes.forEach(function(type) {
        const fileInput = document.querySelector(`input[type="file"][name="${type}"]`);
        const removeInput = document.querySelector(`input[type="hidden"][name="${type}_remove"]`);
        console.log(`Setup for ${type}:`, {
            fileInput: !!fileInput, 
            removeInput: !!removeInput,
            fileInputElement: fileInput
        });
        
        if (fileInput) {
            // Metronic KTImageInput ile uyumlu çalışma
            // Change event'i label içinde gizli olduğu için direkt input'a bağlanıyoruz
            fileInput.addEventListener('change', function(e) {
                console.log(`File input changed for ${type}:`, this.files);
                if (this.files && this.files[0]) {
                    // Küçük bir delay ile Metronic'in UI update'ini bekle
                    setTimeout(function() {
                        uploadCompanyImage(type, fileInput.files[0]);
                    }, 100);
                }
            });
            
            console.log(`Event listener added to ${type} input`);
        } else {
            console.error(`File input not found for ${type}`);
        }
        
        if (removeInput) {
            // Handle remove button click
            const imageInput = removeInput.closest('.image-input');
            if (imageInput) {
                const removeBtn = imageInput.querySelector('[data-kt-image-input-action="remove"]');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function(e) {
                        console.log(`Remove button clicked for ${type}`);
                        // Küçük bir delay ile Metronic'in UI update'ini bekle
                        setTimeout(function() {
                            removeCompanyImage(type);
                        }, 150);
                    });
                }
            }
        }
    });
    
    // Alternatif: Tüm image-input container'ları dinle
    document.querySelectorAll('.image-input[data-kt-image-input="true"]').forEach(function(imageInput) {
        const fileInput = imageInput.querySelector('input[type="file"]');
        if (fileInput) {
            const inputName = fileInput.getAttribute('name');
            console.log(`Found image-input with file input: ${inputName}`);
        }
    });
});

function uploadCompanyImage(type, file) {
    console.log('uploadCompanyImage called:', type, file);
    const formData = new FormData();
    formData.append(type, file);
    
    // Show loading indicator
    Swal.fire({
        text: 'Görsel yükleniyor...',
        icon: 'info',
        buttonsStyling: false,
        showConfirmButton: false,
        allowOutsideClick: false
    });
    
    fetch('/ayarlar/sirket/gorseller', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                text: 'Görsel başarıyla yüklendi',
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
            Swal.fire({
                text: data.message || 'Görsel yüklenemedi',
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

function removeCompanyImage(type) {
    const formData = new FormData();
    formData.append(type + '_remove', '1');
    
    // Show loading indicator
    Swal.fire({
        text: 'Görsel kaldırılıyor...',
        icon: 'info',
        buttonsStyling: false,
        showConfirmButton: false,
        allowOutsideClick: false
    });
    
    fetch('/ayarlar/sirket/gorseller', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                text: 'Görsel başarıyla kaldırıldı',
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
            Swal.fire({
                text: data.message || 'Görsel kaldırılamadı',
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
    
    fetch('/ayarlar/sirket/guncelle', {
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
<?php endif; ?>

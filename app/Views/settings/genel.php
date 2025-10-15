<!--begin::Content-->
<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">
        <!--begin::Navbar-->
        <div class="card mb-5 mb-xl-10">
            <div class="card-body pt-9 pb-0">
                <!--begin::Details-->
                <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                    <!--begin: Avatar-->
                    <div class="me-7 mb-4">
                        <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                            <?php 
                            $avatarUrl = !empty($user['avatar']) 
                                ? '/storage/uploads/avatars/' . htmlspecialchars($user['avatar']) 
                                : '/lisanstema/demo/assets/media/avatars/300-1.jpg';
                            ?>
                            <img src="<?php echo $avatarUrl; ?>" alt="avatar" />
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
                                        <?php echo htmlspecialchars($user['full_name'] ?? 'Kullanıcı'); ?>
                                    </a>
                                </div>
                                <!--begin::Info-->
                                <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                                    <a href="#" class="d-flex align-items-center text-gray-500 text-hover-primary me-5 mb-2">
                                        <i class="ki-duotone ki-profile-circle fs-4 me-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i><?php echo htmlspecialchars($user['email'] ?? ''); ?>
                                    </a>
                                </div>
                                <!--end::Info-->
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
                        <a class="nav-link text-active-primary ms-0 me-10 py-5 active" href="/ayarlar/genel">
                            Genel Bilgiler
                        </a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5" href="/ayarlar/sirket">
                            Şirket Bilgileri
                        </a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5" href="/ayarlar/guvenlik">
                            Güvenlik
                        </a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5" href="/ayarlar/kullanicilar">
                            Kullanıcılar
                        </a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5" href="/ayarlar/kategoriler">
                            Kategoriler
                        </a>
                    </li>
                </ul>
                <!--end::Navs-->
            </div>
        </div>
        <!--end::Navbar-->
        
        <!--begin::Form-->
        <div class="card mb-5 mb-xl-10">
            <!--begin::Card header-->
            <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
                <!--begin::Card title-->
                <div class="card-title m-0">
                    <h3 class="fw-bold m-0">Profil Detayları</h3>
                </div>
                <!--end::Card title-->
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
                            <!--begin::Label-->
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Avatar</label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8">
                                <!--begin::Image input-->
                                <?php 
                                $avatarPreview = !empty($user['avatar']) 
                                    ? '/storage/uploads/avatars/' . htmlspecialchars($user['avatar']) 
                                    : '/lisanstema/demo/assets/media/avatars/300-1.jpg';
                                ?>
                                <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url('/lisanstema/demo/assets/media/avatars/blank.png')">
                                    <!--begin::Preview existing avatar-->
                                    <div class="image-input-wrapper w-125px h-125px" style="background-image: url(<?php echo $avatarPreview; ?>)"></div>
                                    <!--end::Preview existing avatar-->
                                    <!--begin::Label-->
                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Avatar değiştir">
                                        <i class="ki-duotone ki-pencil fs-7">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <!--begin::Inputs-->
                                        <input type="file" name="avatar" accept=".png, .jpg, .jpeg" />
                                        <input type="hidden" name="avatar_remove" />
                                        <!--end::Inputs-->
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Cancel-->
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Avatar iptal">
                                        <i class="ki-duotone ki-cross fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                    <!--end::Cancel-->
                                    <!--begin::Remove-->
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Avatar sil">
                                        <i class="ki-duotone ki-cross fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                    <!--end::Remove-->
                                </div>
                                <!--end::Image input-->
                                <!--begin::Hint-->
                                <div class="form-text">İzin verilen dosya türleri: png, jpg, jpeg.</div>
                                <!--end::Hint-->
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="row mb-6">
                            <!--begin::Label-->
                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Ad Soyad</label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8">
                                <!--begin::Row-->
                                <div class="row">
                                    <!--begin::Col-->
                                    <div class="col-lg-12 fv-row">
                                        <input type="text" name="full_name" class="form-control form-control-lg form-control-solid mb-3 mb-lg-0" placeholder="Ad Soyad" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" />
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <!--end::Row-->
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="row mb-6">
                            <!--begin::Label-->
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                <span class="required">Email</span>
                            </label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8 fv-row">
                                <input type="email" name="email" class="form-control form-control-lg form-control-solid" placeholder="Email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" />
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="row mb-6">
                            <!--begin::Label-->
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                <span class="required">Şirket</span>
                            </label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="company" class="form-control form-control-lg form-control-solid" placeholder="Şirket adı" value="<?php echo htmlspecialchars($company['name'] ?? ''); ?>" readonly />
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="row mb-6">
                            <!--begin::Label-->
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">
                                <span class="required">İletişim Telefonu</span>
                            </label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8 fv-row">
                                <input type="tel" name="phone" class="form-control form-control-lg form-control-solid" placeholder="Telefon numarası" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" />
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="row mb-6">
                            <!--begin::Label-->
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Şirket Web Sitesi</label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8 fv-row">
                                <input type="url" name="website" class="form-control form-control-lg form-control-solid" placeholder="https://sirketim.com" value="<?php echo htmlspecialchars($user['website'] ?? ''); ?>" />
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="row mb-6">
                            <!--begin::Label-->
                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Ülke</label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8 fv-row">
                                <select name="country" class="form-select form-select-solid form-select-lg">
                                    <option value="">Ülke seçin...</option>
                                    <option value="TR" selected>Türkiye</option>
                                    <option value="US">Amerika Birleşik Devletleri</option>
                                    <option value="GB">Birleşik Krallık</option>
                                    <option value="DE">Almanya</option>
                                    <option value="FR">Fransa</option>
                                </select>
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="row mb-6">
                            <!--begin::Label-->
                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Dil</label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8 fv-row">
                                <select name="language" class="form-select form-select-solid form-select-lg">
                                    <option value="">Dil seçin...</option>
                                    <option value="tr" selected>Türkçe</option>
                                    <option value="en">English</option>
                                    <option value="de">Deutsch</option>
                                    <option value="fr">Français</option>
                                </select>
                                <div class="form-text">Tarih, saat ve sayı formatlarını içeren tercih edilen dili seçin.</div>
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="row mb-6">
                            <!--begin::Label-->
                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Saat Dilimi</label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8 fv-row">
                                <select name="timezone" class="form-select form-select-solid form-select-lg">
                                    <option value="">Saat dilimi seçin...</option>
                                    <option value="Europe/Istanbul" selected>İstanbul (GMT+3)</option>
                                    <option value="Europe/London">Londra (GMT+0)</option>
                                    <option value="America/New_York">New York (GMT-5)</option>
                                    <option value="Europe/Berlin">Berlin (GMT+1)</option>
                                </select>
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="row mb-6">
                            <!--begin::Label-->
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Para Birimi</label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8 fv-row">
                                <select name="currency" class="form-select form-select-solid form-select-lg">
                                    <option value="">Para birimi seçin...</option>
                                    <option value="TRY" selected>Türk Lirası (₺)</option>
                                    <option value="USD">Amerikan Doları ($)</option>
                                    <option value="EUR">Euro (€)</option>
                                    <option value="GBP">İngiliz Sterlini (£)</option>
                                </select>
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="row mb-6">
                            <!--begin::Label-->
                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">İletişim</label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8 fv-row">
                                <div class="d-flex align-items-center mt-3">
                                    <label class="form-check form-check-custom form-check-inline form-check-solid me-5">
                                        <input class="form-check-input" name="communication[]" type="checkbox" value="email" checked />
                                        <span class="fw-semibold ps-2 fs-6">Email</span>
                                    </label>
                                    <label class="form-check form-check-custom form-check-inline form-check-solid">
                                        <input class="form-check-input" name="communication[]" type="checkbox" value="phone" checked />
                                        <span class="fw-semibold ps-2 fs-6">Telefon</span>
                                    </label>
                                </div>
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="row mb-6">
                            <!--begin::Label-->
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Pazarlama İzni</label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8 fv-row">
                                <div class="form-check form-check-custom form-check-solid form-switch">
                                    <input class="form-check-input" type="checkbox" name="allow_marketing" value="1" id="allow_marketing" checked />
                                    <label class="form-check-label" for="allow_marketing">
                                        Pazarlama e-postaları ve bildirimleri almak istiyorum
                                    </label>
                                </div>
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->
                    </div>
                    <!--end::Card body-->
                    <!--begin::Actions-->
                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                        <button type="reset" class="btn btn-light btn-active-light-primary me-2">Sıfırla</button>
                        <button type="submit" class="btn btn-primary" id="kt_account_profile_submit">Kaydet</button>
                    </div>
                    <!--end::Actions-->
                </form>
                <!--end::Form-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Form-->
    </div>
    <!--end::Content container-->
</div>
<!--end::Content-->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('kt_account_profile_details_form');
    const submitButton = document.getElementById('kt_account_profile_submit');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Buton durumunu devre dışı bırak
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="indicator-label">Kaydediliyor...</span>';

            // Form verilerini topla
            const formData = new FormData(form);

            // API isteği gönder
            fetch('/profil/guncelle', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        text: 'Profil bilgileriniz başarıyla güncellendi!',
                        icon: 'success',
                        buttonsStyling: false,
                        confirmButtonText: 'Tamam',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    }).then(() => {
                        // Sayfayı yenile (yeni avatar'ı görmek için)
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        text: data.message || 'Bir hata oluştu!',
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
                console.error('Error:', error);
                Swal.fire({
                    text: 'Bir hata oluştu!',
                    icon: 'error',
                    buttonsStyling: false,
                    confirmButtonText: 'Tamam',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
            })
            .finally(() => {
                // Butonu tekrar aktif et
                submitButton.disabled = false;
                submitButton.innerHTML = 'Kaydet';
            });
        });
    }
});
</script>

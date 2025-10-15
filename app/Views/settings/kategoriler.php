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
                        <a class="nav-link text-active-primary ms-0 me-10 py-5 <?= ($activeTab ?? '') === 'guvenlik' ? 'active' : '' ?>" href="/ayarlar/guvenlik">
                            <i class="ki-duotone ki-shield-tick fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Güvenlik
                        </a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5 <?= ($activeTab ?? '') === 'firma' ? 'active' : '' ?>" href="/ayarlar/firma">
                            <i class="ki-duotone ki-briefcase fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Firma Bilgileri
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
                        <a class="nav-link text-active-primary ms-0 me-10 py-5 <?= ($activeTab ?? 'kategoriler') === 'kategoriler' ? 'active' : '' ?>" href="/ayarlar/kategoriler">
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
                                Kategori yönetimi için önce bir şirket oluşturmanız gerekmektedir.
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
        
        <!--begin::Info-->
        <div class="alert alert-dismissible bg-light-info d-flex flex-column flex-sm-row p-5 mb-10">
            <i class="ki-duotone ki-information fs-2hx text-info me-4 mb-5 mb-sm-0">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            <div class="d-flex flex-column pe-0 pe-sm-10">
                <span>Kategorileri sürükleyip bırakarak hiyerarşik olarak düzenleyebilirsiniz. Etiketler ise renk seçimi yapabilirsiniz.</span>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-1 text-info"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
        <!--end::Info-->
        
        <!--begin::Row-->
        <div class="row g-6 g-xl-9 mb-6 mb-xl-9">
            
            <?php foreach ($categoryTypes as $typeKey => $typeName): ?>
            <!--begin::Col-->
            <div class="col-md-6 col-xl-4">
                <!--begin::Card-->
                <div class="card h-100">
                    <!--begin::Card header-->
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1"><?= htmlspecialchars($typeName) ?></span>
                            <span class="text-muted mt-1 fw-semibold fs-7"><?= $categoryCounts[$typeKey] ?? 0 ?> kategori</span>
                        </h3>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-sm btn-icon btn-color-primary btn-active-light-primary" data-bs-toggle="modal" data-bs-target="#modal_add_category" data-category-type="<?= $typeKey ?>">
                                <i class="ki-duotone ki-plus fs-2"></i>
                            </button>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body py-3">
                        <!--begin::Table container-->
                        <div class="table-responsive">
                            <!--begin::Table-->
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                <tbody>
                                    <?php
                                    $filteredCategories = array_filter($categories, function($cat) use ($typeKey) {
                                        return $cat['type'] === $typeKey;
                                    });
                                    ?>
                                    <?php if (empty($filteredCategories)): ?>
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-10">
                                            <i class="ki-duotone ki-information-5 fs-3x mb-3">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                            <div>Henüz kategori eklenmemiş</div>
                                            <button type="button" class="btn btn-sm btn-light-primary mt-3" data-bs-toggle="modal" data-bs-target="#modal_add_category" data-category-type="<?= $typeKey ?>">
                                                YENİ EKLE
                                            </button>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($filteredCategories as $category): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="badge badge-light-primary fw-bold me-3" style="background-color: <?= htmlspecialchars($category['color_bg']) ?>; color: <?= htmlspecialchars($category['color_text']) ?>;">
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-icon btn-sm btn-light btn-active-light-primary me-2" data-bs-toggle="modal" data-bs-target="#modal_edit_category" data-category-id="<?= $category['id'] ?>">
                                                <i class="ki-duotone ki-pencil fs-3">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </button>
                                            <button type="button" class="btn btn-icon btn-sm btn-light btn-active-light-danger" onclick="deleteCategory(<?= $category['id'] ?>)">
                                                <i class="ki-duotone ki-trash fs-3">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    <span class="path4"></span>
                                                    <span class="path5"></span>
                                                </i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <!--end::Table-->
                        </div>
                        <!--end::Table container-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Col-->
            <?php endforeach; ?>
            
        </div>
        <!--end::Row-->
        
        <?php endif; ?>
        
    </div>
    <!--end::Content container-->
</div>
<!--end::Content-->

<!--begin::Modal - Add Category-->
<div class="modal fade" id="modal_add_category" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header" id="modal_add_category_header">
                <h2 class="fw-bold">Yeni Kategori Ekle</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="form_add_category" class="form">
                    <input type="hidden" name="type" id="add_category_type" />
                    
                    <div class="mb-10">
                        <label class="required form-label">Kategori Adı</label>
                        <input type="text" class="form-control form-control-solid" name="name" placeholder="Kategori adı" />
                    </div>
                    
                    <div class="mb-10">
                        <label class="form-label">Açıklama</label>
                        <textarea class="form-control form-control-solid" name="description" rows="3" placeholder="İsteğe bağlı açıklama"></textarea>
                    </div>
                    
                    <div class="row mb-10">
                        <div class="col-md-6">
                            <label class="form-label">Zemin Rengi</label>
                            <input type="color" class="form-control form-control-solid" name="color_bg" value="#3B82F6" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Yazı Rengi</label>
                            <input type="color" class="form-control form-control-solid" name="color_text" value="#FFFFFF" />
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Kaydet</span>
                            <span class="indicator-progress">Lütfen bekleyin...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end::Modal-->

<script>
// Add category modal opened
document.querySelectorAll('[data-bs-target="#modal_add_category"]').forEach(btn => {
    btn.addEventListener('click', function() {
        const categoryType = this.getAttribute('data-category-type');
        document.getElementById('add_category_type').value = categoryType;
    });
});

// Add category form submit
document.getElementById('form_add_category')?.addEventListener('submit', function(e) {
    e.preventDefault();
    // AJAX implementation here
    console.log('Add category form submitted');
});

// Delete category
function deleteCategory(id) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu kategoriyi silmek istediğinize emin misiniz?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Evet, Sil',
        cancelButtonText: 'İptal',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // AJAX delete implementation here
            console.log('Delete category: ' + id);
        }
    });
}
</script>

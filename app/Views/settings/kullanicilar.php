<!--begin::Content-->
<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">
        
        <!--begin::Navbar-->
        <div class="card mb-5 mb-xl-10">
            <div class="card-body pt-9 pb-0">
                <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                    <div class="flex-grow-1">
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
                    </div>
                </div>
                
                <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5" href="/ayarlar/genel">
                            <i class="ki-duotone ki-user fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                            Genel
                        </a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5" href="/ayarlar/sirket">
                            <i class="ki-duotone ki-office-bag fs-2 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                            Şirket
                        </a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5" href="/ayarlar/guvenlik">
                            <i class="ki-duotone ki-shield-tick fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                            Güvenlik
                        </a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5" href="/ayarlar/firma">
                            <i class="ki-duotone ki-briefcase fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                            Firma Bilgileri
                        </a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5 active" href="/ayarlar/kullanicilar">
                            <i class="ki-duotone ki-people fs-2 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                            Kullanıcılar
                        </a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5" href="/ayarlar/kategoriler">
                            <i class="ki-duotone ki-element-11 fs-2 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                            Kategoriler
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <!--end::Navbar-->
        
        <?php if (empty($user['company_id'])): ?>
        <div class="card">
            <div class="card-body p-9">
                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                    <i class="ki-duotone ki-information fs-2tx text-warning me-4">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">Henüz bir şirket tanımlanmamış</h4>
                            <div class="fs-6 text-gray-700 mb-5">
                                Kullanıcı yönetimi için önce bir şirket oluşturmanız gerekmektedir.
                            </div>
                            <a href="/sirket/olustur" class="btn btn-warning">
                                <i class="ki-duotone ki-plus fs-2"><span class="path1"></span><span class="path2"></span></i>
                                Şirket Oluştur
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        
        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <h3>Kullanıcılar Yönetimi</h3>
                </div>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_add_user">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        Kullanıcı Ekle
                    </button>
                </div>
            </div>
            
            <div class="card-body py-4">
                <div class="alert alert-info mb-10">
                    <i class="ki-duotone ki-information fs-2hx text-info me-4">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    <strong>Kullanıcı Yönetimi modülü yakında eklenecektir.</strong><br>
                    Bu sayfada kullanıcı ekleme, düzenleme, yetkilendirme ve kısıtlama özellikleri yer alacaktır.
                </div>
                
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-125px">Kullanıcı</th>
                                <th class="min-w-125px">E-posta</th>
                                <th class="min-w-125px">Rol</th>
                                <th class="min-w-125px">Durum</th>
                                <th class="text-end min-w-100px">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                                <div class="symbol-label">
                                                    <div class="symbol-label fs-3 bg-light-primary text-primary">
                                                        <?= strtoupper(substr($u['full_name'] ?? 'U', 0, 1)) ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 mb-1"><?= htmlspecialchars($u['full_name'] ?? 'İsimsiz') ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <span class="badge badge-light-<?= $u['role'] === 'admin' ? 'danger' : 'primary' ?>">
                                            <?= ucfirst($u['role'] ?? 'user') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($u['is_active']): ?>
                                            <span class="badge badge-light-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge badge-light-danger">Pasif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-icon btn-sm btn-light btn-active-light-primary me-2" disabled>
                                            <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                                        </button>
                                        <button class="btn btn-icon btn-sm btn-light btn-active-light-danger" disabled>
                                            <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-10">Henüz kullanıcı bulunmamaktadır.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <?php endif; ?>
        
    </div>
</div>

<!--begin::Modal - Add User-->
<div class="modal fade" id="modal_add_user" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Yeni Kullanıcı Ekle</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <div class="alert alert-warning">
                    Bu özellik yakında eklenecektir.
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Modal-->

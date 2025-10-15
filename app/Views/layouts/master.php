<!DOCTYPE html>
<html lang="tr">
<!--begin::Head-->
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Ön Muhasebe' ?></title>
    <meta name="description" content="Ön Muhasebe Yönetim Sistemi" />
    
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <!--end::Fonts-->
    
    <!--begin::Global Stylesheets Bundle-->
    <link href="/lisanstema/demo/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/lisanstema/demo/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <!--end::Global Stylesheets Bundle-->
    
    <?php if (isset($additionalCSS)): ?>
        <?= $additionalCSS ?>
    <?php endif; ?>
</head>
<!--end::Head-->

<!--begin::Body-->
<body id="kt_app_body" 
      data-kt-app-layout="dark-sidebar" 
      data-kt-app-header-fixed="true" 
      data-kt-app-sidebar-enabled="true" 
      data-kt-app-sidebar-fixed="true" 
      data-kt-app-sidebar-hoverable="true" 
      data-kt-app-sidebar-push-header="true" 
      data-kt-app-sidebar-push-toolbar="true" 
      data-kt-app-sidebar-push-footer="true" 
      data-kt-app-toolbar-enabled="true" 
      class="app-default">
    
    <!--begin::App-->
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <!--begin::Page-->
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            
            <!--begin::Header-->
            <?php include __DIR__ . '/partials/header.php'; ?>
            <!--end::Header-->
            
            <!--begin::Wrapper-->
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                
                <!--begin::Sidebar-->
                <?php include __DIR__ . '/partials/sidebar.php'; ?>
                <!--end::Sidebar-->
                
                <!--begin::Main-->
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    
                    <!--begin::Content wrapper-->
                    <div class="d-flex flex-column flex-column-fluid">
                        
                        <!--begin::Toolbar-->
                        <?php if (!isset($hideToolbar) || !$hideToolbar): ?>
                        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                                
                                <!--begin::Page title-->
                                <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                                    <h1 class="d-flex text-dark fw-bold fs-3 align-items-center my-1">
                                        <?php if (isset($pageIcon)): ?>
                                        <i class="<?= $pageIcon ?> fs-2 text-primary me-2"></i>
                                        <?php endif; ?>
                                        <?= $pageTitle ?? 'Sayfa' ?>
                                    </h1>
                                    <?php if (isset($breadcrumbs) && count($breadcrumbs) > 0): ?>
                                    <span class="h-20px border-gray-300 border-start mx-4"></span>
                                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
                                        <?php foreach ($breadcrumbs as $index => $crumb): ?>
                                        <li class="breadcrumb-item <?= $index === count($breadcrumbs) - 1 ? 'text-muted' : '' ?>">
                                            <?php if (isset($crumb['url']) && $index !== count($breadcrumbs) - 1): ?>
                                            <a href="<?= $crumb['url'] ?>" class="text-muted text-hover-primary">
                                                <?= $crumb['text'] ?>
                                            </a>
                                            <?php else: ?>
                                            <?= $crumb['text'] ?>
                                            <?php endif; ?>
                                        </li>
                                        <?php if ($index < count($breadcrumbs) - 1): ?>
                                        <li class="breadcrumb-item">
                                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                                        </li>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                </div>
                                <!--end::Page title-->
                                
                                <!--begin::Actions-->
                                <?php if (isset($toolbarActions)): ?>
                                <div class="d-flex align-items-center gap-2 gap-lg-3">
                                    <?= $toolbarActions ?>
                                </div>
                                <?php endif; ?>
                                <!--end::Actions-->
                                
                            </div>
                        </div>
                        <!--end::Toolbar-->
                        
                        <?php endif; ?>
                        
                        <!--begin::Content-->
                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <!--begin::Content container-->
                            <div id="kt_app_content_container" class="app-container container-fluid">
                                
                                <?php 
                                // Main content burada render edilir
                                if (isset($contentFile)) {
                                    require_once $contentFile;
                                } elseif (isset($content)) {
                                    echo $content;
                                }
                                ?>
                                
                            </div>
                            <!--end::Content container-->
                        </div>
                        <!--end::Content-->
                        
                    </div>
                    <!--end::Content wrapper-->
                    
                    <!--begin::Footer-->
                    <?php include __DIR__ . '/partials/footer.php'; ?>
                    <!--end::Footer-->
                    
                </div>
                <!--end:::Main-->
                
            </div>
            <!--end::Wrapper-->
            
        </div>
        <!--end::Page-->
    </div>
    <!--end::App-->
    
    <!--begin::Global Javascript Bundle-->
    <script src="/lisanstema/demo/assets/plugins/global/plugins.bundle.js"></script>
    <script src="/lisanstema/demo/assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->
    
    <?php if (isset($additionalJS)): ?>
        <?= $additionalJS ?>
    <?php endif; ?>
    
</body>
<!--end::Body-->
</html>

<!DOCTYPE html>
<html lang="tr">
<!--begin::Head-->
<head>
    <meta charset="utf-8" />
    <title><?= $title ?? 'Ön Muhasebe Sistemi' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="/lisanstema/demo/assets/media/logos/favicon.ico" />
    
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <!--end::Fonts-->
    
    <!--begin::Global Stylesheets Bundle-->
    <link href="/lisanstema/demo/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/lisanstema/demo/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <!--end::Global Stylesheets Bundle-->
    
    <?= $additionalStyles ?? '' ?>
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
      
    <!--begin::Theme mode setup-->
    <script>
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>
    <!--end::Theme mode setup-->
    
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
                        <?php if (isset($showToolbar) && $showToolbar): ?>
                        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                                <!--begin::Page title-->
                                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                                        <?= $pageTitle ?? 'Dashboard' ?>
                                    </h1>
                                    <?php if (isset($breadcrumbs)): ?>
                                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                                        <?php foreach ($breadcrumbs as $index => $crumb): ?>
                                        <li class="breadcrumb-item <?= $index === array_key_last($breadcrumbs) ? 'text-muted' : '' ?>">
                                            <?php if (isset($crumb['url']) && $index !== array_key_last($breadcrumbs)): ?>
                                            <a href="<?= $crumb['url'] ?>" class="text-muted text-hover-primary"><?= $crumb['title'] ?></a>
                                            <?php else: ?>
                                            <?= $crumb['title'] ?>
                                            <?php endif; ?>
                                        </li>
                                        <?php if ($index !== array_key_last($breadcrumbs)): ?>
                                        <li class="breadcrumb-item">
                                            <span class="bullet bg-gray-500 w-5px h-2px"></span>
                                        </li>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                </div>
                                <!--end::Page title-->
                                
                                <!--begin::Actions-->
                                <?= $toolbarActions ?? '' ?>
                                <!--end::Actions-->
                            </div>
                        </div>
                        <?php endif; ?>
                        <!--end::Toolbar-->
                        
                        <!--begin::Content-->
                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <div id="kt_app_content_container" class="app-container container-xxl">
                                <?= $content ?>
                            </div>
                        </div>
                        <!--end::Content-->
                        
                    </div>
                    <!--end::Content wrapper-->
                    
                    <!--begin::Footer-->
                    <?php include __DIR__ . '/partials/footer.php'; ?>
                    <!--end::Footer-->
                    
                </div>
                <!--end::Main-->
                
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
    
    <?= $additionalScripts ?? '' ?>
    
</body>
<!--end::Body-->
</html>

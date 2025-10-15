<!DOCTYPE html>
<html lang="tr">
<!--begin::Head-->
<head>
    <meta charset="utf-8" />
    <title><?= $title ?? 'Giriş Yap - Ön Muhasebe Sistemi' ?></title>
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
<body id="kt_body" class="app-blank">
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
    
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <?= $content ?>
    </div>
    <!--end::Root-->
    
    <!--begin::Global Javascript Bundle-->
    <script src="/lisanstema/demo/assets/plugins/global/plugins.bundle.js"></script>
    <script src="/lisanstema/demo/assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->
    
    <?= $additionalScripts ?? '' ?>
    
</body>
<!--end::Body-->
</html>

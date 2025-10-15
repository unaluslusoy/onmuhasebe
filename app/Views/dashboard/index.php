<?php
/**
 * Dashboard Ana Sayfa
 * Master layout kullanarak render edilir
 */

// Sayfa değişkenleri
$pageTitle = 'Dashboard';
$pageIcon = 'ki-duotone ki-element-11';
$breadcrumbs = [
    ['text' => 'Ana Sayfa', 'url' => '/'],
    ['text' => 'Dashboard']
];

// Toolbar aksiyonları
ob_start();
?>
<div class="m-0">
    <a href="#" class="btn btn-sm btn-flex btn-info" data-bs-toggle="modal" data-bs-target="#kt_modal_create_app">
        <i class="ki-duotone ki-plus fs-2"></i>
        Hızlı İşlem
    </a>
</div>
<?php
$toolbarActions = ob_get_clean();

// Ek JavaScript
ob_start();
?>
<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<!-- Dashboard JS -->
<script src="/assets/js/pages/dashboard.js"></script>
<?php
$additionalJS = ob_get_clean();

// İçerik dosyası
$contentFile = __DIR__ . '/dashboard-content.php';

// Master layout'u include et
require_once __DIR__ . '/../layouts/master.php';
?>

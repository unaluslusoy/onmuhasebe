/**
 * Dashboard - Store Analytics Style
 * ApexCharts ile modern dashboard widget'ları
 */

"use strict";

// Class definition
var KTDashboard = function() {
    // Demo Data
    const demoData = {
        sales: {
            total: 156750.50,
            change: 12.5,
            chartData: [44, 55, 41, 37, 22, 43, 21, 35, 56, 28, 45, 62] // Son 12 gün
        },
        purchases: {
            total: 89420.25,
            change: -3.8,
            chartData: [22, 28, 35, 21, 43, 22, 37, 41, 28, 45, 32, 38]
        },
        invoices: {
            count: 248,
            change: 8.2,
            goal: 300,
            goalPercentage: 83
        },
        customers: {
            newCount: 47,
            topCustomers: [
                { name: 'ABC Ltd. Şti.', initial: 'A', color: 'warning' },
                { name: 'XYZ A.Ş.', initial: 'X', color: 'primary' },
                { name: 'Deneme Tic.', initial: 'D', color: 'success' },
                { name: 'Test Firma', initial: 'T', color: 'danger' },
                { name: 'Örnek Ltd.', initial: 'Ö', color: 'info' }
            ]
        },
        monthlySales: {
            categories: ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'],
            sales: [65000, 75000, 85000, 72000, 95000, 110000, 125000, 98000, 115000, 135000, 145000, 156000],
            purchases: [45000, 52000, 58000, 48000, 65000, 72000, 85000, 68000, 78000, 88000, 92000, 89000]
        },
        paymentStatus: {
            paid: 145,
            partial: 38,
            unpaid: 42,
            overdue: 23
        },
        categories: {
            names: ['Elektronik', 'Gıda', 'Tekstil', 'Mobilya', 'Diğer'],
            values: [35, 25, 20, 15, 5]
        },
        topProducts: [
            { rank: 1, name: 'Laptop Dell XPS 15', quantity: 45 },
            { rank: 2, name: 'iPhone 15 Pro', quantity: 38 },
            { rank: 3, name: 'Samsung Galaxy S24', quantity: 32 },
            { rank: 4, name: 'Sony WH-1000XM5', quantity: 28 },
            { rank: 5, name: 'MacBook Air M3', quantity: 24 }
        ],
        unpaidInvoices: [
            { customer: 'ABC Tic. Ltd. Şti.', amount: 45250.00, days: 12, type: 'search' },
            { customer: 'XYZ İnşaat A.Ş.', amount: 128340.50, days: 8, type: 'tiktok' },
            { customer: 'Deneme Pazarlama', amount: 35180.25, days: 5, type: 'sms' },
            { customer: 'Test Lojistik', amount: 62450.00, days: 15, type: 'briefcase' },
            { customer: 'Örnek Teknoloji', amount: 18920.75, days: 3, type: 'colors-square' }
        ],
        overdueInvoices: [
            { customer: 'Geciken Firma 1', amount: 85300.00, days: 45, type: 'magnifier' },
            { customer: 'Geciken Firma 2', amount: 125800.50, days: 38, type: 'tiktok' },
            { customer: 'Geciken Firma 3', amount: 42150.25, days: 62, type: 'sms' },
            { customer: 'Geciken Firma 4', amount: 98760.00, days: 28, type: 'picture' }
        ]
    };

    // Türkçe sayı formatı
    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('tr-TR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    };

    // Widget güncelleyiciler
    const updateStatWidgets = () => {
        console.log('updateStatWidgets called');

        // Toplam Satış
        const totalSalesEl = document.getElementById('total-sales');
        console.log('total-sales element:', totalSalesEl);
        if (totalSalesEl) {
            totalSalesEl.textContent = formatCurrency(demoData.sales.total);
        }

        const salesChangeEl = document.getElementById('sales-change');
        if (salesChangeEl) {
            salesChangeEl.textContent = demoData.sales.change.toFixed(1) + '%';
        }

        // Toplam Alış
        const totalPurchasesEl = document.getElementById('total-purchases');
        console.log('total-purchases element:', totalPurchasesEl);
        if (totalPurchasesEl) {
            totalPurchasesEl.textContent = formatCurrency(demoData.purchases.total);
        }

        const purchaseChangeEl = document.getElementById('purchase-change');
        if (purchaseChangeEl) {
            purchaseChangeEl.textContent = Math.abs(demoData.purchases.change).toFixed(1) + '%';
        }

        // Fatura Sayısı
        const invoiceCountEl = document.getElementById('invoice-count');
        console.log('invoice-count element:', invoiceCountEl);
        if (invoiceCountEl) {
            invoiceCountEl.textContent = demoData.invoices.count;
        }

        const invoiceChangeEl = document.getElementById('invoice-change');
        if (invoiceChangeEl) {
            invoiceChangeEl.textContent = demoData.invoices.change.toFixed(1) + '%';
        }

        const goalPercentageEl = document.getElementById('goal-percentage');
        if (goalPercentageEl) {
            goalPercentageEl.textContent = demoData.invoices.goalPercentage;
        }

        const goalProgressEl = document.getElementById('goal-progress');
        if (goalProgressEl) {
            goalProgressEl.style.width = demoData.invoices.goalPercentage + '%';
            goalProgressEl.setAttribute('aria-valuenow', demoData.invoices.goalPercentage);
        }

        // Yeni Müşteriler
        const newCustomersEl = document.getElementById('new-customers');
        console.log('new-customers element:', newCustomersEl);
        if (newCustomersEl) {
            newCustomersEl.textContent = demoData.customers.newCount;
        }
    };

    // Mini chart'lar (satış ve alış widget'larında)
    const initMiniCharts = () => {
        // Satış Mini Chart
        const salesChartEl = document.querySelector("#chart_sales_widget");
        if (salesChartEl) {
            const salesOptions = {
                series: [{
                    name: 'Satış',
                    data: demoData.sales.chartData
                }],
                chart: {
                    type: 'area',
                    height: 125,
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    sparkline: { enabled: true }
                },
                plotOptions: {},
                legend: { show: false },
                dataLabels: { enabled: false },
                fill: {
                    type: 'solid',
                    opacity: 1
                },
                stroke: {
                    curve: 'smooth',
                    show: true,
                    width: 3,
                    colors: ['#50cd89']
                },
                xaxis: {
                    categories: ['', '', '', '', '', '', '', '', '', '', '', ''],
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { show: false },
                    crosshairs: { show: false },
                    tooltip: { enabled: false }
                },
                yaxis: {
                    min: 0,
                    max: 80,
                    labels: { show: false }
                },
                states: {
                    normal: { filter: { type: 'none', value: 0 } },
                    hover: { filter: { type: 'none', value: 0 } },
                    active: { allowMultipleDataPointsSelection: false, filter: { type: 'none', value: 0 } }
                },
                tooltip: {
                    style: { fontSize: '12px' },
                    y: {
                        formatter: function(val) {
                            return "₺" + formatCurrency(val * 1000);
                        }
                    }
                },
                colors: ['#50cd89'],
                markers: {
                    colors: ['#50cd89'],
                    strokeColors: ['#50cd89'],
                    strokeWidth: 3
                }
            };
            const salesChart = new ApexCharts(salesChartEl, salesOptions);
            salesChart.render();
        }

        // Alış Mini Chart
        const purchasesChartEl = document.querySelector("#chart_purchases_widget");
        if (purchasesChartEl) {
            const purchasesOptions = {
                series: [{
                    name: 'Alış',
                    data: demoData.purchases.chartData
                }],
                chart: {
                    type: 'area',
                    height: 125,
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    sparkline: { enabled: true }
                },
                plotOptions: {},
                legend: { show: false },
                dataLabels: { enabled: false },
                fill: {
                    type: 'solid',
                    opacity: 1
                },
                stroke: {
                    curve: 'smooth',
                    show: true,
                    width: 3,
                    colors: ['#f1416c']
                },
                xaxis: {
                    categories: ['', '', '', '', '', '', '', '', '', '', '', ''],
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { show: false },
                    crosshairs: { show: false },
                    tooltip: { enabled: false }
                },
                yaxis: {
                    min: 0,
                    max: 80,
                    labels: { show: false }
                },
                states: {
                    normal: { filter: { type: 'none', value: 0 } },
                    hover: { filter: { type: 'none', value: 0 } },
                    active: { allowMultipleDataPointsSelection: false, filter: { type: 'none', value: 0 } }
                },
                tooltip: {
                    style: { fontSize: '12px' },
                    y: {
                        formatter: function(val) {
                            return "₺" + formatCurrency(val * 1000);
                        }
                    }
                },
                colors: ['#f1416c'],
                markers: {
                    colors: ['#f1416c'],
                    strokeColors: ['#f1416c'],
                    strokeWidth: 3
                }
            };
            const purchasesChart = new ApexCharts(purchasesChartEl, purchasesOptions);
            purchasesChart.render();
        }
    };

    // Aylık Satış Trend Chart (Büyük)
    const initMonthlySalesChart = () => {
        const chartEl = document.querySelector("#chart_monthly_sales");
        if (!chartEl) return;

        const options = {
            series: [{
                    name: 'Satış',
                    data: demoData.monthlySales.sales
                },
                {
                    name: 'Alış',
                    data: demoData.monthlySales.purchases
                }
            ],
            chart: {
                type: 'area',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {},
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'left'
            },
            dataLabels: {
                enabled: false
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1,
                    stops: [0, 90, 100]
                }
            },
            stroke: {
                curve: 'smooth',
                show: true,
                width: 3
            },
            xaxis: {
                categories: demoData.monthlySales.categories,
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: '#A1A5B7',
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return "₺" + (val / 1000).toFixed(0) + "K";
                    },
                    style: {
                        colors: '#A1A5B7',
                        fontSize: '12px'
                    }
                }
            },
            states: {
                normal: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                hover: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                active: {
                    allowMultipleDataPointsSelection: false,
                    filter: {
                        type: 'none',
                        value: 0
                    }
                }
            },
            tooltip: {
                style: {
                    fontSize: '12px'
                },
                y: {
                    formatter: function(val) {
                        return "₺" + formatCurrency(val);
                    }
                }
            },
            colors: ['#3E97FF', '#F1416C'],
            grid: {
                borderColor: '#E4E6EF',
                strokeDashArray: 4,
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            markers: {
                strokeColors: ['#3E97FF', '#F1416C'],
                strokeWidth: 3
            }
        };

        const chart = new ApexCharts(chartEl, options);
        chart.render();
    };

    // Ödeme Durumu Donut Chart
    const initPaymentStatusChart = () => {
        const chartEl = document.querySelector("#chart_payment_status");
        if (!chartEl) return;

        const total = demoData.paymentStatus.paid + demoData.paymentStatus.partial +
            demoData.paymentStatus.unpaid + demoData.paymentStatus.overdue;

        const options = {
            series: [
                demoData.paymentStatus.paid,
                demoData.paymentStatus.partial,
                demoData.paymentStatus.unpaid,
                demoData.paymentStatus.overdue
            ],
            chart: {
                type: 'donut',
                height: 250
            },
            labels: ['Ödendi', 'Kısmi Ödendi', 'Ödenmedi', 'Vadesi Geçti'],
            colors: ['#50CD89', '#3E97FF', '#FFC700', '#F1416C'],
            legend: {
                show: true,
                position: 'bottom'
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                fontSize: '14px',
                                fontWeight: 600
                            },
                            value: {
                                show: true,
                                fontSize: '22px',
                                fontWeight: 700,
                                formatter: function(val) {
                                    return val;
                                }
                            },
                            total: {
                                show: true,
                                label: 'Toplam',
                                formatter: function() {
                                    return total;
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: {
                enabled: false
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " Adet (" + ((val / total) * 100).toFixed(1) + "%)";
                    }
                }
            }
        };

        const chart = new ApexCharts(chartEl, options);
        chart.render();
    };

    // Kategori Dağılımı Donut Chart
    const initCategoryChart = () => {
        const chartEl = document.querySelector("#chart_category_distribution");
        if (!chartEl) return;

        const options = {
            series: demoData.categories.values,
            chart: {
                type: 'donut',
                height: 250
            },
            labels: demoData.categories.names,
            colors: ['#3E97FF', '#50CD89', '#F1416C', '#FFC700', '#7239EA'],
            legend: {
                show: true,
                position: 'bottom'
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                fontSize: '14px',
                                fontWeight: 600
                            },
                            value: {
                                show: true,
                                fontSize: '22px',
                                fontWeight: 700,
                                formatter: function(val) {
                                    return val + "%";
                                }
                            },
                            total: {
                                show: true,
                                label: 'Toplam',
                                formatter: function() {
                                    return "100%";
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: {
                enabled: false
            }
        };

        const chart = new ApexCharts(chartEl, options);
        chart.render();
    };

    // En Çok Satan Ürünler Tablosu
    const initTopProductsTable = () => {
        const tbody = document.querySelector("#top-products-table tbody");
        if (!tbody) return;

        tbody.innerHTML = '';
        demoData.topProducts.forEach(product => {
            const row = `
                <tr>
                    <td><span class="badge badge-light-primary fs-7 fw-bold">#${product.rank}</span></td>
                    <td class="ps-0">
                        <a href="#" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">${product.name}</a>
                    </td>
                    <td class="text-end">
                        <span class="text-gray-800 fw-bold d-block fs-6">${product.quantity} Adet</span>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
    };

    // Ödenmemiş Faturalar Listesi
    const initUnpaidInvoicesList = () => {
        const container = document.getElementById('unpaid-invoices-list');
        if (!container) return;

        let total = demoData.unpaidInvoices.reduce((sum, inv) => sum + inv.amount, 0);
        document.getElementById('unpaid-amount').textContent = '₺' + formatCurrency(total);
        document.getElementById('unpaid-count').textContent = demoData.unpaidInvoices.length;

        container.innerHTML = '';
        demoData.unpaidInvoices.forEach((invoice, index) => {
            const html = `
                <div class="d-flex flex-stack ${index < demoData.unpaidInvoices.length - 1 ? 'mb-3' : ''}">
                    <div class="d-flex align-items-center me-5">
                        <div class="symbol symbol-30px me-5">
                            <span class="symbol-label">
                                <i class="ki-duotone ki-${invoice.type} fs-3 text-gray-600">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div class="me-5">
                            <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6">${invoice.customer}</a>
                            <span class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0">${invoice.days} gün önce</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="text-gray-800 fw-bold fs-6 me-3">₺${formatCurrency(invoice.amount)}</span>
                    </div>
                </div>
                ${index < demoData.unpaidInvoices.length - 1 ? '<div class="separator separator-dashed my-3"></div>' : ''}
            `;
            container.innerHTML += html;
        });
    };

    // Vadesi Geçmiş Faturalar Listesi
    const initOverdueInvoicesList = () => {
        const container = document.getElementById('overdue-invoices-list');
        if (!container) return;

        let total = demoData.overdueInvoices.reduce((sum, inv) => sum + inv.amount, 0);
        document.getElementById('overdue-amount').textContent = '₺' + formatCurrency(total);
        document.getElementById('overdue-count').textContent = demoData.overdueInvoices.length;

        container.innerHTML = '';
        demoData.overdueInvoices.forEach((invoice, index) => {
            const html = `
                <div class="d-flex flex-stack ${index < demoData.overdueInvoices.length - 1 ? 'mb-3' : ''}">
                    <div class="d-flex align-items-center me-5">
                        <div class="symbol symbol-30px me-5">
                            <span class="symbol-label">
                                <i class="ki-duotone ki-${invoice.type} fs-3 text-danger">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div class="me-5">
                            <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6">${invoice.customer}</a>
                            <span class="text-danger fw-semibold fs-7 d-block text-start ps-0">${invoice.days} gün gecikmiş!</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="text-gray-800 fw-bold fs-6 me-3">₺${formatCurrency(invoice.amount)}</span>
                        <span class="badge badge-light-danger fs-base">
                            <i class="ki-duotone ki-cross-circle fs-5 text-danger ms-n1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </span>
                    </div>
                </div>
                ${index < demoData.overdueInvoices.length - 1 ? '<div class="separator separator-dashed my-3"></div>' : ''}
            `;
            container.innerHTML += html;
        });
    };

    // Initialization tracking
    let isInitialized = false;

    // Public methods
    return {
        init: function() {
            // Prevent double initialization
            if (isInitialized) {
                console.log('Dashboard already initialized, skipping...');
                return;
            }

            console.log('Dashboard initializing with Store Analytics design...');
            isInitialized = true;

            // Widget'ları güncelle
            updateStatWidgets();

            // Chart'ları initialize et
            initMiniCharts();
            initMonthlySalesChart();
            initPaymentStatusChart();
            initCategoryChart();

            // Tabloları doldur
            initTopProductsTable();
            initUnpaidInvoicesList();
            initOverdueInvoicesList();

            console.log('Dashboard initialized successfully!');
        }
    };
}();

// Single initialization point - Metronic 8 compatible
(function() {
    // Wait for DOM and KTApp to be ready
    const initDashboard = function() {
        // Ensure we only init once
        if (document.querySelector('#chart_sales_widget canvas')) {
            console.log('Dashboard already rendered, skipping init...');
            return;
        }
        KTDashboard.init();
    };

    // Primary initialization: When DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboard);
    } else {
        // DOM already loaded
        initDashboard();
    }
})();
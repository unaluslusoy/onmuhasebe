/**
 * Invoice List Page - JavaScript
 * Handles DataTable, filters, statistics, and actions
 */

"use strict";

// Global variables
let invoicesTable;
let currentFilters = {
    type: '',
    status: '',
    search: ''
};

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    initializeDataTable();
    initializeFilters();
    initializeSearch();
    loadStatistics();
    initializeExport();
});

/**
 * Initialize DataTables
 */
function initializeDataTable() {
    invoicesTable = $('#invoices-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/api/invoices',
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + getAccessToken()
                    },
                    data: function(d) {
                        // Map DataTables parameters to our API
                        return {
                            page: Math.floor(d.start / d.length) + 1,
                            per_page: d.length,
                            invoice_type: currentFilters.type,
                            payment_status: currentFilters.status,
                            search: currentFilters.search
                        };
                    },
                    dataSrc: function(json) {
                        // Map our API response to DataTables format
                        return json.data || [];
                    },
                    error: function(xhr, error, code) {
                        console.error('DataTable error:', error);
                        showNotification('Faturalar yüklenirken hata oluştu', 'error');

                        // Check for authentication error
                        if (xhr.status === 401) {
                            window.location.href = '/giris';
                        }
                    }
                },
                columns: [{
                            data: 'invoice_number',
                            render: function(data, type, row) {
                                return `<div class="fw-bold text-gray-800">${data || 'N/A'}</div>
                            <div class="fs-7 text-muted">${getInvoiceTypeLabel(row.invoice_type)}</div>`;
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                    const customer = row.customer_name || row.cari_title || 'N/A';
                                    const code = row.customer_code || row.cari_code || '';
                                    return `<div class="fw-semibold">${customer}</div>
                            ${code ? `<div class="fs-7 text-muted">${code}</div>` : ''}`;
                }
            },
            {
                data: 'invoice_date',
                render: function(data) {
                    return formatDate(data);
                }
            },
            {
                data: 'due_date',
                render: function(data, type, row) {
                    const formatted = formatDate(data);
                    const isOverdue = new Date(data) < new Date() && row.payment_status !== 'paid';
                    return isOverdue ? 
                        `<span class="text-danger fw-bold">${formatted}</span>` : 
                        formatted;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    const total = parseFloat(row.total_amount || 0);
                    const currency = row.currency || 'TRY';
                    return `<div class="fw-bold">${formatCurrency(total, currency)}</div>
                            ${row.remaining_amount > 0 ? 
                                `<div class="fs-7 text-muted">Kalan: ${formatCurrency(row.remaining_amount, currency)}</div>` 
                                : ''}`;
                }
            },
            {
                data: 'payment_status',
                render: function(data, type, row) {
                    return getPaymentStatusBadge(data, row);
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-end invoice-actions',
                render: function(data, type, row) {
                    return `
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/fatura/${row.id}" class="btn btn-sm btn-icon btn-light-primary" title="Görüntüle">
                                <i class="ki-duotone ki-eye fs-4">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </a>
                            <a href="/fatura/duzenle/${row.id}" class="btn btn-sm btn-icon btn-light-info" title="Düzenle">
                                <i class="ki-duotone ki-pencil fs-4">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </a>
                            <button type="button" class="btn btn-sm btn-icon btn-light-danger" onclick="deleteInvoice(${row.id})" title="Sil">
                                <i class="ki-duotone ki-trash fs-4">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                    <span class="path5"></span>
                                </i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[2, 'desc']], // Sort by invoice_date DESC
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: true,
        language: {
            processing: "İşleniyor...",
            search: "Ara:",
            lengthMenu: "_MENU_ kayıt göster",
            info: "_TOTAL_ kayıttan _START_ - _END_ arası gösteriliyor",
            infoEmpty: "Kayıt yok",
            infoFiltered: "(_MAX_ kayıt içerisinden bulunan)",
            infoPostFix: "",
            loadingRecords: "Yükleniyor...",
            zeroRecords: "Kayıt bulunamadı",
            emptyTable: "Tabloda veri yok",
            paginate: {
                first: "İlk",
                previous: "Önceki",
                next: "Sonraki",
                last: "Son"
            },
            aria: {
                sortAscending: ": artan sütun sıralamasını aktifleştir",
                sortDescending: ": azalan sütun sıralamasını aktifleştir"
            }
        }
    });
}

/**
 * Initialize filters
 */
function initializeFilters() {
    const filterApply = document.getElementById('filter-apply');
    const filterReset = document.getElementById('filter-reset');
    
    if (filterApply) {
        filterApply.addEventListener('click', function(e) {
            e.preventDefault();
            applyFilters();
        });
    }
    
    if (filterReset) {
        filterReset.addEventListener('click', function(e) {
            e.preventDefault();
            resetFilters();
        });
    }
}

/**
 * Apply filters
 */
function applyFilters() {
    currentFilters.type = document.getElementById('filter-type').value;
    currentFilters.status = document.getElementById('filter-status').value;
    
    invoicesTable.ajax.reload();
    loadStatistics();
}

/**
 * Reset filters
 */
function resetFilters() {
    document.getElementById('filter-type').value = '';
    document.getElementById('filter-status').value = '';
    
    currentFilters.type = '';
    currentFilters.status = '';
    
    invoicesTable.ajax.reload();
    loadStatistics();
}

/**
 * Initialize search
 */
function initializeSearch() {
    const searchInput = document.getElementById('search-input');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                currentFilters.search = searchInput.value;
                invoicesTable.ajax.reload();
            }, 500);
        });
    }
}

/**
 * Load statistics
 */
function loadStatistics() {
    fetch('/api/invoices/statistics', {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + getAccessToken(),
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Statistics fetch failed');
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.data) {
            updateStatisticsDisplay(data.data);
        }
    })
    .catch(error => {
        console.error('Statistics error:', error);
    });
}

/**
 * Update statistics display
 */
function updateStatisticsDisplay(stats) {
    // Calculate totals
    let totalInvoices = 0;
    let unpaidAmount = 0;
    let paidAmount = 0;
    let overdueCount = 0;
    
    stats.forEach(stat => {
        totalInvoices += parseInt(stat.count || 0);
        
        const amount = parseFloat(stat.total || 0);
        
        if (stat.payment_status === 'unpaid' || stat.payment_status === 'partial') {
            unpaidAmount += amount;
        } else if (stat.payment_status === 'paid') {
            paidAmount += amount;
        }
        
        if (stat.payment_status === 'overdue') {
            overdueCount += parseInt(stat.count || 0);
        }
    });
    
    // Update DOM
    document.getElementById('total-invoices').textContent = totalInvoices;
    document.getElementById('unpaid-amount').textContent = formatCurrency(unpaidAmount, 'TRY');
    document.getElementById('paid-amount').textContent = formatCurrency(paidAmount, 'TRY');
    document.getElementById('overdue-count').textContent = overdueCount;
}

/**
 * Initialize export
 */
function initializeExport() {
    const exportBtn = document.getElementById('export-excel');
    
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            exportToExcel();
        });
    }
}

/**
 * Export to Excel
 */
function exportToExcel() {
    showNotification('Excel dışa aktarma özelliği yakında eklenecek', 'info');
    
    // TODO: Implement Excel export
    // Can use libraries like SheetJS or server-side export endpoint
}

/**
 * Delete invoice
 */
function deleteInvoice(id) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu faturayı silmek istediğinizden emin misiniz?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/api/invoices/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + getAccessToken(),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Fatura başarıyla silindi', 'success');
                    invoicesTable.ajax.reload();
                    loadStatistics();
                } else {
                    showNotification(data.message || 'Fatura silinemedi', 'error');
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                showNotification('Fatura silinirken hata oluştu', 'error');
            });
        }
    });
}

/**
 * Helper: Get payment status badge
 */
function getPaymentStatusBadge(status, row) {
    const badges = {
        unpaid: '<span class="badge badge-light-danger">Ödenmemiş</span>',
        partial: '<span class="badge badge-light-warning">Kısmi Ödendi</span>',
        paid: '<span class="badge badge-light-success">Ödendi</span>',
        overdue: '<span class="badge badge-light-danger">Vadesi Geçmiş</span>',
        cancelled: '<span class="badge badge-light-secondary">İptal</span>'
    };
    
    let badge = badges[status] || '<span class="badge badge-light-info">' + status + '</span>';
    
    // Add draft badge if applicable
    if (row.is_draft) {
        badge += ' <span class="badge badge-light-info ms-1">Taslak</span>';
    }
    
    // Add approved badge if applicable
    if (row.is_approved) {
        badge += ' <span class="badge badge-light-primary ms-1">Onaylı</span>';
    }
    
    return badge;
}

/**
 * Helper: Get invoice type label
 */
function getInvoiceTypeLabel(type) {
    const labels = {
        sales: 'Satış Faturası',
        purchase: 'Alış Faturası',
        sales_return: 'Satış İade',
        purchase_return: 'Alış İade'
    };
    
    return labels[type] || type;
}

/**
 * Helper: Format date
 */
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    
    return `${day}.${month}.${year}`;
}

/**
 * Helper: Format currency
 */
function formatCurrency(amount, currency = 'TRY') {
    const symbols = {
        TRY: '₺',
        USD: '$',
        EUR: '€',
        GBP: '£'
    };
    
    const symbol = symbols[currency] || currency;
    const formatted = parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    
    return `${formatted} ${symbol}`;
}

/**
 * Helper: Get access token from localStorage
 */
function getAccessToken() {
    return localStorage.getItem('access_token') || '';
}

/**
 * Helper: Show notification
 */
function showNotification(message, type = 'success') {
    // Using SweetAlert2
    const icons = {
        success: 'success',
        error: 'error',
        warning: 'warning',
        info: 'info'
    };
    
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icons[type] || 'info',
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

// Make deleteInvoice available globally for onclick
window.deleteInvoice = deleteInvoice;
/**
 * Invoice Detail Page - JavaScript
 * Handles invoice display, payments, actions (approve, cancel, print, PDF)
 */

"use strict";

// Global variables
let invoiceData = null;
let paymentsData = [];

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    if (!window.INVOICE_ID) {
        showNotification('Fatura ID bulunamadı', 'error');
        setTimeout(() => window.location.href = '/faturalar', 2000);
        return;
    }

    loadInvoiceData();
    initializeButtons();
    loadPaymentModal();
});

/**
 * Load invoice data from API
 */
function loadInvoiceData() {
    fetch(`/api/invoices/${window.INVOICE_ID}`, {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getAccessToken(),
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                if (response.status === 401) {
                    window.location.href = '/giris';
                    return;
                }
                throw new Error('Fatura yüklenemedi');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.data) {
                invoiceData = data.data;
                renderInvoice(invoiceData);
                loadPayments();
            } else {
                throw new Error(data.message || 'Fatura yüklenemedi');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(error.message, 'error');
        });
}

/**
 * Render invoice data to page
 */
function renderInvoice(invoice) {
    // Update page title and breadcrumb
    document.title = `${invoice.invoice_number} - Fatura Detay`;
    document.getElementById('invoice-number-breadcrumb').textContent = invoice.invoice_number;

    // Invoice number and dates
    document.getElementById('invoice-number').textContent = invoice.invoice_number;
    document.getElementById('invoice-dates').innerHTML = `
        Tarih: ${formatDate(invoice.invoice_date)}<br>
        Vade: ${formatDate(invoice.due_date)}
    `;

    // Status badges
    renderBadges(invoice);

    // Company info
    document.getElementById('company-name').textContent = invoice.company_name || 'Şirket Adı';
    document.getElementById('company-address').textContent = invoice.company_address || '';

    // Customer info
    const customerName = invoice.customer_name || invoice.cari_title || '-';
    const customerCode = invoice.customer_code || invoice.cari_code || '';
    const customerAddress = [
        invoice.customer_address,
        invoice.customer_city,
        invoice.customer_country
    ].filter(Boolean).join(', ') || '-';

    document.getElementById('customer-name').textContent = customerName;
    document.getElementById('customer-address').innerHTML = `
        ${customerCode ? `<strong>${customerCode}</strong><br>` : ''}
        ${customerAddress}
        ${invoice.customer_tax_number ? `<br>VKN/TCKN: ${invoice.customer_tax_number}` : ''}
    `;
    
    // Invoice items
    renderItems(invoice.items || []);
    
    // Totals
    const currency = invoice.currency || 'TRY';
    document.getElementById('subtotal').textContent = formatCurrency(invoice.subtotal_amount || 0, currency);
    document.getElementById('discount').textContent = formatCurrency(invoice.discount_amount || 0, currency);
    document.getElementById('tax').textContent = formatCurrency(invoice.tax_amount || 0, currency);
    document.getElementById('total').textContent = formatCurrency(invoice.total_amount || 0, currency);
    
    // Sidebar payment info
    renderPaymentInfo(invoice);
    
    // Notes
    if (invoice.notes) {
        document.getElementById('notes-section').style.display = 'block';
        document.getElementById('invoice-notes').textContent = invoice.notes;
    }
    
    // Update action buttons visibility
    updateActionButtons(invoice);
}

/**
 * Render status badges
 */
function renderBadges(invoice) {
    const badges = [];
    
    // Payment status
    const paymentStatusBadges = {
        unpaid: '<span class="badge badge-lg badge-light-danger me-2">Ödenmemiş</span>',
        partial: '<span class="badge badge-lg badge-light-warning me-2">Kısmi Ödendi</span>',
        paid: '<span class="badge badge-lg badge-light-success me-2">Ödendi</span>',
        overdue: '<span class="badge badge-lg badge-light-danger me-2">Vadesi Geçmiş</span>',
        cancelled: '<span class="badge badge-lg badge-light-secondary me-2">İptal</span>'
    };
    badges.push(paymentStatusBadges[invoice.payment_status] || '');
    
    // Draft
    if (invoice.is_draft) {
        badges.push('<span class="badge badge-lg badge-light-info me-2">Taslak</span>');
    }
    
    // Approved
    if (invoice.is_approved) {
        badges.push('<span class="badge badge-lg badge-light-primary me-2">Onaylı</span>');
    }
    
    // Cancelled
    if (invoice.is_cancelled) {
        badges.push('<span class="badge badge-lg badge-light-secondary me-2">İptal Edildi</span>');
    }
    
    // Locked
    if (invoice.is_locked) {
        badges.push('<span class="badge badge-lg badge-light-dark me-2">Kilitli</span>');
    }
    
    document.getElementById('invoice-badges').innerHTML = badges.join('');
}

/**
 * Render invoice items
 */
function renderItems(items) {
    const tbody = document.getElementById('invoice-items');
    
    if (!items || items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-gray-600 py-10">Kalem bulunamadı</td></tr>';
        return;
    }
    
    const currency = invoiceData.currency || 'TRY';
    let html = '';
    
    items.forEach(item => {
        const description = item.description ? `<div class="text-gray-600 fs-7">${item.description}</div>` : '';
        html += `
            <tr class="border-bottom border-bottom-dashed">
                <td class="ps-0 py-5">
                    <div class="fw-bold text-gray-800">${item.item_name || item.product_name || '-'}</div>
                    ${item.product_code ? `<div class="text-gray-600 fs-7">Kod: ${item.product_code}</div>` : ''}
                    ${description}
                </td>
                <td class="text-end py-5">${formatQuantity(item.quantity)} ${item.unit || ''}</td>
                <td class="text-end py-5">${formatCurrency(item.unit_price, currency)}</td>
                <td class="text-end py-5 fw-bold text-gray-800">${formatCurrency(item.total_amount, currency)}</td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

/**
 * Render payment info sidebar
 */
function renderPaymentInfo(invoice) {
    const currency = invoice.currency || 'TRY';
    
    // Payment status text
    const statusTexts = {
        unpaid: '<span class="text-danger">Ödenmemiş</span>',
        partial: '<span class="text-warning">Kısmi Ödendi</span>',
        paid: '<span class="text-success">Tamamen Ödendi</span>',
        overdue: '<span class="text-danger">Vadesi Geçmiş</span>',
        cancelled: '<span class="text-muted">İptal Edildi</span>'
    };
    document.getElementById('payment-status-text').innerHTML = statusTexts[invoice.payment_status] || invoice.payment_status;
    
    // Amounts
    document.getElementById('sidebar-total').textContent = formatCurrency(invoice.total_amount, currency);
    document.getElementById('paid-amount').textContent = formatCurrency(invoice.paid_amount || 0, currency);
    document.getElementById('remaining-amount').textContent = formatCurrency(invoice.remaining_amount || 0, currency);
    
    // Due date
    document.getElementById('due-date').textContent = formatDate(invoice.due_date);
}

/**
 * Load payments
 */
function loadPayments() {
    fetch(`/api/invoices/${window.INVOICE_ID}/payments`, {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + getAccessToken(),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data) {
            paymentsData = data.data;
            renderPaymentHistory(paymentsData);
        }
    })
    .catch(error => {
        console.error('Payments error:', error);
    });
}

/**
 * Render payment history
 */
function renderPaymentHistory(payments) {
    const container = document.getElementById('payment-history');
    
    if (!payments || payments.length === 0) {
        container.innerHTML = '<div class="text-gray-600 text-center py-5">Henüz ödeme kaydı bulunmuyor.</div>';
        return;
    }
    
    const currency = invoiceData.currency || 'TRY';
    let html = '<div class="timeline">';
    
    payments.forEach((payment, index) => {
        const methodLabels = {
            cash: 'Nakit',
            bank_transfer: 'Banka Havalesi',
            credit_card: 'Kredi Kartı',
            check: 'Çek',
            promissory_note: 'Senet',
            other: 'Diğer'
        };
        
        html += `
            <div class="timeline-item ${index < payments.length - 1 ? 'mb-5' : ''}">
                <div class="timeline-line w-40px"></div>
                <div class="timeline-icon symbol symbol-circle symbol-40px">
                    <div class="symbol-label bg-light-success">
                        <i class="ki-duotone ki-check fs-2 text-success">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                </div>
                <div class="timeline-content mb-5 mt-n1">
                    <div class="pe-3 mb-2">
                        <div class="fs-5 fw-semibold mb-2">${formatCurrency(payment.amount, currency)}</div>
                        <div class="d-flex align-items-center mt-1 fs-6">
                            <div class="text-muted me-2 fs-7">${methodLabels[payment.payment_method] || payment.payment_method}</div>
                            <div class="text-muted fs-7">${formatDate(payment.payment_date)}</div>
                        </div>
                        ${payment.notes ? `<div class="text-gray-600 fs-7 mt-2">${payment.notes}</div>` : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

/**
 * Initialize action buttons
 */
function initializeButtons() {
    // Print
    document.getElementById('btn-print')?.addEventListener('click', function() {
        window.print();
    });
    
    // PDF
    document.getElementById('btn-pdf')?.addEventListener('click', function() {
        generatePDF();
    });
    
    // Email
    document.getElementById('btn-email')?.addEventListener('click', function() {
        sendEmail();
    });
    
    // Approve
    document.getElementById('btn-approve')?.addEventListener('click', function(e) {
        e.preventDefault();
        approveInvoice();
    });
    
    // Cancel
    document.getElementById('btn-cancel')?.addEventListener('click', function(e) {
        e.preventDefault();
        cancelInvoice();
    });
}

/**
 * Update action buttons based on invoice status
 */
function updateActionButtons(invoice) {
    // Disable approve if already approved or cancelled
    const btnApprove = document.getElementById('btn-approve');
    if (btnApprove) {
        if (invoice.is_approved || invoice.is_cancelled) {
            btnApprove.classList.add('disabled');
            btnApprove.style.pointerEvents = 'none';
            btnApprove.style.opacity = '0.5';
        }
    }
    
    // Disable payment if paid or cancelled
    const btnPayment = document.getElementById('btn-payment');
    if (btnPayment) {
        if (invoice.payment_status === 'paid' || invoice.is_cancelled) {
            btnPayment.classList.add('disabled');
            btnPayment.style.pointerEvents = 'none';
            btnPayment.style.opacity = '0.5';
        }
    }
    
    // Disable cancel if already cancelled
    const btnCancel = document.getElementById('btn-cancel');
    if (btnCancel) {
        if (invoice.is_cancelled) {
            btnCancel.classList.add('disabled');
            btnCancel.style.pointerEvents = 'none';
            btnCancel.style.opacity = '0.5';
        }
    }
    
    // Disable edit if locked or cancelled
    const btnEdit = document.getElementById('btn-edit');
    if (btnEdit) {
        if (invoice.is_locked || invoice.is_cancelled) {
            btnEdit.classList.add('disabled');
            btnEdit.style.pointerEvents = 'none';
            btnEdit.style.opacity = '0.5';
        }
    }
}

/**
 * Approve invoice
 */
function approveInvoice() {
    Swal.fire({
        title: 'Faturayı Onayla',
        text: 'Bu faturayı onaylamak istediğinizden emin misiniz?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Evet, Onayla',
        cancelButtonText: 'İptal',
        confirmButtonColor: '#009ef7'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/api/invoices/${window.INVOICE_ID}/approve`, {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + getAccessToken(),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Fatura başarıyla onaylandı', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(data.message || 'Fatura onaylanamadı', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Fatura onaylanırken hata oluştu', 'error');
            });
        }
    });
}

/**
 * Cancel invoice
 */
function cancelInvoice() {
    Swal.fire({
        title: 'Faturayı İptal Et',
        input: 'textarea',
        inputLabel: 'İptal Nedeni',
        inputPlaceholder: 'İptal nedenini giriniz...',
        inputAttributes: {
            'aria-label': 'İptal nedenini giriniz'
        },
        showCancelButton: true,
        confirmButtonText: 'İptal Et',
        cancelButtonText: 'Vazgeç',
        confirmButtonColor: '#f1416c',
        inputValidator: (value) => {
            if (!value) {
                return 'İptal nedeni girmelisiniz!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/api/invoices/${window.INVOICE_ID}/cancel`, {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + getAccessToken(),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    reason: result.value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Fatura başarıyla iptal edildi', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(data.message || 'Fatura iptal edilemedi', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Fatura iptal edilirken hata oluştu', 'error');
            });
        }
    });
}

/**
 * Generate PDF
 */
function generatePDF() {
    showNotification('PDF oluşturma özelliği yakında eklenecek', 'info');
    // TODO: Implement PDF generation
}

/**
 * Send email
 */
function sendEmail() {
    showNotification('E-posta gönderme özelliği yakında eklenecek', 'info');
    // TODO: Implement email sending
}

/**
 * Load payment modal
 */
function loadPaymentModal() {
    // Payment modal will be loaded separately
    // For now, just show a placeholder
}

/**
 * Helper: Format date
 */
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('tr-TR');
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
    const formatted = parseFloat(amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    
    return `${formatted} ${symbol}`;
}

/**
 * Helper: Format quantity
 */
function formatQuantity(qty) {
    return parseFloat(qty || 0).toFixed(2).replace(/\.?0+$/, '');
}

/**
 * Helper: Get access token
 */
function getAccessToken() {
    return localStorage.getItem('access_token') || '';
}

/**
 * Helper: Show notification
 */
function showNotification(message, type = 'success') {
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
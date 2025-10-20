<?php

namespace App\Services\Notification;

/**
 * Email Templates
 * All email HTML templates in one place
 */
class EmailTemplate
{
    private string $headerColor = '#3b82f6';
    private string $companyName = 'OnMuhasebe';
    private string $companyUrl;

    public function __construct()
    {
        $this->companyUrl = $_ENV['APP_URL'] ?? 'http://localhost:8080';
    }

    /**
     * Base email template wrapper
     */
    private function wrap(string $content, string $preheader = ''): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$this->companyName}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background-color: {$this->headerColor};
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 40px 30px;
            color: #374151;
            line-height: 1.6;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            margin: 20px 0;
            background-color: {$this->headerColor};
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
        .divider {
            border: 0;
            border-top: 1px solid #e5e7eb;
            margin: 30px 0;
        }
        .info-box {
            background-color: #f3f4f6;
            border-left: 4px solid {$this->headerColor};
            padding: 15px;
            margin: 20px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th {
            background-color: #f9fafb;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
        }
        .table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-muted { color: #6b7280; }
        .text-success { color: #10b981; }
        .text-danger { color: #ef4444; }
        .text-warning { color: #f59e0b; }
    </style>
</head>
<body>
    <span style="display:none !important;visibility:hidden;mso-hide:all;font-size:1px;color:#ffffff;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;">
        {$preheader}
    </span>
    <div class="container">
        {$content}
        <div class="footer">
            <p><strong>{$this->companyName}</strong></p>
            <p>Muhasebe ve Finans Yönetim Sistemi</p>
            <p class="text-muted">
                Bu otomatik bir e-postadır. Lütfen yanıtlamayın.<br>
                Sorularınız için: <a href="mailto:destek@onmuhasebe.com">destek@onmuhasebe.com</a>
            </p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Registration welcome email
     */
    public function registration(array $user): string
    {
        $loginUrl = $this->companyUrl . '/giris';
        $name = $user['ad'] . ' ' . $user['soyad'];

        $content = <<<HTML
<div class="header">
    <h1>Hoş Geldiniz!</h1>
</div>
<div class="content">
    <p>Merhaba <strong>{$name}</strong>,</p>

    <p>{$this->companyName} ailesine katıldığınız için teşekkür ederiz! Hesabınız başarıyla oluşturuldu ve artık tüm özelliklerimizden yararlanabilirsiniz.</p>

    <div class="info-box">
        <strong>Hesap Bilgileriniz:</strong><br>
        E-posta: {$user['email']}<br>
        Kullanıcı Tipi: {$user['tip']}
    </div>

    <p>Şimdi sisteme giriş yaparak aşağıdaki özellikleri kullanmaya başlayabilirsiniz:</p>
    <ul>
        <li>Fatura ve Teklif Oluşturma</li>
        <li>Gelir/Gider Takibi</li>
        <li>Stok Yönetimi</li>
        <li>Müşteri ve Tedarikçi Yönetimi</li>
        <li>Finansal Raporlar</li>
    </ul>

    <div class="text-center">
        <a href="{$loginUrl}" class="button">Sisteme Giriş Yap</a>
    </div>

    <hr class="divider">

    <p class="text-muted">
        <strong>Güvenlik İpuçları:</strong><br>
        - Şifrenizi kimseyle paylaşmayın<br>
        - Güçlü bir şifre kullanın<br>
        - Düzenli olarak şifrenizi değiştirin
    </p>
</div>
HTML;

        return $this->wrap($content, "Hoş geldiniz {$name}! Hesabınız oluşturuldu.");
    }

    /**
     * Password reset email
     */
    public function passwordReset(string $token, string $name): string
    {
        $resetUrl = $this->companyUrl . '/sifre-sifirla?token=' . $token;
        $expiryMinutes = 60;

        $content = <<<HTML
<div class="header">
    <h1>Şifre Sıfırlama</h1>
</div>
<div class="content">
    <p>Merhaba <strong>{$name}</strong>,</p>

    <p>Hesabınız için bir şifre sıfırlama talebinde bulundunuz. Şifrenizi sıfırlamak için aşağıdaki butona tıklayın:</p>

    <div class="text-center">
        <a href="{$resetUrl}" class="button">Şifremi Sıfırla</a>
    </div>

    <div class="info-box">
        <strong>⚠️ Önemli:</strong> Bu bağlantı yalnızca {$expiryMinutes} dakika geçerlidir.
    </div>

    <p class="text-muted">
        Eğer bu talebi siz yapmadıysanız, bu e-postayı görmezden gelebilirsiniz. Şifreniz değiştirilmeyecektir.
    </p>

    <hr class="divider">

    <p class="text-muted" style="font-size: 12px;">
        Buton çalışmıyorsa, aşağıdaki linki tarayıcınıza kopyalayın:<br>
        <a href="{$resetUrl}">{$resetUrl}</a>
    </p>
</div>
HTML;

        return $this->wrap($content, "Şifre sıfırlama talebiniz alındı");
    }

    /**
     * Invoice created notification
     */
    public function invoiceCreated(array $invoice, array $customer): string
    {
        $invoiceUrl = $this->companyUrl . '/fatura/' . $invoice['id'];
        $amount = number_format($invoice['toplam_tutar'], 2, ',', '.');

        $content = <<<HTML
<div class="header">
    <h1>Yeni Fatura</h1>
</div>
<div class="content">
    <p>Sayın <strong>{$customer['unvan']}</strong>,</p>

    <p>Sizin için yeni bir fatura oluşturulmuştur:</p>

    <table class="table">
        <tr>
            <td><strong>Fatura No:</strong></td>
            <td>#{$invoice['fatura_no']}</td>
        </tr>
        <tr>
            <td><strong>Tarih:</strong></td>
            <td>{$invoice['tarih']}</td>
        </tr>
        <tr>
            <td><strong>Vade Tarihi:</strong></td>
            <td>{$invoice['vade_tarihi']}</td>
        </tr>
        <tr>
            <td><strong>Toplam Tutar:</strong></td>
            <td class="text-success"><strong>{$amount} {$invoice['para_birimi']}</strong></td>
        </tr>
    </table>

    <div class="text-center">
        <a href="{$invoiceUrl}" class="button">Faturayı Görüntüle</a>
    </div>

    <p class="text-muted">
        Faturanızı görüntülemek ve ödeme yapmak için yukarıdaki butona tıklayabilirsiniz.
    </p>
</div>
HTML;

        return $this->wrap($content, "Yeni fatura: #{$invoice['fatura_no']} - {$amount} {$invoice['para_birimi']}");
    }

    /**
     * Payment received notification
     */
    public function paymentReceived(array $payment, array $invoice, array $customer): string
    {
        $amount = number_format($payment['tutar'], 2, ',', '.');
        $remaining = number_format($invoice['kalan_tutar'], 2, ',', '.');

        $content = <<<HTML
<div class="header">
    <h1>Ödeme Alındı</h1>
</div>
<div class="content">
    <p>Sayın <strong>{$customer['unvan']}</strong>,</p>

    <p class="text-success"><strong>✓ Ödemeniz başarıyla alınmıştır.</strong></p>

    <table class="table">
        <tr>
            <td><strong>Fatura No:</strong></td>
            <td>#{$invoice['fatura_no']}</td>
        </tr>
        <tr>
            <td><strong>Ödeme Tarihi:</strong></td>
            <td>{$payment['tarih']}</td>
        </tr>
        <tr>
            <td><strong>Ödeme Tutarı:</strong></td>
            <td class="text-success"><strong>{$amount} {$invoice['para_birimi']}</strong></td>
        </tr>
        <tr>
            <td><strong>Ödeme Yöntemi:</strong></td>
            <td>{$payment['odeme_yontemi']}</td>
        </tr>
        <tr>
            <td><strong>Kalan Tutar:</strong></td>
            <td class="text-danger"><strong>{$remaining} {$invoice['para_birimi']}</strong></td>
        </tr>
    </table>

    <p>Ödemeniz için teşekkür ederiz.</p>
</div>
HTML;

        return $this->wrap($content, "Ödemeniz alındı - {$amount} {$invoice['para_birimi']}");
    }

    /**
     * Due date reminder
     */
    public function dueDateReminder(array $invoice, array $customer, int $daysLeft): string
    {
        $amount = number_format($invoice['kalan_tutar'], 2, ',', '.');
        $invoiceUrl = $this->companyUrl . '/fatura/' . $invoice['id'];

        $urgency = $daysLeft <= 3 ? 'text-danger' : 'text-warning';
        $daysText = $daysLeft == 1 ? '1 gün' : $daysLeft . ' gün';

        $content = <<<HTML
<div class="header" style="background-color: #f59e0b;">
    <h1>⚠️ Vade Tarihi Yaklaşıyor</h1>
</div>
<div class="content">
    <p>Sayın <strong>{$customer['unvan']}</strong>,</p>

    <p>Aşağıdaki faturanızın vade tarihine <strong class="{$urgency}">{$daysText}</strong> kalmıştır:</p>

    <table class="table">
        <tr>
            <td><strong>Fatura No:</strong></td>
            <td>#{$invoice['fatura_no']}</td>
        </tr>
        <tr>
            <td><strong>Vade Tarihi:</strong></td>
            <td class="{$urgency}"><strong>{$invoice['vade_tarihi']}</strong></td>
        </tr>
        <tr>
            <td><strong>Kalan Tutar:</strong></td>
            <td class="text-danger"><strong>{$amount} {$invoice['para_birimi']}</strong></td>
        </tr>
    </table>

    <div class="info-box">
        <strong>Hatırlatma:</strong> Geç ödemelerden kaçınmak için lütfen vade tarihinden önce ödemenizi gerçekleştirin.
    </div>

    <div class="text-center">
        <a href="{$invoiceUrl}" class="button">Faturayı Görüntüle ve Öde</a>
    </div>
</div>
HTML;

        return $this->wrap($content, "Fatura #{$invoice['fatura_no']} - {$daysText} sonra vadesi dolacak");
    }

    /**
     * Low stock alert
     */
    public function lowStockAlert(array $product): string
    {
        $stockUrl = $this->companyUrl . '/stok/' . $product['id'];

        $content = <<<HTML
<div class="header" style="background-color: #ef4444;">
    <h1>🔴 Düşük Stok Uyarısı</h1>
</div>
<div class="content">
    <p><strong>Dikkat!</strong> Aşağıdaki ürünün stok miktarı kritik seviyenin altına düşmüştür:</p>

    <table class="table">
        <tr>
            <td><strong>Ürün Adı:</strong></td>
            <td><strong>{$product['urun_adi']}</strong></td>
        </tr>
        <tr>
            <td><strong>Ürün Kodu:</strong></td>
            <td>{$product['urun_kodu']}</td>
        </tr>
        <tr>
            <td><strong>Mevcut Stok:</strong></td>
            <td class="text-danger"><strong>{$product['stok_miktari']} {$product['birim']}</strong></td>
        </tr>
        <tr>
            <td><strong>Minimum Stok:</strong></td>
            <td>{$product['min_stok']} {$product['birim']}</td>
        </tr>
    </table>

    <div class="info-box">
        <strong>⚠️ Eylem Gerekli:</strong> Stok tükenmeden önce tedarikçinizle iletişime geçin ve sipariş verin.
    </div>

    <div class="text-center">
        <a href="{$stockUrl}" class="button">Stok Detaylarını Gör</a>
    </div>
</div>
HTML;

        return $this->wrap($content, "Düşük stok: {$product['urun_adi']}");
    }
}

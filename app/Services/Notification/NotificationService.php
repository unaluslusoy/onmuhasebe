<?php

namespace App\Services\Notification;

use OneSignal\OneSignal;
use App\Helpers\Logger;

/**
 * Notification Service
 * Handles push notifications via OneSignal
 */
class NotificationService
{
    private ?OneSignal $client = null;
    private string $appId;
    private string $restApiKey;
    private bool $enabled;

    public function __construct()
    {
        $this->appId = $_ENV['ONESIGNAL_APP_ID'] ?? '';
        $this->restApiKey = $_ENV['ONESIGNAL_REST_API_KEY'] ?? '';
        $this->enabled = ($_ENV['ONESIGNAL_ENABLED'] ?? 'false') === 'true';

        if ($this->enabled && !empty($this->appId) && !empty($this->restApiKey)) {
            $this->initializeClient();
        }
    }

    /**
     * Initialize OneSignal client
     */
    private function initializeClient(): void
    {
        try {
            $config = [
                'app_id' => $this->appId,
                'rest_api_key' => $this->restApiKey
            ];

            $this->client = new OneSignal($config);

        } catch (\Exception $e) {
            Logger::error('OneSignal initialization failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send notification to specific user(s)
     */
    public function sendToUsers(array $userIds, string $title, string $message, array $data = []): bool
    {
        if (!$this->enabled || !$this->client) {
            Logger::info('OneSignal disabled or not configured, skipping notification');
            return false;
        }

        try {
            $params = [
                'app_id' => $this->appId,
                'contents' => ['en' => $message, 'tr' => $message],
                'headings' => ['en' => $title, 'tr' => $title],
                'include_external_user_ids' => $userIds,
                'data' => $data
            ];

            // Add icon and badge
            if (isset($_ENV['ONESIGNAL_ICON_URL'])) {
                $params['small_icon'] = $_ENV['ONESIGNAL_ICON_URL'];
                $params['large_icon'] = $_ENV['ONESIGNAL_ICON_URL'];
            }

            $response = $this->client->notifications->add($params);

            Logger::info('OneSignal notification sent', [
                'users' => $userIds,
                'title' => $title,
                'response' => $response
            ]);

            return true;

        } catch (\Exception $e) {
            Logger::error('OneSignal notification failed', [
                'users' => $userIds,
                'title' => $title,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send notification to all users
     */
    public function sendToAll(string $title, string $message, array $data = []): bool
    {
        if (!$this->enabled || !$this->client) {
            Logger::info('OneSignal disabled or not configured, skipping notification');
            return false;
        }

        try {
            $params = [
                'app_id' => $this->appId,
                'contents' => ['en' => $message, 'tr' => $message],
                'headings' => ['en' => $title, 'tr' => $title],
                'included_segments' => ['All'],
                'data' => $data
            ];

            if (isset($_ENV['ONESIGNAL_ICON_URL'])) {
                $params['small_icon'] = $_ENV['ONESIGNAL_ICON_URL'];
                $params['large_icon'] = $_ENV['ONESIGNAL_ICON_URL'];
            }

            $response = $this->client->notifications->add($params);

            Logger::info('OneSignal broadcast sent', [
                'title' => $title,
                'response' => $response
            ]);

            return true;

        } catch (\Exception $e) {
            Logger::error('OneSignal broadcast failed', [
                'title' => $title,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send notification to users with specific tags
     */
    public function sendToTags(array $tags, string $title, string $message, array $data = []): bool
    {
        if (!$this->enabled || !$this->client) {
            Logger::info('OneSignal disabled or not configured, skipping notification');
            return false;
        }

        try {
            $params = [
                'app_id' => $this->appId,
                'contents' => ['en' => $message, 'tr' => $message],
                'headings' => ['en' => $title, 'tr' => $title],
                'filters' => $this->buildTagFilters($tags),
                'data' => $data
            ];

            if (isset($_ENV['ONESIGNAL_ICON_URL'])) {
                $params['small_icon'] = $_ENV['ONESIGNAL_ICON_URL'];
                $params['large_icon'] = $_ENV['ONESIGNAL_ICON_URL'];
            }

            $response = $this->client->notifications->add($params);

            Logger::info('OneSignal tag notification sent', [
                'tags' => $tags,
                'title' => $title,
                'response' => $response
            ]);

            return true;

        } catch (\Exception $e) {
            Logger::error('OneSignal tag notification failed', [
                'tags' => $tags,
                'title' => $title,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Build tag filters for OneSignal
     */
    private function buildTagFilters(array $tags): array
    {
        $filters = [];
        $index = 0;

        foreach ($tags as $key => $value) {
            if ($index > 0) {
                $filters[] = ['operator' => 'OR'];
            }

            $filters[] = [
                'field' => 'tag',
                'key' => $key,
                'relation' => '=',
                'value' => $value
            ];

            $index++;
        }

        return $filters;
    }

    /**
     * Send invoice created notification
     */
    public function notifyInvoiceCreated(array $invoice, array $customer, string $userId): bool
    {
        $title = '🧾 Yeni Fatura';
        $message = "Fatura #{$invoice['fatura_no']} oluşturuldu - " .
                   number_format($invoice['toplam_tutar'], 2) . ' ' . $invoice['para_birimi'];

        $data = [
            'type' => 'invoice_created',
            'invoice_id' => $invoice['id'],
            'invoice_no' => $invoice['fatura_no'],
            'url' => '/fatura/' . $invoice['id']
        ];

        return $this->sendToUsers([$userId], $title, $message, $data);
    }

    /**
     * Send payment received notification
     */
    public function notifyPaymentReceived(array $payment, array $invoice, string $userId): bool
    {
        $title = '✅ Ödeme Alındı';
        $message = "Fatura #{$invoice['fatura_no']} için " .
                   number_format($payment['tutar'], 2) . ' ' . $invoice['para_birimi'] .
                   ' ödeme alındı';

        $data = [
            'type' => 'payment_received',
            'payment_id' => $payment['id'],
            'invoice_id' => $invoice['id'],
            'url' => '/fatura/' . $invoice['id']
        ];

        return $this->sendToUsers([$userId], $title, $message, $data);
    }

    /**
     * Send due date reminder notification
     */
    public function notifyDueDateReminder(array $invoice, string $userId, int $daysLeft): bool
    {
        $urgency = $daysLeft <= 3 ? '🔴' : '⚠️';
        $title = "{$urgency} Vade Tarihi Yaklaşıyor";
        $message = "Fatura #{$invoice['fatura_no']} - {$daysLeft} gün sonra vadesi dolacak";

        $data = [
            'type' => 'due_date_reminder',
            'invoice_id' => $invoice['id'],
            'days_left' => $daysLeft,
            'url' => '/fatura/' . $invoice['id']
        ];

        return $this->sendToUsers([$userId], $title, $message, $data);
    }

    /**
     * Send low stock alert notification
     */
    public function notifyLowStock(array $product, string $userId): bool
    {
        $title = '📦 Düşük Stok Uyarısı';
        $message = "{$product['urun_adi']} - Stok: {$product['stok_miktari']} {$product['birim']} " .
                   "(Min: {$product['min_stok']})";

        $data = [
            'type' => 'low_stock',
            'product_id' => $product['id'],
            'url' => '/stok/' . $product['id']
        ];

        return $this->sendToUsers([$userId], $title, $message, $data);
    }

    /**
     * Send new order notification
     */
    public function notifyNewOrder(array $order, string $userId): bool
    {
        $title = '🛒 Yeni Sipariş';
        $message = "Sipariş #{$order['siparis_no']} - " .
                   number_format($order['toplam_tutar'], 2) . ' ' . $order['para_birimi'];

        $data = [
            'type' => 'new_order',
            'order_id' => $order['id'],
            'url' => '/siparis/' . $order['id']
        ];

        return $this->sendToUsers([$userId], $title, $message, $data);
    }

    /**
     * Send system announcement to all users
     */
    public function sendAnnouncement(string $title, string $message, array $data = []): bool
    {
        $data['type'] = 'announcement';
        return $this->sendToAll('📢 ' . $title, $message, $data);
    }

    /**
     * Send to admin users only
     */
    public function notifyAdmins(string $title, string $message, array $data = []): bool
    {
        return $this->sendToTags(['role' => 'admin'], $title, $message, $data);
    }

    /**
     * Check if notifications are enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled && $this->client !== null;
    }

    /**
     * Get OneSignal App ID (for frontend integration)
     */
    public function getAppId(): string
    {
        return $this->appId;
    }
}

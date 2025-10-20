<?php

namespace App\Services\Notification;

use App\Helpers\Logger;

/**
 * Notification Manager
 * Unified interface for sending both email and push notifications
 */
class NotificationManager
{
    private EmailService $emailService;
    private NotificationService $pushService;

    public function __construct()
    {
        $this->emailService = new EmailService();
        $this->pushService = new NotificationService();
    }

    /**
     * Send registration notification (email + push)
     */
    public function sendRegistration(array $user): array
    {
        $results = [
            'email' => false,
            'push' => false
        ];

        // Send email
        try {
            $results['email'] = $this->emailService->sendRegistrationEmail($user);
        } catch (\Exception $e) {
            Logger::error('Registration email failed', [
                'user_id' => $user['id'],
                'error' => $e->getMessage()
            ]);
        }

        // Note: Push notification not sent for registration
        // User needs to opt-in via browser first

        return $results;
    }

    /**
     * Send password reset notification (email only - high priority)
     */
    public function sendPasswordReset(string $email, string $token, string $name): array
    {
        $results = [
            'email' => false,
            'push' => false
        ];

        try {
            $results['email'] = $this->emailService->sendPasswordResetEmail($email, $token, $name);
        } catch (\Exception $e) {
            Logger::error('Password reset email failed', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
        }

        return $results;
    }

    /**
     * Send invoice created notification (email + push)
     */
    public function sendInvoiceCreated(array $invoice, array $customer, string $userId): array
    {
        $results = [
            'email' => false,
            'push' => false
        ];

        // Check user preferences
        $prefs = $this->getUserPreferences($userId);

        // Send email
        if ($prefs['email_invoice']) {
            try {
                $results['email'] = $this->emailService->sendInvoiceCreatedEmail($invoice, $customer);
            } catch (\Exception $e) {
                Logger::error('Invoice email failed', [
                    'invoice_id' => $invoice['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Send push notification
        if ($prefs['push_invoice']) {
            try {
                $results['push'] = $this->pushService->notifyInvoiceCreated($invoice, $customer, $userId);
            } catch (\Exception $e) {
                Logger::error('Invoice push notification failed', [
                    'invoice_id' => $invoice['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Send payment received notification (email + push)
     */
    public function sendPaymentReceived(array $payment, array $invoice, array $customer, string $userId): array
    {
        $results = [
            'email' => false,
            'push' => false
        ];

        $prefs = $this->getUserPreferences($userId);

        // Send email
        if ($prefs['email_payment']) {
            try {
                $results['email'] = $this->emailService->sendPaymentReceivedEmail($payment, $invoice, $customer);
            } catch (\Exception $e) {
                Logger::error('Payment email failed', [
                    'payment_id' => $payment['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Send push notification
        if ($prefs['push_payment']) {
            try {
                $results['push'] = $this->pushService->notifyPaymentReceived($payment, $invoice, $userId);
            } catch (\Exception $e) {
                Logger::error('Payment push notification failed', [
                    'payment_id' => $payment['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Send due date reminder (email + push)
     */
    public function sendDueDateReminder(array $invoice, array $customer, string $userId, int $daysLeft): array
    {
        $results = [
            'email' => false,
            'push' => false
        ];

        $prefs = $this->getUserPreferences($userId);

        // Send email
        if ($prefs['email_reminder']) {
            try {
                $results['email'] = $this->emailService->sendDueDateReminderEmail($invoice, $customer, $daysLeft);
            } catch (\Exception $e) {
                Logger::error('Reminder email failed', [
                    'invoice_id' => $invoice['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Send push notification
        if ($prefs['push_reminder']) {
            try {
                $results['push'] = $this->pushService->notifyDueDateReminder($invoice, $userId, $daysLeft);
            } catch (\Exception $e) {
                Logger::error('Reminder push notification failed', [
                    'invoice_id' => $invoice['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Send low stock alert (email + push to admins)
     */
    public function sendLowStockAlert(array $product, string $adminEmail, string $adminUserId): array
    {
        $results = [
            'email' => false,
            'push' => false
        ];

        $prefs = $this->getUserPreferences($adminUserId);

        // Send email
        if ($prefs['email_stock']) {
            try {
                $results['email'] = $this->emailService->sendLowStockAlertEmail($product, $adminEmail);
            } catch (\Exception $e) {
                Logger::error('Low stock email failed', [
                    'product_id' => $product['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Send push notification
        if ($prefs['push_stock']) {
            try {
                $results['push'] = $this->pushService->notifyLowStock($product, $adminUserId);
            } catch (\Exception $e) {
                Logger::error('Low stock push notification failed', [
                    'product_id' => $product['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Send new order notification
     */
    public function sendNewOrder(array $order, string $userId): array
    {
        $results = [
            'email' => false,
            'push' => false
        ];

        $prefs = $this->getUserPreferences($userId);

        // Send push notification
        if ($prefs['push_order']) {
            try {
                $results['push'] = $this->pushService->notifyNewOrder($order, $userId);
            } catch (\Exception $e) {
                Logger::error('Order push notification failed', [
                    'order_id' => $order['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Send system announcement to all users
     */
    public function sendAnnouncement(string $title, string $message): array
    {
        $results = [
            'email' => false,
            'push' => false
        ];

        try {
            $results['push'] = $this->pushService->sendAnnouncement($title, $message);
        } catch (\Exception $e) {
            Logger::error('Announcement failed', [
                'title' => $title,
                'error' => $e->getMessage()
            ]);
        }

        return $results;
    }

    /**
     * Get user notification preferences
     * Returns default preferences if not found
     */
    private function getUserPreferences(string $userId): array
    {
        try {
            $db = \App\Helpers\Database::getInstance();

            $stmt = $db->prepare("SELECT * FROM user_notification_preferences WHERE user_id = ?");
            $stmt->execute([$userId]);
            $prefs = $stmt->fetch();

            if ($prefs) {
                return [
                    'email_invoice' => (bool)$prefs['email_invoice'],
                    'email_payment' => (bool)$prefs['email_payment'],
                    'email_reminder' => (bool)$prefs['email_reminder'],
                    'email_stock' => (bool)$prefs['email_stock'],
                    'push_invoice' => (bool)$prefs['push_invoice'],
                    'push_payment' => (bool)$prefs['push_payment'],
                    'push_reminder' => (bool)$prefs['push_reminder'],
                    'push_stock' => (bool)$prefs['push_stock'],
                    'push_order' => (bool)$prefs['push_order']
                ];
            }
        } catch (\Exception $e) {
            Logger::warning('Could not fetch notification preferences', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }

        // Default preferences (all enabled)
        return [
            'email_invoice' => true,
            'email_payment' => true,
            'email_reminder' => true,
            'email_stock' => true,
            'push_invoice' => true,
            'push_payment' => true,
            'push_reminder' => true,
            'push_stock' => true,
            'push_order' => true
        ];
    }

    /**
     * Update user notification preferences
     */
    public function updateUserPreferences(string $userId, array $preferences): bool
    {
        try {
            $db = \App\Helpers\Database::getInstance();

            $sql = "INSERT INTO user_notification_preferences (
                user_id, email_invoice, email_payment, email_reminder, email_stock,
                push_invoice, push_payment, push_reminder, push_stock, push_order,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                email_invoice = VALUES(email_invoice),
                email_payment = VALUES(email_payment),
                email_reminder = VALUES(email_reminder),
                email_stock = VALUES(email_stock),
                push_invoice = VALUES(push_invoice),
                push_payment = VALUES(push_payment),
                push_reminder = VALUES(push_reminder),
                push_stock = VALUES(push_stock),
                push_order = VALUES(push_order),
                updated_at = NOW()";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                $userId,
                $preferences['email_invoice'] ?? true,
                $preferences['email_payment'] ?? true,
                $preferences['email_reminder'] ?? true,
                $preferences['email_stock'] ?? true,
                $preferences['push_invoice'] ?? true,
                $preferences['push_payment'] ?? true,
                $preferences['push_reminder'] ?? true,
                $preferences['push_stock'] ?? true,
                $preferences['push_order'] ?? true
            ]);

            Logger::info('Notification preferences updated', [
                'user_id' => $userId
            ]);

            return true;

        } catch (\Exception $e) {
            Logger::error('Failed to update notification preferences', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get notification statistics for a user
     */
    public function getStatistics(string $userId): array
    {
        try {
            $db = \App\Helpers\Database::getInstance();

            // Email stats from queue
            $stmt = $db->prepare("
                SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                FROM email_queue
                WHERE to_email = (SELECT email FROM users WHERE id = ?)
            ");
            $stmt->execute([$userId]);
            $emailStats = $stmt->fetch();

            return [
                'email' => $emailStats,
                'push_enabled' => $this->pushService->isEnabled()
            ];

        } catch (\Exception $e) {
            Logger::error('Failed to get notification statistics', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'email' => ['total' => 0, 'sent' => 0, 'failed' => 0, 'pending' => 0],
                'push_enabled' => false
            ];
        }
    }
}

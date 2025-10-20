<?php

namespace App\Services\Notification;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Helpers\Logger;

/**
 * Email Service
 * Handles all email sending operations with queue support
 */
class EmailService
{
    private PHPMailer $mailer;
    private bool $queueEnabled;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->queueEnabled = $_ENV['MAIL_QUEUE_ENABLED'] ?? true;

        $this->configure();
    }

    /**
     * Configure PHPMailer with SMTP settings
     */
    private function configure(): void
    {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $_ENV['MAIL_USERNAME'] ?? '';
            $this->mailer->Password = $_ENV['MAIL_PASSWORD'] ?? '';
            $this->mailer->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = $_ENV['MAIL_PORT'] ?? 587;
            $this->mailer->CharSet = 'UTF-8';

            // From address
            $this->mailer->setFrom(
                $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@onmuhasebe.com',
                $_ENV['MAIL_FROM_NAME'] ?? 'OnMuhasebe'
            );

            // Debug mode (0 = off, 1 = client, 2 = server)
            $this->mailer->SMTPDebug = $_ENV['MAIL_DEBUG'] ?? SMTP::DEBUG_OFF;

        } catch (Exception $e) {
            Logger::error('Email configuration failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email immediately or add to queue
     */
    public function send(string $to, string $subject, string $body, array $options = []): bool
    {
        if ($this->queueEnabled) {
            return $this->addToQueue($to, $subject, $body, $options);
        }

        return $this->sendNow($to, $subject, $body, $options);
    }

    /**
     * Send email immediately
     */
    public function sendNow(string $to, string $subject, string $body, array $options = []): bool
    {
        try {
            // Clear previous addresses
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            $this->mailer->clearCCs();
            $this->mailer->clearBCCs();
            $this->mailer->clearReplyTos();

            // Recipients
            $this->mailer->addAddress($to, $options['to_name'] ?? '');

            // CC
            if (isset($options['cc'])) {
                if (is_array($options['cc'])) {
                    foreach ($options['cc'] as $cc) {
                        $this->mailer->addCC($cc);
                    }
                } else {
                    $this->mailer->addCC($options['cc']);
                }
            }

            // BCC
            if (isset($options['bcc'])) {
                if (is_array($options['bcc'])) {
                    foreach ($options['bcc'] as $bcc) {
                        $this->mailer->addBCC($bcc);
                    }
                } else {
                    $this->mailer->addBCC($options['bcc']);
                }
            }

            // Reply-To
            if (isset($options['reply_to'])) {
                $this->mailer->addReplyTo(
                    $options['reply_to'],
                    $options['reply_to_name'] ?? ''
                );
            }

            // Attachments
            if (isset($options['attachments']) && is_array($options['attachments'])) {
                foreach ($options['attachments'] as $attachment) {
                    if (is_array($attachment)) {
                        $this->mailer->addAttachment(
                            $attachment['path'],
                            $attachment['name'] ?? ''
                        );
                    } else {
                        $this->mailer->addAttachment($attachment);
                    }
                }
            }

            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;

            // Plain text alternative
            if (isset($options['alt_body'])) {
                $this->mailer->AltBody = $options['alt_body'];
            } else {
                $this->mailer->AltBody = strip_tags($body);
            }

            // Send
            $result = $this->mailer->send();

            Logger::info('Email sent successfully', [
                'to' => $to,
                'subject' => $subject
            ]);

            return $result;

        } catch (Exception $e) {
            Logger::error('Email sending failed', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Add email to queue for later sending
     */
    private function addToQueue(string $to, string $subject, string $body, array $options = []): bool
    {
        try {
            $db = \App\Helpers\Database::getInstance();

            $sql = "INSERT INTO email_queue (
                to_email, to_name, subject, body, alt_body,
                cc, bcc, reply_to, attachments,
                priority, scheduled_at, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                $to,
                $options['to_name'] ?? null,
                $subject,
                $body,
                $options['alt_body'] ?? null,
                isset($options['cc']) ? json_encode($options['cc']) : null,
                isset($options['bcc']) ? json_encode($options['bcc']) : null,
                $options['reply_to'] ?? null,
                isset($options['attachments']) ? json_encode($options['attachments']) : null,
                $options['priority'] ?? 'normal',
                $options['scheduled_at'] ?? null
            ]);

            Logger::info('Email added to queue', [
                'to' => $to,
                'subject' => $subject,
                'queue_id' => $db->lastInsertId()
            ]);

            return true;

        } catch (\Exception $e) {
            Logger::error('Failed to add email to queue', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Process email queue
     */
    public function processQueue(int $limit = 10): array
    {
        $stats = [
            'processed' => 0,
            'sent' => 0,
            'failed' => 0
        ];

        try {
            $db = \App\Helpers\Database::getInstance();

            // Get pending emails
            $sql = "SELECT * FROM email_queue
                    WHERE status = 'pending'
                    AND (scheduled_at IS NULL OR scheduled_at <= NOW())
                    AND attempts < 3
                    ORDER BY
                        CASE priority
                            WHEN 'high' THEN 1
                            WHEN 'normal' THEN 2
                            WHEN 'low' THEN 3
                        END,
                        created_at ASC
                    LIMIT ?";

            $stmt = $db->prepare($sql);
            $stmt->execute([$limit]);
            $emails = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($emails as $email) {
                $stats['processed']++;

                // Prepare options
                $options = [
                    'to_name' => $email['to_name'],
                    'alt_body' => $email['alt_body'],
                    'reply_to' => $email['reply_to']
                ];

                if ($email['cc']) {
                    $options['cc'] = json_decode($email['cc'], true);
                }

                if ($email['bcc']) {
                    $options['bcc'] = json_decode($email['bcc'], true);
                }

                if ($email['attachments']) {
                    $options['attachments'] = json_decode($email['attachments'], true);
                }

                // Try to send
                $sent = $this->sendNow(
                    $email['to_email'],
                    $email['subject'],
                    $email['body'],
                    $options
                );

                // Update queue record
                if ($sent) {
                    $updateSql = "UPDATE email_queue
                                 SET status = 'sent', sent_at = NOW()
                                 WHERE id = ?";
                    $stats['sent']++;
                } else {
                    $updateSql = "UPDATE email_queue
                                 SET status = IF(attempts + 1 >= 3, 'failed', 'pending'),
                                     attempts = attempts + 1,
                                     last_attempt_at = NOW(),
                                     error_message = ?
                                 WHERE id = ?";
                    $stats['failed']++;
                }

                $updateStmt = $db->prepare($updateSql);
                if ($sent) {
                    $updateStmt->execute([$email['id']]);
                } else {
                    $updateStmt->execute([
                        $this->mailer->ErrorInfo ?? 'Unknown error',
                        $email['id']
                    ]);
                }
            }

            Logger::info('Email queue processed', $stats);

        } catch (\Exception $e) {
            Logger::error('Email queue processing failed', [
                'error' => $e->getMessage()
            ]);
        }

        return $stats;
    }

    /**
     * Send registration welcome email
     */
    public function sendRegistrationEmail(array $user): bool
    {
        $template = new EmailTemplate();
        $body = $template->registration($user);

        return $this->send(
            $user['email'],
            'OnMuhasebe\'ye Hoş Geldiniz!',
            $body,
            ['to_name' => $user['ad'] . ' ' . $user['soyad']]
        );
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail(string $email, string $token, string $name): bool
    {
        $template = new EmailTemplate();
        $body = $template->passwordReset($token, $name);

        return $this->send(
            $email,
            'Şifre Sıfırlama Talebi',
            $body,
            [
                'to_name' => $name,
                'priority' => 'high'
            ]
        );
    }

    /**
     * Send invoice created notification
     */
    public function sendInvoiceCreatedEmail(array $invoice, array $customer): bool
    {
        $template = new EmailTemplate();
        $body = $template->invoiceCreated($invoice, $customer);

        return $this->send(
            $customer['email'],
            "Fatura #{$invoice['fatura_no']} Oluşturuldu",
            $body,
            ['to_name' => $customer['unvan']]
        );
    }

    /**
     * Send payment received notification
     */
    public function sendPaymentReceivedEmail(array $payment, array $invoice, array $customer): bool
    {
        $template = new EmailTemplate();
        $body = $template->paymentReceived($payment, $invoice, $customer);

        return $this->send(
            $customer['email'],
            "Ödeme Alındı - Fatura #{$invoice['fatura_no']}",
            $body,
            ['to_name' => $customer['unvan']]
        );
    }

    /**
     * Send due date reminder
     */
    public function sendDueDateReminderEmail(array $invoice, array $customer, int $daysLeft): bool
    {
        $template = new EmailTemplate();
        $body = $template->dueDateReminder($invoice, $customer, $daysLeft);

        return $this->send(
            $customer['email'],
            "Hatırlatma: Fatura #{$invoice['fatura_no']} Yaklaşan Vade",
            $body,
            [
                'to_name' => $customer['unvan'],
                'priority' => 'high'
            ]
        );
    }

    /**
     * Send low stock alert
     */
    public function sendLowStockAlertEmail(array $product, string $adminEmail): bool
    {
        $template = new EmailTemplate();
        $body = $template->lowStockAlert($product);

        return $this->send(
            $adminEmail,
            "Düşük Stok Uyarısı: {$product['urun_adi']}",
            $body,
            ['priority' => 'high']
        );
    }
}

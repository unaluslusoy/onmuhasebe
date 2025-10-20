# OneSignal Push Notification Integration

## Overview

OnMuhasebe sistemi, kullanıcılara anlık bildirim göndermek için OneSignal entegrasyonuna sahiptir. Bu dokümantasyon, OneSignal'in nasıl kurulacağını ve kullanılacağını açıklar.

## Features

- ✅ **Unified Notification System**: Email ve push bildirimleri tek bir arayüzden yönetin
- ✅ **User Preferences**: Kullanıcılar hangi bildirimleri almak istediklerini seçebilir
- ✅ **8 Notification Types**:
  - Registration (Kayıt) - Email only
  - Password Reset (Şifre Sıfırlama) - Email only (security)
  - Invoice Created (Fatura Oluşturuldu) - Email + Push
  - Payment Received (Ödeme Alındı) - Email + Push
  - Due Date Reminder (Vade Hatırlatma) - Email + Push
  - Low Stock Alert (Düşük Stok) - Email + Push
  - New Order (Yeni Sipariş) - Push only
  - System Announcement (Sistem Duyurusu) - Push only

## Setup

### 1. Create OneSignal Account

1. Go to [https://onesignal.com](https://onesignal.com)
2. Sign up for a free account
3. Create a new app
4. Choose "Web Push" platform

### 2. Get Credentials

1. Navigate to **Settings > Keys & IDs**
2. Copy your **App ID**
3. Copy your **REST API Key**

### 3. Configure .env

Update your `.env` file:

```env
# OneSignal Push Notifications
ONESIGNAL_ENABLED=true
ONESIGNAL_APP_ID=your-onesignal-app-id-here
ONESIGNAL_REST_API_KEY=your-rest-api-key-here
ONESIGNAL_ICON_URL=http://yourdomain.com/assets/images/logo.png
```

### 4. Install OneSignal Web SDK

Add to your HTML `<head>` section:

```html
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(function(OneSignal) {
    OneSignal.init({
      appId: "<?= $_ENV['ONESIGNAL_APP_ID'] ?>",
      allowLocalhostAsSecureOrigin: true, // For testing on localhost
    });
  });
</script>
```

### 5. Configure Web Push Settings in OneSignal Dashboard

1. Go to **Settings > Web Configuration**
2. Add your site URL
3. Upload notification icons (256x256 recommended)
4. Configure permission prompt

## Usage

### Basic Usage

```php
use App\Services\Notification\NotificationManager;

$manager = new NotificationManager();

// Send invoice notification (both email and push)
$result = $manager->sendInvoiceCreated($invoice, $customer, $userId);

// Send payment notification
$result = $manager->sendPaymentReceived($payment, $invoice, $customer, $userId);

// Send due date reminder
$result = $manager->sendDueDateReminder($invoice, $customer, $userId, $daysLeft);

// Send low stock alert
$result = $manager->sendLowStockAlert($product, $adminEmail, $adminUserId);

// Send system announcement to all users
$result = $manager->sendAnnouncement('Maintenance', 'System will be down tonight');
```

### Direct Push Notification

```php
use App\Services\Notification\NotificationService;

$pushService = new NotificationService();

// Send to specific users
$pushService->sendToUsers(
    [$userId1, $userId2],
    'Title',
    'Message',
    ['url' => '/dashboard'] // Additional data
);

// Send to all users
$pushService->sendToAll('Title', 'Message');

// Send to users with specific tags
$pushService->sendToTags(
    ['role' => 'admin'],
    'Admin Alert',
    'Important message for admins'
);
```

### User Preferences

```php
// Update user notification preferences
$preferences = [
    'email_invoice' => true,
    'email_payment' => true,
    'email_reminder' => true,
    'email_stock' => true,
    'push_invoice' => true,
    'push_payment' => true,
    'push_reminder' => false, // User disabled reminder push
    'push_stock' => true,
    'push_order' => true
];

$manager->updateUserPreferences($userId, $preferences);

// Get notification statistics
$stats = $manager->getStatistics($userId);
```

## Database

### user_notification_preferences Table

Stores user preferences for each notification type:

```sql
CREATE TABLE user_notification_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,

    -- Email preferences
    email_invoice BOOLEAN DEFAULT TRUE,
    email_payment BOOLEAN DEFAULT TRUE,
    email_reminder BOOLEAN DEFAULT TRUE,
    email_stock BOOLEAN DEFAULT TRUE,

    -- Push preferences
    push_invoice BOOLEAN DEFAULT TRUE,
    push_payment BOOLEAN DEFAULT TRUE,
    push_reminder BOOLEAN DEFAULT TRUE,
    push_stock BOOLEAN DEFAULT TRUE,
    push_order BOOLEAN DEFAULT TRUE,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## Testing

Run the test script to verify the integration:

```bash
php public/test-notifications.php
```

This will:
- Check configuration status
- Test all 8 notification types
- Create user preferences
- Display statistics

## Frontend Implementation

### 1. Request Permission

```javascript
// Request push notification permission
OneSignal.push(function() {
  OneSignal.showNativePrompt();
});
```

### 2. Tag Users

Tag users with their ID for targeted notifications:

```javascript
OneSignal.push(function() {
  OneSignal.sendTag("user_id", "123");
  OneSignal.sendTag("role", "admin");
});
```

### 3. Handle Notification Clicks

```javascript
OneSignal.push(function() {
  OneSignal.on('notificationDisplay', function(event) {
    console.log('Notification displayed:', event);
  });

  OneSignal.on('notificationClick', function(event) {
    console.log('Notification clicked:', event);

    // Redirect to specific page if URL is provided
    if (event.data && event.data.url) {
      window.location.href = event.data.url;
    }
  });
});
```

## Architecture

```
NotificationManager (Unified Interface)
├── EmailService (PHPMailer)
│   ├── Queue System
│   ├── Email Templates
│   └── SMTP Delivery
└── NotificationService (OneSignal)
    ├── Push to Users
    ├── Push to All
    └── Tag-based Targeting
```

## Best Practices

1. **Always check user preferences** before sending notifications
2. **Use appropriate notification types** (don't spam users)
3. **Provide opt-out options** in user settings
4. **Test on staging** before pushing to production
5. **Monitor delivery rates** in OneSignal dashboard
6. **Keep notification messages short** and actionable
7. **Include deep links** to relevant pages

## Troubleshooting

### Notifications not sending?

1. Check OneSignal dashboard for errors
2. Verify `ONESIGNAL_ENABLED=true` in .env
3. Check App ID and REST API Key are correct
4. Ensure user has subscribed to push notifications
5. Check browser console for errors

### User not receiving notifications?

1. Verify user has granted browser permission
2. Check if user's preferences allow this notification type
3. Ensure user has active push subscription
4. Check OneSignal dashboard > Audience > All Users

### Testing on localhost?

Set `allowLocalhostAsSecureOrigin: true` in OneSignal init config.

## Resources

- [OneSignal Documentation](https://documentation.onesignal.com/)
- [OneSignal PHP SDK](https://github.com/OneSignal/onesignal-php-api)
- [Web Push Setup Guide](https://documentation.onesignal.com/docs/web-push-quickstart)

## Support

For issues related to:
- **OnMuhasebe Integration**: Create an issue in the project repository
- **OneSignal Service**: Visit [OneSignal Support](https://onesignal.com/support)

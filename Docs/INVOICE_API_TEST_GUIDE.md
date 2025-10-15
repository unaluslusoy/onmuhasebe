# Invoice API Manual Test Guide

## Base URL
```
http://localhost/onmuhasebe/public/api
```

## 1. Authentication (Login)

### Request
**POST** `/auth/login`

**Headers:**
```
Content-Type: application/json
```

**Body:**
```json
{
  "email": "admin@example.com",
  "password": "password123"
}
```

### Expected Response (200 OK)
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {...},
    "tokens": {
      "access_token": "eyJ0eXAiOiJKV1QiLCJh...",
      "refresh_token": "..."
    }
  }
}
```

**⚠️ IMPORTANT:** Copy the `access_token` - you'll need it for all subsequent requests!

---

## 2. Create Session (Required for Invoice Operations)

### Request
**POST** `/auth/create-session`

**Headers:**
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN_HERE
```

**Body:**
```json
{}
```

### Expected Response (200 OK)
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "message": "Session created successfully"
  }
}
```

---

## 3. Create Sales Invoice

### Request
**POST** `/invoices`

**Headers:**
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN_HERE
```

**Body:**
```json
{
  "invoice_type": "sales",
  "invoice_category": "normal",
  "invoice_date": "2025-10-03",
  "due_date": "2025-11-02",
  "customer_name": "Test Müşteri A.Ş.",
  "customer_email": "test@musteri.com",
  "customer_phone": "0555 123 4567",
  "customer_tax_number": "1234567890",
  "customer_tax_office": "Kadıköy",
  "customer_address": "Test Mahallesi Test Sokak No:1",
  "customer_city": "İstanbul",
  "customer_district": "Kadıköy",
  "discount_type": "percentage",
  "discount_value": 5,
  "currency": "TRY",
  "exchange_rate": 1.0,
  "notes": "Test faturası",
  "is_draft": false,
  "items": [
    {
      "item_type": "product",
      "item_code": "PROD001",
      "item_name": "Test Ürün 1",
      "description": "Test ürün açıklaması",
      "quantity": 10,
      "unit": "Adet",
      "unit_price": 100.00,
      "discount_type": "percentage",
      "discount_value": 0,
      "tax_rate": 20
    },
    {
      "item_type": "product",
      "item_code": "PROD002",
      "item_name": "Test Ürün 2",
      "description": "İkinci test ürünü",
      "quantity": 5,
      "unit": "Adet",
      "unit_price": 200.00,
      "discount_type": "fixed",
      "discount_value": 50,
      "tax_rate": 20
    }
  ]
}
```

### Expected Response (201 Created)
```json
{
  "success": true,
  "message": "Fatura başarıyla oluşturuldu",
  "data": {
    "id": 5,
    "invoice_number": "SF-202510-0004",
    "invoice_type": "sales",
    "total": 1710,
    "payment_status": "unpaid",
    "items": [...]
  }
}
```

**💡 Note:** Copy the `id` from response (e.g., `5`) for next tests

---

## 4. Get Invoice Details

### Request
**GET** `/invoices/{id}`

Example: **GET** `/invoices/5`

**Headers:**
```
Authorization: Bearer YOUR_ACCESS_TOKEN_HERE
```

### Expected Response (200 OK)
```json
{
  "success": true,
  "message": "Fatura başarıyla getirildi",
  "data": {
    "id": 5,
    "invoice_number": "SF-202510-0004",
    "customer_name": "Test Müşteri A.Ş.",
    "total": 1710,
    "items": [...],
    "payments": []
  }
}
```

---

## 5. List All Invoices

### Request
**GET** `/invoices?page=1&per_page=10`

**Headers:**
```
Authorization: Bearer YOUR_ACCESS_TOKEN_HERE
```

### Expected Response (200 OK)
```json
{
  "success": true,
  "message": "Faturalar başarıyla listelendi",
  "data": [...],
  "pagination": {
    "total": 5,
    "per_page": 10,
    "current_page": 1,
    "last_page": 1
  }
}
```

---

## 6. Filter Invoices

### Sales Invoices Only
**GET** `/invoices?invoice_type=sales`

### Unpaid Invoices
**GET** `/invoices?payment_status=unpaid`

### Search by Name
**GET** `/invoices?search=Test`

### Date Range
**GET** `/invoices?date_from=2025-10-01&date_to=2025-10-31`

---

## 7. Update Invoice

### Request
**PUT** `/invoices/{id}`

**Headers:**
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN_HERE
```

**Body:**
```json
{
  "notes": "Updated test notes",
  "internal_notes": "Internal note",
  "discount_value": 10
}
```

### Expected Response (200 OK)
```json
{
  "success": true,
  "message": "Fatura başarıyla güncellendi",
  "data": {...}
}
```

---

## 8. Approve Invoice

### Request
**POST** `/invoices/{id}/approve`

**Headers:**
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN_HERE
```

**Body:**
```json
{}
```

### Expected Response (200 OK)
```json
{
  "success": true,
  "message": "Fatura başarıyla onaylandı",
  "data": {
    "is_approved": true,
    "approved_at": "2025-10-03 19:00:00"
  }
}
```

---

## 9. Lock Invoice

### Request
**POST** `/invoices/{id}/lock`

**Headers:**
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN_HERE
```

**Body:**
```json
{
  "locked": true
}
```

### Expected Response (200 OK)
```json
{
  "success": true,
  "message": "Fatura kilitlendi",
  "data": {
    "is_locked": true
  }
}
```

---

## 10. Record Payment (Partial)

### Request
**POST** `/invoices/{id}/payments`

**Headers:**
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN_HERE
```

**Body:**
```json
{
  "payment_date": "2025-10-03",
  "amount": 500.00,
  "payment_method": "bank_transfer",
  "transaction_reference": "TRX123456",
  "notes": "İlk ödeme - 500 TL"
}
```

### Expected Response (201 Created)
```json
{
  "success": true,
  "message": "Ödeme başarıyla kaydedildi",
  "data": {
    "payment_status": "partial",
    "paid_amount": 500,
    "remaining_amount": 1210
  }
}
```

---

## 11. Get Invoice Payments

### Request
**GET** `/invoices/{id}/payments`

**Headers:**
```
Authorization: Bearer YOUR_ACCESS_TOKEN_HERE
```

### Expected Response (200 OK)
```json
{
  "success": true,
  "message": "Ödemeler başarıyla listelendi",
  "data": [
    {
      "id": 1,
      "amount": 500,
      "payment_date": "2025-10-03",
      "payment_method": "bank_transfer"
    }
  ]
}
```

---

## 12. Get Statistics

### Request
**GET** `/invoices/statistics`

**Headers:**
```
Authorization: Bearer YOUR_ACCESS_TOKEN_HERE
```

### Optional Query Parameters
- `?date_from=2025-10-01`
- `?date_to=2025-10-31`

### Expected Response (200 OK)
```json
{
  "success": true,
  "message": "İstatistikler başarıyla getirildi",
  "data": [
    {
      "invoice_type": "sales",
      "payment_status": "unpaid",
      "count": 3,
      "total_amount": 5130,
      "paid_amount": 500,
      "remaining_amount": 4630
    }
  ]
}
```

---

## 13. Get Monthly Summary

### Request
**GET** `/invoices/monthly-summary?year=2025&month=10`

**Headers:**
```
Authorization: Bearer YOUR_ACCESS_TOKEN_HERE
```

### Expected Response (200 OK)
```json
{
  "success": true,
  "message": "Aylık özet başarıyla getirildi",
  "data": {
    "year": 2025,
    "month": 10,
    "summary": [
      {
        "invoice_type": "sales",
        "count": 4,
        "total": 5130,
        "paid": 500,
        "remaining": 4630
      }
    ]
  }
}
```

---

## 14. Get Overdue Invoices

### Request
**GET** `/invoices/overdue`

**Headers:**
```
Authorization: Bearer YOUR_ACCESS_TOKEN_HERE
```

### Expected Response (200 OK)
```json
{
  "success": true,
  "message": "Vadesi geçmiş faturalar listelendi",
  "data": []
}
```

---

## 15. Create Draft Invoice

### Request
**POST** `/invoices`

**Body:**
```json
{
  "invoice_type": "sales",
  "invoice_date": "2025-10-03",
  "due_date": "2025-11-02",
  "customer_name": "Draft Customer",
  "is_draft": true,
  "items": [
    {
      "item_type": "product",
      "item_name": "Draft Item",
      "quantity": 1,
      "unit_price": 100,
      "tax_rate": 20
    }
  ]
}
```

---

## 16. Purchase Invoice

### Request
**POST** `/invoices`

**Body:**
```json
{
  "invoice_type": "purchase",
  "invoice_date": "2025-10-03",
  "due_date": "2025-11-02",
  "customer_name": "Tedarikçi A.Ş.",
  "customer_tax_number": "9876543210",
  "items": [
    {
      "item_type": "product",
      "item_name": "Satın Alınan Ürün",
      "quantity": 20,
      "unit_price": 50,
      "tax_rate": 20
    }
  ]
}
```

---

## 17. Cancel Invoice

### Request
**POST** `/invoices/{id}/cancel`

**Body:**
```json
{
  "reason": "Test amaçlı iptal"
}
```

---

## 18. Create Recurring Invoice

### Request
**POST** `/invoices`

**Body:**
```json
{
  "invoice_type": "sales",
  "invoice_date": "2025-10-03",
  "due_date": "2025-11-02",
  "customer_name": "Recurring Customer",
  "is_recurring": true,
  "recurring_frequency": "monthly",
  "recurring_interval": 1,
  "recurring_start_date": "2025-10-03",
  "recurring_end_date": "2026-10-03",
  "items": [
    {
      "item_type": "service",
      "item_name": "Monthly Subscription",
      "quantity": 1,
      "unit_price": 1000,
      "tax_rate": 20
    }
  ]
}
```

---

## 19. Get Recurring Invoices

### Request
**GET** `/invoices/recurring`

**Headers:**
```
Authorization: Bearer YOUR_ACCESS_TOKEN_HERE
```

---

## 20. Delete Invoice

### Request
**DELETE** `/invoices/{id}`

**Headers:**
```
Authorization: Bearer YOUR_ACCESS_TOKEN_HERE
```

**⚠️ Note:** Only drafts and unpaid invoices can be deleted

---

## Common Error Responses

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Geçersiz oturum"
}
```
**Solution:** Login again and create session

### 400 Bad Request
```json
{
  "success": false,
  "message": "Validasyon hatası",
  "errors": [...]
}
```
**Solution:** Check required fields

### 404 Not Found
```json
{
  "success": false,
  "message": "Fatura bulunamadı"
}
```
**Solution:** Check invoice ID

---

## Test Checklist

- [ ] 1. Login successful
- [ ] 2. Session created
- [ ] 3. Sales invoice created
- [ ] 4. Invoice details retrieved
- [ ] 5. Invoice list retrieved
- [ ] 6. Invoice filtered by type
- [ ] 7. Invoice updated
- [ ] 8. Invoice approved
- [ ] 9. Invoice locked
- [ ] 10. Payment recorded
- [ ] 11. Payments listed
- [ ] 12. Statistics retrieved
- [ ] 13. Monthly summary retrieved
- [ ] 14. Draft created
- [ ] 15. Purchase invoice created
- [ ] 16. Invoice cancelled
- [ ] 17. Recurring invoice created
- [ ] 18. Invoice deleted

---

## Tips

1. **Save Token:** After login, save the access_token for all requests
2. **Create Session:** Always create session after login for invoice operations
3. **Check Response:** Each response has `success`, `message`, and `data` fields
4. **Test Order:** Follow the order above for best results
5. **Database Check:** Use MySQL to verify data:
   ```sql
   SELECT * FROM invoices ORDER BY id DESC LIMIT 5;
   SELECT * FROM invoice_items WHERE invoice_id = YOUR_INVOICE_ID;
   SELECT * FROM invoice_payments WHERE invoice_id = YOUR_INVOICE_ID;
   ```

# Modül 7: Fatura Yönetimi

## 📋 Modül Özeti

Satış ve alış faturalarının yönetildiği, e-Fatura entegrasyonlu, kapsamlı faturalama modülü.

### Özellikler
- ✅ Satış ve alış faturaları
- ✅ İade faturaları
- ✅ e-Fatura / e-Arşiv
- ✅ Vade takibi
- ✅ Ödeme planı
- ✅ Çoklu para birimi
- ✅ Otomatik KDV hesaplama
- ✅ Tevkifat hesaplama
- ✅ İrsaliyeli/irsaliyesiz fatura
- ✅ Yinelenen faturalar
- ✅ PDF export ve yazdırma
- ✅ E-posta gönderimi
- ✅ Ödeme linki oluşturma

---

## 🗄️ Veritabanı Tabloları

### faturalar (Ana Tablo)
```sql
CREATE TABLE faturalar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cari_id INT NOT NULL,
    
    -- Fatura bilgileri
    fatura_no VARCHAR(50) NOT NULL,
    fatura_uuid VARCHAR(36) UNIQUE COMMENT 'e-Fatura UUID',
    fatura_type ENUM('alis', 'satis', 'iade_alis', 'iade_satis') NOT NULL,
    fatura_category ENUM('normal', 'proforma', 'efatura', 'earsiv', 'esm', 'eihracat') DEFAULT 'normal',
    fatura_date DATE NOT NULL,
    due_date DATE COMMENT 'Vade tarihi',
    
    -- Depo
    warehouse_id INT,
    
    -- Müşteri bilgileri (snapshot)
    customer_name VARCHAR(200),
    customer_address TEXT,
    customer_tax_office VARCHAR(100),
    customer_tax_number VARCHAR(20),
    customer_email VARCHAR(100),
    customer_phone VARCHAR(20),
    
    -- Tutarlar
    subtotal DECIMAL(15,2) NOT NULL COMMENT 'Ara toplam',
    discount_amount DECIMAL(15,2) DEFAULT 0.00 COMMENT 'İskonto tutarı',
    total_amount DECIMAL(15,2) NOT NULL COMMENT 'İskonto sonrası toplam',
    kdv_amount DECIMAL(15,2) DEFAULT 0.00 COMMENT 'KDV tutarı',
    tevkifat_amount DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Stopaj/tevkifat',
    grand_total DECIMAL(15,2) NOT NULL COMMENT 'Genel toplam',
    
    -- Para birimi
    currency ENUM('TRY', 'USD', 'EUR', 'GBP') DEFAULT 'TRY',
    exchange_rate DECIMAL(10,4) DEFAULT 1.0000,
    
    -- Ödeme durumu
    payment_status ENUM('odenmedi', 'kismi', 'odendi', 'vadeli', 'gecikti') DEFAULT 'odenmedi',
    paid_amount DECIMAL(15,2) DEFAULT 0.00,
    remaining_amount DECIMAL(15,2) GENERATED ALWAYS AS (grand_total - paid_amount) STORED,
    
    -- e-Fatura bilgileri
    efatura_status ENUM('beklemede', 'gonderildi', 'teslim_edildi', 'kabul', 'red', 'timeout') DEFAULT NULL,
    efatura_sent_date TIMESTAMP NULL,
    efatura_response_date TIMESTAMP NULL,
    efatura_response_code VARCHAR(10),
    efatura_response_desc TEXT,
    efatura_gb_id VARCHAR(100) COMMENT 'GİB entegratör ID',
    
    -- İrsaliye
    irsaliye_no VARCHAR(50),
    irsaliye_date DATE,
    irsaliye_id INT,
    
    -- Sipariş
    siparis_no VARCHAR(50),
    
    -- Notlar
    notes TEXT COMMENT 'Müşteri görecek',
    internal_notes TEXT COMMENT 'Sadece bizim göreceğimiz',
    
    -- Etiketler
    tags TEXT COMMENT 'JSON array',
    
    -- Dosya
    file_path VARCHAR(255) COMMENT 'PDF dosya yolu',
    xml_path VARCHAR(255) COMMENT 'e-Fatura XML',
    
    -- Yinelenen fatura
    is_recurring BOOLEAN DEFAULT FALSE,
    recurring_frequency ENUM('gunluk', 'haftalik', 'aylik', 'yillik') DEFAULT NULL,
    recurring_end_date DATE,
    recurring_last_created DATE,
    parent_invoice_id INT COMMENT 'Yinelenen faturanın ana fatura ID',
    
    -- İptal/İade
    cancelled_at TIMESTAMP NULL,
    cancelled_by INT,
    cancellation_reason TEXT,
    original_invoice_id INT COMMENT 'İade faturası ise orijinal fatura',
    
    -- Onay
    approved_by INT,
    approved_at TIMESTAMP NULL,
    
    -- Durum
    is_draft BOOLEAN DEFAULT FALSE,
    is_locked BOOLEAN DEFAULT FALSE COMMENT 'Muhasebeci kilitledi mi?',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (cari_id) REFERENCES cari_accounts(id) ON DELETE RESTRICT,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL,
    FOREIGN KEY (irsaliye_id) REFERENCES irsaliyeler(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_invoice_id) REFERENCES faturalar(id) ON DELETE SET NULL,
    FOREIGN KEY (original_invoice_id) REFERENCES faturalar(id) ON DELETE SET NULL,
    FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_fatura_no (fatura_no),
    INDEX idx_fatura_uuid (fatura_uuid),
    INDEX idx_fatura_date (fatura_date),
    INDEX idx_due_date (due_date),
    INDEX idx_payment_status (payment_status),
    INDEX idx_fatura_type (fatura_type),
    INDEX idx_efatura_status (efatura_status),
    INDEX idx_cari_id (cari_id),
    UNIQUE KEY unique_fatura_no (user_id, fatura_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### fatura_items (Fatura Kalemleri)
```sql
CREATE TABLE fatura_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fatura_id INT NOT NULL,
    product_id INT,
    
    -- Sıra
    line_no INT DEFAULT 0,
    
    -- Ürün bilgileri
    description VARCHAR(500) NOT NULL,
    product_code VARCHAR(50),
    barcode VARCHAR(100),
    
    -- Miktar
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20) DEFAULT 'adet',
    
    -- Fiyatlandırma
    unit_price DECIMAL(15,2) NOT NULL,
    discount_rate DECIMAL(5,2) DEFAULT 0.00,
    discount_amount DECIMAL(15,2) DEFAULT 0.00,
    net_unit_price DECIMAL(15,2) GENERATED ALWAYS AS (unit_price - discount_amount) STORED,
    
    -- KDV
    kdv_rate DECIMAL(5,2) DEFAULT 18.00,
    kdv_amount DECIMAL(15,2) NOT NULL,
    kdv_exception_code VARCHAR(10) COMMENT 'KDV istisna kodu',
    kdv_exception_reason VARCHAR(255),
    
    -- Tevkifat
    tevkifat_rate DECIMAL(5,2) DEFAULT 0.00,
    tevkifat_amount DECIMAL(15,2) DEFAULT 0.00,
    
    -- Toplam
    subtotal DECIMAL(15,2) GENERATED ALWAYS AS (quantity * net_unit_price) STORED,
    total_amount DECIMAL(15,2) NOT NULL COMMENT 'KDV dahil toplam',
    
    -- Stok hareketi
    stock_movement_id INT COMMENT 'İlişkili stok hareketi',
    
    -- e-Fatura için
    gtip_code VARCHAR(20) COMMENT 'Gümrük Tarife İstatistik Pozisyonu',
    
    notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (fatura_id) REFERENCES faturalar(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    FOREIGN KEY (stock_movement_id) REFERENCES stock_movements(id) ON DELETE SET NULL,
    
    INDEX idx_fatura_id (fatura_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### fatura_payments (Fatura Ödemeleri)
```sql
CREATE TABLE fatura_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fatura_id INT NOT NULL,
    payment_id INT NOT NULL,
    
    amount DECIMAL(15,2) NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (fatura_id) REFERENCES faturalar(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_fatura_payment (fatura_id, payment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### fatura_history (Fatura Değişiklik Geçmişi)
```sql
CREATE TABLE fatura_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fatura_id INT NOT NULL,
    user_id INT NOT NULL,
    
    action ENUM('created', 'updated', 'sent', 'paid', 'cancelled', 'approved') NOT NULL,
    old_values JSON,
    new_values JSON,
    description TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (fatura_id) REFERENCES faturalar(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_fatura_id (fatura_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🔌 API Endpoints

```
# CRUD İşlemleri
GET    /api/faturalar                      - Fatura listesi (filtreleme, sayfalama)
GET    /api/faturalar/{id}                 - Fatura detayı
POST   /api/faturalar                      - Yeni fatura
PUT    /api/faturalar/{id}                 - Fatura güncelle
DELETE /api/faturalar/{id}                 - Fatura sil (iptal et)

# Durum Yönetimi
PUT    /api/faturalar/{id}/approve         - Onayla
POST   /api/faturalar/{id}/cancel          - İptal et
POST   /api/faturalar/{id}/lock            - Kilitle

# Ödeme
POST   /api/faturalar/{id}/payment         - Ödeme kaydet
GET    /api/faturalar/{id}/payments        - Ödemeleri listele
DELETE /api/faturalar/{id}/payments/{paymentId} - Ödeme sil

# e-Fatura
POST   /api/faturalar/{id}/efatura/send    - e-Fatura gönder
GET    /api/faturalar/{id}/efatura/status  - e-Fatura durumu
POST   /api/faturalar/{id}/earsiv/send     - e-Arşiv gönder

# Dosya İşlemleri
GET    /api/faturalar/{id}/pdf             - PDF indir
POST   /api/faturalar/{id}/email           - E-posta gönder
POST   /api/faturalar/{id}/upload          - Dosya yükle (gelen faturalar)
GET    /api/faturalar/{id}/xml             - e-Fatura XML

# Yinelenen Faturalar
GET    /api/faturalar/recurring            - Yinelenen faturalar
POST   /api/faturalar/{id}/recurring/stop  - Yinelemeyi durdur
POST   /api/faturalar/recurring/process    - Yinelenen faturaları oluştur (cron)

# Raporlar ve Sorgular
GET    /api/faturalar/vadeli               - Vadeli faturalar
GET    /api/faturalar/geciken              - Vadesi geçenler
GET    /api/faturalar/odenmemis            - Ödenmemiş faturalar
GET    /api/faturalar/bugun-vade           - Bugün vadesi dolanlar
GET    /api/faturalar/stats                - Fatura istatistikleri
GET    /api/faturalar/monthly-summary      - Aylık özet

# İade
POST   /api/faturalar/{id}/refund          - İade faturası oluştur

# Toplu İşlemler
POST   /api/faturalar/bulk-approve         - Toplu onaylama
POST   /api/faturalar/bulk-send            - Toplu e-posta gönderimi
```

---

## 💻 Backend Implementasyonu

### Fatura Model

```php
<?php
namespace App\Models;

class Fatura {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Fatura listesi
     */
    public function getList($userId, $filters = [], $page = 1, $perPage = 50) {
        $offset = ($page - 1) * $perPage;
        $where = ['f.user_id = ?'];
        $params = [$userId];
        
        // Tip filtresi
        if (!empty($filters['fatura_type'])) {
            $where[] = 'f.fatura_type = ?';
            $params[] = $filters['fatura_type'];
        }
        
        // Kategori filtresi
        if (!empty($filters['fatura_category'])) {
            $where[] = 'f.fatura_category = ?';
            $params[] = $filters['fatura_category'];
        }
        
        // Ödeme durumu
        if (!empty($filters['payment_status'])) {
            $where[] = 'f.payment_status = ?';
            $params[] = $filters['payment_status'];
        }
        
        // Cari filtresi
        if (!empty($filters['cari_id'])) {
            $where[] = 'f.cari_id = ?';
            $params[] = $filters['cari_id'];
        }
        
        // Tarih aralığı
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $where[] = 'f.fatura_date BETWEEN ? AND ?';
            $params[] = $filters['start_date'];
            $params[] = $filters['end_date'];
        }
        
        // Arama
        if (!empty($filters['search'])) {
            $where[] = '(f.fatura_no LIKE ? OR f.customer_name LIKE ? OR c.cari_name LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        // Taslak hariç
        if (!isset($filters['include_draft']) || !$filters['include_draft']) {
            $where[] = 'f.is_draft = FALSE';
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Toplam kayıt
        $countSql = "SELECT COUNT(*) as total FROM faturalar f WHERE $whereClause";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];
        
        // Liste
        $sql = "SELECT f.*, 
                c.cari_name, c.cari_code,
                (SELECT COUNT(*) FROM fatura_items WHERE fatura_id = f.id) as item_count
                FROM faturalar f
                LEFT JOIN cari_accounts c ON f.cari_id = c.id
                WHERE $whereClause
                ORDER BY f.fatura_date DESC, f.id DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * ID ile fatura getir
     */
    public function findById($id, $userId) {
        $sql = "SELECT f.*, 
                c.cari_name, c.cari_code, c.email as cari_email,
                w.warehouse_name
                FROM faturalar f
                LEFT JOIN cari_accounts c ON f.cari_id = c.id
                LEFT JOIN warehouses w ON f.warehouse_id = w.id
                WHERE f.id = ? AND f.user_id = ?
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $userId]);
        
        $fatura = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($fatura) {
            // Kalemleri getir
            $fatura['items'] = $this->getItems($id);
            
            // Ödemeleri getir
            $fatura['payments'] = $this->getPayments($id);
        }
        
        return $fatura;
    }
    
    /**
     * Fatura kalemlerini getir
     */
    public function getItems($faturaId) {
        $sql = "SELECT fi.*, p.product_name, p.current_stock
                FROM fatura_items fi
                LEFT JOIN products p ON fi.product_id = p.id
                WHERE fi.fatura_id = ?
                ORDER BY fi.line_no ASC, fi.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$faturaId]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Fatura ödemelerini getir
     */
    public function getPayments($faturaId) {
        $sql = "SELECT fp.*, p.payment_date, p.amount as payment_amount, 
                p.payment_method, p.notes
                FROM fatura_payments fp
                JOIN payments p ON fp.payment_id = p.id
                WHERE fp.fatura_id = ?
                ORDER BY p.payment_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$faturaId]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Yeni fatura oluştur
     */
    public function create($userId, $data) {
        // Fatura numarası oluştur
        if (empty($data['fatura_no'])) {
            $data['fatura_no'] = $this->generateFaturaNo($userId, $data['fatura_type']);
        }
        
        // UUID oluştur (e-Fatura için)
        if (empty($data['fatura_uuid'])) {
            $data['fatura_uuid'] = $this->generateUUID();
        }
        
        // Cari bilgilerini al (snapshot)
        $cariModel = new \App\Models\CariAccount($this->db);
        $cari = $cariModel->findById($data['cari_id'], $userId);
        
        // Vade tarihi hesapla
        $dueDate = null;
        if (!empty($cari['payment_term_days'])) {
            $dueDate = date('Y-m-d', strtotime("+{$cari['payment_term_days']} days", strtotime($data['fatura_date'])));
        } elseif (!empty($data['due_date'])) {
            $dueDate = $data['due_date'];
        }
        
        $sql = "INSERT INTO faturalar (
            user_id, cari_id, fatura_no, fatura_uuid,
            fatura_type, fatura_category, fatura_date, due_date,
            warehouse_id,
            customer_name, customer_address, customer_tax_office, customer_tax_number,
            customer_email, customer_phone,
            subtotal, discount_amount, total_amount, kdv_amount, tevkifat_amount, grand_total,
            currency, exchange_rate,
            irsaliye_no, irsaliye_date,
            notes, internal_notes, tags,
            is_draft
        ) VALUES (
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?,
            ?, ?, ?, ?,
            ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?,
            ?, ?,
            ?, ?, ?,
            ?
        )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $userId,
            $data['cari_id'],
            $data['fatura_no'],
            $data['fatura_uuid'],
            $data['fatura_type'],
            $data['fatura_category'] ?? 'normal',
            $data['fatura_date'] ?? date('Y-m-d'),
            $dueDate,
            $data['warehouse_id'] ?? null,
            $cari['cari_name'],
            $cari['address'],
            $cari['tax_office'],
            $cari['tax_number'],
            $cari['email'],
            $cari['phone'],
            $data['subtotal'],
            $data['discount_amount'] ?? 0,
            $data['total_amount'],
            $data['kdv_amount'],
            $data['tevkifat_amount'] ?? 0,
            $data['grand_total'],
            $data['currency'] ?? 'TRY',
            $data['exchange_rate'] ?? 1.0000,
            $data['irsaliye_no'] ?? null,
            $data['irsaliye_date'] ?? null,
            $data['notes'] ?? null,
            $data['internal_notes'] ?? null,
            isset($data['tags']) ? json_encode($data['tags']) : null,
            $data['is_draft'] ?? false
        ]);
        
        $faturaId = $this->db->lastInsertId();
        
        // Kalemleri ekle
        if (!empty($data['items'])) {
            $this->addItems($faturaId, $data['items']);
        }
        
        // Stok hareketleri oluştur (satış faturası ise)
        if ($data['fatura_type'] === 'satis' && !$data['is_draft']) {
            $this->createStockMovements($faturaId, $userId, $data);
        }
        
        // Cari hareket oluştur
        if (!$data['is_draft']) {
            $this->createCariTransaction($faturaId, $userId, $data);
        }
        
        // Geçmişe kaydet
        $this->logHistory($faturaId, $userId, 'created', null, $data);
        
        return $faturaId;
    }
    
    /**
     * Fatura kalemlerini ekle
     */
    public function addItems($faturaId, $items) {
        $sql = "INSERT INTO fatura_items (
            fatura_id, product_id, line_no,
            description, product_code, barcode,
            quantity, unit,
            unit_price, discount_rate, discount_amount,
            kdv_rate, kdv_amount, tevkifat_rate, tevkifat_amount,
            total_amount, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($items as $index => $item) {
            $stmt->execute([
                $faturaId,
                $item['product_id'] ?? null,
                $index + 1,
                $item['description'],
                $item['product_code'] ?? null,
                $item['barcode'] ?? null,
                $item['quantity'],
                $item['unit'] ?? 'adet',
                $item['unit_price'],
                $item['discount_rate'] ?? 0,
                $item['discount_amount'] ?? 0,
                $item['kdv_rate'] ?? 18,
                $item['kdv_amount'],
                $item['tevkifat_rate'] ?? 0,
                $item['tevkifat_amount'] ?? 0,
                $item['total_amount'],
                $item['notes'] ?? null
            ]);
        }
    }
    
    /**
     * Stok hareketlerini oluştur
     */
    private function createStockMovements($faturaId, $userId, $data) {
        $movementModel = new \App\Models\StockMovement($this->db);
        
        foreach ($data['items'] as $item) {
            if (!empty($item['product_id'])) {
                $movementId = $movementModel->create($userId, [
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'movement_type' => 'cikis',
                    'movement_date' => $data['fatura_date'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'reference_type' => 'fatura',
                    'reference_id' => $faturaId,
                    'reference_no' => $data['fatura_no']
                ]);
                
                // Stok hareket ID'sini fatura kalemine kaydet
                $this->db->prepare("UPDATE fatura_items SET stock_movement_id = ? WHERE fatura_id = ? AND product_id = ?")
                    ->execute([$movementId, $faturaId, $item['product_id']]);
            }
        }
    }
    
    /**
     * Cari hareket oluştur
     */
    private function createCariTransaction($faturaId, $userId, $data) {
        $cariModel = new \App\Models\CariAccount($this->db);
        
        $transactionType = $data['fatura_type'] === 'satis' ? 'borc' : 'alacak';
        
        $cariModel->createTransaction($userId, $data['cari_id'], [
            'transaction_date' => $data['fatura_date'],
            'transaction_type' => $transactionType,
            'amount' => $data['grand_total'],
            'currency' => $data['currency'] ?? 'TRY',
            'exchange_rate' => $data['exchange_rate'] ?? 1.0000,
            'reference_type' => 'fatura',
            'reference_id' => $faturaId,
            'reference_no' => $data['fatura_no'],
            'description' => 'Fatura No: ' . $data['fatura_no']
        ]);
    }
    
    /**
     * Ödeme durumunu güncelle
     */
    public function updatePaymentStatus($faturaId) {
        $fatura = $this->findById($faturaId, null);
        
        if (!$fatura) return;
        
        $paidAmount = array_sum(array_column($fatura['payments'], 'amount'));
        
        $status = 'odenmedi';
        if ($paidAmount >= $fatura['grand_total']) {
            $status = 'odendi';
        } elseif ($paidAmount > 0) {
            $status = 'kismi';
        } elseif ($fatura['due_date'] && strtotime($fatura['due_date']) < time()) {
            $status = 'gecikti';
        } elseif ($fatura['due_date']) {
            $status = 'vadeli';
        }
        
        $sql = "UPDATE faturalar SET 
                payment_status = ?,
                paid_amount = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$status, $paidAmount, $faturaId]);
    }
    
    /**
     * Faturayı iptal et
     */
    public function cancel($faturaId, $userId, $reason) {
        // Önce faturayı kontrol et
        $fatura = $this->findById($faturaId, $userId);
        
        if (!$fatura) {
            throw new \Exception('Fatura bulunamadı');
        }
        
        if ($fatura['cancelled_at']) {
            throw new \Exception('Fatura zaten iptal edilmiş');
        }
        
        // İptal et
        $sql = "UPDATE faturalar SET 
                cancelled_at = NOW(),
                cancelled_by = ?,
                cancellation_reason = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $reason, $faturaId]);
        
        // Stok hareketlerini geri al
        $this->reverseStockMovements($faturaId);
        
        // Cari hareketini geri al
        $this->reverseCariTransaction($faturaId, $userId);
        
        // Geçmişe kaydet
        $this->logHistory($faturaId, $userId, 'cancelled', $fatura, ['reason' => $reason]);
        
        return true;
    }
    
    /**
     * Stok hareketlerini geri al
     */
    private function reverseStockMovements($faturaId) {
        $items = $this->getItems($faturaId);
        $movementModel = new \App\Models\StockMovement($this->db);
        
        foreach ($items as $item) {
            if ($item['stock_movement_id']) {
                // Ters hareket oluştur
                // İmplementasyon detayı...
            }
        }
    }
    
    /**
     * Fatura numarası oluştur
     */
    private function generateFaturaNo($userId, $type) {
        // Şirket ayarlarından prefix al
        $companyModel = new \App\Models\Company($this->db);
        $company = $companyModel->getDefault($userId);
        $prefix = $company['invoice_prefix'] ?? 'FAT';
        
        $prefix .= date('Ym');
        
        $sql = "SELECT fatura_no FROM faturalar
                WHERE user_id = ? AND fatura_no LIKE ?
                ORDER BY id DESC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $prefix . '%']);
        $last = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($last) {
            $number = (int) substr($last['fatura_no'], -6) + 1;
        } else {
            $number = 1;
        }
        
        return $prefix . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * UUID oluştur
     */
    private function generateUUID() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * Geçmişe kaydet
     */
    private function logHistory($faturaId, $userId, $action, $oldValues, $newValues) {
        $sql = "INSERT INTO fatura_history (fatura_id, user_id, action, old_values, new_values)
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $faturaId,
            $userId,
            $action,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null
        ]);
    }
    
    /**
     * Fatura istatistikleri
     */
    public function getStats($userId, $startDate = null, $endDate = null) {
        $where = 'user_id = ? AND is_draft = FALSE AND cancelled_at IS NULL';
        $params = [$userId];
        
        if ($startDate && $endDate) {
            $where .= ' AND fatura_date BETWEEN ? AND ?';
            $params[] = $startDate;
            $params[] = $endDate;
        }
        
        $sql = "SELECT 
                COUNT(*) as total_faturalar,
                COUNT(CASE WHEN fatura_type = 'satis' THEN 1 END) as satis_count,
                COUNT(CASE WHEN fatura_type = 'alis' THEN 1 END) as alis_count,
                SUM(CASE WHEN fatura_type = 'satis' THEN grand_total ELSE 0 END) as satis_total,
                SUM(CASE WHEN fatura_type = 'alis' THEN grand_total ELSE 0 END) as alis_total,
                SUM(CASE WHEN payment_status = 'odenmedi' THEN grand_total ELSE 0 END) as odenmemis_total,
                COUNT(CASE WHEN payment_status = 'odenmedi' THEN 1 END) as odenmemis_count,
                COUNT(CASE WHEN payment_status = 'gecikti' THEN 1 END) as geciken_count,
                SUM(CASE WHEN fatura_category = 'efatura' THEN 1 ELSE 0 END) as efatura_count
                FROM faturalar
                WHERE $where";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
```

---

## 🎨 Frontend - Fatura Formu (Özet)

```javascript
class FaturaManager {
    constructor() {
        this.items = [];
        this.init();
    }
    
    async loadCari(cariId) {
        const response = await apiClient.get(`/cari/${cariId}`);
        if (response.success) {
            // Cari bilgilerini forma doldur
            document.getElementById('customer_name').textContent = response.data.cari_name;
            document.getElementById('customer_address').textContent = response.data.address;
        }
    }
    
    async searchProduct(query) {
        const response = await apiClient.get(`/products/search?q=${query}`);
        if (response.success) {
            this.renderProductSuggestions(response.data);
        }
    }
    
    addItem(product) {
        const item = {
            product_id: product.id,
            description: product.product_name,
            quantity: 1,
            unit: product.unit,
            unit_price: product.sale_price,
            kdv_rate: product.kdv_rate,
            discount_rate: 0
        };
        
        this.calculateItemTotal(item);
        this.items.push(item);
        this.renderItems();
        this.calculateTotals();
    }
    
    calculateItemTotal(item) {
        // İskonto hesapla
        item.discount_amount = (item.unit_price * item.discount_rate) / 100;
        item.net_price = item.unit_price - item.discount_amount;
        
        // Ara toplam
        item.subtotal = item.net_price * item.quantity;
        
        // KDV hesapla
        item.kdv_amount = (item.subtotal * item.kdv_rate) / 100;
        
        // Toplam
        item.total_amount = item.subtotal + item.kdv_amount;
    }
    
    calculateTotals() {
        const subtotal = this.items.reduce((sum, item) => sum + item.subtotal, 0);
        const kdvTotal = this.items.reduce((sum, item) => sum + item.kdv_amount, 0);
        const grandTotal = subtotal + kdvTotal;
        
        // Formda göster
        document.getElementById('subtotal').value = subtotal.toFixed(2);
        document.getElementById('kdv_amount').value = kdvTotal.toFixed(2);
        document.getElementById('grand_total').value = grandTotal.toFixed(2);
    }
    
    async saveFatura(isDraft = false) {
        const data = {
            cari_id: document.getElementById('cari_id').value,
            fatura_type: document.getElementById('fatura_type').value,
            fatura_date: document.getElementById('fatura_date').value,
            warehouse_id: document.getElementById('warehouse_id').value,
            items: this.items,
            notes: document.getElementById('notes').value,
            is_draft: isDraft
        };
        
        // Toplamları ekle
        this.calculateTotals();
        data.subtotal = this.items.reduce((sum, item) => sum + item.subtotal, 0);
        data.kdv_amount = this.items.reduce((sum, item) => sum + item.kdv_amount, 0);
        data.grand_total = data.subtotal + data.kdv_amount;
        data.total_amount = data.subtotal;
        
        const response = await apiClient.post('/faturalar', data);
        
        if (response.success) {
            alert(isDraft ? 'Taslak kaydedildi' : 'Fatura oluşturuldu');
            window.location.href = `/faturalar/${response.data.id}`;
        }
    }
}
```

---

## 📝 Özet

Bu modül ile:
- ✅ Kapsamlı fatura yönetimi
- ✅ Satış ve alış faturaları
- ✅ Otomatik stok ve cari hareketleri
- ✅ Vade takibi
- ✅ Ödeme yönetimi
- ✅ e-Fatura altyapısı
- ✅ Geçmiş/log takibi
- ✅ İptal ve iade işlemleri

**Sonraki Modül:** e-Fatura Entegrasyonu (GİB API)
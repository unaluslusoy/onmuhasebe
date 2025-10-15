# Modül 6: Teklif ve Proforma Yönetimi

## 📋 Modül Özeti

Satış öncesi teklif ve proforma fatura oluşturma, takip ve faturaya dönüştürme modülü.

### Özellikler
- ✅ Teklif oluşturma ve yönetimi
- ✅ Proforma fatura
- ✅ Faturaya dönüştürme
- ✅ Geçerlilik süresi takibi
- ✅ Kar marjı hesaplama
- ✅ PDF export ve e-posta gönderimi
- ✅ Durum yönetimi (taslak, gönderildi, kabul, red)
- ✅ Revizyon/versiyon takibi
- ✅ Şablonlar

---

## 🗄️ Veritabanı Tablosu

### teklifler
```sql
CREATE TABLE teklifler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cari_id INT NOT NULL,
    
    teklif_no VARCHAR(50) NOT NULL,
    teklif_type ENUM('teklif', 'proforma') DEFAULT 'teklif',
    teklif_date DATE NOT NULL,
    valid_until DATE COMMENT 'Geçerlilik tarihi',
    
    -- Müşteri bilgileri (snapshot)
    customer_name VARCHAR(200),
    customer_address TEXT,
    customer_tax_office VARCHAR(100),
    customer_tax_number VARCHAR(20),
    
    -- İletişim
    contact_person VARCHAR(100),
    contact_phone VARCHAR(20),
    contact_email VARCHAR(100),
    
    -- Tutarlar
    subtotal DECIMAL(15,2) NOT NULL COMMENT 'Ara toplam',
    discount_amount DECIMAL(15,2) DEFAULT 0.00,
    total_amount DECIMAL(15,2) NOT NULL COMMENT 'İskonto sonrası',
    kdv_amount DECIMAL(15,2) DEFAULT 0.00,
    grand_total DECIMAL(15,2) NOT NULL COMMENT 'Genel toplam',
    
    -- Para birimi
    currency ENUM('TRY', 'USD', 'EUR', 'GBP') DEFAULT 'TRY',
    exchange_rate DECIMAL(10,4) DEFAULT 1.0000,
    
    -- Ödeme koşulları
    payment_terms TEXT COMMENT 'Ödeme şartları',
    delivery_terms TEXT COMMENT 'Teslimat şartları',
    delivery_time VARCHAR(100) COMMENT 'Teslimat süresi',
    
    -- Durum
    status ENUM('taslak', 'gonderildi', 'goruldu', 'kabul', 'red', 'iptal', 'suresi_doldu') DEFAULT 'taslak',
    status_note TEXT COMMENT 'Durum notu',
    
    -- Dönüşüm
    converted_to_invoice_id INT COMMENT 'Faturaya dönüştürüldü mü?',
    converted_at TIMESTAMP NULL,
    
    -- İçerik
    notes TEXT COMMENT 'Notlar',
    terms_conditions TEXT COMMENT 'Şartlar ve koşullar',
    footer_text TEXT COMMENT 'Alt bilgi',
    
    -- Etiketler
    tags TEXT COMMENT 'JSON array',
    
    -- Dosya
    file_path VARCHAR(255) COMMENT 'PDF dosya yolu',
    
    -- Takip
    viewed_at TIMESTAMP NULL COMMENT 'Görüntülenme',
    view_count INT DEFAULT 0,
    
    -- Revizyon
    revision_of INT COMMENT 'Hangi teklifin revizyonu',
    revision_number INT DEFAULT 1,
    
    -- Kar marjı
    total_cost DECIMAL(15,2) COMMENT 'Toplam maliyet',
    profit_amount DECIMAL(15,2) COMMENT 'Kar tutarı',
    profit_rate DECIMAL(5,2) COMMENT 'Kar oranı',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (cari_id) REFERENCES cari_accounts(id) ON DELETE RESTRICT,
    FOREIGN KEY (converted_to_invoice_id) REFERENCES faturalar(id) ON DELETE SET NULL,
    FOREIGN KEY (revision_of) REFERENCES teklifler(id) ON DELETE SET NULL,
    
    INDEX idx_teklif_no (teklif_no),
    INDEX idx_teklif_date (teklif_date),
    INDEX idx_status (status),
    INDEX idx_valid_until (valid_until),
    UNIQUE KEY unique_teklif_no (user_id, teklif_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### teklif_items
```sql
CREATE TABLE teklif_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teklif_id INT NOT NULL,
    product_id INT,
    
    -- Sıra
    line_no INT DEFAULT 0,
    
    -- Ürün bilgileri (snapshot)
    description VARCHAR(500) NOT NULL,
    
    -- Miktar ve birim
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
    
    -- Toplam
    total_amount DECIMAL(15,2) NOT NULL,
    
    -- Maliyet (kar hesabı için)
    unit_cost DECIMAL(15,2) COMMENT 'Birim maliyet',
    total_cost DECIMAL(15,2) GENERATED ALWAYS AS (quantity * unit_cost) STORED,
    profit DECIMAL(15,2) GENERATED ALWAYS AS (total_amount - (quantity * unit_cost)) STORED,
    
    -- Stok bilgisi
    current_stock DECIMAL(10,2) COMMENT 'O andaki stok durumu',
    
    notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (teklif_id) REFERENCES teklifler(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    
    INDEX idx_teklif_id (teklif_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### teklif_templates
```sql
CREATE TABLE teklif_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    
    template_name VARCHAR(100) NOT NULL,
    description TEXT,
    
    -- İçerik
    payment_terms TEXT,
    delivery_terms TEXT,
    terms_conditions TEXT,
    footer_text TEXT,
    
    -- Varsayılan ayarlar
    validity_days INT DEFAULT 30,
    
    is_default BOOLEAN DEFAULT FALSE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🔌 API Endpoints

```
# CRUD
GET    /api/teklifler                      - Teklif listesi
GET    /api/teklifler/{id}                 - Teklif detayı
POST   /api/teklifler                      - Yeni teklif
PUT    /api/teklifler/{id}                 - Güncelle
DELETE /api/teklifler/{id}                 - Sil

# Durum Yönetimi
PUT    /api/teklifler/{id}/status          - Durum değiştir
POST   /api/teklifler/{id}/send            - Müşteriye gönder
POST   /api/teklifler/{id}/approve         - Onayla
POST   /api/teklifler/{id}/reject          - Reddet

# Dönüşüm
POST   /api/teklifler/{id}/convert         - Faturaya dönüştür
POST   /api/teklifler/{id}/revise          - Revize et

# Export ve Paylaşım
GET    /api/teklifler/{id}/pdf             - PDF indir
POST   /api/teklifler/{id}/email           - E-posta gönder
GET    /api/teklifler/{id}/preview         - Önizleme

# Şablonlar
GET    /api/teklif-templates               - Şablon listesi
POST   /api/teklif-templates               - Yeni şablon
GET    /api/teklif-templates/{id}          - Şablon detayı
PUT    /api/teklif-templates/{id}          - Şablon güncelle
DELETE /api/teklif-templates/{id}          - Şablon sil

# Raporlar
GET    /api/teklifler/stats                - İstatistikler
GET    /api/teklifler/conversion-rate      - Dönüşüm oranı
GET    /api/teklifler/expiring             - Süresi dolacaklar
```

---

## 💻 Backend Implementasyonu

### Teklif Model

```php
<?php
namespace App\Models;

class Teklif {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Teklif listesi
     */
    public function getList($userId, $filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $where = ['t.user_id = ?'];
        $params = [$userId];
        
        if (!empty($filters['status'])) {
            $where[] = 't.status = ?';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['cari_id'])) {
            $where[] = 't.cari_id = ?';
            $params[] = $filters['cari_id'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = '(t.teklif_no LIKE ? OR t.customer_name LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $where[] = 't.teklif_date BETWEEN ? AND ?';
            $params[] = $filters['start_date'];
            $params[] = $filters['end_date'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT t.*, c.cari_name
                FROM teklifler t
                LEFT JOIN cari_accounts c ON t.cari_id = c.id
                WHERE $whereClause
                ORDER BY t.teklif_date DESC, t.id DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * ID ile teklif getir
     */
    public function findById($id, $userId) {
        $sql = "SELECT t.*, c.cari_name, c.email as cari_email
                FROM teklifler t
                LEFT JOIN cari_accounts c ON t.cari_id = c.id
                WHERE t.id = ? AND t.user_id = ?
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $userId]);
        
        $teklif = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($teklif) {
            // Kalemleri getir
            $teklif['items'] = $this->getItems($id);
        }
        
        return $teklif;
    }
    
    /**
     * Teklif kalemlerini getir
     */
    public function getItems($teklifId) {
        $sql = "SELECT ti.*, p.product_name, p.barcode
                FROM teklif_items ti
                LEFT JOIN products p ON ti.product_id = p.id
                WHERE ti.teklif_id = ?
                ORDER BY ti.line_no ASC, ti.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$teklifId]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Yeni teklif oluştur
     */
    public function create($userId, $data) {
        // Teklif numarası oluştur
        if (empty($data['teklif_no'])) {
            $data['teklif_no'] = $this->generateTeklifNo($userId, $data['teklif_type']);
        }
        
        // Cari bilgilerini al (snapshot)
        $cariModel = new \App\Models\CariAccount($this->db);
        $cari = $cariModel->findById($data['cari_id'], $userId);
        
        // Geçerlilik tarihi
        $validUntil = null;
        if (!empty($data['validity_days'])) {
            $validUntil = date('Y-m-d', strtotime("+{$data['validity_days']} days"));
        }
        
        $sql = "INSERT INTO teklifler (
            user_id, cari_id, teklif_no, teklif_type, teklif_date, valid_until,
            customer_name, customer_address, customer_tax_office, customer_tax_number,
            contact_person, contact_phone, contact_email,
            subtotal, discount_amount, total_amount, kdv_amount, grand_total,
            currency, exchange_rate,
            payment_terms, delivery_terms, delivery_time,
            notes, terms_conditions, footer_text,
            tags
        ) VALUES (
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            ?
        )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $userId,
            $data['cari_id'],
            $data['teklif_no'],
            $data['teklif_type'] ?? 'teklif',
            $data['teklif_date'] ?? date('Y-m-d'),
            $validUntil,
            $cari['cari_name'],
            $cari['address'],
            $cari['tax_office'],
            $cari['tax_number'],
            $data['contact_person'] ?? $cari['contact_person'],
            $data['contact_phone'] ?? $cari['phone'],
            $data['contact_email'] ?? $cari['email'],
            $data['subtotal'],
            $data['discount_amount'] ?? 0,
            $data['total_amount'],
            $data['kdv_amount'],
            $data['grand_total'],
            $data['currency'] ?? 'TRY',
            $data['exchange_rate'] ?? 1.0000,
            $data['payment_terms'] ?? null,
            $data['delivery_terms'] ?? null,
            $data['delivery_time'] ?? null,
            $data['notes'] ?? null,
            $data['terms_conditions'] ?? null,
            $data['footer_text'] ?? null,
            isset($data['tags']) ? json_encode($data['tags']) : null
        ]);
        
        $teklifId = $this->db->lastInsertId();
        
        // Kalemleri ekle
        if (!empty($data['items'])) {
            $this->addItems($teklifId, $data['items']);
        }
        
        // Kar marjını hesapla
        $this->calculateProfit($teklifId);
        
        return $teklifId;
    }
    
    /**
     * Teklif kalemlerini ekle
     */
    public function addItems($teklifId, $items) {
        $sql = "INSERT INTO teklif_items (
            teklif_id, product_id, line_no, description,
            quantity, unit, unit_price, discount_rate, discount_amount,
            kdv_rate, kdv_amount, total_amount,
            unit_cost, current_stock, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($items as $index => $item) {
            // Eğer product_id varsa, maliyet ve stok bilgisini al
            $unitCost = $item['unit_cost'] ?? 0;
            $currentStock = $item['current_stock'] ?? 0;
            
            if (!empty($item['product_id'])) {
                $productModel = new \App\Models\Product($this->db);
                $product = $productModel->findById($item['product_id'], null);
                if ($product) {
                    $unitCost = $product['purchase_price'];
                    $currentStock = $product['current_stock'];
                }
            }
            
            $stmt->execute([
                $teklifId,
                $item['product_id'] ?? null,
                $index + 1,
                $item['description'],
                $item['quantity'],
                $item['unit'] ?? 'adet',
                $item['unit_price'],
                $item['discount_rate'] ?? 0,
                $item['discount_amount'] ?? 0,
                $item['kdv_rate'] ?? 18,
                $item['kdv_amount'],
                $item['total_amount'],
                $unitCost,
                $currentStock,
                $item['notes'] ?? null
            ]);
        }
    }
    
    /**
     * Kar marjını hesapla
     */
    public function calculateProfit($teklifId) {
        $sql = "SELECT 
                SUM(total_cost) as total_cost,
                SUM(profit) as profit_amount
                FROM teklif_items
                WHERE teklif_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$teklifId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $totalCost = $result['total_cost'] ?? 0;
        $profitAmount = $result['profit_amount'] ?? 0;
        
        // Kar oranı hesapla
        $profitRate = 0;
        if ($totalCost > 0) {
            $profitRate = ($profitAmount / $totalCost) * 100;
        }
        
        // Güncelle
        $sql = "UPDATE teklifler SET
                total_cost = ?,
                profit_amount = ?,
                profit_rate = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$totalCost, $profitAmount, $profitRate, $teklifId]);
    }
    
    /**
     * Durumu güncelle
     */
    public function updateStatus($id, $status, $note = null) {
        $sql = "UPDATE teklifler SET status = ?, status_note = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $note, $id]);
    }
    
    /**
     * Faturaya dönüştür
     */
    public function convertToInvoice($teklifId, $userId) {
        $teklif = $this->findById($teklifId, $userId);
        
        if (!$teklif) {
            throw new \Exception('Teklif bulunamadı');
        }
        
        if ($teklif['status'] !== 'kabul') {
            throw new \Exception('Sadece kabul edilmiş teklifler faturaya dönüştürülebilir');
        }
        
        // Fatura oluştur
        $faturaModel = new \App\Models\Fatura($this->db);
        
        $faturaData = [
            'cari_id' => $teklif['cari_id'],
            'fatura_type' => 'satis',
            'fatura_category' => 'normal',
            'fatura_date' => date('Y-m-d'),
            'total_amount' => $teklif['total_amount'],
            'kdv_amount' => $teklif['kdv_amount'],
            'grand_total' => $teklif['grand_total'],
            'currency' => $teklif['currency'],
            'exchange_rate' => $teklif['exchange_rate'],
            'notes' => 'Teklif No: ' . $teklif['teklif_no'] . ($teklif['notes'] ? "\n" . $teklif['notes'] : ''),
            'items' => []
        ];
        
        // Kalemleri ekle
        foreach ($teklif['items'] as $item) {
            $faturaData['items'][] = [
                'product_id' => $item['product_id'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'unit_price' => $item['unit_price'],
                'discount_rate' => $item['discount_rate'],
                'discount_amount' => $item['discount_amount'],
                'kdv_rate' => $item['kdv_rate'],
                'kdv_amount' => $item['kdv_amount'],
                'total_amount' => $item['total_amount']
            ];
        }
        
        $faturaId = $faturaModel->create($userId, $faturaData);
        
        // Teklifi güncelle
        $sql = "UPDATE teklifler SET 
                converted_to_invoice_id = ?,
                converted_at = NOW()
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$faturaId, $teklifId]);
        
        return $faturaId;
    }
    
    /**
     * Revize et
     */
    public function revise($teklifId, $userId) {
        $originalTeklif = $this->findById($teklifId, $userId);
        
        if (!$originalTeklif) {
            throw new \Exception('Teklif bulunamadı');
        }
        
        // Yeni teklif oluştur
        $data = $originalTeklif;
        unset($data['id'], $data['teklif_no'], $data['created_at'], $data['updated_at']);
        
        $data['revision_of'] = $teklifId;
        $data['revision_number'] = $originalTeklif['revision_number'] + 1;
        $data['status'] = 'taslak';
        $data['teklif_no'] = $this->generateTeklifNo($userId, $data['teklif_type'], true);
        
        return $this->create($userId, $data);
    }
    
    /**
     * Teklif numarası oluştur
     */
    private function generateTeklifNo($userId, $type = 'teklif', $isRevision = false) {
        $prefix = $type === 'teklif' ? 'TKL' : 'PRO';
        $prefix .= date('Ym');
        
        $sql = "SELECT teklif_no FROM teklifler
                WHERE user_id = ? AND teklif_no LIKE ?
                ORDER BY id DESC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $prefix . '%']);
        $last = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($last) {
            $number = (int) substr($last['teklif_no'], -4) + 1;
        } else {
            $number = 1;
        }
        
        $teklifNo = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
        
        if ($isRevision) {
            $teklifNo .= '-R';
        }
        
        return $teklifNo;
    }
    
    /**
     * İstatistikler
     */
    public function getStats($userId, $startDate = null, $endDate = null) {
        $where = 'user_id = ?';
        $params = [$userId];
        
        if ($startDate && $endDate) {
            $where .= ' AND teklif_date BETWEEN ? AND ?';
            $params[] = $startDate;
            $params[] = $endDate;
        }
        
        $sql = "SELECT 
                COUNT(*) as total_teklifler,
                COUNT(CASE WHEN status = 'gonderildi' THEN 1 END) as gonderildi,
                COUNT(CASE WHEN status = 'kabul' THEN 1 END) as kabul,
                COUNT(CASE WHEN status = 'red' THEN 1 END) as red,
                COUNT(CASE WHEN converted_to_invoice_id IS NOT NULL THEN 1 END) as converted,
                SUM(grand_total) as total_value,
                SUM(CASE WHEN status = 'kabul' THEN grand_total ELSE 0 END) as accepted_value,
                AVG(profit_rate) as avg_profit_rate
                FROM teklifler
                WHERE $where";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Dönüşüm oranı
        if ($stats['gonderildi'] > 0) {
            $stats['conversion_rate'] = ($stats['kabul'] / $stats['gonderildi']) * 100;
        } else {
            $stats['conversion_rate'] = 0;
        }
        
        return $stats;
    }
}
```

---

## 🎨 Frontend - Teklif Formu (Özet)

```javascript
// Teklif yönetimi için ana JavaScript
class TeklifManager {
    constructor() {
        this.items = [];
        this.init();
    }
    
    init() {
        this.attachEventListeners();
        this.loadCariList();
        this.loadProductList();
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
        
        this.items.push(item);
        this.calculateItemTotal(item);
        this.renderItems();
        this.calculateTotals();
    }
    
    calculateItemTotal(item) {
        item.discount_amount = (item.unit_price * item.discount_rate) / 100;
        item.net_price = item.unit_price - item.discount_amount;
        item.subtotal = item.net_price * item.quantity;
        item.kdv_amount = (item.subtotal * item.kdv_rate) / 100;
        item.total_amount = item.subtotal + item.kdv_amount;
    }
    
    calculateTotals() {
        const subtotal = this.items.reduce((sum, item) => sum + item.subtotal, 0);
        const kdvTotal = this.items.reduce((sum, item) => sum + item.kdv_amount, 0);
        const grandTotal = subtotal + kdvTotal;
        
        document.getElementById('subtotal').textContent = this.formatMoney(subtotal);
        document.getElementById('kdvTotal').textContent = this.formatMoney(kdvTotal);
        document.getElementById('grandTotal').textContent = this.formatMoney(grandTotal);
    }
    
    async saveTeklif() {
        const data = {
            cari_id: document.getElementById('cari_id').value,
            teklif_type: document.getElementById('teklif_type').value,
            teklif_date: document.getElementById('teklif_date').value,
            validity_days: document.getElementById('validity_days').value,
            items: this.items,
            notes: document.getElementById('notes').value
        };
        
        // Toplamları hesapla
        this.calculateTotals();
        data.subtotal = this.items.reduce((sum, item) => sum + item.subtotal, 0);
        data.kdv_amount = this.items.reduce((sum, item) => sum + item.kdv_amount, 0);
        data.grand_total = data.subtotal + data.kdv_amount;
        data.total_amount = data.subtotal;
        
        const response = await apiClient.post('/teklifler', data);
        
        if (response.success) {
            alert('Teklif kaydedildi');
            window.location.href = '/teklifler';
        }
    }
}
```

---

## 📝 Özet

Bu modül ile:
- ✅ Profesyonel teklif yönetimi
- ✅ Kar marjı hesaplama
- ✅ Faturaya otomatik dönüştürme
- ✅ Revizyon takibi
- ✅ Durum yönetimi
- ✅ PDF export

**Sonraki Modül:** Fatura Yönetimi (En kapsamlı modül!)
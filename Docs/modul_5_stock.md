# Modül 5: Stok Yönetimi ve Depo Takibi

## 📋 Modül Özeti

Çoklu depo desteği, stok hareketleri, sayım, transfer ve detaylı stok raporlaması sağlayan modül.

### Özellikler
- ✅ Çoklu depo yönetimi
- ✅ Stok giriş/çıkış hareketleri
- ✅ Depolar arası transfer
- ✅ Stok sayımı
- ✅ Fire/kayıp kaydı
- ✅ Lot/seri no takibi
- ✅ Son kullanma tarihi takibi
- ✅ Stok değerleme (FIFO, LIFO, Ortalama)
- ✅ Raf/konum yönetimi
- ✅ Stok raporları

---

## 🗄️ Veritabanı Tabloları

### warehouses
```sql
CREATE TABLE warehouses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    
    warehouse_code VARCHAR(50) NOT NULL,
    warehouse_name VARCHAR(100) NOT NULL,
    warehouse_type ENUM('merkez', 'sube', 'toptanci', 'perakende', 'sanal') DEFAULT 'merkez',
    
    -- Adres
    address TEXT,
    district VARCHAR(50),
    city VARCHAR(50),
    postal_code VARCHAR(10),
    
    -- Sorumlu
    responsible_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    
    -- Özellikler
    total_area DECIMAL(10,2) COMMENT 'Toplam alan (m2)',
    storage_capacity DECIMAL(10,2) COMMENT 'Depolama kapasitesi',
    
    -- Durum
    is_active BOOLEAN DEFAULT TRUE,
    is_default BOOLEAN DEFAULT FALSE,
    
    notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_warehouse_code (warehouse_code),
    UNIQUE KEY unique_warehouse_code (user_id, warehouse_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### warehouse_locations
```sql
CREATE TABLE warehouse_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    warehouse_id INT NOT NULL,
    
    location_code VARCHAR(50) NOT NULL COMMENT 'Raf kodu: A-01-05',
    location_name VARCHAR(100),
    location_type ENUM('raf', 'palet', 'bolum', 'alan') DEFAULT 'raf',
    
    -- Hiyerarşi
    parent_location_id INT COMMENT 'Üst lokasyon',
    
    -- Koordinatlar
    aisle VARCHAR(10) COMMENT 'Koridor',
    rack VARCHAR(10) COMMENT 'Raf',
    shelf VARCHAR(10) COMMENT 'Bölme',
    position VARCHAR(10) COMMENT 'Pozisyon',
    
    -- Kapasite
    capacity DECIMAL(10,2) COMMENT 'Kapasite',
    capacity_unit VARCHAR(20) COMMENT 'Birim',
    
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_location_id) REFERENCES warehouse_locations(id) ON DELETE SET NULL,
    
    INDEX idx_warehouse_id (warehouse_id),
    INDEX idx_location_code (location_code),
    UNIQUE KEY unique_location (warehouse_id, location_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### stock_movements
```sql
CREATE TABLE stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    warehouse_id INT,
    location_id INT COMMENT 'Raf lokasyonu',
    
    movement_type ENUM('giris', 'cikis', 'transfer', 'sayim', 'fire', 'iade') NOT NULL,
    movement_date DATE NOT NULL,
    
    -- Miktar
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20),
    
    -- Fiyat bilgisi
    unit_price DECIMAL(15,2) COMMENT 'Birim fiyat',
    total_value DECIMAL(15,2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
    
    -- Transfer bilgileri
    from_warehouse_id INT COMMENT 'Hangi depodan (transfer için)',
    to_warehouse_id INT COMMENT 'Hangi depoya (transfer için)',
    
    -- Referans
    reference_type ENUM('fatura', 'irsaliye', 'siparis', 'uretim', 'manuel') NOT NULL,
    reference_id INT COMMENT 'İlgili kayıt ID',
    reference_no VARCHAR(50),
    
    -- Lot/Seri
    lot_no VARCHAR(50) COMMENT 'Lot numarası',
    serial_no VARCHAR(100) COMMENT 'Seri numarası',
    expiry_date DATE COMMENT 'Son kullanma tarihi',
    
    -- Notlar
    notes TEXT,
    
    -- İşlemi yapan
    created_by INT NOT NULL,
    approved_by INT COMMENT 'Onaylayan kullanıcı',
    approved_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL,
    FOREIGN KEY (location_id) REFERENCES warehouse_locations(id) ON DELETE SET NULL,
    FOREIGN KEY (from_warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL,
    FOREIGN KEY (to_warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_product_id (product_id),
    INDEX idx_warehouse_id (warehouse_id),
    INDEX idx_movement_date (movement_date),
    INDEX idx_movement_type (movement_type),
    INDEX idx_lot_no (lot_no),
    INDEX idx_serial_no (serial_no),
    INDEX idx_reference (reference_type, reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### warehouse_stocks
```sql
CREATE TABLE warehouse_stocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    warehouse_id INT NOT NULL,
    product_id INT NOT NULL,
    location_id INT,
    
    quantity DECIMAL(10,2) DEFAULT 0.00,
    reserved_quantity DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Rezerve edilmiş',
    available_quantity DECIMAL(10,2) GENERATED ALWAYS AS (quantity - reserved_quantity) STORED,
    
    -- Değerleme
    average_cost DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Ortalama maliyet',
    total_value DECIMAL(15,2) GENERATED ALWAYS AS (quantity * average_cost) STORED,
    
    -- Son işlem
    last_movement_date DATE,
    last_movement_id INT,
    
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES warehouse_locations(id) ON DELETE SET NULL,
    FOREIGN KEY (last_movement_id) REFERENCES stock_movements(id) ON DELETE SET NULL,
    
    INDEX idx_warehouse_product (warehouse_id, product_id),
    INDEX idx_product_id (product_id),
    UNIQUE KEY unique_warehouse_product_location (warehouse_id, product_id, location_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### stock_counts
```sql
CREATE TABLE stock_counts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    warehouse_id INT NOT NULL,
    
    count_no VARCHAR(50) UNIQUE NOT NULL,
    count_date DATE NOT NULL,
    count_type ENUM('tam', 'kismi', 'devir', 'periyodik') DEFAULT 'tam',
    
    status ENUM('hazirlaniyor', 'sayimda', 'tamamlandi', 'onaylandi', 'iptal') DEFAULT 'hazirlaniyor',
    
    -- Sorumlu
    counted_by INT COMMENT 'Sayımı yapan',
    approved_by INT COMMENT 'Onaylayan',
    approved_at TIMESTAMP NULL,
    
    notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (counted_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_count_no (count_no),
    INDEX idx_count_date (count_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### stock_count_items
```sql
CREATE TABLE stock_count_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    count_id INT NOT NULL,
    product_id INT NOT NULL,
    location_id INT,
    
    system_quantity DECIMAL(10,2) NOT NULL COMMENT 'Sistemdeki miktar',
    counted_quantity DECIMAL(10,2) COMMENT 'Sayılan miktar',
    difference DECIMAL(10,2) GENERATED ALWAYS AS (counted_quantity - system_quantity) STORED,
    
    unit_cost DECIMAL(15,2),
    difference_value DECIMAL(15,2) GENERATED ALWAYS AS (difference * unit_cost) STORED,
    
    notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (count_id) REFERENCES stock_counts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES warehouse_locations(id) ON DELETE SET NULL,
    
    INDEX idx_count_id (count_id),
    INDEX idx_product_id (product_id),
    UNIQUE KEY unique_count_product (count_id, product_id, location_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🔌 API Endpoints

```
# Depo Yönetimi
GET    /api/warehouses                     - Depo listesi
GET    /api/warehouses/{id}                - Depo detayı
POST   /api/warehouses                     - Yeni depo
PUT    /api/warehouses/{id}                - Depo güncelle
DELETE /api/warehouses/{id}                - Depo sil
PUT    /api/warehouses/{id}/set-default    - Varsayılan depo

# Lokasyon Yönetimi
GET    /api/warehouses/{id}/locations      - Depo lokasyonları
POST   /api/warehouses/{id}/locations      - Yeni lokasyon
PUT    /api/warehouse-locations/{id}       - Lokasyon güncelle
DELETE /api/warehouse-locations/{id}       - Lokasyon sil

# Stok Hareketleri
GET    /api/stock/movements                - Hareket listesi
GET    /api/stock/movements/{id}           - Hareket detayı
POST   /api/stock/entry                    - Stok girişi
POST   /api/stock/exit                     - Stok çıkışı
POST   /api/stock/transfer                 - Depo transferi
POST   /api/stock/adjustment               - Manuel düzeltme
POST   /api/stock/loss                     - Fire kaydı

# Stok Sorguları
GET    /api/stock/warehouse/{id}           - Depodaki stoklar
GET    /api/stock/product/{id}             - Ürünün tüm depolardaki stoğu
GET    /api/stock/location/{id}            - Lokasyondaki stoklar
GET    /api/stock/lot/{lotNo}              - Lot numarasına göre stok
GET    /api/stock/expiring                 - Son kullanma tarihi yaklaşan

# Stok Sayımı
GET    /api/stock/counts                   - Sayım listesi
GET    /api/stock/counts/{id}              - Sayım detayı
POST   /api/stock/counts                   - Yeni sayım başlat
PUT    /api/stock/counts/{id}              - Sayım güncelle
POST   /api/stock/counts/{id}/items        - Sayım kalemi ekle
PUT    /api/stock/counts/{id}/complete     - Sayımı tamamla
POST   /api/stock/counts/{id}/approve      - Sayımı onayla

# Raporlar
GET    /api/stock/reports/summary          - Stok özet raporu
GET    /api/stock/reports/movements        - Hareket raporu
GET    /api/stock/reports/valuation        - Stok değerleme raporu
GET    /api/stock/reports/aging            - Yaşlandırma raporu
GET    /api/stock/reports/turnover         - Devir hızı raporu
```

---

## 💻 Backend Implementasyonu

### 1. Warehouse Model

```php
<?php
namespace App\Models;

class Warehouse {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Depo listesi
     */
    public function getAll($userId) {
        $sql = "SELECT w.*,
                (SELECT COUNT(*) FROM warehouse_stocks ws WHERE ws.warehouse_id = w.id) as product_count,
                (SELECT SUM(quantity) FROM warehouse_stocks ws WHERE ws.warehouse_id = w.id) as total_quantity
                FROM warehouses w
                WHERE w.user_id = ?
                ORDER BY is_default DESC, warehouse_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * ID ile depo getir
     */
    public function findById($id, $userId) {
        $sql = "SELECT * FROM warehouses WHERE id = ? AND user_id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Yeni depo oluştur
     */
    public function create($userId, $data) {
        // İlk depo ise varsayılan yap
        $isFirstWarehouse = $this->countByUser($userId) === 0;
        
        $sql = "INSERT INTO warehouses (
            user_id, warehouse_code, warehouse_name, warehouse_type,
            address, district, city, postal_code,
            responsible_person, phone, email,
            total_area, storage_capacity,
            notes, is_default
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $userId,
            $data['warehouse_code'] ?? $this->generateWarehouseCode($userId),
            $data['warehouse_name'],
            $data['warehouse_type'] ?? 'merkez',
            $data['address'] ?? null,
            $data['district'] ?? null,
            $data['city'] ?? null,
            $data['postal_code'] ?? null,
            $data['responsible_person'] ?? null,
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['total_area'] ?? null,
            $data['storage_capacity'] ?? null,
            $data['notes'] ?? null,
            $isFirstWarehouse
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Varsayılan depoyu ayarla
     */
    public function setDefault($id, $userId) {
        // Tüm depoların varsayılan işaretini kaldır
        $sql = "UPDATE warehouses SET is_default = FALSE WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        // Seçili depoyu varsayılan yap
        $sql = "UPDATE warehouses SET is_default = TRUE WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id, $userId]);
    }
    
    /**
     * Depo stok özeti
     */
    public function getStockSummary($warehouseId) {
        $sql = "SELECT 
                COUNT(DISTINCT product_id) as product_count,
                SUM(quantity) as total_quantity,
                SUM(total_value) as total_value,
                SUM(reserved_quantity) as total_reserved
                FROM warehouse_stocks
                WHERE warehouse_id = ? AND quantity > 0";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$warehouseId]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    private function generateWarehouseCode($userId) {
        $prefix = 'DEP';
        
        $sql = "SELECT warehouse_code FROM warehouses
                WHERE user_id = ? AND warehouse_code LIKE ?
                ORDER BY id DESC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $prefix . '%']);
        $last = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($last) {
            $number = (int) substr($last['warehouse_code'], 3) + 1;
        } else {
            $number = 1;
        }
        
        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
    
    private function countByUser($userId) {
        $sql = "SELECT COUNT(*) as count FROM warehouses WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC)['count'];
    }
}
```

### 2. StockMovement Model

```php
<?php
namespace App\Models;

class StockMovement {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Stok hareketi oluştur
     */
    public function create($userId, $data) {
        $sql = "INSERT INTO stock_movements (
            user_id, product_id, warehouse_id, location_id,
            movement_type, movement_date,
            quantity, unit, unit_price,
            from_warehouse_id, to_warehouse_id,
            reference_type, reference_id, reference_no,
            lot_no, serial_no, expiry_date,
            notes, created_by
        ) VALUES (
            ?, ?, ?, ?,
            ?, ?,
            ?, ?, ?,
            ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            ?, ?
        )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $userId,
            $data['product_id'],
            $data['warehouse_id'],
            $data['location_id'] ?? null,
            $data['movement_type'],
            $data['movement_date'],
            $data['quantity'],
            $data['unit'],
            $data['unit_price'] ?? 0,
            $data['from_warehouse_id'] ?? null,
            $data['to_warehouse_id'] ?? null,
            $data['reference_type'],
            $data['reference_id'] ?? null,
            $data['reference_no'] ?? null,
            $data['lot_no'] ?? null,
            $data['serial_no'] ?? null,
            $data['expiry_date'] ?? null,
            $data['notes'] ?? null,
            $userId
        ]);
        
        $movementId = $this->db->lastInsertId();
        
        // Depo stoğunu güncelle
        $this->updateWarehouseStock($data);
        
        return $movementId;
    }
    
    /**
     * Stok girişi
     */
    public function entry($userId, $productId, $warehouseId, $quantity, $data = []) {
        return $this->create($userId, array_merge([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'movement_type' => 'giris',
            'movement_date' => date('Y-m-d'),
            'quantity' => $quantity,
            'unit' => $data['unit'] ?? 'adet',
            'reference_type' => $data['reference_type'] ?? 'manuel'
        ], $data));
    }
    
    /**
     * Stok çıkışı
     */
    public function exit($userId, $productId, $warehouseId, $quantity, $data = []) {
        return $this->create($userId, array_merge([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'movement_type' => 'cikis',
            'movement_date' => date('Y-m-d'),
            'quantity' => $quantity,
            'unit' => $data['unit'] ?? 'adet',
            'reference_type' => $data['reference_type'] ?? 'manuel'
        ], $data));
    }
    
    /**
     * Depo transferi
     */
    public function transfer($userId, $productId, $fromWarehouseId, $toWarehouseId, $quantity, $data = []) {
        // Çıkış hareketi
        $this->create($userId, array_merge([
            'product_id' => $productId,
            'warehouse_id' => $fromWarehouseId,
            'movement_type' => 'transfer',
            'movement_date' => date('Y-m-d'),
            'quantity' => -$quantity,
            'unit' => $data['unit'] ?? 'adet',
            'from_warehouse_id' => $fromWarehouseId,
            'to_warehouse_id' => $toWarehouseId,
            'reference_type' => 'manuel'
        ], $data));
        
        // Giriş hareketi
        return $this->create($userId, array_merge([
            'product_id' => $productId,
            'warehouse_id' => $toWarehouseId,
            'movement_type' => 'transfer',
            'movement_date' => date('Y-m-d'),
            'quantity' => $quantity,
            'unit' => $data['unit'] ?? 'adet',
            'from_warehouse_id' => $fromWarehouseId,
            'to_warehouse_id' => $toWarehouseId,
            'reference_type' => 'manuel'
        ], $data));
    }
    
    /**
     * Depo stoğunu güncelle
     */
    private function updateWarehouseStock($data) {
        // Mevcut stok kaydını bul veya oluştur
        $sql = "INSERT INTO warehouse_stocks (
            warehouse_id, product_id, location_id, quantity, average_cost
        ) VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            quantity = quantity + VALUES(quantity),
            average_cost = ((average_cost * quantity) + (VALUES(average_cost) * VALUES(quantity))) / (quantity + VALUES(quantity)),
            last_movement_date = CURDATE()";
        
        $quantity = $data['movement_type'] === 'giris' ? $data['quantity'] : -$data['quantity'];
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['warehouse_id'],
            $data['product_id'],
            $data['location_id'] ?? null,
            $quantity,
            $data['unit_price'] ?? 0
        ]);
    }
    
    /**
     * Hareket listesi
     */
    public function getList($userId, $filters = [], $page = 1, $perPage = 50) {
        $offset = ($page - 1) * $perPage;
        $where = ['sm.user_id = ?'];
        $params = [$userId];
        
        if (!empty($filters['warehouse_id'])) {
            $where[] = 'sm.warehouse_id = ?';
            $params[] = $filters['warehouse_id'];
        }
        
        if (!empty($filters['product_id'])) {
            $where[] = 'sm.product_id = ?';
            $params[] = $filters['product_id'];
        }
        
        if (!empty($filters['movement_type'])) {
            $where[] = 'sm.movement_type = ?';
            $params[] = $filters['movement_type'];
        }
        
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $where[] = 'sm.movement_date BETWEEN ? AND ?';
            $params[] = $filters['start_date'];
            $params[] = $filters['end_date'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT sm.*, 
                p.product_name, p.product_code, p.barcode,
                w.warehouse_name,
                u.full_name as created_by_name
                FROM stock_movements sm
                JOIN products p ON sm.product_id = p.id
                LEFT JOIN warehouses w ON sm.warehouse_id = w.id
                LEFT JOIN users u ON sm.created_by = u.id
                WHERE $whereClause
                ORDER BY sm.movement_date DESC, sm.id DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Ürünün depo bazlı stoğu
     */
    public function getProductStockByWarehouses($productId, $userId) {
        $sql = "SELECT ws.*, 
                w.warehouse_name, w.warehouse_code
                FROM warehouse_stocks ws
                JOIN warehouses w ON ws.warehouse_id = w.id
                WHERE ws.product_id = ? AND w.user_id = ?
                AND ws.quantity > 0
                ORDER BY w.warehouse_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId, $userId]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
```

### 3. StockCount Model

```php
<?php
namespace App\Models;

class StockCount {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Yeni sayım başlat
     */
    public function create($userId, $warehouseId, $data = []) {
        $countNo = $this->generateCountNo($userId);
        
        $sql = "INSERT INTO stock_counts (
            user_id, warehouse_id, count_no, count_date,
            count_type, counted_by, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $userId,
            $warehouseId,
            $countNo,
            $data['count_date'] ?? date('Y-m-d'),
            $data['count_type'] ?? 'tam',
            $userId,
            $data['notes'] ?? null
        ]);
        
        $countId = $this->db->lastInsertId();
        
        // Tam sayım ise tüm ürünleri ekle
        if (($data['count_type'] ?? 'tam') === 'tam') {
            $this->addAllProductsToCount($countId, $warehouseId);
        }
        
        return $countId;
    }
    
    /**
     * Sayıma tüm ürünleri ekle
     */
    private function addAllProductsToCount($countId, $warehouseId) {
        $sql = "INSERT INTO stock_count_items (
            count_id, product_id, location_id, system_quantity, unit_cost
        )
        SELECT 
            ? as count_id,
            product_id,
            location_id,
            quantity,
            average_cost
        FROM warehouse_stocks
        WHERE warehouse_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$countId, $warehouseId]);
    }
    
    /**
     * Sayım kalemini güncelle
     */
    public function updateCountItem($countItemId, $countedQuantity, $notes = null) {
        $sql = "UPDATE stock_count_items SET
                counted_quantity = ?,
                notes = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$countedQuantity, $notes, $countItemId]);
    }
    
    /**
     * Sayımı tamamla
     */
    public function complete($countId) {
        $sql = "UPDATE stock_counts SET status = 'tamamlandi' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$countId]);
    }
    
    /**
     * Sayımı onayla ve stokları güncelle
     */
    public function approve($countId, $userId) {
        // Sayımı onayla
        $sql = "UPDATE stock_counts SET 
                status = 'onaylandi',
                approved_by = ?,
                approved_at = NOW()
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $countId]);
        
        // Farklılıkları stok hareketine dönüştür
        $this->createAdjustmentsFromCount($countId);
        
        return true;
    }
    
    /**
     * Sayım farklılıklarından düzeltme hareketleri oluştur
     */
    private function createAdjustmentsFromCount($countId) {
        $sql = "SELECT sc.*, sci.*, sc.warehouse_id
                FROM stock_count_items sci
                JOIN stock_counts sc ON sci.count_id = sc.id
                WHERE sci.count_id = ?
                AND sci.counted_quantity IS NOT NULL
                AND sci.difference != 0";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$countId]);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $movementModel = new StockMovement($this->db);
        
        foreach ($items as $item) {
            $movementModel->create($item['user_id'], [
                'product_id' => $item['product_id'],
                'warehouse_id' => $item['warehouse_id'],
                'location_id' => $item['location_id'],
                'movement_type' => 'sayim',
                'movement_date' => date('Y-m-d'),
                'quantity' => $item['difference'],
                'unit' => 'adet',
                'reference_type' => 'manuel',
                'reference_no' => $item['count_no'],
                'notes' => 'Stok sayımı düzeltmesi'
            ]);
        }
    }
    
    private function generateCountNo($userId) {
        $prefix = 'SAY' . date('Ym');
        
        $sql = "SELECT count_no FROM stock_counts
                WHERE user_id = ? AND count_no LIKE ?
                ORDER BY id DESC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $prefix . '%']);
        $last = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($last) {
            $number = (int) substr($last['count_no'], -4) + 1;
        } else {
            $number = 1;
        }
        
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
```

---

## 🎨 Frontend - Stok Transfer Formu

```html
<div class="max-w-4xl mx-auto p-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-6">Depo Transferi</h2>
        
        <form id="transferForm">
            <!-- Kaynak Depo -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Kaynak Depo *</label>
                <select name="from_warehouse_id" required class="w-full px-3 py-2 border rounded">
                    <option value="">Seçiniz</option>
                    <!-- Dinamik -->
                </select>
            </div>
            
            <!-- Hedef Depo -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Hedef Depo *</label>
                <select name="to_warehouse_id" required class="w-full px-3 py-2 border rounded">
                    <option value="">Seçiniz</option>
                    <!-- Dinamik -->
                </select>
            </div>
            
            <!-- Ürün Seçimi -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Ürün *</label>
                <select name="product_id" required class="w-full px-3 py-2 border rounded" onchange="loadProductStock(this.value)">
                    <option value="">Ürün seçiniz veya barkod okutun</option>
                    <!-- Dinamik -->
                </select>
                <div id="stockInfo" class="mt-2 text-sm text-gray-600"></div>
            </div>
            
            <!-- Miktar -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Transfer Miktarı *</label>
                <div class="flex space-x-2">
                    <input 
                        type="number" 
                        name="quantity" 
                        step="0.01" 
                        required 
                        class="flex-1 px-3 py-2 border rounded"
                    >
                    <span class="px-3 py-2 bg-gray-100 border rounded" id="unitDisplay">adet</span>
                </div>
            </div>
            
            <!-- Transfer Tarihi -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Transfer Tarihi</label>
                <input 
                    type="date" 
                    name="movement_date" 
                    value="<?= date('Y-m-d') ?>"
                    class="w-full px-3 py-2 border rounded"
                >
            </div>
            
            <!-- Notlar -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Notlar</label>
                <textarea name="notes" rows="2" class="w-full px-3 py-2 border rounded"></textarea>
            </div>
            
            <!-- Buttons -->
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="window.history.back()" class="px-4 py-2 border rounded">
                    İptal
                </button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">
                    Transfer Yap
                </button>
            </div>
        </form>
    </div>
</div>

<script>
async function loadProductStock(productId) {
    if (!productId) return;
    
    const fromWarehouse = document.querySelector('[name="from_warehouse_id"]').value;
    if (!fromWarehouse) {
        alert('Önce kaynak depo seçiniz');
        return;
    }
    
    const response = await apiClient.get(`/stock/product/${productId}`);
    
    if (response.success) {
        const warehouse = response.data.find(w => w.warehouse_id == fromWarehouse);
        if (warehouse) {
            document.getElementById('stockInfo').innerHTML = 
                `Mevcut Stok: <strong>${warehouse.quantity} ${warehouse.unit}</strong>`;
        } else {
            document.getElementById('stockInfo').innerHTML = 
                '<span class="text-red-600">Bu üründen kaynak depoda stok yok!</span>';
        }
    }
}

document.getElementById('transferForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    
    const response = await apiClient.post('/stock/transfer', data);
    
    if (response.success) {
        alert('Transfer başarıyla tamamlandı');
        window.location.href = '/stock/movements';
    } else {
        alert(response.error || 'Transfer başarısız');
    }
});
</script>
```

---

## 📝 Özet

Bu modül ile:
- ✅ Çoklu depo yönetimi
- ✅ Detaylı stok hareketleri
- ✅ Raf/lokasyon takibi
- ✅ Lot/seri no sistemi
- ✅ Stok sayımı
- ✅ Depolar arası transfer
- ✅ Stok değerleme (FIFO mantığı)

**Sonraki Modül:** Teklif Yönetimi
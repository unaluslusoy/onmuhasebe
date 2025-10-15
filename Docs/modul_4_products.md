# Modül 4: Ürün ve Hizmet Yönetimi

## 📋 Modül Özeti

Ürün ve hizmetlerin yönetildiği, fiyatlandırma, stok takibi ve barkod okuma özelliklerini içeren modül.

### Özellikler
- ✅ Ürün/Hizmet kartları
- ✅ Barkod yönetimi
- ✅ Kategori sistemi
- ✅ Fiyat yönetimi (alış/satış)
- ✅ Stok takibi entegrasyonu
- ✅ Varyant yönetimi (beden, renk)
- ✅ Ürün görselleri
- ✅ KDV oranı ayarları
- ✅ Birim tanımları
- ✅ Toplu import/export
- ✅ Hızlı arama

---

## 🗄️ Veritabanı Tabloları

### products
```sql
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    
    -- Temel bilgiler
    product_code VARCHAR(50) NOT NULL,
    barcode VARCHAR(100),
    product_name VARCHAR(200) NOT NULL,
    product_type ENUM('urun', 'hizmet', 'dijital', 'hammadde') DEFAULT 'urun',
    
    -- Kategori
    category_id INT,
    
    -- Açıklama
    description TEXT,
    short_description VARCHAR(500),
    
    -- Birim
    unit ENUM('adet', 'kg', 'gr', 'litre', 'ml', 'metre', 'm2', 'm3', 
              'paket', 'kutu', 'koli', 'ton', 'saat', 'gun', 'ay') DEFAULT 'adet',
    
    -- Fiyatlandırma
    purchase_price DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Alış fiyatı',
    sale_price DECIMAL(15,2) NOT NULL COMMENT 'Satış fiyatı',
    discount_rate DECIMAL(5,2) DEFAULT 0.00 COMMENT 'İskonto oranı',
    
    -- KDV
    kdv_rate DECIMAL(5,2) DEFAULT 18.00,
    kdv_included BOOLEAN DEFAULT FALSE COMMENT 'KDV dahil mi?',
    
    -- Para birimi
    currency ENUM('TRY', 'USD', 'EUR', 'GBP') DEFAULT 'TRY',
    
    -- Stok bilgileri
    stock_tracking BOOLEAN DEFAULT TRUE COMMENT 'Stok takibi yapılsın mı?',
    current_stock DECIMAL(10,2) DEFAULT 0.00,
    min_stock_level DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Minimum stok seviyesi',
    max_stock_level DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Maksimum stok seviyesi',
    critical_stock_level DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Kritik stok seviyesi',
    
    -- Depo
    warehouse_id INT,
    shelf_location VARCHAR(50) COMMENT 'Raf konumu',
    
    -- Boyut ve ağırlık
    weight DECIMAL(10,2) COMMENT 'Ağırlık (kg)',
    width DECIMAL(10,2) COMMENT 'Genişlik (cm)',
    height DECIMAL(10,2) COMMENT 'Yükseklik (cm)',
    depth DECIMAL(10,2) COMMENT 'Derinlik (cm)',
    
    -- Görseller
    image_path VARCHAR(255),
    images TEXT COMMENT 'JSON array of image paths',
    
    -- Varyantlar
    has_variants BOOLEAN DEFAULT FALSE,
    variant_attributes TEXT COMMENT 'JSON: beden, renk, vs.',
    
    -- e-Ticaret
    is_published BOOLEAN DEFAULT FALSE COMMENT 'Yayında mı?',
    seo_title VARCHAR(200),
    seo_description TEXT,
    seo_keywords TEXT,
    
    -- Tedarikçi
    supplier_id INT COMMENT 'Varsayılan tedarikçi',
    supplier_code VARCHAR(50) COMMENT 'Tedarikçi ürün kodu',
    
    -- Muhasebe kodu
    accounting_code VARCHAR(50) COMMENT 'Muhasebe hesap kodu',
    
    -- Etiketler
    tags TEXT COMMENT 'JSON array',
    
    -- Durum
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Tarih bilgileri
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES cari_accounts(id) ON DELETE SET NULL,
    
    INDEX idx_product_code (product_code),
    INDEX idx_barcode (barcode),
    INDEX idx_product_name (product_name),
    INDEX idx_category_id (category_id),
    INDEX idx_current_stock (current_stock),
    UNIQUE KEY unique_product_code (user_id, product_code),
    UNIQUE KEY unique_barcode (user_id, barcode),
    FULLTEXT idx_search (product_name, description, barcode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### product_categories
```sql
CREATE TABLE product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    
    category_name VARCHAR(100) NOT NULL,
    parent_category_id INT COMMENT 'Alt kategori için',
    
    description TEXT,
    
    -- Görsel
    image_path VARCHAR(255),
    
    -- Renk (UI için)
    color_code VARCHAR(7) DEFAULT '#6366F1',
    icon VARCHAR(50),
    
    -- Sıralama
    sort_order INT DEFAULT 0,
    
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_category_id) REFERENCES product_categories(id) ON DELETE CASCADE,
    
    INDEX idx_parent_category (parent_category_id),
    UNIQUE KEY unique_category_name (user_id, category_name, parent_category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### product_variants
```sql
CREATE TABLE product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    
    variant_name VARCHAR(100) NOT NULL COMMENT 'Örn: Kırmızı - L',
    variant_sku VARCHAR(100) UNIQUE COMMENT 'Varyant SKU kodu',
    
    -- Varyant özellikleri
    attributes TEXT COMMENT 'JSON: {"renk": "kirmizi", "beden": "L"}',
    
    -- Fiyat farklılığı
    price_difference DECIMAL(15,2) DEFAULT 0.00 COMMENT '+/- fiyat farkı',
    
    -- Stok
    stock_quantity DECIMAL(10,2) DEFAULT 0.00,
    
    -- Barkod
    barcode VARCHAR(100),
    
    -- Görseller
    image_path VARCHAR(255),
    
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    
    INDEX idx_product_id (product_id),
    INDEX idx_barcode (barcode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### product_price_history
```sql
CREATE TABLE product_price_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    
    old_purchase_price DECIMAL(15,2),
    new_purchase_price DECIMAL(15,2),
    
    old_sale_price DECIMAL(15,2),
    new_sale_price DECIMAL(15,2),
    
    changed_by INT NOT NULL COMMENT 'User ID',
    change_reason VARCHAR(255),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_product_id (product_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🔌 API Endpoints

```
# CRUD İşlemleri
GET    /api/products                   - Ürün listesi (filtreleme, arama)
GET    /api/products/{id}              - Ürün detayı
POST   /api/products                   - Yeni ürün
PUT    /api/products/{id}              - Ürün güncelle
DELETE /api/products/{id}              - Ürün sil

# Arama
GET    /api/products/search            - Ürün arama (isim, barkod, kod)
GET    /api/products/barcode/{barcode} - Barkod ile ürün bul

# Stok İşlemleri
GET    /api/products/low-stock         - Düşük stok ürünleri
GET    /api/products/out-of-stock      - Stokta olmayan ürünler
PUT    /api/products/{id}/stock        - Stok güncelle

# Görseller
POST   /api/products/{id}/images       - Ürün görseli yükle
DELETE /api/products/{id}/images/{imageId} - Görsel sil

# Varyantlar
GET    /api/products/{id}/variants     - Ürün varyantları
POST   /api/products/{id}/variants     - Yeni varyant
PUT    /api/products/{id}/variants/{variantId} - Varyant güncelle
DELETE /api/products/{id}/variants/{variantId} - Varyant sil

# Kategoriler
GET    /api/product-categories         - Kategori listesi
POST   /api/product-categories         - Yeni kategori
PUT    /api/product-categories/{id}    - Kategori güncelle
DELETE /api/product-categories/{id}    - Kategori sil

# Toplu İşlemler
POST   /api/products/import            - Excel/CSV import
GET    /api/products/export            - Excel export
POST   /api/products/bulk-update       - Toplu güncelleme
POST   /api/products/bulk-delete       - Toplu silme

# Fiyat Geçmişi
GET    /api/products/{id}/price-history - Fiyat değişim geçmişi

# Raporlar
GET    /api/products/stats             - Ürün istatistikleri
GET    /api/products/bestsellers       - En çok satanlar
GET    /api/products/profit-report     - Karlılık raporu
```

---

## 💻 Backend Implementasyonu

### 1. Product Model

```php
<?php
namespace App\Models;

class Product {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Ürün listesi
     */
    public function getList($userId, $filters = [], $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $where = ['user_id = ?'];
        $params = [$userId];
        
        // Filtreleme
        if (!empty($filters['category_id'])) {
            $where[] = 'category_id = ?';
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['product_type'])) {
            $where[] = 'product_type = ?';
            $params[] = $filters['product_type'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = '(product_name LIKE ? OR product_code LIKE ? OR barcode LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        if (isset($filters['is_active'])) {
            $where[] = 'is_active = ?';
            $params[] = $filters['is_active'];
        }
        
        if (isset($filters['stock_tracking'])) {
            $where[] = 'stock_tracking = ?';
            $params[] = $filters['stock_tracking'];
        }
        
        // Stok filtresi
        if (!empty($filters['stock_status'])) {
            switch ($filters['stock_status']) {
                case 'low':
                    $where[] = 'current_stock > 0 AND current_stock <= min_stock_level';
                    break;
                case 'out':
                    $where[] = 'current_stock <= 0';
                    break;
                case 'in':
                    $where[] = 'current_stock > 0';
                    break;
            }
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Toplam kayıt
        $countSql = "SELECT COUNT(*) as total FROM products WHERE $whereClause";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];
        
        // Sıralama
        $orderBy = $filters['order_by'] ?? 'product_name';
        $orderDir = $filters['order_dir'] ?? 'ASC';
        
        // Liste
        $sql = "SELECT p.*, pc.category_name
                FROM products p
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                WHERE $whereClause
                ORDER BY $orderBy $orderDir
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
     * ID ile ürün getir
     */
    public function findById($id, $userId) {
        $sql = "SELECT p.*, pc.category_name
                FROM products p
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                WHERE p.id = ? AND p.user_id = ?
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $userId]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Barkod ile ürün bul
     */
    public function findByBarcode($barcode, $userId) {
        $sql = "SELECT p.*, pc.category_name
                FROM products p
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                WHERE p.barcode = ? AND p.user_id = ?
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$barcode, $userId]);
        
        $product = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Ana üründe bulamazsa varyantlarda ara
        if (!$product) {
            $sql = "SELECT p.*, pv.variant_name, pv.price_difference, pv.stock_quantity
                    FROM product_variants pv
                    JOIN products p ON pv.product_id = p.id
                    WHERE pv.barcode = ? AND p.user_id = ?
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$barcode, $userId]);
            $product = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($product) {
                // Varyant fiyatını hesapla
                $product['sale_price'] += $product['price_difference'];
                $product['is_variant'] = true;
            }
        }
        
        return $product;
    }
    
    /**
     * Ürün kodu ile bul
     */
    public function findByCode($code, $userId) {
        $sql = "SELECT * FROM products 
                WHERE product_code = ? AND user_id = ?
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$code, $userId]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Yeni ürün oluştur
     */
    public function create($userId, $data) {
        // Ürün kodu yoksa otomatik oluştur
        if (empty($data['product_code'])) {
            $data['product_code'] = $this->generateProductCode($userId);
        }
        
        $sql = "INSERT INTO products (
            user_id, product_code, barcode, product_name, product_type,
            category_id, description, short_description, unit,
            purchase_price, sale_price, discount_rate,
            kdv_rate, kdv_included, currency,
            stock_tracking, current_stock, min_stock_level, max_stock_level, critical_stock_level,
            warehouse_id, shelf_location,
            weight, width, height, depth,
            image_path, images,
            has_variants, variant_attributes,
            supplier_id, supplier_code,
            accounting_code, tags, is_active
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?,
            ?, ?, ?, ?,
            ?, ?,
            ?, ?,
            ?, ?,
            ?, ?, ?
        )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $userId,
            $data['product_code'],
            $data['barcode'] ?? null,
            $data['product_name'],
            $data['product_type'] ?? 'urun',
            $data['category_id'] ?? null,
            $data['description'] ?? null,
            $data['short_description'] ?? null,
            $data['unit'] ?? 'adet',
            $data['purchase_price'] ?? 0,
            $data['sale_price'],
            $data['discount_rate'] ?? 0,
            $data['kdv_rate'] ?? 18,
            $data['kdv_included'] ?? false,
            $data['currency'] ?? 'TRY',
            $data['stock_tracking'] ?? true,
            $data['current_stock'] ?? 0,
            $data['min_stock_level'] ?? 0,
            $data['max_stock_level'] ?? 0,
            $data['critical_stock_level'] ?? 0,
            $data['warehouse_id'] ?? null,
            $data['shelf_location'] ?? null,
            $data['weight'] ?? null,
            $data['width'] ?? null,
            $data['height'] ?? null,
            $data['depth'] ?? null,
            $data['image_path'] ?? null,
            isset($data['images']) ? json_encode($data['images']) : null,
            $data['has_variants'] ?? false,
            isset($data['variant_attributes']) ? json_encode($data['variant_attributes']) : null,
            $data['supplier_id'] ?? null,
            $data['supplier_code'] ?? null,
            $data['accounting_code'] ?? null,
            isset($data['tags']) ? json_encode($data['tags']) : null,
            $data['is_active'] ?? true
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Ürün güncelle
     */
    public function update($id, $userId, $data) {
        // Fiyat değişikliği varsa geçmişe kaydet
        $currentProduct = $this->findById($id, $userId);
        if ($currentProduct && 
            ($currentProduct['sale_price'] != $data['sale_price'] || 
             $currentProduct['purchase_price'] != $data['purchase_price'])) {
            $this->logPriceChange($id, $userId, $currentProduct, $data);
        }
        
        $sql = "UPDATE products SET
                product_name = ?,
                product_type = ?,
                category_id = ?,
                description = ?,
                short_description = ?,
                unit = ?,
                purchase_price = ?,
                sale_price = ?,
                discount_rate = ?,
                kdv_rate = ?,
                kdv_included = ?,
                currency = ?,
                stock_tracking = ?,
                min_stock_level = ?,
                max_stock_level = ?,
                critical_stock_level = ?,
                warehouse_id = ?,
                shelf_location = ?,
                weight = ?,
                width = ?,
                height = ?,
                depth = ?,
                supplier_id = ?,
                supplier_code = ?,
                accounting_code = ?,
                tags = ?,
                is_active = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['product_name'],
            $data['product_type'] ?? 'urun',
            $data['category_id'] ?? null,
            $data['description'] ?? null,
            $data['short_description'] ?? null,
            $data['unit'] ?? 'adet',
            $data['purchase_price'] ?? 0,
            $data['sale_price'],
            $data['discount_rate'] ?? 0,
            $data['kdv_rate'] ?? 18,
            $data['kdv_included'] ?? false,
            $data['currency'] ?? 'TRY',
            $data['stock_tracking'] ?? true,
            $data['min_stock_level'] ?? 0,
            $data['max_stock_level'] ?? 0,
            $data['critical_stock_level'] ?? 0,
            $data['warehouse_id'] ?? null,
            $data['shelf_location'] ?? null,
            $data['weight'] ?? null,
            $data['width'] ?? null,
            $data['height'] ?? null,
            $data['depth'] ?? null,
            $data['supplier_id'] ?? null,
            $data['supplier_code'] ?? null,
            $data['accounting_code'] ?? null,
            isset($data['tags']) ? json_encode($data['tags']) : null,
            $data['is_active'] ?? true,
            $id,
            $userId
        ]);
    }
    
    /**
     * Stok güncelle
     */
    public function updateStock($id, $quantity, $operation = 'set') {
        switch ($operation) {
            case 'add':
                $sql = "UPDATE products SET current_stock = current_stock + ? WHERE id = ?";
                break;
            case 'subtract':
                $sql = "UPDATE products SET current_stock = current_stock - ? WHERE id = ?";
                break;
            default: // 'set'
                $sql = "UPDATE products SET current_stock = ? WHERE id = ?";
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$quantity, $id]);
    }
    
    /**
     * Düşük stok ürünleri
     */
    public function getLowStockProducts($userId) {
        $sql = "SELECT * FROM products
                WHERE user_id = ?
                AND stock_tracking = TRUE
                AND is_active = TRUE
                AND current_stock > 0
                AND current_stock <= min_stock_level
                ORDER BY current_stock ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Stokta olmayan ürünler
     */
    public function getOutOfStockProducts($userId) {
        $sql = "SELECT * FROM products
                WHERE user_id = ?
                AND stock_tracking = TRUE
                AND is_active = TRUE
                AND current_stock <= 0
                ORDER BY product_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Otomatik ürün kodu oluştur
     */
    private function generateProductCode($userId) {
        $prefix = 'URN';
        
        $sql = "SELECT product_code FROM products
                WHERE user_id = ? AND product_code LIKE ?
                ORDER BY id DESC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $prefix . '%']);
        $last = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($last) {
            $number = (int) substr($last['product_code'], 3) + 1;
        } else {
            $number = 1;
        }
        
        return $prefix . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Fiyat değişikliğini logla
     */
    private function logPriceChange($productId, $userId, $oldData, $newData) {
        $sql = "INSERT INTO product_price_history (
            product_id, 
            old_purchase_price, new_purchase_price,
            old_sale_price, new_sale_price,
            changed_by, change_reason
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $productId,
            $oldData['purchase_price'],
            $newData['purchase_price'],
            $oldData['sale_price'],
            $newData['sale_price'],
            $userId,
            $newData['price_change_reason'] ?? 'Manuel güncelleme'
        ]);
    }
    
    /**
     * Ürün istatistikleri
     */
    public function getStats($userId) {
        $sql = "SELECT 
                COUNT(*) as total_products,
                COUNT(CASE WHEN is_active = TRUE THEN 1 END) as active_products,
                COUNT(CASE WHEN stock_tracking = TRUE THEN 1 END) as tracked_products,
                COUNT(CASE WHEN current_stock <= 0 AND stock_tracking = TRUE THEN 1 END) as out_of_stock,
                COUNT(CASE WHEN current_stock > 0 AND current_stock <= min_stock_level AND stock_tracking = TRUE THEN 1 END) as low_stock,
                SUM(current_stock * purchase_price) as total_stock_value,
                AVG(sale_price) as avg_sale_price
                FROM products
                WHERE user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
```

### 2. ProductCategory Model

```php
<?php
namespace App\Models;

class ProductCategory {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Tüm kategorileri getir (ağaç yapısı)
     */
    public function getAll($userId) {
        $sql = "SELECT * FROM product_categories
                WHERE user_id = ? AND is_active = TRUE
                ORDER BY sort_order ASC, category_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Ağaç yapısına çevir
        return $this->buildTree($categories);
    }
    
    /**
     * Kategori ağacı oluştur
     */
    private function buildTree($categories, $parentId = null) {
        $branch = [];
        
        foreach ($categories as $category) {
            if ($category['parent_category_id'] == $parentId) {
                $children = $this->buildTree($categories, $category['id']);
                if ($children) {
                    $category['children'] = $children;
                }
                $branch[] = $category;
            }
        }
        
        return $branch;
    }
    
    /**
     * Yeni kategori oluştur
     */
    public function create($userId, $data) {
        $sql = "INSERT INTO product_categories (
            user_id, category_name, parent_category_id,
            description, color_code, icon, sort_order
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $userId,
            $data['category_name'],
            $data['parent_category_id'] ?? null,
            $data['description'] ?? null,
            $data['color_code'] ?? '#6366F1',
            $data['icon'] ?? null,
            $data['sort_order'] ?? 0
        ]);
        
        return $this->db->lastInsertId();
    }
}
```

---

## 🎨 Frontend - Ürün Formu

```html
<div class="max-w-5xl mx-auto p-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-6">Yeni Ürün/Hizmet</h2>
        
        <form id="productForm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Sol Kolon -->
                <div>
                    <!-- Ürün Tipi -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Ürün Tipi *</label>
                        <select name="product_type" required class="w-full px-3 py-2 border rounded">
                            <option value="urun">Ürün</option>
                            <option value="hizmet">Hizmet</option>
                            <option value="dijital">Dijital Ürün</option>
                        </select>
                    </div>
                    
                    <!-- Ürün Adı -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Ürün/Hizmet Adı *</label>
                        <input type="text" name="product_name" required class="w-full px-3 py-2 border rounded">
                    </div>
                    
                    <!-- Ürün Kodu -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Ürün Kodu</label>
                        <input type="text" name="product_code" placeholder="Otomatik oluşturulacak" class="w-full px-3 py-2 border rounded">
                    </div>
                    
                    <!-- Barkod -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Barkod</label>
                        <div class="flex space-x-2">
                            <input type="text" name="barcode" class="flex-1 px-3 py-2 border rounded">
                            <button type="button" onclick="scanBarcode()" class="px-4 py-2 bg-indigo-600 text-white rounded">
                                📷 Tara
                            </button>
                        </div>
                    </div>
                    
                    <!-- Kategori -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Kategori</label>
                        <select name="category_id" class="w-full px-3 py-2 border rounded">
                            <option value="">Seçiniz</option>
                            <!-- Dinamik olarak doldurulacak -->
                        </select>
                    </div>
                    
                    <!-- Birim -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Birim</label>
                        <select name="unit" class="w-full px-3 py-2 border rounded">
                            <option value="adet">Adet</option>
                            <option value="kg">Kilogram</option>
                            <option value="gr">Gram</option>
                            <option value="litre">Litre</option>
                            <option value="metre">Metre</option>
                            <option value="paket">Paket</option>
                            <option value="kutu">Kutu</option>
                        </select>
                    </div>
                </div>
                
                <!-- Sağ Kolon -->
                <div>
                    <!-- Fiyatlandırma -->
                    <div class="bg-gray-50 p-4 rounded mb-4">
                        <h3 class="font-semibold mb-3">Fiyatlandırma</h3>
                        
                        <div class="mb-3">
                            <label class="block text-sm mb-1">Alış Fiyatı</label>
                            <input type="number" step="0.01" name="purchase_price" class="w-full px-3 py-2 border rounded">
                        </div>
                        
                        <div class="mb-3">
                            <label class="block text-sm mb-1">Satış Fiyatı *</label>
                            <input type="number" step="0.01" name="sale_price" required class="w-full px-3 py-2 border rounded">
                        </div>
                        
                        <div class="mb-3">
                            <label class="block text-sm mb-1">KDV Oranı (%)</label>
                            <select name="kdv_rate" class="w-full px-3 py-2 border rounded">
                                <option value="0">%0</option>
                                <option value="1">%1</option>
                                <option value="10">%10</option>
                                <option value="20" selected>%20</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="kdv_included" class="mr-2">
                                <span class="text-sm">KDV Dahil</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Stok Takibi -->
                    <div class="bg-gray-50 p-4 rounded">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold">Stok Takibi</h3>
                            <label class="flex items-center">
                                <input type="checkbox" name="stock_tracking" checked class="mr-2">
                                <span class="text-sm">Aktif</span>
                            </label>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm mb-1">Mevcut Stok</label>
                                <input type="number" step="0.01" name="current_stock" value="0" class="w-full px-3 py-2 border rounded">
                            </div>
                            
                            <div>
                                <label class="block text-sm mb-1">Min. Stok</label>
                                <input type="number" step="0.01" name="min_stock_level" value="0" class="w-full px-3 py-2 border rounded">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Açıklama -->
            <div class="mt-4">
                <label class="block text-sm font-medium mb-1">Açıklama</label>
                <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded"></textarea>
            </div>
            
            <!-- Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="window.history.back()" class="px-4 py-2 border rounded">
                    İptal
                </button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">
                    Kaydet
                </button>
            </div>
        </form>
    </div>
</div>
```

---

## 📝 Özet

Bu modül ile:
- ✅ Kapsamlı ürün/hizmet yönetimi
- ✅ Barkod sistemli hızlı arama
- ✅ Kategori yönetimi
- ✅ Varyant desteği
- ✅ Stok takibi entegrasyonu
- ✅ Fiyat geçmişi
- ✅ Düşük stok uyarıları

**Sonraki Modül:** Stok Yönetimi ve Depo Takibi
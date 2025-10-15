<?php

namespace App\Controllers\Web;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Helpers\Response;

/**
 * ProductController
 * Handles product and service management operations
 */
class ProductController
{
    private Product $productModel;
    private ProductCategory $categoryModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->categoryModel = new ProductCategory();
    }

    /**
     * Get all products with filters and pagination
     * GET /api/products
     */
    public function index(): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        if (!$companyId) {
            Response::json([
                'success' => false,
                'message' => 'Şirket bilgisi bulunamadı'
            ], 400);
            return;
        }

        $filters = [
            'category_id' => $_GET['category_id'] ?? null,
            'product_type' => $_GET['product_type'] ?? null,
            'search' => $_GET['search'] ?? null,
            'is_active' => isset($_GET['is_active']) ? (int)$_GET['is_active'] : null,
            'stock_tracking' => isset($_GET['stock_tracking']) ? (int)$_GET['stock_tracking'] : null,
            'stock_status' => $_GET['stock_status'] ?? null,
            'order_by' => $_GET['order_by'] ?? 'p.product_name',
            'order_dir' => $_GET['order_dir'] ?? 'ASC'
        ];

        $page = $_GET['page'] ?? 1;
        $perPage = $_GET['per_page'] ?? 25;

        $result = $this->productModel->getAll($companyId, $filters, (int)$page, (int)$perPage);

        Response::json([
            'success' => true,
            'data' => $result['data'],
            'pagination' => $result['pagination']
        ]);
    }

    /**
     * Get product by ID
     * GET /api/products/{id}
     */
    public function show(int $id): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        $product = $this->productModel->find($id, $companyId);

        if (!$product) {
            Response::json([
                'success' => false,
                'message' => 'Ürün bulunamadı'
            ], 404);
            return;
        }

        Response::json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Search products
     * GET /api/products/search
     */
    public function search(): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        $query = $_GET['q'] ?? '';
        
        if (empty($query)) {
            Response::json([
                'success' => false,
                'message' => 'Arama sorgusu boş olamaz'
            ], 400);
            return;
        }

        $filters = ['search' => $query];
        $result = $this->productModel->getAll($companyId, $filters, 1, 50);

        Response::json([
            'success' => true,
            'data' => $result['data']
        ]);
    }

    /**
     * Find product by barcode
     * GET /api/products/barcode/{barcode}
     */
    public function findByBarcode(string $barcode): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        $product = $this->productModel->findByBarcode($barcode, $companyId);

        if (!$product) {
            Response::json([
                'success' => false,
                'message' => 'Ürün bulunamadı'
            ], 404);
            return;
        }

        Response::json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Create new product
     * POST /api/products
     */
    public function store(): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        if (!$companyId) {
            Response::json([
                'success' => false,
                'message' => 'Şirket bilgisi bulunamadı'
            ], 400);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $data['company_id'] = $companyId;

        // Validation
        $errors = [];
        if (empty($data['product_name'])) {
            $errors['product_name'] = 'Ürün adı zorunludur';
        }
        if (empty($data['sale_price']) || $data['sale_price'] <= 0) {
            $errors['sale_price'] = 'Satış fiyatı zorunludur ve sıfırdan büyük olmalıdır';
        }

        if (!empty($errors)) {
            Response::json([
                'success' => false,
                'message' => 'Validasyon hatası',
                'errors' => $errors
            ], 400);
            return;
        }

        // Check if product code exists (if provided)
        if (!empty($data['product_code'])) {
            $existing = $this->productModel->findByCode($data['product_code'], $companyId);
            if ($existing) {
                Response::json([
                    'success' => false,
                    'message' => 'Bu ürün kodu zaten kullanılıyor'
                ], 409);
                return;
            }
        }

        // Check if barcode exists (if provided)
        if (!empty($data['barcode'])) {
            $existing = $this->productModel->findByBarcode($data['barcode'], $companyId);
            if ($existing) {
                Response::json([
                    'success' => false,
                    'message' => 'Bu barkod zaten kullanılıyor'
                ], 409);
                return;
            }
        }

        $productId = $this->productModel->create($data);

        if (!$productId) {
            Response::json([
                'success' => false,
                'message' => 'Ürün oluşturulamadı'
            ], 500);
            return;
        }

        $product = $this->productModel->find($productId, $companyId);

        Response::json([
            'success' => true,
            'message' => 'Ürün başarıyla oluşturuldu',
            'data' => $product
        ], 201);
    }

    /**
     * Update product
     * PUT /api/products/{id}
     */
    public function update(int $id): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        $data = json_decode(file_get_contents('php://input'), true);
        $data['changed_by'] = $user['id'] ?? null;

        // Check if product exists
        $product = $this->productModel->find($id, $companyId);
        if (!$product) {
            Response::json([
                'success' => false,
                'message' => 'Ürün bulunamadı'
            ], 404);
            return;
        }

        // Validation
        if (isset($data['sale_price']) && $data['sale_price'] <= 0) {
            Response::json([
                'success' => false,
                'message' => 'Satış fiyatı sıfırdan büyük olmalıdır'
            ], 400);
            return;
        }

        $success = $this->productModel->update($id, $data, $companyId);

        if (!$success) {
            Response::json([
                'success' => false,
                'message' => 'Ürün güncellenemedi'
            ], 500);
            return;
        }

        $updated = $this->productModel->find($id, $companyId);

        Response::json([
            'success' => true,
            'message' => 'Ürün başarıyla güncellendi',
            'data' => $updated
        ]);
    }

    /**
     * Delete product (soft delete)
     * DELETE /api/products/{id}
     */
    public function delete(int $id): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        $product = $this->productModel->find($id, $companyId);
        if (!$product) {
            Response::json([
                'success' => false,
                'message' => 'Ürün bulunamadı'
            ], 404);
            return;
        }

        $success = $this->productModel->delete($id, $companyId);

        if (!$success) {
            Response::json([
                'success' => false,
                'message' => 'Ürün silinemedi'
            ], 500);
            return;
        }

        Response::json([
            'success' => true,
            'message' => 'Ürün başarıyla silindi'
        ]);
    }

    /**
     * Update product stock
     * PUT /api/products/{id}/stock
     */
    public function updateStock(int $id): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        $data = json_decode(file_get_contents('php://input'), true);

        $product = $this->productModel->find($id, $companyId);
        if (!$product) {
            Response::json([
                'success' => false,
                'message' => 'Ürün bulunamadı'
            ], 404);
            return;
        }

        if (!isset($data['quantity'])) {
            Response::json([
                'success' => false,
                'message' => 'Miktar bilgisi gereklidir'
            ], 400);
            return;
        }

        $operation = $data['operation'] ?? 'set'; // set, add, subtract
        $success = $this->productModel->updateStock($id, (float)$data['quantity'], $operation);

        if (!$success) {
            Response::json([
                'success' => false,
                'message' => 'Stok güncellenemedi'
            ], 500);
            return;
        }

        $updated = $this->productModel->find($id, $companyId);

        Response::json([
            'success' => true,
            'message' => 'Stok başarıyla güncellendi',
            'data' => [
                'product_id' => $id,
                'old_stock' => $product['current_stock'],
                'new_stock' => $updated['current_stock']
            ]
        ]);
    }

    /**
     * Get low stock products
     * GET /api/products/low-stock
     */
    public function lowStock(): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        if (!$companyId) {
            Response::json([
                'success' => false,
                'message' => 'Şirket bilgisi bulunamadı'
            ], 400);
            return;
        }

        $products = $this->productModel->getLowStock($companyId);

        Response::json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get out of stock products
     * GET /api/products/out-of-stock
     */
    public function outOfStock(): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        if (!$companyId) {
            Response::json([
                'success' => false,
                'message' => 'Şirket bilgisi bulunamadı'
            ], 400);
            return;
        }

        $products = $this->productModel->getOutOfStock($companyId);

        Response::json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get product statistics
     * GET /api/products/stats
     */
    public function stats(): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        if (!$companyId) {
            Response::json([
                'success' => false,
                'message' => 'Şirket bilgisi bulunamadı'
            ], 400);
            return;
        }

        $stats = $this->productModel->getStats($companyId);

        Response::json([
            'success' => true,
            'data' => $stats
        ]);
    }

    // ==========================================
    // CATEGORY ENDPOINTS
    // ==========================================

    /**
     * Get all categories (tree structure)
     * GET /api/product-categories
     */
    public function categories(): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        if (!$companyId) {
            Response::json([
                'success' => false,
                'message' => 'Şirket bilgisi bulunamadı'
            ], 400);
            return;
        }

        $flat = isset($_GET['flat']) && $_GET['flat'] === 'true';
        
        if ($flat) {
            $categories = $this->categoryModel->getFlat($companyId);
        } else {
            $categories = $this->categoryModel->getAll($companyId);
        }

        Response::json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get category by ID
     * GET /api/product-categories/{id}
     */
    public function showCategory(int $id): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        $category = $this->categoryModel->find($id, $companyId);

        if (!$category) {
            Response::json([
                'success' => false,
                'message' => 'Kategori bulunamadı'
            ], 404);
            return;
        }

        // Get breadcrumb
        $breadcrumb = $this->categoryModel->getBreadcrumb($id);
        $category['breadcrumb'] = $breadcrumb;

        Response::json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Create new category
     * POST /api/product-categories
     */
    public function storeCategory(): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        if (!$companyId) {
            Response::json([
                'success' => false,
                'message' => 'Şirket bilgisi bulunamadı'
            ], 400);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $data['company_id'] = $companyId;

        // Validation
        if (empty($data['category_name'])) {
            Response::json([
                'success' => false,
                'message' => 'Kategori adı zorunludur'
            ], 400);
            return;
        }

        $categoryId = $this->categoryModel->create($data);

        if (!$categoryId) {
            Response::json([
                'success' => false,
                'message' => 'Kategori oluşturulamadı'
            ], 500);
            return;
        }

        $category = $this->categoryModel->find($categoryId, $companyId);

        Response::json([
            'success' => true,
            'message' => 'Kategori başarıyla oluşturuldu',
            'data' => $category
        ], 201);
    }

    /**
     * Update category
     * PUT /api/product-categories/{id}
     */
    public function updateCategory(int $id): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        $data = json_decode(file_get_contents('php://input'), true);

        $category = $this->categoryModel->find($id, $companyId);
        if (!$category) {
            Response::json([
                'success' => false,
                'message' => 'Kategori bulunamadı'
            ], 404);
            return;
        }

        $success = $this->categoryModel->update($id, $data, $companyId);

        if (!$success) {
            Response::json([
                'success' => false,
                'message' => 'Kategori güncellenemedi'
            ], 500);
            return;
        }

        $updated = $this->categoryModel->find($id, $companyId);

        Response::json([
            'success' => true,
            'message' => 'Kategori başarıyla güncellendi',
            'data' => $updated
        ]);
    }

    /**
     * Delete category
     * DELETE /api/product-categories/{id}
     */
    public function deleteCategory(int $id): void
    {
        $user = $_REQUEST['auth_user'] ?? null;
        $companyId = $user['company_id'] ?? null;

        $category = $this->categoryModel->find($id, $companyId);
        if (!$category) {
            Response::json([
                'success' => false,
                'message' => 'Kategori bulunamadı'
            ], 404);
            return;
        }

        $success = $this->categoryModel->delete($id, $companyId);

        if (!$success) {
            Response::json([
                'success' => false,
                'message' => 'Kategori silinemedi. Alt kategorileri veya ürünleri olabilir.'
            ], 400);
            return;
        }

        Response::json([
            'success' => true,
            'message' => 'Kategori başarıyla silindi'
        ]);
    }
}


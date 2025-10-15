<?php

namespace App\Controllers\Web;

use App\Models\Company;
use App\Helpers\Response;

/**
 * CompanyController
 * Handles company management operations
 */
class CompanyController
{
    private Company $companyModel;

    public function __construct()
    {
        $this->companyModel = new Company();
    }

    /**
     * Get all companies (admin only)
     * GET /api/companies
     */
    public function index(): void
    {
        $page = $_GET['page'] ?? 1;
        $perPage = $_GET['per_page'] ?? 25;

        $result = $this->companyModel->paginate((int)$page, (int)$perPage);

        Response::json([
            'success' => true,
            'data' => $result['data'],
            'pagination' => $result['pagination']
        ]);
    }

    /**
     * Get company by ID
     * GET /api/companies/{id}
     */
    public function show(int $id): void
    {
        $company = $this->companyModel->find($id);

        if (!$company) {
            Response::json([
                'success' => false,
                'message' => 'Şirket bulunamadı'
            ], 404);
            return;
        }

        Response::json([
            'success' => true,
            'data' => $company
        ]);
    }

    /**
     * Get current user's company
     * GET /api/company/me
     */
    public function me(): void
    {
        $user = $_REQUEST['auth_user'] ?? null;

        if (!$user || !isset($user['company_id'])) {
            Response::json([
                'success' => false,
                'message' => 'Kullanıcı şirket bilgisi bulunamadı'
            ], 400);
            return;
        }

        $company = $this->companyModel->find($user['company_id']);

        if (!$company) {
            Response::json([
                'success' => false,
                'message' => 'Şirket bulunamadı'
            ], 404);
            return;
        }

        Response::json([
            'success' => true,
            'data' => $company
        ]);
    }

    /**
     * Create new company
     * POST /api/companies
     */
    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validation
        $errors = [];
        if (empty($data['name'])) {
            $errors['name'] = 'Şirket adı zorunludur';
        }
        if (empty($data['owner_id'])) {
            $errors['owner_id'] = 'Şirket sahibi zorunludur';
        }

        if (!empty($errors)) {
            Response::json([
                'success' => false,
                'message' => 'Validasyon hatası',
                'errors' => $errors
            ], 400);
            return;
        }

        // Check if name already exists
        if ($this->companyModel->nameExists($data['name'])) {
            Response::json([
                'success' => false,
                'message' => 'Bu şirket adı zaten kullanılıyor'
            ], 409);
            return;
        }

        $companyId = $this->companyModel->create($data);

        if (!$companyId) {
            Response::json([
                'success' => false,
                'message' => 'Şirket oluşturulamadı'
            ], 500);
            return;
        }

        $company = $this->companyModel->find($companyId);

        Response::json([
            'success' => true,
            'message' => 'Şirket başarıyla oluşturuldu',
            'data' => $company
        ], 201);
    }

    /**
     * Update company
     * PUT /api/companies/{id}
     */
    public function update(int $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $user = $_REQUEST['auth_user'] ?? null;

        // Security: User can only update their own company (unless super admin)
        if (!$user['is_super_admin'] && $user['company_id'] != $id) {
            Response::json([
                'success' => false,
                'message' => 'Bu şirketi güncelleme yetkiniz yok'
            ], 403);
            return;
        }

        // Check if company exists
        $company = $this->companyModel->find($id);
        if (!$company) {
            Response::json([
                'success' => false,
                'message' => 'Şirket bulunamadı'
            ], 404);
            return;
        }

        // Check name uniqueness if name is being updated
        if (isset($data['name']) && $data['name'] !== $company['name']) {
            if ($this->companyModel->nameExists($data['name'], $id)) {
                Response::json([
                    'success' => false,
                    'message' => 'Bu şirket adı zaten kullanılıyor'
                ], 409);
                return;
            }
        }

        $success = $this->companyModel->update($id, $data);

        if (!$success) {
            Response::json([
                'success' => false,
                'message' => 'Şirket güncellenemedi'
            ], 500);
            return;
        }

        $updated = $this->companyModel->find($id);

        Response::json([
            'success' => true,
            'message' => 'Şirket başarıyla güncellendi',
            'data' => $updated
        ]);
    }

    /**
     * Delete company (soft delete)
     * DELETE /api/companies/{id}
     */
    public function delete(int $id): void
    {
        $user = $_REQUEST['auth_user'] ?? null;

        // Only super admin can delete companies
        if (!$user['is_super_admin']) {
            Response::json([
                'success' => false,
                'message' => 'Bu işlem için yetkiniz yok'
            ], 403);
            return;
        }

        $company = $this->companyModel->find($id);
        if (!$company) {
            Response::json([
                'success' => false,
                'message' => 'Şirket bulunamadı'
            ], 404);
            return;
        }

        $success = $this->companyModel->delete($id);

        if (!$success) {
            Response::json([
                'success' => false,
                'message' => 'Şirket silinemedi'
            ], 500);
            return;
        }

        Response::json([
            'success' => true,
            'message' => 'Şirket başarıyla silindi'
        ]);
    }

    /**
     * Upload company logo
     * POST /api/companies/{id}/logo
     */
    public function uploadLogo(int $id): void
    {
        $user = $_REQUEST['auth_user'] ?? null;

        // Security check
        if (!$user['is_super_admin'] && $user['company_id'] != $id) {
            Response::json([
                'success' => false,
                'message' => 'Bu işlem için yetkiniz yok'
            ], 403);
            return;
        }

        if (!isset($_FILES['logo'])) {
            Response::json([
                'success' => false,
                'message' => 'Logo dosyası yüklenmedi'
            ], 400);
            return;
        }

        $file = $_FILES['logo'];

        // Validate file
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            Response::json([
                'success' => false,
                'message' => 'Sadece resim dosyaları yüklenebilir (JPG, PNG, GIF)'
            ], 400);
            return;
        }

        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($file['size'] > $maxSize) {
            Response::json([
                'success' => false,
                'message' => 'Dosya boyutu 2MB\'dan küçük olmalıdır'
            ], 400);
            return;
        }

        // Create upload directory
        $uploadDir = __DIR__ . '/../../../storage/uploads/logos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'company_' . $id . '_' . time() . '.' . $extension;
        $uploadPath = $uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            Response::json([
                'success' => false,
                'message' => 'Dosya yüklenemedi'
            ], 500);
            return;
        }

        // Update company logo path
        $logoPath = '/storage/uploads/logos/' . $filename;
        $success = $this->companyModel->update($id, ['logo_path' => $logoPath]);

        if (!$success) {
            Response::json([
                'success' => false,
                'message' => 'Logo yolu güncellenemedi'
            ], 500);
            return;
        }

        Response::json([
            'success' => true,
            'message' => 'Logo başarıyla yüklendi',
            'data' => [
                'logo_path' => $logoPath
            ]
        ]);
    }
}


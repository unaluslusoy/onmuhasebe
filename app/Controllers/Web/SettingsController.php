<?php

namespace App\Controllers\Web;

use App\Models\User;
use App\Models\Company;
use App\Models\Category;
use App\Helpers\Response;

/**
 * Settings Controller
 * Sistem ayarları yönetimi (Genel, Şirket, Güvenlik)
 */
class SettingsController
{
    private User $userModel;
    private Company $companyModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->companyModel = new Company();
        $this->categoryModel = new Category();
    }

    /**
     * Genel Ayarlar Sayfası
     * Kullanıcı profil bilgileri
     */
    public function genel($params = [])
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            header('Location: /giris');
            exit;
        }

        $user = $this->userModel->find($userId);
        
        if (!$user) {
            header('Location: /giris');
            exit;
        }

        $company = null;
        if (!empty($user['company_id'])) {
            $company = $this->companyModel->find($user['company_id']);
        }

        $data = [
            'user' => $user,
            'company' => $company,
            'pageTitle' => 'Genel Ayarlar',
            'pageIcon' => 'ki-duotone ki-user',
            'breadcrumbs' => [
                ['text' => 'Anasayfa', 'url' => '/'],
                ['text' => 'Ayarlar', 'url' => '/ayarlar/genel'],
                ['text' => 'Genel', 'url' => null]
            ],
            'activeTab' => 'genel'
        ];

        extract($data);
        $contentFile = __DIR__ . '/../../Views/settings/genel.php';
        require_once __DIR__ . '/../../Views/layouts/master.php';
    }

    /**
     * Şirket Ayarları Sayfası
     * Şirket bilgileri ve kurumsal ayarlar
     */
    public function sirket($params = [])
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            header('Location: /giris');
            exit;
        }

        $user = $this->userModel->find($userId);
        
        if (!$user) {
            header('Location: /giris');
            exit;
        }

        $company = null;
        if (!empty($user['company_id'])) {
            $company = $this->companyModel->find($user['company_id']);
        }

        $data = [
            'user' => $user,
            'company' => $company,
            'pageTitle' => 'Şirket Ayarları',
            'pageIcon' => 'ki-duotone ki-office-bag',
            'breadcrumbs' => [
                ['text' => 'Anasayfa', 'url' => '/'],
                ['text' => 'Ayarlar', 'url' => '/ayarlar/genel'],
                ['text' => 'Şirket', 'url' => null]
            ],
            'activeTab' => 'sirket'
        ];

        extract($data);
        $contentFile = __DIR__ . '/../../Views/settings/sirket.php';
        require_once __DIR__ . '/../../Views/layouts/master.php';
    }

    /**
     * Güvenlik Ayarları Sayfası
     * Şifre değiştirme, 2FA, oturum yönetimi
     */
    public function guvenlik($params = [])
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            header('Location: /giris');
            exit;
        }

        $user = $this->userModel->find($userId);
        
        if (!$user) {
            header('Location: /giris');
            exit;
        }

        $data = [
            'user' => $user,
            'pageTitle' => 'Güvenlik Ayarları',
            'pageIcon' => 'ki-duotone ki-shield-tick',
            'breadcrumbs' => [
                ['text' => 'Anasayfa', 'url' => '/'],
                ['text' => 'Ayarlar', 'url' => '/ayarlar/genel'],
                ['text' => 'Güvenlik', 'url' => null]
            ],
            'activeTab' => 'guvenlik'
        ];

        extract($data);
        $contentFile = __DIR__ . '/../../Views/settings/guvenlik.php';
        require_once __DIR__ . '/../../Views/layouts/master.php';
    }

    /**
     * Genel Bilgileri Güncelle (POST)
     */
    public function updateGenel()
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = [
            'full_name' => $_POST['full_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? ''
        ];

        // Validasyon
        $errors = [];

        if (empty($data['full_name'])) {
            $errors['full_name'] = 'Ad Soyad zorunludur';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'E-posta zorunludur';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Geçerli bir e-posta adresi giriniz';
        }

        // Email başka kullanıcı tarafından kullanılıyor mu?
        if (!empty($data['email'])) {
            if ($this->userModel->emailExists($data['email'], $userId)) {
                $errors['email'] = 'Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor';
            }
        }

        if (!empty($errors)) {
            return Response::json([
                'success' => false,
                'message' => 'Lütfen hataları düzeltin',
                'errors' => $errors
            ], 422);
        }

        $updated = $this->userModel->update($userId, $data);

        if ($updated) {
            $_SESSION['user_name'] = $data['full_name'];
            $_SESSION['user_email'] = $data['email'];

            return Response::json([
                'success' => true,
                'message' => 'Bilgileriniz başarıyla güncellendi'
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Güncelleme sırasında bir hata oluştu'
        ], 500);
    }

    /**
     * Şirket Bilgilerini Güncelle (POST)
     */
    public function updateSirket()
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user = $this->userModel->find($userId);
        
        if (!$user || empty($user['company_id'])) {
            return Response::json([
                'success' => false,
                'message' => 'Şirket bulunamadı'
            ], 404);
        }

        $data = [
            'name' => $_POST['company_name'] ?? '',
            'trade_name' => $_POST['trade_name'] ?? '',
            'tax_office' => $_POST['tax_office'] ?? '',
            'tax_number' => $_POST['tax_number'] ?? '',
            'vkn' => $_POST['vkn'] ?? '',
            'address' => $_POST['address'] ?? '',
            'district' => $_POST['district'] ?? '',
            'city' => $_POST['city'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? '',
            'phone' => $_POST['company_phone'] ?? '',
            'email' => $_POST['company_email'] ?? '',
            'website' => $_POST['website'] ?? ''
        ];

        $errors = [];

        if (empty($data['name'])) {
            $errors['company_name'] = 'Şirket adı zorunludur';
        }

        if (!empty($errors)) {
            return Response::json([
                'success' => false,
                'message' => 'Lütfen hataları düzeltin',
                'errors' => $errors
            ], 422);
        }

        $updated = $this->companyModel->update($user['company_id'], $data);

        if ($updated) {
            return Response::json([
                'success' => true,
                'message' => 'Şirket bilgileri başarıyla güncellendi'
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Güncelleme sırasında bir hata oluştu'
        ], 500);
    }

    /**
     * Şifre Değiştir (POST)
     */
    public function changePassword()
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];

        if (empty($currentPassword)) {
            $errors['current_password'] = 'Mevcut şifre zorunludur';
        }

        if (empty($newPassword)) {
            $errors['new_password'] = 'Yeni şifre zorunludur';
        } elseif (strlen($newPassword) < 8) {
            $errors['new_password'] = 'Şifre en az 8 karakter olmalıdır';
        }

        if ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'Şifreler eşleşmiyor';
        }

        if (!empty($errors)) {
            return Response::json([
                'success' => false,
                'message' => 'Lütfen hataları düzeltin',
                'errors' => $errors
            ], 422);
        }

        // Mevcut şifreyi kontrol et
        $user = $this->userModel->findForAuth($_SESSION['user_email']);
        
        if (!$user || !$this->userModel->verifyPassword($currentPassword, $user['password'])) {
            return Response::json([
                'success' => false,
                'message' => 'Mevcut şifreniz hatalı',
                'errors' => ['current_password' => 'Mevcut şifreniz hatalı']
            ], 422);
        }

        $changed = $this->userModel->changePassword($userId, $newPassword);

        if ($changed) {
            return Response::json([
                'success' => true,
                'message' => 'Şifreniz başarıyla değiştirildi'
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Şifre değiştirme sırasında bir hata oluştu'
        ], 500);
    }

    /**
     * Hesabı Devre Dışı Bırak (POST)
     */
    public function deactivateAccount()
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $password = $_POST['confirm_password'] ?? '';

        if (empty($password)) {
            return Response::json([
                'success' => false,
                'message' => 'Şifre zorunludur',
                'errors' => ['confirm_password' => 'Şifre zorunludur']
            ], 422);
        }

        // Şifreyi kontrol et
        $user = $this->userModel->findForAuth($_SESSION['user_email']);
        
        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            return Response::json([
                'success' => false,
                'message' => 'Şifreniz hatalı',
                'errors' => ['confirm_password' => 'Şifreniz hatalı']
            ], 422);
        }

        // Hesabı pasif yap
        $deactivated = $this->userModel->setActive($userId, false);

        if ($deactivated) {
            // Session'ı temizle
            session_destroy();
            
            return Response::json([
                'success' => true,
                'message' => 'Hesabınız devre dışı bırakıldı'
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'İşlem sırasında bir hata oluştu'
        ], 500);
    }

    /**
     * Firma Bilgileri Sayfası
     */
    public function firma($params = [])
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            header('Location: /giris');
            exit;
        }

        $user = $this->userModel->find($userId);
        
        if (!$user) {
            header('Location: /giris');
            exit;
        }

        // Get company data if exists
        $company = null;
        if (!empty($user['company_id'])) {
            $company = $this->companyModel->find($user['company_id']);
        }

        $data = [
            'user' => $user,
            'company' => $company,
            'pageTitle' => 'Firma Bilgileri',
            'pageIcon' => 'ki-duotone ki-briefcase',
            'breadcrumbs' => [
                ['text' => 'Anasayfa', 'url' => '/'],
                ['text' => 'Ayarlar', 'url' => '/ayarlar/genel'],
                ['text' => 'Firma Bilgileri', 'url' => null]
            ],
            'activeTab' => 'firma'
        ];

        extract($data);
        $contentFile = __DIR__ . '/../../Views/settings/firma.php';
        require_once __DIR__ . '/../../Views/layouts/master.php';
    }

    /**
     * Kullanıcılar Yönetimi Sayfası
     */
    public function kullanicilar($params = [])
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            header('Location: /giris');
            exit;
        }

        $user = $this->userModel->find($userId);
        
        if (!$user) {
            header('Location: /giris');
            exit;
        }

        // Get all users for the company
        $users = [];
        if (!empty($user['company_id'])) {
            $users = $this->userModel->all(['company_id' => $user['company_id']]);
        }

        $data = [
            'user' => $user,
            'users' => $users,
            'pageTitle' => 'Kullanıcılar Yönetimi',
            'pageIcon' => 'ki-duotone ki-people',
            'breadcrumbs' => [
                ['text' => 'Anasayfa', 'url' => '/'],
                ['text' => 'Ayarlar', 'url' => '/ayarlar/genel'],
                ['text' => 'Kullanıcılar', 'url' => null]
            ],
            'activeTab' => 'kullanicilar'
        ];

        extract($data);
        $contentFile = __DIR__ . '/../../Views/settings/kullanicilar.php';
        require_once __DIR__ . '/../../Views/layouts/master.php';
    }

    /**
     * Kategoriler Yönetimi Sayfası
     */
    public function kategoriler($params = [])
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            header('Location: /giris');
            exit;
        }

        $user = $this->userModel->find($userId);
        
        if (!$user) {
            header('Location: /giris');
            exit;
        }

        // Get all categories for the company
        $categories = [];
        $categoryCounts = [];
        
        if (!empty($user['company_id'])) {
            $categories = $this->categoryModel->getByCompany($user['company_id']);
            
            // Get counts per type
            foreach (Category::getTypes() as $typeKey => $typeName) {
                $categoryCounts[$typeKey] = $this->categoryModel->countByType($user['company_id'], $typeKey);
            }
        }

        $data = [
            'user' => $user,
            'categories' => $categories,
            'categoryCounts' => $categoryCounts,
            'categoryTypes' => Category::getTypes(),
            'pageTitle' => 'Kategori ve Etiketler',
            'pageIcon' => 'ki-duotone ki-element-11',
            'breadcrumbs' => [
                ['text' => 'Anasayfa', 'url' => '/'],
                ['text' => 'Ayarlar', 'url' => '/ayarlar/genel'],
                ['text' => 'Kategoriler', 'url' => null]
            ],
            'activeTab' => 'kategoriler'
        ];

        extract($data);
        $contentFile = __DIR__ . '/../../Views/settings/kategoriler.php';
        require_once __DIR__ . '/../../Views/layouts/master.php';
    }

    /**
     * Firma Bilgileri Güncelleme
     */
    public function updateFirma()
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::json(['success' => false, 'message' => 'Oturum bulunamadı'], 401);
        }

        $user = $this->userModel->find($userId);
        
        if (!$user || empty($user['company_id'])) {
            return Response::json(['success' => false, 'message' => 'Şirket bilgisi bulunamadı'], 404);
        }

        try {
            // Prepare update data
            $data = [
                'document_type' => $_POST['document_type'] ?? 'invoice',
                'sector' => $_POST['sector'] ?? null,
                'annual_revenue' => !empty($_POST['annual_revenue']) ? floatval($_POST['annual_revenue']) : null,
                'employee_count' => !empty($_POST['employee_count']) ? intval($_POST['employee_count']) : null,
                'foundation_year' => !empty($_POST['foundation_year']) ? intval($_POST['foundation_year']) : null,
                'business_description' => $_POST['business_description'] ?? null
            ];

            // Handle logo upload
            if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->handleFileUpload($_FILES['company_logo'], $user['company_id'], 'logo');
                if ($uploadResult) {
                    $data['company_logo'] = $uploadResult;
                }
            }

            // Handle signature upload
            if (isset($_FILES['company_signature']) && $_FILES['company_signature']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->handleFileUpload($_FILES['company_signature'], $user['company_id'], 'signature');
                if ($uploadResult) {
                    $data['company_signature'] = $uploadResult;
                }
            }

            // Update company
            $updated = $this->companyModel->update($user['company_id'], $data);

            if ($updated) {
                return Response::json([
                    'success' => true,
                    'message' => 'Firma bilgileri başarıyla güncellendi'
                ]);
            }

            return Response::json([
                'success' => false,
                'message' => 'Firma bilgileri güncellenemedi'
            ], 500);

        } catch (\Exception $e) {
            logger('Firma güncelleme hatası: ' . $e->getMessage(), 'error');
            return Response::json([
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle file upload for company images
     */
    private function handleFileUpload($file, $companyId, $type): ?string
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }

        if ($file['size'] > $maxSize) {
            return null;
        }

        $uploadDir = __DIR__ . '/../../../storage/uploads/companies/' . $companyId . '/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $type . '_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return $filename;
        }

        return null;
    }
}


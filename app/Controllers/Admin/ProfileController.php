<?php

namespace App\Controllers\Admin;

use App\Models\User;
use App\Models\Company;
use App\Helpers\Response;

/**
 * Profile Controller
 * Kullanıcı profil yönetimi
 */
class ProfileController
{
    private User $userModel;
    private Company $companyModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->companyModel = new Company();
    }

    /**
     * Profil sayfası
     */
    public function index()
    {
        // Session'dan kullanıcı bilgilerini al
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            header('Location: /auth/login');
            exit;
        }

        // Kullanıcı bilgilerini getir
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            header('Location: /auth/login');
            exit;
        }

        // Şirket bilgilerini getir (varsa)
        $company = null;
        if (!empty($user['company_id'])) {
            $company = $this->companyModel->find($user['company_id']);
        }

        // View'e gönderilecek veriler
        $data = [
            'user' => $user,
            'company' => $company,
            'pageTitle' => 'Profilim',
            'pageIcon' => 'ki-duotone ki-user',
            'breadcrumbs' => [
                ['text' => 'Anasayfa', 'url' => '/'],
                ['text' => 'Profilim', 'url' => null]
            ]
        ];

        // View'i yükle
        extract($data);
        $contentFile = __DIR__ . '/../../Views/profile/profile-content.php';
        require_once __DIR__ . '/../../Views/layouts/master.php';
    }

    /**
     * Profil düzenleme sayfası
     */
    public function edit()
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            header('Location: /auth/login');
            exit;
        }

        $user = $this->userModel->find($userId);
        
        if (!$user) {
            header('Location: /auth/login');
            exit;
        }

        $company = null;
        if (!empty($user['company_id'])) {
            $company = $this->companyModel->find($user['company_id']);
        }

        $data = [
            'user' => $user,
            'company' => $company,
            'pageTitle' => 'Profili Düzenle',
            'pageIcon' => 'ki-duotone ki-pencil',
            'breadcrumbs' => [
                ['text' => 'Anasayfa', 'url' => '/'],
                ['text' => 'Profilim', 'url' => '/profil'],
                ['text' => 'Düzenle', 'url' => null]
            ]
        ];

        extract($data);
        $contentFile = __DIR__ . '/../../Views/profile/edit-content.php';
        require_once __DIR__ . '/../../Views/layouts/master.php';
    }

    /**
     * Profil güncelleme (POST)
     */
    public function update()
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // POST verilerini al
        $data = [
            'full_name' => $_POST['full_name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'email' => $_POST['email'] ?? ''
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

        // Avatar yükleme işlemi
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../storage/uploads/avatars/';
            
            // Klasör yoksa oluştur
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileInfo = pathinfo($_FILES['avatar']['name']);
            $extension = strtolower($fileInfo['extension']);
            
            // Dosya türü kontrolü
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            if (!in_array($extension, $allowedExtensions)) {
                return Response::json([
                    'success' => false,
                    'message' => 'Sadece JPG, JPEG ve PNG dosyaları yüklenebilir'
                ], 422);
            }

            // Dosya boyutu kontrolü (max 2MB)
            if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
                return Response::json([
                    'success' => false,
                    'message' => 'Dosya boyutu maksimum 2MB olabilir'
                ], 422);
            }

            // Benzersiz dosya adı oluştur
            $fileName = 'avatar_' . $userId . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . $fileName;

            // Dosyayı kaydet
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $filePath)) {
                // Eski avatar'ı sil
                $user = $this->userModel->find($userId);
                if (!empty($user['avatar']) && file_exists(__DIR__ . '/../../../storage/uploads/avatars/' . $user['avatar'])) {
                    unlink(__DIR__ . '/../../../storage/uploads/avatars/' . $user['avatar']);
                }
                
                $data['avatar'] = $fileName;
            }
        }

        // Avatar silme işlemi
        if (isset($_POST['avatar_remove']) && $_POST['avatar_remove'] === '1') {
            $user = $this->userModel->find($userId);
            if (!empty($user['avatar']) && file_exists(__DIR__ . '/../../../storage/uploads/avatars/' . $user['avatar'])) {
                unlink(__DIR__ . '/../../../storage/uploads/avatars/' . $user['avatar']);
            }
            $data['avatar'] = null;
        }

        // Kullanıcıyı güncelle
        $updated = $this->userModel->update($userId, $data);

        if ($updated) {
            // Session'daki bilgileri güncelle
            $_SESSION['user_name'] = $data['full_name'];
            $_SESSION['user_email'] = $data['email'];

            return Response::json([
                'success' => true,
                'message' => 'Profiliniz başarıyla güncellendi'
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Profil güncellenirken bir hata oluştu'
        ], 500);
    }

    /**
     * Şifre değiştirme (POST)
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

        // Validasyon
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

        // Şifreyi değiştir
        $changed = $this->userModel->changePassword($userId, $newPassword);

        if ($changed) {
            return Response::json([
                'success' => true,
                'message' => 'Şifreniz başarıyla değiştirildi'
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Şifre değiştirilirken bir hata oluştu'
        ], 500);
    }

    /**
     * Ayarlar sayfası (tüm sekmeler)
     */
    public function settings()
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            header('Location: /auth/login');
            exit;
        }

        $user = $this->userModel->find($userId);
        
        if (!$user) {
            header('Location: /auth/login');
            exit;
        }

        $company = null;
        if (!empty($user['company_id'])) {
            $company = $this->companyModel->find($user['company_id']);
        }

        $data = [
            'user' => $user,
            'company' => $company,
            'pageTitle' => 'Ayarlar',
            'pageIcon' => 'ki-duotone ki-setting-2',
            'breadcrumbs' => [
                ['text' => 'Anasayfa', 'url' => '/'],
                ['text' => 'Profilim', 'url' => '/profil'],
                ['text' => 'Ayarlar', 'url' => null]
            ]
        ];

        extract($data);
        $contentFile = __DIR__ . '/../../Views/profile/settings-content.php';
        require_once __DIR__ . '/../../Views/layouts/master.php';
    }

    /**
     * Şirket bilgilerini güncelle (POST)
     */
    public function updateCompany()
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

        // POST verilerini al
        $data = [
            'name' => $_POST['company_name'] ?? '',
            'tax_office' => $_POST['tax_office'] ?? '',
            'tax_number' => $_POST['tax_number'] ?? '',
            'address' => $_POST['address'] ?? '',
            'district' => $_POST['district'] ?? '',
            'city' => $_POST['city'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? '',
            'phone' => $_POST['company_phone'] ?? '',
            'email' => $_POST['company_email'] ?? '',
            'website' => $_POST['website'] ?? ''
        ];

        // Validasyon
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

        // Şirketi güncelle
        $updated = $this->companyModel->update($user['company_id'], $data);

        if ($updated) {
            return Response::json([
                'success' => true,
                'message' => 'Şirket bilgileri başarıyla güncellendi'
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Şirket bilgileri güncellenirken bir hata oluştu'
        ], 500);
    }

    /**
     * Hesabı devre dışı bırak (POST)
     */
    public function deactivate()
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $password = $_POST['confirm_password'] ?? '';

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
            'message' => 'Hesap devre dışı bırakılırken bir hata oluştu'
        ], 500);
    }
}

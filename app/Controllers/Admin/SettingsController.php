<?php

namespace App\Controllers\Admin;

use App\Models\User;
use App\Models\Company;
use App\Helpers\Response;

/**
 * Settings Controller
 * Handles settings pages and company image uploads
 */
class SettingsController
{
    private User $userModel;
    private Company $companyModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->companyModel = new Company();
    }

    /**
     * General settings page
     */
    public function genel()
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
            'activeTab' => 'genel',
            'pageTitle' => 'Genel Ayarlar',
            'pageIcon' => 'ki-duotone ki-setting-2',
            'breadcrumbs' => [
                ['text' => 'Anasayfa', 'url' => '/'],
                ['text' => 'Ayarlar', 'url' => '/ayarlar'],
                ['text' => 'Genel', 'url' => null]
            ]
        ];

        extract($data);
        $contentFile = __DIR__ . '/../../Views/settings/genel.php';
        require_once __DIR__ . '/../../Views/layouts/master.php';
    }

    /**
     * Company settings page
     */
    public function sirket()
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
            'activeTab' => 'sirket',
            'pageTitle' => 'Şirket Ayarları',
            'pageIcon' => 'ki-duotone ki-setting-2',
            'breadcrumbs' => [
                ['text' => 'Anasayfa', 'url' => '/'],
                ['text' => 'Ayarlar', 'url' => '/ayarlar'],
                ['text' => 'Şirket', 'url' => null]
            ]
        ];

        extract($data);
        $contentFile = __DIR__ . '/../../Views/settings/sirket.php';
        require_once __DIR__ . '/../../Views/layouts/master.php';
    }

    /**
     * Update company images (logo, stamp, signature)
     */
    public function updateCompanyImages()
    {
        error_log('=== updateCompanyImages CALLED ===');
        error_log('POST: ' . json_encode($_POST));
        error_log('FILES: ' . json_encode(array_keys($_FILES)));
        
        $userId = $_SESSION['user_id'] ?? null;
        error_log('User ID: ' . ($userId ?? 'NULL'));
        
        if (!$userId) {
            error_log('ERROR: No user ID in session');
            return Response::json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user = $this->userModel->find($userId);
        error_log('User found: ' . ($user ? 'YES' : 'NO'));
        
        if (!$user || empty($user['company_id'])) {
            error_log('ERROR: User not found or no company');
            return Response::json([
                'success' => false,
                'message' => 'Şirket bulunamadı'
            ], 404);
        }

        $companyId = $user['company_id'];
        error_log('Company ID: ' . $companyId);
        $company = $this->companyModel->find($companyId);
        
        if (!$company) {
            return Response::json([
                'success' => false,
                'message' => 'Şirket bilgileri bulunamadı'
            ], 404);
        }

        $uploadDir = __DIR__ . '/../../../storage/uploads/companies/' . $companyId . '/';
        
        // Klasör yoksa oluştur
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $data = [];
        $imageTypes = ['company_logo', 'company_stamp', 'company_signature'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        foreach ($imageTypes as $type) {
            // Silme isteği kontrolü
            if (isset($_POST[$type . '_remove']) && $_POST[$type . '_remove'] === '1') {
                if (!empty($company[$type]) && file_exists($uploadDir . $company[$type])) {
                    unlink($uploadDir . $company[$type]);
                }
                $data[$type] = null;
                continue;
            }

            // Yeni dosya yükleme kontrolü
            if (isset($_FILES[$type]) && $_FILES[$type]['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES[$type];
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                // Dosya türü kontrolü
                if (!in_array($extension, $allowedExtensions)) {
                    return Response::json([
                        'success' => false,
                        'message' => 'Geçersiz dosya türü. Sadece ' . implode(', ', $allowedExtensions) . ' dosyaları yüklenebilir.'
                    ], 400);
                }

                // Dosya boyutu kontrolü
                if ($file['size'] > $maxSize) {
                    return Response::json([
                        'success' => false,
                        'message' => 'Dosya boyutu çok büyük. Maksimum 5MB yüklenebilir.'
                    ], 400);
                }

                // Eski dosyayı sil
                if (!empty($company[$type]) && file_exists($uploadDir . $company[$type])) {
                    unlink($uploadDir . $company[$type]);
                }

                // Yeni dosya adı oluştur
                $newFileName = $type . '_' . time() . '_' . uniqid() . '.' . $extension;
                $targetPath = $uploadDir . $newFileName;

                // Dosyayı kaydet
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $data[$type] = $newFileName;
                } else {
                    return Response::json([
                        'success' => false,
                        'message' => 'Dosya yüklenirken bir hata oluştu.'
                    ], 500);
                }
            }
        }

        // Eğer güncelleme yapıldıysa veritabanını güncelle
        if (!empty($data)) {
            $updated = $this->companyModel->update($companyId, $data);
            
            if ($updated) {
                return Response::json([
                    'success' => true,
                    'message' => 'Görseller başarıyla güncellendi'
                ]);
            } else {
                return Response::json([
                    'success' => false,
                    'message' => 'Veritabanı güncellenirken bir hata oluştu'
                ], 500);
            }
        }

        return Response::json([
            'success' => false,
            'message' => 'Güncelleme yapılacak veri bulunamadı'
        ], 400);
    }

    /**
     * Update general settings
     */
    public function updateGenel()
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Genel ayarlar güncelleme mantığı buraya gelecek
        return Response::json([
            'success' => true,
            'message' => 'Genel ayarlar güncellendi'
        ]);
    }

    /**
     * Update company information
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

        $companyId = $user['company_id'];
        
        // Şirket temel bilgileri
        $data = [
            'name' => $_POST['name'] ?? null,
            'tax_office' => $_POST['tax_office'] ?? null,
            'tax_number' => $_POST['tax_number'] ?? null,
            'address' => $_POST['address'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'email' => $_POST['email'] ?? null,
            'website' => $_POST['website'] ?? null,
        ];
        
        // Firma iş bilgileri
        if (isset($_POST['document_type'])) {
            $data['document_type'] = $_POST['document_type'];
        }
        if (isset($_POST['sector'])) {
            $data['sector'] = $_POST['sector'];
        }
        if (isset($_POST['annual_revenue']) && $_POST['annual_revenue'] !== '') {
            $data['annual_revenue'] = floatval($_POST['annual_revenue']);
        }
        if (isset($_POST['employee_count']) && $_POST['employee_count'] !== '') {
            $data['employee_count'] = intval($_POST['employee_count']);
        }
        if (isset($_POST['foundation_year']) && $_POST['foundation_year'] !== '') {
            $data['foundation_year'] = intval($_POST['foundation_year']);
        }
        if (isset($_POST['business_description'])) {
            $data['business_description'] = $_POST['business_description'];
        }

        // Boş değerleri temizle
        $data = array_filter($data, fn($value) => $value !== null && $value !== '');

        if (empty($data)) {
            return Response::json([
                'success' => false,
                'message' => 'Güncelleme yapılacak veri bulunamadı'
            ], 400);
        }

        $updated = $this->companyModel->update($companyId, $data);
        
        if ($updated) {
            return Response::json([
                'success' => true,
                'message' => 'Şirket bilgileri başarıyla güncellendi'
            ]);
        } else {
            return Response::json([
                'success' => false,
                'message' => 'Güncelleme yapılırken bir hata oluştu'
            ], 500);
        }
    }
}

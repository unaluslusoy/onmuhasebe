<?php

namespace App\Controllers\Web;

use App\Models\Company;
use App\Models\User;

/**
 * CompanyRegisterController
 * Handles company registration for users
 */
class CompanyRegisterController
{
    private Company $companyModel;
    private User $userModel;

    public function __construct()
    {
        $this->companyModel = new Company();
        $this->userModel = new User();
    }

    /**
     * Show company registration form
     * GET /sirket/olustur
     */
    public function create(): void
    {
        // Check authentication
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: /giris');
            exit;
        }

        // Get user data
        $user = $this->userModel->find($userId);
        if (!$user) {
            header('Location: /giris');
            exit;
        }

        // Check if user already has a company
        if (!empty($user['company_id'])) {
            $_SESSION['error'] = 'Zaten bir şirket kaydınız bulunmaktadır.';
            header('Location: /ayarlar/sirket');
            exit;
        }

        // Prepare view data
        $data = [
            'user' => $user,
            'pageTitle' => 'Şirket Oluştur',
            'pageIcon' => 'ki-duotone ki-office-bag',
            'breadcrumbs' => [
                ['text' => 'Anasayfa', 'url' => '/'],
                ['text' => 'Şirket Oluştur']
            ]
        ];

        extract($data);
        $contentFile = __DIR__ . '/../../Views/company/create.php';
        require_once __DIR__ . '/../../Views/layouts/master.php';
    }

    /**
     * Store new company
     * POST /sirket/kaydet
     */
    public function store(): void
    {
        header('Content-Type: application/json');

        // Check authentication
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            echo json_encode([
                'success' => false,
                'message' => 'Oturum bulunamadı. Lütfen tekrar giriş yapın.'
            ]);
            exit;
        }

        // Get user data
        $user = $this->userModel->find($userId);
        if (!$user) {
            echo json_encode([
                'success' => false,
                'message' => 'Kullanıcı bulunamadı.'
            ]);
            exit;
        }

        // Check if user already has a company
        if (!empty($user['company_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Zaten bir şirket kaydınız bulunmaktadır.'
            ]);
            exit;
        }

        // Get POST data
        $name = trim($_POST['name'] ?? '');
        $tradeName = trim($_POST['trade_name'] ?? '');
        $companyType = trim($_POST['company_type'] ?? 'limited');
        $taxOffice = trim($_POST['tax_office'] ?? '');
        $taxNumber = trim($_POST['tax_number'] ?? '');
        $vkn = trim($_POST['vkn'] ?? '');
        $tckn = trim($_POST['tckn'] ?? '');
        $mersisNo = trim($_POST['mersis_no'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $country = trim($_POST['country'] ?? 'Türkiye');
        $postalCode = trim($_POST['postal_code'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $fax = trim($_POST['fax'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $website = trim($_POST['website'] ?? '');

        // Validation
        $errors = [];

        if (empty($name)) {
            $errors['name'] = 'Şirket adı zorunludur.';
        }

        if (empty($companyType)) {
            $errors['company_type'] = 'Şirket tipi zorunludur.';
        }

        // VKN validation (10 digits)
        if (!empty($vkn) && !preg_match('/^\d{10}$/', $vkn)) {
            $errors['vkn'] = 'VKN 10 haneli olmalıdır.';
        }

        // TCKN validation (11 digits)
        if (!empty($tckn) && !preg_match('/^\d{11}$/', $tckn)) {
            $errors['tckn'] = 'TCKN 11 haneli olmalıdır.';
        }

        // MERSİS validation (16 digits)
        if (!empty($mersisNo) && !preg_match('/^\d{16}$/', $mersisNo)) {
            $errors['mersis_no'] = 'MERSİS No 16 haneli olmalıdır.';
        }

        // Email validation
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Geçerli bir e-posta adresi giriniz.';
        }

        // Phone validation (basic)
        if (!empty($phone) && !preg_match('/^[0-9\s\-\+\(\)]+$/', $phone)) {
            $errors['phone'] = 'Geçerli bir telefon numarası giriniz.';
        }

        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => 'Lütfen zorunlu alanları doldurun.',
                'errors' => $errors
            ]);
            exit;
        }

        // Check if company name exists
        if ($this->companyModel->nameExists($name)) {
            echo json_encode([
                'success' => false,
                'message' => 'Bu şirket adı zaten kayıtlı.',
                'errors' => ['name' => 'Bu şirket adı zaten kullanılmaktadır.']
            ]);
            exit;
        }

        // Create company data
        $companyData = [
            'owner_id' => $userId,
            'name' => $name,
            'trade_name' => $tradeName,
            'company_type' => $companyType,
            'tax_office' => $taxOffice,
            'tax_number' => $taxNumber,
            'vkn' => $vkn,
            'tckn' => $tckn,
            'mersis_no' => $mersisNo,
            'address' => $address,
            'district' => $district,
            'city' => $city,
            'country' => $country,
            'postal_code' => $postalCode,
            'phone' => $phone,
            'fax' => $fax,
            'email' => $email,
            'website' => $website
        ];

        // Create company
        $companyId = $this->companyModel->create($companyData);

        if (!$companyId) {
            echo json_encode([
                'success' => false,
                'message' => 'Şirket oluşturulurken bir hata oluştu. Lütfen tekrar deneyin.'
            ]);
            exit;
        }

        // Update user's company_id
        $updateResult = $this->userModel->updateCompanyId($userId, $companyId);

        if (!$updateResult) {
            echo json_encode([
                'success' => false,
                'message' => 'Şirket oluşturuldu ancak kullanıcı bilgileri güncellenirken hata oluştu.'
            ]);
            exit;
        }

        // Update session
        $_SESSION['company_id'] = $companyId;

        echo json_encode([
            'success' => true,
            'message' => 'Şirket başarıyla oluşturuldu!',
            'data' => [
                'company_id' => $companyId,
                'redirect' => '/ayarlar/sirket'
            ]
        ]);
    }
}

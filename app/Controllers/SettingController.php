<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Setting;
use App\Models\InstallmentPlan;
use App\Models\User;

class SettingController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
    }
    
    public function index(): void
    {
        $this->requireRole(['admin']);
        
        $settingModel = new Setting();
        $settings = $settingModel->getAll();
        
        // Load user's menu config for settings page
        $userModel = new User();
        $menuConfig = $userModel->getMenuConfig($_SESSION['user_id']);
        
        $this->view('settings/index', [
            'pageTitle' => 'الإعدادات',
            'settings' => $settings,
            'menuConfig' => $menuConfig
        ]);
    }
    
    public function update(): void
    {
        $this->requireRole(['admin']);
        
        $settingModel = new Setting();
        
        $fields = [
            'store_name', 'store_phone', 'store_phone2', 'store_address',
            'currency', 'tax_rate', 'invoice_prefix', 'receipt_prefix',
            'invoice_footer', 'dark_mode', 'primary_color', 'sidebar_color'
        ];
        
        foreach ($fields as $field) {
            $value = $this->input($field);
            if ($value !== null) {
                $settingModel->set($field, $value);
            }
        }
        
        if (!empty($_FILES['store_logo']['name'])) {
            $logo = $this->uploadLogo($_FILES['store_logo']);
            if ($logo) {
                $settingModel->set('store_logo', $logo);
            }
        }
        
        $this->logActivity('update', 'settings', null, 'تحديث الإعدادات');
        $this->success('تم حفظ الإعدادات بنجاح');
        $this->redirect(url('/settings'));
    }
    
    /**
     * حفظ إعدادات القائمة للمستخدم الحالي
     */
    public function saveMenuConfig(): void
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة']);
            return;
        }
        
        $userModel = new User();
        $userId = $_SESSION['user_id'];
        
        $result = $userModel->saveMenuConfig($userId, $input);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'تم حفظ إعدادات القائمة']);
        } else {
            echo json_encode(['success' => false, 'message' => 'فشل في حفظ الإعدادات']);
        }
    }
    
    /**
     * جلب إعدادات القائمة للمستخدم الحالي (API endpoint)
     */
    public function fetchMenuConfig(): void
    {
        header('Content-Type: application/json');
        
        $userModel = new User();
        $userId = $_SESSION['user_id'];
        
        $config = $userModel->getMenuConfig($userId);
        
        echo json_encode([
            'success' => true,
            'config' => $config
        ]);
    }
    
    /**
     * حفظ إعدادات المظهر للمستخدم الحالي
     */
    public function saveThemeConfig(): void
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($input === null) {
            $input = []; // Empty config to reset
        }
        
        $userModel = new User();
        $userId = $_SESSION['user_id'];
        
        $result = $userModel->saveThemeConfig($userId, $input);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'تم حفظ إعدادات المظهر']);
        } else {
            echo json_encode(['success' => false, 'message' => 'فشل في حفظ الإعدادات']);
        }
    }
    
    public function installmentPlans(): void
    {
        $this->requireRole(['admin']);
        
        $planModel = new InstallmentPlan();
        $plans = $planModel->all('months');
        
        $this->view('settings/installment', [
            'pageTitle' => 'خطط التقسيط',
            'plans' => $plans
        ]);
    }
    
    public function updateInstallmentPlan(?int $id = null): void
    {
        $this->requireRole(['admin']);
        
        $planModel = new InstallmentPlan();
        
        // Use route parameter if provided, otherwise fallback to POST data
        $id = $id ?? $this->input('id');
        $data = [
            'name' => $this->input('name'),
            'months' => (int) $this->input('months'),
            'increase_percent' => (float) $this->input('increase_percent'),
            'min_down_payment_percent' => (float) $this->input('min_down_payment_percent'),
            'is_active' => $this->input('is_active') ? 1 : 0
        ];
        
        if ($id) {
            $planModel->update($id, $data);
        } else {
            $planModel->create($data);
        }
        
        $this->success('تم حفظ خطة التقسيط');
        $this->redirect(url('/settings/installment'));
    }
    
    public function deleteInstallmentPlan(int $id): void
    {
        $this->requireRole(['admin']);
        
        $planModel = new InstallmentPlan();
        $plan = $planModel->find($id);
        
        if (!$plan) {
            $this->error('خطة التقسيط غير موجودة');
            $this->redirect(url('/settings/installment'));
            return;
        }
        
        $planModel->delete($id);
        $this->logActivity('delete', 'installment_plan', $id, "حذف خطة التقسيط: {$plan['name']}");
        $this->success('تم حذف خطة التقسيط بنجاح');
        $this->redirect(url('/settings/installment'));
    }
    
    private function uploadLogo(array $file): ?string
    {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo.' . $ext;
        $uploadDir = dirname(dirname(__DIR__)) . '/public/assets/images/';
        
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            return $filename;
        }
        return null;
    }
}

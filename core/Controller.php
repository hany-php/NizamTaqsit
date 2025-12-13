<?php
namespace Core;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    نظام تقسيط - المتحكم الأساسي                   ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
abstract class Controller
{
    protected Database $db;
    protected array $data = [];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->data['user'] = $this->getCurrentUser();
        $this->data['settings'] = $this->getSettings();
        $this->data['menuConfig'] = $this->getMenuConfig();
        $this->data['themeConfig'] = $this->getThemeConfig();
    }
    
    /**
     * عرض صفحة
     */
    protected function view(string $view, array $data = []): void
    {
        $data = array_merge($this->data, $data);
        extract($data);
        
        $viewPath = dirname(__DIR__) . "/app/Views/{$view}.php";
        
        if (!file_exists($viewPath)) {
            throw new \Exception("عرض غير موجود: {$view}");
        }
        
        // بدء التخزين المؤقت
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        
        // عرض القالب الرئيسي
        $layoutPath = dirname(__DIR__) . '/app/Views/layouts/master.php';
        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            echo $content;
        }
    }
    
    /**
     * عرض صفحة بدون قالب
     */
    protected function viewOnly(string $view, array $data = []): void
    {
        $data = array_merge($this->data, $data);
        extract($data);
        
        $viewPath = dirname(__DIR__) . "/app/Views/{$view}.php";
        
        if (!file_exists($viewPath)) {
            throw new \Exception("عرض غير موجود: {$view}");
        }
        
        include $viewPath;
    }
    
    /**
     * إرسال JSON
     */
    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * إعادة توجيه
     */
    protected function redirect(string $url, array $flash = []): void
    {
        if (!empty($flash)) {
            foreach ($flash as $key => $value) {
                $_SESSION['flash'][$key] = $value;
            }
        }
        
        header("Location: {$url}");
        exit;
    }
    
    /**
     * رسالة نجاح
     */
    protected function success(string $message): void
    {
        $_SESSION['flash']['success'] = $message;
    }
    
    /**
     * رسالة خطأ
     */
    protected function error(string $message): void
    {
        $_SESSION['flash']['error'] = $message;
    }
    
    /**
     * الحصول على رسالة Flash
     */
    protected function getFlash(string $key): ?string
    {
        $message = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    
    /**
     * التحقق من تسجيل الدخول
     */
    protected function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }
    
    /**
     * التحقق من الصلاحية
     */
    protected function requireRole(string|array $roles): void
    {
        $this->requireAuth();
        
        $userRole = $_SESSION['user_role'] ?? '';
        $roles = is_array($roles) ? $roles : [$roles];
        
        if (!in_array($userRole, $roles)) {
            $this->error('ليس لديك صلاحية للوصول لهذه الصفحة');
            $this->redirect('/dashboard');
        }
    }
    
    /**
     * الحصول على المستخدم الحالي
     */
    protected function getCurrentUser(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        return $this->db->fetch(
            "SELECT id, username, full_name, role FROM users WHERE id = ?",
            [$_SESSION['user_id']]
        );
    }
    
    /**
     * الحصول على الإعدادات
     */
    protected function getSettings(): array
    {
        $rows = $this->db->fetchAll("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }
    
    /**
     * الحصول على إعدادات القائمة للمستخدم الحالي
     */
    protected function getMenuConfig(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        $user = $this->db->fetch(
            "SELECT menu_config FROM users WHERE id = ?",
            [$_SESSION['user_id']]
        );
        
        if ($user && !empty($user['menu_config'])) {
            return json_decode($user['menu_config'], true);
        }
        
        return null;
    }
    
    /**
     * الحصول على إعدادات المظهر للمستخدم الحالي
     */
    protected function getThemeConfig(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        $user = $this->db->fetch(
            "SELECT theme_config FROM users WHERE id = ?",
            [$_SESSION['user_id']]
        );
        
        if ($user && !empty($user['theme_config'])) {
            return json_decode($user['theme_config'], true);
        }
        
        return null;
    }
    
    /**
     * الحصول على قيمة من الطلب
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
    
    /**
     * الحصول على كل بيانات الطلب
     */
    protected function all(): array
    {
        return array_merge($_GET, $_POST);
    }
    
    /**
     * التحقق من البيانات
     */
    protected function validate(array $rules): array
    {
        $errors = [];
        $data = [];
        
        foreach ($rules as $field => $rule) {
            $value = $this->input($field);
            $rulesList = explode('|', $rule);
            
            foreach ($rulesList as $r) {
                if ($r === 'required' && empty($value)) {
                    $errors[$field] = "الحقل {$field} مطلوب";
                    break;
                }
                
                if (!empty($value)) {
                    if ($r === 'numeric' && !is_numeric($value)) {
                        $errors[$field] = "الحقل {$field} يجب أن يكون رقماً";
                    }
                    
                    if ($r === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = "البريد الإلكتروني غير صالح";
                    }
                    
                    if (preg_match('/^min:(\d+)$/', $r, $matches) && strlen($value) < $matches[1]) {
                        $errors[$field] = "الحقل {$field} يجب أن يكون {$matches[1]} أحرف على الأقل";
                    }
                    
                    if (preg_match('/^max:(\d+)$/', $r, $matches) && strlen($value) > $matches[1]) {
                        $errors[$field] = "الحقل {$field} يجب ألا يتجاوز {$matches[1]} حرفاً";
                    }
                }
            }
            
            $data[$field] = $value;
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
        }
        
        return ['errors' => $errors, 'data' => $data];
    }
    
    /**
     * تسجيل نشاط
     */
    protected function logActivity(string $action, string $entityType = null, int $entityId = null, string $description = null): void
    {
        if (!isset($_SESSION['user_id'])) {
            return;
        }
        
        $this->db->insert('activity_log', [
            'user_id' => $_SESSION['user_id'],
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}

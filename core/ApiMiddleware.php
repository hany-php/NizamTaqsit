<?php
namespace Core;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    نظام تقسيط - API Middleware                    ║
 * ║                  التحقق من صحة API Key                           ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
class ApiMiddleware
{
    private Database $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * التحقق من API Key
     * @return array|false بيانات المفتاح أو false إذا فشل التحقق
     */
    public function authenticate(): array|false
    {
        // الحصول على API Key من الـ Header
        $apiKey = $this->getApiKeyFromRequest();
        
        if (!$apiKey) {
            return false;
        }
        
        // البحث عن المفتاح في قاعدة البيانات
        $keyData = $this->db->fetch(
            "SELECT * FROM api_keys WHERE api_key = ? AND is_active = 1",
            [$apiKey]
        );
        
        if (!$keyData) {
            return false;
        }
        
        // التحقق من انتهاء الصلاحية
        if ($keyData['expires_at'] && strtotime($keyData['expires_at']) < time()) {
            return false;
        }
        
        // تحديث آخر استخدام
        $this->db->update('api_keys', [
            'last_used_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$keyData['id']]);
        
        return $keyData;
    }
    
    /**
     * استخراج API Key من الطلب
     */
    private function getApiKeyFromRequest(): ?string
    {
        // من Header
        $headers = $this->getRequestHeaders();
        
        if (isset($headers['X-API-KEY'])) {
            return $headers['X-API-KEY'];
        }
        
        if (isset($headers['x-api-key'])) {
            return $headers['x-api-key'];
        }
        
        // من Query Parameter (للاختبار فقط)
        if (isset($_GET['api_key'])) {
            return $_GET['api_key'];
        }
        
        return null;
    }
    
    /**
     * الحصول على headers الطلب
     */
    private function getRequestHeaders(): array
    {
        $headers = [];
        
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) === 'HTTP_') {
                    $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                    $headers[$header] = $value;
                }
            }
        }
        
        return $headers;
    }
    
    /**
     * إرجاع خطأ عدم المصادقة
     */
    public function unauthorized(string $message = 'مفتاح API غير صالح أو مفقود'): void
    {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => $message,
            'error_code' => 'UNAUTHORIZED'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * التحقق من صلاحية معينة
     */
    public function hasPermission(array $keyData, string $permission): bool
    {
        if (empty($keyData['permissions'])) {
            return true; // إذا لم يتم تحديد صلاحيات، السماح بالكل
        }
        
        $permissions = json_decode($keyData['permissions'], true);
        
        if (!is_array($permissions)) {
            return true;
        }
        
        return in_array($permission, $permissions) || in_array('*', $permissions);
    }
    
    /**
     * إنشاء مفتاح API جديد
     */
    public static function generateApiKey(): string
    {
        return bin2hex(random_bytes(32)); // 64 character hex string
    }
}

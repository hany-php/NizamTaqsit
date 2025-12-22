<?php
namespace Core;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    نظام تقسيط - Base API Controller               ║
 * ║            Controller أساسي لجميع نقاط نهاية API                  ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
abstract class BaseApiController
{
    protected Database $db;
    protected ?array $apiKey = null;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->setupHeaders();
        $this->authenticate();
    }
    
    /**
     * إعداد Headers للـ API
     */
    protected function setupHeaders(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-API-KEY, Authorization');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
    
    /**
     * التحقق من API Key
     */
    protected function authenticate(): void
    {
        $middleware = new ApiMiddleware();
        $keyData = $middleware->authenticate();
        
        if (!$keyData) {
            $middleware->unauthorized();
        }
        
        $this->apiKey = $keyData;
    }
    
    /**
     * إرجاع استجابة ناجحة
     */
    protected function success(mixed $data = null, ?string $message = null, int $status = 200): void
    {
        http_response_code($status);
        $response = ['success' => true];
        
        if ($message) {
            $response['message'] = $message;
        }
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * إرجاع استجابة مع Pagination
     */
    protected function paginated(array $data, int $total, int $page, int $perPage): void
    {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * إرجاع خطأ
     */
    protected function error(string $message, int $status = 400, ?string $errorCode = null): void
    {
        http_response_code($status);
        $response = [
            'success' => false,
            'error' => $message
        ];
        
        if ($errorCode) {
            $response['error_code'] = $errorCode;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * خطأ 404
     */
    protected function notFound(string $message = 'المورد غير موجود'): void
    {
        $this->error($message, 404, 'NOT_FOUND');
    }
    
    /**
     * خطأ التحقق
     */
    protected function validationError(array $errors): void
    {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'error' => 'خطأ في البيانات المدخلة',
            'error_code' => 'VALIDATION_ERROR',
            'errors' => $errors
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * الحصول على بيانات JSON من الطلب
     */
    protected function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        return is_array($data) ? $data : [];
    }
    
    /**
     * الحصول على قيمة من الإدخال
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        $jsonData = $this->getJsonInput();
        return $jsonData[$key] ?? $_POST[$key] ?? $_GET[$key] ?? $default;
    }
    
    /**
     * الحصول على معلمات Pagination
     */
    protected function getPagination(int $defaultPerPage = 20): array
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(100, max(1, (int) ($_GET['per_page'] ?? $defaultPerPage)));
        $offset = ($page - 1) * $perPage;
        
        return [
            'page' => $page,
            'per_page' => $perPage,
            'offset' => $offset
        ];
    }
    
    /**
     * تسجيل نشاط API
     */
    protected function logApiActivity(string $action, ?string $entityType = null, ?int $entityId = null): void
    {
        $this->db->insert('activity_log', [
            'user_id' => $this->apiKey['created_by'] ?? 0,
            'action' => 'api_' . $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => 'API: ' . $action,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}

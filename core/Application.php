<?php
namespace Core;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    نظام تقسيط - كلاس التطبيق                      ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
class Application
{
    private static ?Application $instance = null;
    private array $config;
    private Router $router;
    private Database $db;
    
    /**
     * إنشاء التطبيق
     */
    public function __construct()
    {
        self::$instance = $this;
        $this->loadConfig();
        $this->initTimezone();
        $this->initSession();
        $this->initDatabase();
    }
    
    /**
     * الحصول على نسخة التطبيق
     */
    public static function getInstance(): ?Application
    {
        return self::$instance;
    }
    
    /**
     * تحميل الإعدادات
     */
    private function loadConfig(): void
    {
        $this->config = require dirname(__DIR__) . '/config/app.php';
    }
    
    /**
     * تهيئة المنطقة الزمنية
     */
    private function initTimezone(): void
    {
        date_default_timezone_set($this->config['timezone'] ?? 'Africa/Cairo');
    }
    
    /**
     * تهيئة الجلسة
     */
    private function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $sessionConfig = $this->config['session'] ?? [];
            
            session_name($sessionConfig['name'] ?? 'nizam_session');
            session_set_cookie_params([
                'lifetime' => ($sessionConfig['lifetime'] ?? 120) * 60,
                'secure' => $sessionConfig['secure'] ?? false,
                'httponly' => $sessionConfig['httponly'] ?? true,
                'samesite' => 'Lax'
            ]);
            
            session_start();
        }
    }
    
    /**
     * تهيئة قاعدة البيانات
     */
    private function initDatabase(): void
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * الحصول على الإعدادات
     */
    public function getConfig(string $key = null)
    {
        if ($key === null) {
            return $this->config;
        }
        return $this->config[$key] ?? null;
    }
    
    /**
     * الحصول على قاعدة البيانات
     */
    public function getDb(): Database
    {
        return $this->db;
    }
    
    /**
     * تشغيل التطبيق
     */
    public function run(): void
    {
        try {
            // تحميل المسارات
            $this->router = require dirname(__DIR__) . '/config/routes.php';
            
            // معالجة الطلب
            $this->router->dispatch();
            
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * معالجة الاستثناءات
     */
    private function handleException(\Exception $e): void
    {
        if ($this->config['debug'] ?? false) {
            echo '<div dir="rtl" style="font-family: Arial; padding: 20px; background: #fee; border: 1px solid #f00; margin: 20px;">';
            echo '<h2 style="color: #c00;">خطأ في النظام</h2>';
            echo '<p><strong>الرسالة:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p><strong>الملف:</strong> ' . $e->getFile() . '</p>';
            echo '<p><strong>السطر:</strong> ' . $e->getLine() . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            echo '</div>';
        } else {
            // صفحة خطأ عامة
            http_response_code(500);
            include dirname(__DIR__) . '/app/Views/errors/500.php';
        }
        
        // تسجيل الخطأ
        $this->logError($e);
    }
    
    /**
     * تسجيل الخطأ
     */
    private function logError(\Exception $e): void
    {
        $logPath = dirname(__DIR__) . '/storage/logs/error.log';
        $message = sprintf(
            "[%s] %s in %s:%d\n%s\n\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        
        file_put_contents($logPath, $message, FILE_APPEND);
    }
}

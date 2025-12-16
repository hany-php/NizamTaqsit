<?php
namespace Core;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    نظام تقسيط - كلاس الموجّه                      ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
class Router
{
    private array $routes = [];
    private array $params = [];
    
    /**
     * إضافة مسار GET
     */
    public function get(string $path, string $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }
    
    /**
     * إضافة مسار POST
     */
    public function post(string $path, string $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }
    
    /**
     * إضافة مسار
     */
    private function addRoute(string $method, string $path, string $handler): self
    {
        // تحويل المتغيرات في المسار إلى regex - فقط أرقام أو حروف وأرقام بدون /
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        
        // حساب عدد الأجزاء في المسار للترتيب
        $segmentCount = substr_count($path, '/');
        
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
            'segments' => $segmentCount
        ];
        
        // ترتيب المسارات: الأطول أولاً
        usort($this->routes, function($a, $b) {
            return $b['segments'] - $a['segments'];
        });
        
        return $this;
    }
    
    /**
     * معالجة الطلب
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getUri();
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                // استخراج المتغيرات
                $this->params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                // تنفيذ المتحكم
                $this->executeHandler($route['handler']);
                return;
            }
        }
        
        // 404 - صفحة غير موجودة
        $this->notFound();
    }
    
    /**
     * الحصول على URI
     */
    private function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // إزالة query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        // إزالة المسار الأساسي
        $basePath = '/nizam-taqsit/public';
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        // التأكد من أن المسار يبدأ بـ /
        if (empty($uri) || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }
        
        return $uri;
    }
    
    /**
     * تنفيذ المتحكم
     */
    private function executeHandler(string $handler): void
    {
        list($controllerName, $method) = explode('@', $handler);
        
        $controllerClass = "App\\Controllers\\{$controllerName}";
        
        if (!class_exists($controllerClass)) {
            throw new \Exception("المتحكم غير موجود: {$controllerClass}");
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            throw new \Exception("الدالة غير موجودة: {$method}");
        }
        
        // تمرير المتغيرات للدالة - فقط القيم
        call_user_func_array([$controller, $method], array_values($this->params));
    }
    
    /**
     * صفحة 404
     */
    private function notFound(): void
    {
        http_response_code(404);
        include dirname(__DIR__) . '/app/Views/errors/404.php';
    }
    
    /**
     * الحصول على المتغيرات
     */
    public function getParams(): array
    {
        return $this->params;
    }
}

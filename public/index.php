<?php
/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    نظام تقسيط - نقطة الدخول                       ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */

// منع التخزين المؤقت للمتصفح - حل نهائي
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

// تعريف المسار الأساسي
define('BASE_PATH', dirname(__DIR__));

// تحميل الـ Autoloader
spl_autoload_register(function ($class) {
    $prefix = '';
    $baseDir = BASE_PATH . '/';
    
    // تحويل namespace إلى مسار ملف
    $class = str_replace('\\', '/', $class);
    
    // App namespace
    if (strpos($class, 'App/') === 0) {
        $file = $baseDir . 'app/' . substr($class, 4) . '.php';
    }
    // Core namespace
    elseif (strpos($class, 'Core/') === 0) {
        $file = $baseDir . 'core/' . substr($class, 5) . '.php';
    }
    else {
        $file = $baseDir . $class . '.php';
    }
    
    if (file_exists($file)) {
        require $file;
    }
});

// تحميل الدوال المساعدة
require BASE_PATH . '/app/Helpers/functions.php';

// تشغيل التطبيق
$app = new Core\Application();
$app->run();

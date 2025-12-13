<?php
/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    نظام تقسيط - إعدادات التطبيق                   ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */

return [
    // اسم التطبيق
    'name' => 'نظام تقسيط',
    
    // الإصدار
    'version' => '1.0.0',
    
    // وضع التطوير
    'debug' => true,
    
    // المنطقة الزمنية
    'timezone' => 'Africa/Cairo',
    
    // اللغة الافتراضية
    'locale' => 'ar',
    
    // مسار الجذر
    'base_path' => dirname(__DIR__),
    
    // مسار العام
    'public_path' => dirname(__DIR__) . '/public',
    
    // مسار التخزين
    'storage_path' => dirname(__DIR__) . '/storage',
    
    // مسار الرفع
    'upload_path' => dirname(__DIR__) . '/public/uploads',
    
    // الرابط الأساسي
    'base_url' => 'http://localhost/nizam-taqsit/public',
    
    // مفتاح التشفير
    'app_key' => 'nizam-taqsit-secret-key-2024',
    
    // إعدادات الجلسة
    'session' => [
        'name' => 'nizam_session',
        'lifetime' => 120, // بالدقائق
        'secure' => false,
        'httponly' => true,
    ],
];

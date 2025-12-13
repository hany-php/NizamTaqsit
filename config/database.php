<?php
/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    نظام تقسيط - إعدادات قاعدة البيانات            ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */

return [
    // نوع قاعدة البيانات
    'driver' => 'sqlite',
    
    // مسار ملف SQLite
    'database' => dirname(__DIR__) . '/database/database.sqlite',
    
    // إعدادات إضافية
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];

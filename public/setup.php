<?php
/**
 * تهيئة قاعدة البيانات من المتصفح
 * Access: http://localhost/nizam-taqsit/public/setup.php
 */

header('Content-Type: text/html; charset=utf-8');

$dbPath = dirname(__DIR__) . '/database/database.sqlite';
$schemaPath = dirname(__DIR__) . '/database/schema.sql';
$seedsPath = dirname(__DIR__) . '/database/seeds.sql';

echo '<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تهيئة نظام تقسيط</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: Cairo, sans-serif; background: #f5f7fa; padding: 50px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        h1 { color: #1a237e; text-align: center; margin-bottom: 30px; }
        .step { padding: 15px; margin: 10px 0; border-radius: 8px; }
        .success { background: #e8f5e9; color: #2e7d32; }
        .error { background: #ffebee; color: #c62828; }
        .info { background: #e3f2fd; color: #1565c0; }
        code { background: #f5f5f5; padding: 2px 8px; border-radius: 4px; }
        .credentials { background: #fff3e0; padding: 20px; border-radius: 10px; margin-top: 30px; }
        .btn { display: inline-block; padding: 12px 30px; background: #1e88e5; color: white; text-decoration: none; border-radius: 8px; margin-top: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h1>🏪 تهيئة نظام تقسيط</h1>';

try {
    // Check SQLite extension
    if (!extension_loaded('pdo_sqlite')) {
        throw new Exception('إضافة PDO SQLite غير مفعلة. يرجى تفعيلها في php.ini');
    }
    echo '<div class="step success">✓ PDO SQLite متاح</div>';
    
    // Check if database already exists
    if (file_exists($dbPath)) {
        echo '<div class="step info">ℹ قاعدة البيانات موجودة بالفعل</div>';
        
        // Check if tables exist
        $pdo = new PDO('sqlite:' . $dbPath);
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 3) {
            echo '<div class="step success">✓ قاعدة البيانات جاهزة (' . count($tables) . ' جدول)</div>';
            echo '<div class="credentials">
                <h3>بيانات الدخول:</h3>
                <p><strong>اسم المستخدم:</strong> <code>admin</code></p>
                <p><strong>كلمة المرور:</strong> <code>admin123</code></p>
            </div>';
            echo '<a href="' . dirname($_SERVER['PHP_SELF']) . '/" class="btn">الدخول للنظام</a>';
            echo '</div></body></html>';
            exit;
        }
    }
    
    // Create database directory
    $dbDir = dirname($dbPath);
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0755, true);
    }
    
    // Create database connection
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
    echo '<div class="step success">✓ تم إنشاء قاعدة البيانات</div>';
    
    // Execute schema
    if (!file_exists($schemaPath)) {
        throw new Exception('ملف schema.sql غير موجود');
    }
    $schema = file_get_contents($schemaPath);
    $pdo->exec($schema);
    echo '<div class="step success">✓ تم إنشاء الجداول</div>';
    
    // Execute seeds
    if (file_exists($seedsPath)) {
        $seeds = file_get_contents($seedsPath);
        $pdo->exec($seeds);
        echo '<div class="step success">✓ تم إدخال البيانات الأولية</div>';
    }
    
    // Verify
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name != 'sqlite_sequence'")->fetchAll(PDO::FETCH_COLUMN);
    echo '<div class="step success">✓ عدد الجداول: ' . count($tables) . '</div>';
    
    echo '<div class="credentials">
        <h3>🎉 تمت التهيئة بنجاح!</h3>
        <p><strong>اسم المستخدم:</strong> <code>admin</code></p>
        <p><strong>كلمة المرور:</strong> <code>admin123</code></p>
    </div>';
    
    echo '<a href="' . dirname($_SERVER['PHP_SELF']) . '/" class="btn">الدخول للنظام</a>';
    
} catch (Exception $e) {
    echo '<div class="step error">✗ خطأ: ' . $e->getMessage() . '</div>';
}

echo '</div></body></html>';

<?php
/**
 * سكريبت تهيئة قاعدة البيانات
 * يتم تشغيله مرة واحدة لإنشاء الجداول وإضافة البيانات الأولية
 */

// التأكد من أن السكريبت يعمل من سطر الأوامر أو بتفويض
if (php_sapi_name() !== 'cli' && !isset($_GET['setup_key'])) {
    die('يجب تشغيل هذا السكريبت من سطر الأوامر');
}

echo "<pre style='font-family:monospace;direction:rtl;text-align:right'>\n";
echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║           نظام تقسيط - تهيئة قاعدة البيانات                      ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

$dbPath = __DIR__ . '/database.sqlite';
$schemaPath = __DIR__ . '/schema.sql';
$seedsPath = __DIR__ . '/seeds.sql';

// التحقق من وجود ملفات SQL
if (!file_exists($schemaPath)) {
    die("خطأ: ملف schema.sql غير موجود\n");
}

// إنشاء قاعدة البيانات
echo "» إنشاء قاعدة البيانات...\n";

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // تفعيل المفاتيح الخارجية
    $pdo->exec('PRAGMA foreign_keys = ON');
    
    echo "✓ تم إنشاء قاعدة البيانات\n\n";
    
    // تنفيذ سكريبت الجداول
    echo "» إنشاء الجداول...\n";
    $schema = file_get_contents($schemaPath);
    $pdo->exec($schema);
    echo "✓ تم إنشاء الجداول\n\n";
    
    // تنفيذ البيانات الأولية
    if (file_exists($seedsPath)) {
        echo "» إدخال البيانات الأولية...\n";
        $seeds = file_get_contents($seedsPath);
        $pdo->exec($seeds);
        echo "✓ تم إدخال البيانات الأولية\n\n";
    }
    
    // التحقق من الجداول
    echo "» التحقق من الجداول:\n";
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        if ($table !== 'sqlite_sequence') {
            $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
            echo "  - {$table}: {$count} سجل\n";
        }
    }
    
    echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
    echo "║                    ✓ تمت التهيئة بنجاح                          ║\n";
    echo "╚══════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "بيانات تسجيل الدخول:\n";
    echo "  اسم المستخدم: admin\n";
    echo "  كلمة المرور: admin123\n\n";
    
    echo "رابط النظام: http://localhost/nizam-taqsit/public/\n";
    
} catch (PDOException $e) {
    die("\nخطأ: " . $e->getMessage() . "\n");
}

echo "</pre>";

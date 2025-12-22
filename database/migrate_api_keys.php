<?php
/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    نظام تقسيط - إنشاء جدول API Keys               ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */

// تحميل الإعدادات
define('BASE_PATH', dirname(__DIR__));

// الاتصال بقاعدة البيانات
$dbPath = BASE_PATH . '/database/database.sqlite';

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // إنشاء جدول API Keys
    $sql = "
    CREATE TABLE IF NOT EXISTS api_keys (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100) NOT NULL,
        api_key VARCHAR(64) UNIQUE NOT NULL,
        permissions TEXT DEFAULT NULL,
        is_active INTEGER DEFAULT 1,
        last_used_at DATETIME DEFAULT NULL,
        expires_at DATETIME DEFAULT NULL,
        created_by INTEGER DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    );
    ";
    
    $pdo->exec($sql);
    
    // إنشاء فهرس للبحث السريع
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_api_keys_key ON api_keys(api_key);");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_api_keys_active ON api_keys(is_active);");
    
    echo "✅ تم إنشاء جدول api_keys بنجاح!\n";
    echo "✅ تم إنشاء الفهارس بنجاح!\n";
    
} catch (PDOException $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    exit(1);
}

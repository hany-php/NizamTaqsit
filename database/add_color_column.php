<?php
/**
 * سكربت لإضافة عمود color إلى جدول categories
 * شغله من: php database/add_color_column.php
 */

// مسار قاعدة البيانات
$dbPath = __DIR__ . '/database.sqlite';

if (!file_exists($dbPath)) {
    die("❌ قاعدة البيانات غير موجودة في: $dbPath\n");
}

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // التحقق من وجود العمود
    $stmt = $pdo->query("PRAGMA table_info(categories)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $hasColor = false;
    
    foreach ($columns as $column) {
        if ($column['name'] === 'color') {
            $hasColor = true;
            break;
        }
    }
    
    if (!$hasColor) {
        // إضافة العمود
        $pdo->exec("ALTER TABLE categories ADD COLUMN color VARCHAR(20) DEFAULT '#1e88e5'");
        echo "✅ تم إضافة عمود color بنجاح!\n";
    } else {
        echo "ℹ️ عمود color موجود مسبقاً.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}

<?php
/**
 * Migration: Add menu_config and theme_config columns to users table
 */

// تعريف المسار الأساسي
define('BASE_PATH', dirname(__DIR__));

// تحميل الـ Autoloader
spl_autoload_register(function ($class) {
    $baseDir = BASE_PATH . '/';
    $class = str_replace('\\', '/', $class);
    
    if (strpos($class, 'Core/') === 0) {
        $file = $baseDir . 'core/' . substr($class, 5) . '.php';
    } else {
        $file = $baseDir . $class . '.php';
    }
    
    if (file_exists($file)) {
        require $file;
    }
});

try {
    $db = Core\Database::getInstance();
    
    // Check if columns already exist
    $columns = $db->fetchAll("PRAGMA table_info(users)");
    $hasMenuConfig = false;
    $hasThemeConfig = false;
    
    foreach ($columns as $col) {
        if ($col['name'] === 'menu_config') $hasMenuConfig = true;
        if ($col['name'] === 'theme_config') $hasThemeConfig = true;
    }
    
    if (!$hasMenuConfig) {
        $db->query("ALTER TABLE users ADD COLUMN menu_config TEXT DEFAULT NULL");
        echo "تم إضافة عمود menu_config بنجاح\n";
    } else {
        echo "العمود menu_config موجود بالفعل\n";
    }
    
    if (!$hasThemeConfig) {
        $db->query("ALTER TABLE users ADD COLUMN theme_config TEXT DEFAULT NULL");
        echo "تم إضافة عمود theme_config بنجاح\n";
    } else {
        echo "العمود theme_config موجود بالفعل\n";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}

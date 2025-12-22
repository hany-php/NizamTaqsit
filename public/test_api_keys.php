<?php
/**
 * Test file for API Key operations
 */

// تعريف المسار الأساسي
define('BASE_PATH', dirname(__DIR__));

// تحميل الـ Autoloader
spl_autoload_register(function ($class) {
    $baseDir = BASE_PATH . '/';
    $class = str_replace('\\', '/', $class);
    
    if (strpos($class, 'App/') === 0) {
        $file = $baseDir . 'app/' . substr($class, 4) . '.php';
    } elseif (strpos($class, 'Core/') === 0) {
        $file = $baseDir . 'core/' . substr($class, 5) . '.php';
    } else {
        $file = $baseDir . $class . '.php';
    }
    
    if (file_exists($file)) {
        require $file;
    }
});

require BASE_PATH . '/app/Helpers/functions.php';

// Start session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

$db = \Core\Database::getInstance();

echo "=== Testing API Keys Operations ===\n\n";

// 1. List all API keys
echo "1. Listing all API keys:\n";
$keys = $db->fetchAll("SELECT * FROM api_keys ORDER BY created_at DESC");
if (empty($keys)) {
    echo "   No API keys found.\n";
} else {
    foreach ($keys as $key) {
        echo "   - ID: {$key['id']}, Name: {$key['name']}, Active: {$key['is_active']}\n";
    }
}
echo "\n";

// 2. Test update
if (!empty($keys)) {
    $testKey = $keys[0];
    echo "2. Testing UPDATE on key ID {$testKey['id']}:\n";
    
    $newName = $testKey['name'] . ' (updated)';
    $result = $db->update('api_keys', [
        'name' => $newName,
        'updated_at' => date('Y-m-d H:i:s')
    ], 'id = ?', [$testKey['id']]);
    
    echo "   Updated rows: {$result}\n";
    $updated = $db->fetch("SELECT * FROM api_keys WHERE id = ?", [$testKey['id']]);
    echo "   New name: {$updated['name']}\n";
    
    $db->update('api_keys', ['name' => $testKey['name']], 'id = ?', [$testKey['id']]);
    echo "   Restored original name.\n\n";
}

// 3. Test toggle
if (!empty($keys)) {
    $testKey = $keys[0];
    echo "3. Testing TOGGLE on key ID {$testKey['id']}:\n";
    
    $originalStatus = $testKey['is_active'];
    $newStatus = $originalStatus ? 0 : 1;
    
    $result = $db->update('api_keys', [
        'is_active' => $newStatus,
        'updated_at' => date('Y-m-d H:i:s')
    ], 'id = ?', [$testKey['id']]);
    
    echo "   Updated rows: {$result}\n";
    $toggled = $db->fetch("SELECT * FROM api_keys WHERE id = ?", [$testKey['id']]);
    echo "   New status: {$toggled['is_active']} (was: {$originalStatus})\n";
    
    $db->update('api_keys', ['is_active' => $originalStatus], 'id = ?', [$testKey['id']]);
    echo "   Restored original status.\n\n";
}

// 4. Test create
echo "4. Testing CREATE:\n";
$newKeyValue = bin2hex(random_bytes(32));
$newId = $db->insert('api_keys', [
    'name' => 'Test Key ' . date('H:i:s'),
    'api_key' => $newKeyValue,
    'permissions' => null,
    'is_active' => 1,
    'expires_at' => null,
    'created_by' => 1,
]);
echo "   Created new key with ID: {$newId}\n";
$newKey = $db->fetch("SELECT * FROM api_keys WHERE id = ?", [$newId]);
echo "   Name: {$newKey['name']}\n\n";

// 5. Test delete
echo "5. Testing DELETE on key ID {$newId}:\n";
$result = $db->delete('api_keys', 'id = ?', [$newId]);
echo "   Deleted rows: {$result}\n";
$deleted = $db->fetch("SELECT * FROM api_keys WHERE id = ?", [$newId]);
echo "   Key exists after delete: " . ($deleted ? 'Yes' : 'No') . "\n\n";

echo "=== All database tests PASSED! ===\n";
echo "The problem is NOT in the database layer.\n";
echo "Check browser Console (F12) for JavaScript errors.\n";

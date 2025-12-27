<?php
/**
 * Debug API Authentication
 * curl -X GET "https://taqsit.tiqnia.cloud/debug_api.php" -H "X-API-KEY:your_api_key"
 */

header('Content-Type: application/json; charset=utf-8');

// المسار الصحيح لقاعدة البيانات (نفس المسار في config/database.php)
$dbPath = dirname(__DIR__) . '/database/database.sqlite';

// 1. Get all request headers
$allHeaders = [];
if (function_exists('getallheaders')) {
    $allHeaders = getallheaders();
} else {
    foreach ($_SERVER as $key => $value) {
        if (substr($key, 0, 5) === 'HTTP_') {
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $allHeaders[$header] = $value;
        }
    }
}

// 2. Check for API Key - case insensitive
$headersLower = array_change_key_case($allHeaders, CASE_LOWER);
$apiKeyFromHeader = $headersLower['x-api-key'] ?? $_SERVER['HTTP_X_API_KEY'] ?? null;

// 3. Check database
$dbCheck = [
    'path' => $dbPath,
    'exists' => file_exists($dbPath),
    'readable' => is_readable($dbPath),
    'size' => file_exists($dbPath) ? filesize($dbPath) : 0
];

// 4. Check for API key in database
$apiKeyInDb = null;
$allApiKeys = [];
$tableExists = false;

if ($dbCheck['exists'] && $dbCheck['readable']) {
    try {
        $db = new SQLite3($dbPath);
        
        // Check if api_keys table exists
        $tableCheck = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='api_keys'");
        $tableExists = !empty($tableCheck);
        
        if ($tableExists) {
            // List all API keys
            $result = $db->query('SELECT id, name, substr(api_key, 1, 15) as key_prefix, is_active, expires_at FROM api_keys');
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $allApiKeys[] = $row;
            }
            
            // Check if the provided key exists
            if ($apiKeyFromHeader) {
                $stmt = $db->prepare('SELECT id, name, is_active, expires_at FROM api_keys WHERE api_key = ?');
                $stmt->bindValue(1, $apiKeyFromHeader, SQLITE3_TEXT);
                $result = $stmt->execute();
                $apiKeyInDb = $result->fetchArray(SQLITE3_ASSOC);
            }
        }
        
        // Get all tables
        $tables = [];
        $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $tables[] = $row['name'];
        }
        $dbCheck['tables'] = $tables;
        
        $db->close();
    } catch (Exception $e) {
        $dbCheck['error'] = $e->getMessage();
    }
}

echo json_encode([
    'debug_info' => [
        'timestamp' => date('Y-m-d H:i:s'),
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
    ],
    'headers_received' => $allHeaders,
    'api_key_detection' => [
        'found' => $apiKeyFromHeader !== null,
        'key_preview' => $apiKeyFromHeader ? substr($apiKeyFromHeader, 0, 20) . '...' : null,
        'key_length' => $apiKeyFromHeader ? strlen($apiKeyFromHeader) : 0,
    ],
    'database_check' => $dbCheck,
    'api_keys_table_exists' => $tableExists,
    'api_key_found_in_db' => $apiKeyInDb,
    'all_api_keys_in_db' => $allApiKeys,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

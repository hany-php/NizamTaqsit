<?php
$db = new SQLite3('database/database.sqlite');

// Get all tables
$result = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
echo "=== Tables in Database ===\n";
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    echo "- " . $row['name'] . "\n";
}

echo "\n=== API Keys ===\n";
$result = $db->query('SELECT id, name, api_key, is_active, expires_at FROM api_keys');
if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo "ID: " . $row['id'] . "\n";
        echo "Name: " . $row['name'] . "\n";
        echo "API Key: " . $row['api_key'] . "\n";
        echo "Active: " . ($row['is_active'] ? 'Yes' : 'No') . "\n";
        echo "Expires: " . ($row['expires_at'] ?? 'Never') . "\n";
        echo "---\n";
    }
} else {
    echo "Table api_keys does not exist\n";
}

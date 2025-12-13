<?php
// Fix admin password
$dbPath = __DIR__ . '/database/database.sqlite';
$pdo = new PDO('sqlite:' . $dbPath);
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$pdo->exec("UPDATE users SET password_hash='" . $hash . "' WHERE username='admin'");
echo "Password updated to: admin123\n";

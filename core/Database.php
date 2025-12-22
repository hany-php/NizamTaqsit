<?php
namespace Core;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    نظام تقسيط - كلاس قاعدة البيانات               ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
class Database
{
    private static ?Database $instance = null;
    private \PDO $pdo;
    
    /**
     * إنشاء الاتصال
     */
    private function __construct()
    {
        $config = require dirname(__DIR__) . '/config/database.php';
        
        $dbPath = $config['database'];
        $isNew = !file_exists($dbPath);
        
        // إنشاء مجلد قاعدة البيانات إن لم يكن موجوداً
        $dbDir = dirname($dbPath);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        
        // الاتصال بقاعدة البيانات
        $this->pdo = new \PDO(
            "sqlite:{$dbPath}",
            null,
            null,
            $config['options']
        );
        
        // تفعيل المفاتيح الأجنبية
        $this->pdo->exec('PRAGMA foreign_keys = ON');
        
        // إنشاء الجداول إن كانت قاعدة البيانات جديدة
        if ($isNew) {
            $this->initSchema();
        }
    }
    
    /**
     * الحصول على النسخة الوحيدة
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * تهيئة الجداول
     */
    private function initSchema(): void
    {
        $schemaPath = dirname(__DIR__) . '/database/schema.sql';
        $seedsPath = dirname(__DIR__) . '/database/seeds.sql';
        
        if (file_exists($schemaPath)) {
            $sql = file_get_contents($schemaPath);
            $this->pdo->exec($sql);
        }
        
        if (file_exists($seedsPath)) {
            $sql = file_get_contents($seedsPath);
            $this->pdo->exec($sql);
        }
    }
    
    /**
     * تنفيذ استعلام
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * جلب صف واحد
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * جلب كل الصفوف
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * جلب عمود واحد
     */
    public function fetchColumn(string $sql, array $params = []): mixed
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }
    
    /**
     * إدراج صف
     */
    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        
        return (int) $this->pdo->lastInsertId();
    }
    
    /**
     * تحديث صفوف
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = [];
        $values = [];
        foreach ($data as $column => $value) {
            $set[] = "{$column} = ?";
            $values[] = $value;
        }
        $setString = implode(', ', $set);
        
        $sql = "UPDATE {$table} SET {$setString} WHERE {$where}";
        $stmt = $this->query($sql, array_merge($values, $whereParams));
        
        return $stmt->rowCount();
    }
    
    /**
     * حذف صفوف
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        
        return $stmt->rowCount();
    }
    
    /**
     * بدء معاملة
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * تأكيد المعاملة
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }
    
    /**
     * التراجع عن المعاملة
     */
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }
    
    /**
     * الحصول على PDO
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }
    
    /**
     * آخر ID مُدرج
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
}

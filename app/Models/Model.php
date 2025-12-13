<?php
namespace App\Models;

use Core\Database;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    نظام تقسيط - النموذج الأساسي                   ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
abstract class Model
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * جلب كل السجلات
     */
    public function all(string $orderBy = 'id DESC'): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy}"
        );
    }
    
    /**
     * جلب سجل بالمعرف
     */
    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }
    
    /**
     * جلب سجل بشرط
     */
    public function findWhere(string $column, mixed $value): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE {$column} = ?",
            [$value]
        );
    }
    
    /**
     * جلب سجلات بشرط
     */
    public function where(string $column, mixed $value, string $orderBy = 'id DESC'): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE {$column} = ? ORDER BY {$orderBy}",
            [$value]
        );
    }
    
    /**
     * جلب سجلات بشروط متعددة
     */
    public function whereMultiple(array $conditions, string $orderBy = 'id DESC'): array
    {
        $where = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $where[] = "{$column} = ?";
            $params[] = $value;
        }
        
        $whereString = implode(' AND ', $where);
        
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE {$whereString} ORDER BY {$orderBy}",
            $params
        );
    }
    
    /**
     * إنشاء سجل جديد
     */
    public function create(array $data): int
    {
        // تصفية البيانات
        $data = $this->filterData($data);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * تحديث سجل
     */
    public function update(int $id, array $data): bool
    {
        $data = $this->filterData($data);
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update(
            $this->table,
            $data,
            "{$this->primaryKey} = :id",
            ['id' => $id]
        ) > 0;
    }
    
    /**
     * حذف سجل
     */
    public function delete(int $id): bool
    {
        return $this->db->delete(
            $this->table,
            "{$this->primaryKey} = ?",
            [$id]
        ) > 0;
    }
    
    /**
     * عدد السجلات
     */
    public function count(string $where = '1=1', array $params = []): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table} WHERE {$where}",
            $params
        );
    }
    
    /**
     * مجموع عمود
     */
    public function sum(string $column, string $where = '1=1', array $params = []): float
    {
        return (float) $this->db->fetchColumn(
            "SELECT COALESCE(SUM({$column}), 0) FROM {$this->table} WHERE {$where}",
            $params
        );
    }
    
    /**
     * البحث
     */
    public function search(string $query, array $columns, int $limit = 20): array
    {
        $where = [];
        $params = [];
        
        foreach ($columns as $column) {
            $where[] = "{$column} LIKE ?";
            $params[] = "%{$query}%";
        }
        
        $whereString = implode(' OR ', $where);
        
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE {$whereString} LIMIT {$limit}",
            $params
        );
    }
    
    /**
     * التصفح
     */
    public function paginate(int $page = 1, int $perPage = 15, string $where = '1=1', array $params = []): array
    {
        $offset = ($page - 1) * $perPage;
        
        $total = $this->count($where, $params);
        $data = $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$this->primaryKey} DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }
    
    /**
     * تصفية البيانات
     */
    protected function filterData(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * استعلام مخصص
     */
    public function raw(string $sql, array $params = []): array
    {
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * استعلام مخصص - صف واحد
     */
    public function rawOne(string $sql, array $params = []): ?array
    {
        return $this->db->fetch($sql, $params);
    }
}

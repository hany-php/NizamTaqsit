<?php
namespace App\Models;

/**
 * نموذج التصنيف
 */
class Category extends Model
{
    protected string $table = 'categories';
    protected array $fillable = [
        'name', 'description', 'parent_id', 'icon', 'color', 'sort_order', 'is_active'
    ];
    
    /**
     * جلب التصنيفات النشطة
     */
    public function getActive(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY sort_order, name"
        );
    }
    
    /**
     * جلب التصنيفات الرئيسية
     */
    public function getParents(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE parent_id IS NULL AND is_active = 1 ORDER BY sort_order"
        );
    }
    
    /**
     * جلب التصنيفات الفرعية
     */
    public function getChildren(int $parentId): array
    {
        return $this->where('parent_id', $parentId, 'sort_order');
    }
    
    /**
     * عدد المنتجات في التصنيف
     */
    public function getProductCount(int $categoryId): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM products WHERE category_id = ?",
            [$categoryId]
        );
    }
    
    /**
     * جلب التصنيفات مع عدد المنتجات
     */
    public function getAllWithProductCount(): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, COUNT(p.id) as products_count 
             FROM {$this->table} c 
             LEFT JOIN products p ON c.id = p.category_id 
             GROUP BY c.id 
             ORDER BY c.id DESC"
        );
    }
}

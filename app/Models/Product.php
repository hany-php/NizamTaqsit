<?php
namespace App\Models;

/**
 * نموذج المنتج
 */
class Product extends Model
{
    protected string $table = 'products';
    protected array $fillable = [
        'name', 'description', 'category_id', 'barcode', 'sku',
        'cash_price', 'installment_price', 'cost_price',
        'quantity', 'min_quantity', 'image',
        'brand', 'model', 'warranty_months', 'is_active'
    ];
    
    /**
     * جلب المنتجات النشطة
     */
    public function getActive(): array
    {
        return $this->where('is_active', 1);
    }
    
    /**
     * جلب منتج بالباركود
     */
    public function findByBarcode(string $barcode): ?array
    {
        return $this->findWhere('barcode', $barcode);
    }
    
    /**
     * جلب منتجات تصنيف معين
     */
    public function getByCategory(int $categoryId): array
    {
        return $this->where('category_id', $categoryId);
    }
    
    /**
     * بحث في المنتجات
     */
    public function searchProducts(string $query, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, c.name as category_name 
             FROM {$this->table} p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.is_active = 1 AND (p.name LIKE ? OR p.barcode LIKE ? OR p.brand LIKE ?)
             ORDER BY p.name 
             LIMIT ?",
            ["%{$query}%", "%{$query}%", "%{$query}%", $limit]
        );
    }
    
    /**
     * جلب المنتجات مع التصنيف
     */
    public function getAllWithCategory(): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, c.name as category_name 
             FROM {$this->table} p 
             LEFT JOIN categories c ON p.category_id = c.id 
             ORDER BY p.id DESC"
        );
    }
    
    /**
     * المنتجات منخفضة المخزون
     */
    public function getLowStock(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE quantity <= min_quantity AND is_active = 1"
        );
    }
    
    /**
     * تحديث الكمية
     */
    public function updateQuantity(int $id, int $change): bool
    {
        return $this->db->query(
            "UPDATE {$this->table} SET quantity = quantity + ?, updated_at = ? WHERE id = ?",
            [$change, date('Y-m-d H:i:s'), $id]
        )->rowCount() > 0;
    }
    
    /**
     * أفضل المنتجات مبيعاً
     */
    public function getTopSelling(int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, SUM(ii.quantity) as sold_quantity, SUM(ii.total_price) as total_sales
             FROM {$this->table} p
             INNER JOIN invoice_items ii ON p.id = ii.product_id
             INNER JOIN invoices i ON ii.invoice_id = i.id
             WHERE i.status != 'cancelled'
             GROUP BY p.id
             ORDER BY sold_quantity DESC
             LIMIT ?",
            [$limit]
        );
    }
}

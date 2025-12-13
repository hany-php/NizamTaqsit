<?php
namespace App\Models;

/**
 * نموذج العميل
 */
class Customer extends Model
{
    protected string $table = 'customers';
    protected array $fillable = [
        'full_name', 'phone', 'phone2', 'national_id', 'national_id_image',
        'address', 'city', 'work_address', 'work_phone',
        'guarantor_name', 'guarantor_phone', 'guarantor_national_id',
        'credit_limit', 'notes', 'is_active'
    ];
    
    /**
     * جلب عميل برقم الهاتف
     */
    public function findByPhone(string $phone): ?array
    {
        return $this->findWhere('phone', $phone);
    }
    
    /**
     * جلب عميل برقم الهوية
     */
    public function findByNationalId(string $nationalId): ?array
    {
        return $this->findWhere('national_id', $nationalId);
    }
    
    /**
     * بحث في العملاء
     */
    public function searchCustomers(string $query, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} 
             WHERE is_active = 1 AND (full_name LIKE ? OR phone LIKE ? OR national_id LIKE ?)
             ORDER BY full_name 
             LIMIT ?",
            ["%{$query}%", "%{$query}%", "%{$query}%", $limit]
        );
    }
    
    /**
     * الرصيد المستحق للعميل
     */
    public function getBalance(int $customerId): float
    {
        return (float) $this->db->fetchColumn(
            "SELECT COALESCE(SUM(remaining_amount), 0) 
             FROM invoices 
             WHERE customer_id = ? AND status = 'active' AND invoice_type = 'installment'",
            [$customerId]
        );
    }
    
    /**
     * عدد الأقساط المتأخرة
     */
    public function getOverdueCount(int $customerId): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM installments inst
             INNER JOIN invoices inv ON inst.invoice_id = inv.id
             WHERE inv.customer_id = ? AND inst.status = 'overdue'",
            [$customerId]
        );
    }
    
    /**
     * جلب العملاء مع الأرصدة
     */
    public function getAllWithBalance(): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, 
                    COALESCE(SUM(i.remaining_amount), 0) as balance,
                    COUNT(DISTINCT CASE WHEN i.status = 'active' THEN i.id END) as active_invoices
             FROM {$this->table} c
             LEFT JOIN invoices i ON c.id = i.customer_id AND i.invoice_type = 'installment'
             GROUP BY c.id
             ORDER BY c.full_name"
        );
    }
    
    /**
     * فواتير العميل
     */
    public function getInvoices(int $customerId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM invoices WHERE customer_id = ? ORDER BY created_at DESC",
            [$customerId]
        );
    }
    
    /**
     * مدفوعات العميل
     */
    public function getPayments(int $customerId): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, i.invoice_number 
             FROM payments p
             INNER JOIN invoices i ON p.invoice_id = i.id
             WHERE i.customer_id = ?
             ORDER BY p.payment_date DESC",
            [$customerId]
        );
    }
    
    /**
     * العملاء المتأخرين
     */
    public function getOverdueCustomers(): array
    {
        return $this->db->fetchAll(
            "SELECT DISTINCT c.*, COUNT(inst.id) as overdue_count, SUM(inst.remaining_amount) as overdue_amount
             FROM {$this->table} c
             INNER JOIN invoices inv ON c.id = inv.customer_id
             INNER JOIN installments inst ON inv.id = inst.invoice_id
             WHERE inst.status = 'overdue'
             GROUP BY c.id
             ORDER BY overdue_amount DESC"
        );
    }
}

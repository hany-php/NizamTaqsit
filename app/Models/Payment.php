<?php
namespace App\Models;

/**
 * نموذج الدفعة
 */
class Payment extends Model
{
    protected string $table = 'payments';
    protected array $fillable = [
        'invoice_id', 'installment_id', 'amount', 'payment_method',
        'payment_date', 'receipt_number', 'user_id', 'notes'
    ];
    
    /**
     * جلب المدفوعات مع التفاصيل
     */
    public function getAllWithDetails(): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, i.invoice_number, c.full_name as customer_name, u.full_name as user_name
             FROM {$this->table} p
             INNER JOIN invoices i ON p.invoice_id = i.id
             LEFT JOIN customers c ON i.customer_id = c.id
             LEFT JOIN users u ON p.user_id = u.id
             ORDER BY p.payment_date DESC"
        );
    }
    
    /**
     * مدفوعات اليوم
     */
    public function getToday(): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, i.invoice_number, c.full_name as customer_name
             FROM {$this->table} p
             INNER JOIN invoices i ON p.invoice_id = i.id
             LEFT JOIN customers c ON i.customer_id = c.id
             WHERE DATE(p.payment_date) = ?
             ORDER BY p.payment_date DESC",
            [date('Y-m-d')]
        );
    }
    
    /**
     * إجمالي مدفوعات اليوم
     */
    public function getTodayTotal(): float
    {
        return (float) $this->db->fetchColumn(
            "SELECT COALESCE(SUM(amount), 0) FROM {$this->table} WHERE DATE(payment_date) = ?",
            [date('Y-m-d')]
        );
    }
    
    /**
     * مدفوعات فترة معينة
     */
    public function getByDateRange(string $from, string $to): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, i.invoice_number, c.full_name as customer_name
             FROM {$this->table} p
             INNER JOIN invoices i ON p.invoice_id = i.id
             LEFT JOIN customers c ON i.customer_id = c.id
             WHERE DATE(p.payment_date) BETWEEN ? AND ?
             ORDER BY p.payment_date DESC",
            [$from, $to]
        );
    }
    
    /**
     * إجمالي مدفوعات فترة
     */
    public function getTotalByDateRange(string $from, string $to): float
    {
        return (float) $this->db->fetchColumn(
            "SELECT COALESCE(SUM(amount), 0) FROM {$this->table} 
             WHERE DATE(payment_date) BETWEEN ? AND ?",
            [$from, $to]
        );
    }
    
    /**
     * مدفوعات مستخدم معين
     */
    public function getByUser(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, i.invoice_number, c.full_name as customer_name
             FROM {$this->table} p
             INNER JOIN invoices i ON p.invoice_id = i.id
             LEFT JOIN customers c ON i.customer_id = c.id
             WHERE p.user_id = ?
             ORDER BY p.payment_date DESC",
            [$userId]
        );
    }
}

<?php
namespace App\Models;

/**
 * نموذج الفاتورة
 */
class Invoice extends Model
{
    protected string $table = 'invoices';
    protected array $fillable = [
        'invoice_number', 'invoice_type', 'customer_id', 'user_id',
        'subtotal', 'discount_amount', 'discount_percent', 'tax_amount', 'total_amount',
        'paid_amount', 'remaining_amount',
        'installment_plan_id', 'down_payment', 'monthly_installment', 'installments_count',
        'first_installment_date', 'notes', 'status'
    ];
    
    /**
     * إنشاء رقم فاتورة جديد
     */
    public function generateNumber(): string
    {
        $prefix = setting('invoice_prefix', 'INV');
        $year = date('Y');
        
        $lastNumber = $this->db->fetchColumn(
            "SELECT invoice_number FROM {$this->table} 
             WHERE invoice_number LIKE ? 
             ORDER BY id DESC LIMIT 1",
            ["{$prefix}-{$year}-%"]
        );
        
        if ($lastNumber) {
            $parts = explode('-', $lastNumber);
            $number = (int) end($parts) + 1;
        } else {
            $number = 1;
        }
        
        return sprintf("%s-%s-%05d", $prefix, $year, $number);
    }
    
    /**
     * جلب الفاتورة مع التفاصيل
     */
    public function getWithDetails(int $id): ?array
    {
        $invoice = $this->db->fetch(
            "SELECT i.*, c.full_name as customer_name, c.phone as customer_phone,
                    u.full_name as user_name, ip.name as plan_name
             FROM {$this->table} i
             LEFT JOIN customers c ON i.customer_id = c.id
             LEFT JOIN users u ON i.user_id = u.id
             LEFT JOIN installment_plans ip ON i.installment_plan_id = ip.id
             WHERE i.id = ?",
            [$id]
        );
        
        if ($invoice) {
            $invoice['items'] = $this->getItems($id);
            if ($invoice['invoice_type'] === 'installment') {
                $invoice['installments'] = $this->getInstallments($id);
            }
        }
        
        return $invoice;
    }
    
    /**
     * جلب بنود الفاتورة
     */
    public function getItems(int $invoiceId): array
    {
        return $this->db->fetchAll(
            "SELECT ii.*, p.image as product_image
             FROM invoice_items ii
             LEFT JOIN products p ON ii.product_id = p.id
             WHERE ii.invoice_id = ?",
            [$invoiceId]
        );
    }
    
    /**
     * جلب أقساط الفاتورة
     */
    public function getInstallments(int $invoiceId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM installments WHERE invoice_id = ? ORDER BY installment_number",
            [$invoiceId]
        );
    }
    
    /**
     * جلب الفواتير مع بيانات العميل
     */
    public function getAllWithCustomer(): array
    {
        return $this->db->fetchAll(
            "SELECT i.*, c.full_name as customer_name, c.phone as customer_phone,
                    u.full_name as user_name
             FROM {$this->table} i
             LEFT JOIN customers c ON i.customer_id = c.id
             LEFT JOIN users u ON i.user_id = u.id
             ORDER BY i.id DESC"
        );
    }
    
    /**
     * فواتير التقسيط النشطة
     */
    public function getActiveInstallments(): array
    {
        return $this->db->fetchAll(
            "SELECT i.*, c.full_name as customer_name, c.phone as customer_phone
             FROM {$this->table} i
             LEFT JOIN customers c ON i.customer_id = c.id
             WHERE i.invoice_type = 'installment' AND i.status = 'active'
             ORDER BY i.id DESC"
        );
    }
    
    /**
     * إحصائيات اليوم
     */
    public function getTodayStats(): array
    {
        $today = date('Y-m-d');
        
        return [
            'cash_count' => (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM {$this->table} 
                 WHERE DATE(created_at) = ? AND invoice_type = 'cash' AND status != 'cancelled'",
                [$today]
            ),
            'cash_total' => (float) $this->db->fetchColumn(
                "SELECT COALESCE(SUM(total_amount), 0) FROM {$this->table} 
                 WHERE DATE(created_at) = ? AND invoice_type = 'cash' AND status != 'cancelled'",
                [$today]
            ),
            'installment_count' => (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM {$this->table} 
                 WHERE DATE(created_at) = ? AND invoice_type = 'installment' AND status != 'cancelled'",
                [$today]
            ),
            'installment_total' => (float) $this->db->fetchColumn(
                "SELECT COALESCE(SUM(total_amount), 0) FROM {$this->table} 
                 WHERE DATE(created_at) = ? AND invoice_type = 'installment' AND status != 'cancelled'",
                [$today]
            ),
        ];
    }
    
    /**
     * تحديث الحالة
     */
    public function updateStatus(int $id): void
    {
        $invoice = $this->find($id);
        if (!$invoice || $invoice['invoice_type'] !== 'installment') {
            return;
        }
        
        // التحقق من اكتمال جميع الأقساط
        $unpaidCount = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM installments WHERE invoice_id = ? AND status != 'paid'",
            [$id]
        );
        
        if ($unpaidCount === 0) {
            $this->update($id, ['status' => 'completed', 'remaining_amount' => 0]);
        }
    }
}

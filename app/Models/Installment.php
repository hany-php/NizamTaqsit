<?php
namespace App\Models;

/**
 * نموذج القسط
 */
class Installment extends Model
{
    protected string $table = 'installments';
    protected array $fillable = [
        'invoice_id', 'installment_number', 'amount', 'due_date',
        'paid_amount', 'remaining_amount', 'status', 'paid_date', 'notes'
    ];
    
    /**
     * جلب أقساط اليوم
     */
    public function getToday(): array
    {
        return $this->db->fetchAll(
            "SELECT inst.*, inv.invoice_number, c.full_name as customer_name, c.phone as customer_phone
             FROM {$this->table} inst
             INNER JOIN invoices inv ON inst.invoice_id = inv.id
             INNER JOIN customers c ON inv.customer_id = c.id
             WHERE inst.due_date = ? AND inst.status IN ('pending', 'partial')
             ORDER BY c.full_name",
            [date('Y-m-d')]
        );
    }
    
    /**
     * جلب الأقساط المتأخرة
     */
    public function getOverdue(): array
    {
        return $this->db->fetchAll(
            "SELECT inst.*, inv.invoice_number, c.full_name as customer_name, c.phone as customer_phone,
                    julianday('now') - julianday(inst.due_date) as days_overdue
             FROM {$this->table} inst
             INNER JOIN invoices inv ON inst.invoice_id = inv.id
             INNER JOIN customers c ON inv.customer_id = c.id
             WHERE inst.due_date < ? AND inst.status IN ('pending', 'partial', 'overdue')
             ORDER BY inst.due_date",
            [date('Y-m-d')]
        );
    }
    
    /**
     * جلب الأقساط القادمة
     */
    public function getUpcoming(int $days = 7): array
    {
        $endDate = date('Y-m-d', strtotime("+{$days} days"));
        
        return $this->db->fetchAll(
            "SELECT inst.*, inv.invoice_number, c.full_name as customer_name, c.phone as customer_phone
             FROM {$this->table} inst
             INNER JOIN invoices inv ON inst.invoice_id = inv.id
             INNER JOIN customers c ON inv.customer_id = c.id
             WHERE inst.due_date > ? AND inst.due_date <= ? AND inst.status = 'pending'
             ORDER BY inst.due_date",
            [date('Y-m-d'), $endDate]
        );
    }
    
    /**
     * جلب القسط مع التفاصيل
     */
    public function getWithDetails(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT inst.*, inv.invoice_number, inv.total_amount as invoice_total,
                    c.full_name as customer_name, c.phone as customer_phone, c.address as customer_address
             FROM {$this->table} inst
             INNER JOIN invoices inv ON inst.invoice_id = inv.id
             INNER JOIN customers c ON inv.customer_id = c.id
             WHERE inst.id = ?",
            [$id]
        );
    }
    
    /**
     * سداد قسط
     */
    public function pay(int $id, float $amount, int $userId): bool
    {
        $installment = $this->find($id);
        if (!$installment) {
            return false;
        }
        
        $this->db->beginTransaction();
        
        try {
            // حساب المبالغ
            $newPaidAmount = $installment['paid_amount'] + $amount;
            $newRemaining = $installment['amount'] - $newPaidAmount;
            
            // تحديد الحالة
            if ($newRemaining <= 0) {
                $status = 'paid';
                $newRemaining = 0;
                $paidDate = date('Y-m-d');
            } elseif ($newPaidAmount > 0) {
                $status = 'partial';
                $paidDate = null;
            } else {
                $status = 'pending';
                $paidDate = null;
            }
            
            // تحديث القسط
            $this->update($id, [
                'paid_amount' => $newPaidAmount,
                'remaining_amount' => $newRemaining,
                'status' => $status,
                'paid_date' => $paidDate
            ]);
            
            // إضافة الدفعة
            $this->db->insert('payments', [
                'invoice_id' => $installment['invoice_id'],
                'installment_id' => $id,
                'amount' => $amount,
                'payment_method' => 'cash',
                'receipt_number' => generateReceiptNumber(),
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // تحديث الفاتورة
            $invoice = $this->db->fetch(
                "SELECT * FROM invoices WHERE id = ?",
                [$installment['invoice_id']]
            );
            
            $this->db->update('invoices', [
                'paid_amount' => $invoice['paid_amount'] + $amount,
                'remaining_amount' => $invoice['remaining_amount'] - $amount,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = :id', ['id' => $installment['invoice_id']]);
            
            // تحديث حالة الفاتورة
            $invoiceModel = new Invoice();
            $invoiceModel->updateStatus($installment['invoice_id']);
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * تحديث حالات الأقساط المتأخرة
     */
    public function updateOverdueStatus(): int
    {
        return $this->db->query(
            "UPDATE {$this->table} SET status = 'overdue', updated_at = ? 
             WHERE due_date < ? AND status IN ('pending', 'partial')",
            [date('Y-m-d H:i:s'), date('Y-m-d')]
        )->rowCount();
    }
    
    /**
     * إحصائيات الأقساط
     */
    public function getStats(): array
    {
        // Count overdue using same logic as getOverdue() method
        $overdueCount = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table} 
             WHERE due_date < ? AND status IN ('pending', 'partial', 'overdue')",
            [date('Y-m-d')]
        );
        
        $overdueAmount = (float) $this->db->fetchColumn(
            "SELECT COALESCE(SUM(remaining_amount), 0) FROM {$this->table} 
             WHERE due_date < ? AND status IN ('pending', 'partial', 'overdue')",
            [date('Y-m-d')]
        );
        
        return [
            'today_count' => count($this->getToday()),
            'today_amount' => (float) $this->db->fetchColumn(
                "SELECT COALESCE(SUM(remaining_amount), 0) FROM {$this->table} 
                 WHERE due_date = ? AND status IN ('pending', 'partial')",
                [date('Y-m-d')]
            ),
            'overdue_count' => $overdueCount,
            'overdue_amount' => $overdueAmount,
            'upcoming_week' => count($this->getUpcoming(7)),
        ];
    }
}

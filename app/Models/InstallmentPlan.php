<?php
namespace App\Models;

/**
 * نموذج خطة التقسيط
 */
class InstallmentPlan extends Model
{
    protected string $table = 'installment_plans';
    protected array $fillable = [
        'name', 'months', 'increase_percent', 'min_down_payment_percent', 'is_active'
    ];
    
    /**
     * جلب الخطط النشطة
     */
    public function getActive(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY months"
        );
    }
    
    /**
     * حساب سعر التقسيط
     */
    public function calculateInstallmentPrice(float $cashPrice, int $planId): array
    {
        $plan = $this->find($planId);
        if (!$plan) {
            return [];
        }
        
        $increasePercent = $plan['increase_percent'] / 100;
        $installmentPrice = $cashPrice * (1 + $increasePercent);
        $minDownPayment = $installmentPrice * ($plan['min_down_payment_percent'] / 100);
        
        return [
            'plan' => $plan,
            'cash_price' => $cashPrice,
            'installment_price' => round($installmentPrice, 2),
            'increase_amount' => round($installmentPrice - $cashPrice, 2),
            'min_down_payment' => round($minDownPayment, 2),
            'min_down_payment_percent' => $plan['min_down_payment_percent']
        ];
    }
    
    /**
     * حساب القسط الشهري
     */
    public function calculateMonthlyInstallment(float $total, float $downPayment, int $months): float
    {
        $remaining = $total - $downPayment;
        return round($remaining / $months, 2);
    }
}

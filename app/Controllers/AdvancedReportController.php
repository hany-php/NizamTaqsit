<?php
/**
 * تقارير متقدمة - أرباح وتحليلات
 */

namespace App\Controllers;

use Core\Controller;

class AdvancedReportController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->db = \Core\Database::getInstance();
    }

    /**
     * تقرير الأرباح
     */
    public function profits(): void
    {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');

        // أرباح المنتجات المباعة
        $profits = $this->db->fetchAll(
            "SELECT p.name, p.cost_price,
                    SUM(ii.quantity) as total_quantity,
                    SUM(ii.total_price) as total_revenue,
                    SUM(ii.quantity * p.cost_price) as total_cost,
                    SUM(ii.total_price) - SUM(ii.quantity * p.cost_price) as total_profit
             FROM invoice_items ii
             JOIN products p ON ii.product_id = p.id
             JOIN invoices i ON ii.invoice_id = i.id
             WHERE DATE(i.created_at) >= ? AND DATE(i.created_at) <= ?
             GROUP BY p.id
             ORDER BY total_profit DESC",
            [$from, $to]
        );

        $totalRevenue = array_sum(array_column($profits, 'total_revenue'));
        $totalCost = array_sum(array_column($profits, 'total_cost'));
        $totalProfit = array_sum(array_column($profits, 'total_profit'));

        $this->view('reports/profits', [
            'title' => 'تقرير الأرباح',
            'profits' => $profits,
            'from' => $from,
            'to' => $to,
            'totalRevenue' => $totalRevenue,
            'totalCost' => $totalCost,
            'totalProfit' => $totalProfit
        ]);
    }

    /**
     * تقرير أداء الموظفين
     */
    public function employees(): void
    {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');

        $performance = $this->db->fetchAll(
            "SELECT u.full_name,
                    COUNT(i.id) as invoices_count,
                    SUM(i.total_amount) as total_sales,
                    COUNT(CASE WHEN i.invoice_type = 'cash' THEN 1 END) as cash_sales,
                    COUNT(CASE WHEN i.invoice_type = 'installment' THEN 1 END) as installment_sales
             FROM invoices i
             JOIN users u ON i.user_id = u.id
             WHERE DATE(i.created_at) >= ? AND DATE(i.created_at) <= ?
             GROUP BY u.id
             ORDER BY total_sales DESC",
            [$from, $to]
        );

        $this->view('reports/employees', [
            'title' => 'تقرير أداء الموظفين',
            'performance' => $performance,
            'from' => $from,
            'to' => $to
        ]);
    }

    /**
     * تقرير التدفق النقدي
     */
    public function cashflow(): void
    {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');

        // المدخلات (المدفوعات)
        $inflows = $this->db->fetchAll(
            "SELECT DATE(payment_date) as date, SUM(amount) as total
             FROM payments
             WHERE DATE(payment_date) >= ? AND DATE(payment_date) <= ?
             GROUP BY DATE(payment_date)
             ORDER BY date",
            [$from, $to]
        );

        // ملخص
        $summary = $this->db->fetch(
            "SELECT 
                SUM(CASE WHEN invoice_type = 'cash' THEN total_amount ELSE 0 END) as cash_sales,
                SUM(CASE WHEN invoice_type = 'installment' THEN down_payment ELSE 0 END) as down_payments
             FROM invoices
             WHERE DATE(created_at) >= ? AND DATE(created_at) <= ?",
            [$from, $to]
        );

        $totalPayments = $this->db->fetchColumn(
            "SELECT SUM(amount) FROM payments 
             WHERE DATE(payment_date) >= ? AND DATE(payment_date) <= ?",
            [$from, $to]
        );

        $this->view('reports/cashflow', [
            'title' => 'تقرير التدفق النقدي',
            'inflows' => $inflows,
            'summary' => $summary,
            'totalPayments' => $totalPayments ?: 0,
            'from' => $from,
            'to' => $to
        ]);
    }

    /**
     * المبيعات حسب الفترة (للرسم البياني)
     */
    public function salesChart(): void
    {
        $period = $_GET['period'] ?? 'daily';
        $days = $_GET['days'] ?? 30;

        $format = $period === 'monthly' ? '%Y-%m' : '%Y-%m-%d';
        
        $data = $this->db->fetchAll(
            "SELECT strftime(?, created_at) as period,
                    SUM(CASE WHEN invoice_type = 'cash' THEN total_amount ELSE 0 END) as cash,
                    SUM(CASE WHEN invoice_type = 'installment' THEN total_amount ELSE 0 END) as installment
             FROM invoices
             WHERE created_at >= date('now', ?)
             GROUP BY strftime(?, created_at)
             ORDER BY period",
            [$format, "-{$days} days", $format]
        );

        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * تصدير PDF
     */
    public function exportPdf(): void
    {
        $report = $_GET['report'] ?? 'sales';
        // سيتم إضافة مكتبة PDF لاحقاً
        $_SESSION['flash']['info'] = 'سيتم إضافة تصدير PDF قريباً';
        header('Location: ' . url('/reports'));
        exit;
    }
}

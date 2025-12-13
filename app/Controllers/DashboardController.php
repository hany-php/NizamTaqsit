<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Invoice;
use App\Models\Installment;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Payment;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    نظام تقسيط - متحكم لوحة التحكم                 ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
    }
    
    /**
     * لوحة التحكم الرئيسية
     */
    public function index(): void
    {
        $invoiceModel = new Invoice();
        $installmentModel = new Installment();
        $productModel = new Product();
        $customerModel = new Customer();
        $paymentModel = new Payment();
        
        // تحديث حالات الأقساط المتأخرة
        $installmentModel->updateOverdueStatus();
        
        // إحصائيات اليوم
        $todayStats = $invoiceModel->getTodayStats();
        $todayPayments = $paymentModel->getTodayTotal();
        
        // إحصائيات الأقساط
        $installmentStats = $installmentModel->getStats();
        
        // إحصائيات عامة
        $stats = [
            'products_count' => $productModel->count('is_active = 1'),
            'customers_count' => $customerModel->count('is_active = 1'),
            'low_stock_count' => count($productModel->getLowStock()),
            'active_installments' => $invoiceModel->count("invoice_type = 'installment' AND status = 'active'"),
        ];
        
        // أقساط اليوم
        $todayInstallments = $installmentModel->getToday();
        
        // الأقساط المتأخرة
        $overdueInstallments = $installmentModel->getOverdue();
        
        // آخر الفواتير
        $recentInvoices = $this->db->fetchAll(
            "SELECT i.*, c.full_name as customer_name 
             FROM invoices i 
             LEFT JOIN customers c ON i.customer_id = c.id 
             ORDER BY i.id DESC 
             LIMIT 5"
        );
        
        // آخر المدفوعات
        $recentPayments = $paymentModel->getToday();
        
        $this->view('dashboard/index', [
            'pageTitle' => 'لوحة التحكم',
            'todayStats' => $todayStats,
            'todayPayments' => $todayPayments,
            'installmentStats' => $installmentStats,
            'stats' => $stats,
            'todayInstallments' => $todayInstallments,
            'overdueInstallments' => array_slice($overdueInstallments, 0, 5),
            'recentInvoices' => $recentInvoices,
            'recentPayments' => array_slice($recentPayments, 0, 5),
        ]);
    }
    
    /**
     * إحصائيات AJAX
     */
    public function stats(): void
    {
        $invoiceModel = new Invoice();
        $installmentModel = new Installment();
        $paymentModel = new Payment();
        
        $this->json([
            'today' => $invoiceModel->getTodayStats(),
            'installments' => $installmentModel->getStats(),
            'payments' => $paymentModel->getTodayTotal()
        ]);
    }
}

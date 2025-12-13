<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Payment;

class PaymentController extends Controller
{
    private Payment $paymentModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->paymentModel = new Payment();
    }
    
    public function index(): void
    {
        $from = $this->input('from', date('Y-m-01'));
        $to = $this->input('to', date('Y-m-d'));
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = (int) ($_GET['per_page'] ?? 10);
        
        // Count total
        $totalCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM payments p WHERE DATE(p.payment_date) >= ? AND DATE(p.payment_date) <= ?",
            [$from, $to]
        );
        
        $pagination = new \Core\Pagination($totalCount, $perPage, $page);
        
        // Get paginated payments
        $payments = $this->db->fetchAll(
            "SELECT p.*, i.invoice_number, c.full_name as customer_name, u.full_name as user_name
             FROM payments p
             INNER JOIN invoices i ON p.invoice_id = i.id
             LEFT JOIN customers c ON i.customer_id = c.id
             LEFT JOIN users u ON p.user_id = u.id
             WHERE DATE(p.payment_date) >= ? AND DATE(p.payment_date) <= ?
             ORDER BY p.payment_date DESC
             LIMIT {$pagination->getLimit()} OFFSET {$pagination->getOffset()}",
            [$from, $to]
        );
        
        $total = $this->paymentModel->getTotalByDateRange($from, $to);
        
        $this->view('payments/index', [
            'pageTitle' => 'سجل المدفوعات',
            'payments' => $payments,
            'total' => $total,
            'from' => $from,
            'to' => $to,
            'pagination' => $pagination
        ]);
    }
    
    public function receipt(int $id): void
    {
        $payment = $this->db->fetch(
            "SELECT p.*, i.invoice_number, c.full_name as customer_name, 
                    c.phone as customer_phone, u.full_name as user_name
             FROM payments p
             INNER JOIN invoices i ON p.invoice_id = i.id
             LEFT JOIN customers c ON i.customer_id = c.id
             LEFT JOIN users u ON p.user_id = u.id
             WHERE p.id = ?",
            [$id]
        );
        
        if (!$payment) {
            $this->error('الإيصال غير موجود');
            $this->redirect(url('/payments'));
            return;
        }
        
        $this->viewOnly('payments/receipt', [
            'payment' => $payment,
            'settings' => $this->getSettings()
        ]);
    }
}

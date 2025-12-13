<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Installment;
use App\Models\Invoice;

/**
 * متحكم الأقساط
 */
class InstallmentController extends Controller
{
    private Installment $installmentModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->installmentModel = new Installment();
    }
    
    public function index(): void
    {
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = (int) ($_GET['per_page'] ?? 10);
        $search = $_GET['q'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $where = "WHERE i.invoice_type = 'installment'";
        $params = [];
        
        if ($search) {
            $where .= " AND (i.invoice_number LIKE ? OR c.full_name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if ($status) {
            $where .= " AND i.status = ?";
            $params[] = $status;
        }
        
        $totalCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM invoices i LEFT JOIN customers c ON i.customer_id = c.id $where", 
            $params
        );
        $pagination = new \Core\Pagination($totalCount, $perPage, $page);
        
        $sql = "SELECT i.*, c.full_name as customer_name,
                (SELECT COUNT(*) FROM installments WHERE invoice_id = i.id AND status = 'paid') as paid_count,
                (SELECT COUNT(*) FROM installments WHERE invoice_id = i.id AND status = 'pending') as pending_count
                FROM invoices i 
                LEFT JOIN customers c ON i.customer_id = c.id 
                $where 
                ORDER BY i.id DESC 
                LIMIT {$pagination->getLimit()} OFFSET {$pagination->getOffset()}";
        
        $invoices = $this->db->fetchAll($sql, $params);
        
        $this->view('installments/index', [
            'pageTitle' => 'إدارة الأقساط',
            'invoices' => $invoices,
            'pagination' => $pagination,
            'search' => $search,
            'status' => $status
        ]);
    }
    
    public function today(): void
    {
        $installments = $this->installmentModel->getToday();
        
        $this->view('installments/today', [
            'pageTitle' => 'أقساط اليوم',
            'installments' => $installments
        ]);
    }
    
    public function overdue(): void
    {
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = (int) ($_GET['per_page'] ?? 10);
        
        // Include 'overdue' status since updateOverdueStatus() changes pending/partial to 'overdue'
        $totalCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM installments WHERE status IN ('pending', 'partial', 'overdue') AND due_date < date('now')"
        );
        $pagination = new \Core\Pagination($totalCount, $perPage, $page);
        
        $installments = $this->db->fetchAll(
            "SELECT inst.*, i.invoice_number, c.full_name as customer_name, c.phone as customer_phone,
                    julianday('now') - julianday(inst.due_date) as days_overdue
             FROM installments inst
             JOIN invoices i ON inst.invoice_id = i.id
             JOIN customers c ON i.customer_id = c.id
             WHERE inst.status IN ('pending', 'partial', 'overdue') AND inst.due_date < date('now')
             ORDER BY inst.due_date ASC
             LIMIT {$pagination->getLimit()} OFFSET {$pagination->getOffset()}"
        );
        
        $total = $this->db->fetchColumn(
            "SELECT SUM(remaining_amount) FROM installments WHERE status IN ('pending', 'partial', 'overdue') AND due_date < date('now')"
        );
        
        $this->view('installments/overdue', [
            'pageTitle' => 'الأقساط المتأخرة',
            'installments' => $installments,
            'pagination' => $pagination,
            'total' => $total ?? 0
        ]);
    }
    
    public function upcoming(): void
    {
        $days = (int) $this->input('days', 7);
        $installments = $this->installmentModel->getUpcoming($days);
        
        $this->view('installments/upcoming', [
            'pageTitle' => 'الأقساط القادمة',
            'installments' => $installments,
            'days' => $days
        ]);
    }
    
    public function show(int $id): void
    {
        $installment = $this->installmentModel->getWithDetails($id);
        
        if (!$installment) {
            $this->error('القسط غير موجود');
            $this->redirect(url('/installments'));
            return;
        }
        
        // جلب مدفوعات هذا القسط
        $payments = $this->db->fetchAll(
            "SELECT p.*, u.full_name as user_name 
             FROM payments p 
             LEFT JOIN users u ON p.user_id = u.id 
             WHERE p.installment_id = ? 
             ORDER BY p.payment_date DESC",
            [$id]
        );
        
        $this->view('installments/show', [
            'pageTitle' => 'تفاصيل القسط',
            'installment' => $installment,
            'payments' => $payments
        ]);
    }
    
    public function pay(int $id): void
    {
        $installment = $this->installmentModel->find($id);
        
        if (!$installment) {
            $this->json(['success' => false, 'message' => 'القسط غير موجود'], 404);
            return;
        }
        
        $amount = (float) $this->input('amount');
        
        if ($amount <= 0) {
            $this->json(['success' => false, 'message' => 'المبلغ غير صالح'], 400);
            return;
        }
        
        if ($amount > $installment['remaining_amount']) {
            $amount = $installment['remaining_amount'];
        }
        
        try {
            $this->installmentModel->pay($id, $amount, $_SESSION['user_id']);
            
            $this->logActivity('payment', 'installment', $id, "سداد قسط بمبلغ {$amount}");
            
            $this->json([
                'success' => true,
                'message' => 'تم تسجيل الدفعة بنجاح',
                'receipt_number' => $this->db->fetchColumn(
                    "SELECT receipt_number FROM payments WHERE installment_id = ? ORDER BY id DESC LIMIT 1",
                    [$id]
                )
            ]);
            
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }
}

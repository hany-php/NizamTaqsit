<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Installment;
use App\Models\Customer;
use App\Models\Product;

class ReportController extends Controller
{
    private Invoice $invoiceModel;
    private Payment $paymentModel;
    private Installment $installmentModel;
    private Customer $customerModel;
    private Product $productModel;

    public function __construct()
    {
        parent::__construct();
        $this->invoiceModel = new Invoice();
        $this->paymentModel = new Payment();
        $this->installmentModel = new Installment();
        $this->customerModel = new Customer();
        $this->productModel = new Product();
    }

    /**
     * صفحة التقارير الرئيسية
     */
    public function index(): void
    {
        $this->view('reports/index', [
            'title' => 'التقارير'
        ]);
    }

    /**
     * تقرير المبيعات
     */
    public function sales(): void
    {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = (int) ($_GET['per_page'] ?? 10);

        $db = \Core\Database::getInstance();
        
        $totalCount = $db->fetchColumn(
            "SELECT COUNT(*) FROM invoices WHERE DATE(created_at) >= ? AND DATE(created_at) <= ?",
            [$from, $to]
        );
        
        $pagination = new \Core\Pagination($totalCount, $perPage, $page);
        
        $invoices = $db->fetchAll(
            "SELECT i.*, c.full_name as customer_name 
             FROM invoices i 
             LEFT JOIN customers c ON i.customer_id = c.id 
             WHERE DATE(i.created_at) >= ? AND DATE(i.created_at) <= ?
             ORDER BY i.created_at DESC
             LIMIT {$pagination->getLimit()} OFFSET {$pagination->getOffset()}",
            [$from, $to]
        );

        // Get totals (full, not paginated)
        $totals = $db->fetch(
            "SELECT 
                SUM(CASE WHEN invoice_type = 'cash' THEN total_amount ELSE 0 END) as total_cash,
                SUM(CASE WHEN invoice_type = 'installment' THEN total_amount ELSE 0 END) as total_installment
             FROM invoices 
             WHERE DATE(created_at) >= ? AND DATE(created_at) <= ?",
            [$from, $to]
        );

        $this->view('reports/sales', [
            'title' => 'تقرير المبيعات',
            'invoices' => $invoices,
            'from' => $from,
            'to' => $to,
            'totalCash' => $totals['total_cash'] ?? 0,
            'totalInstallment' => $totals['total_installment'] ?? 0,
            'total' => ($totals['total_cash'] ?? 0) + ($totals['total_installment'] ?? 0),
            'pagination' => $pagination
        ]);
    }

    /**
     * تقرير التحصيلات
     */
    public function collections(): void
    {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = (int) ($_GET['per_page'] ?? 10);

        $db = \Core\Database::getInstance();
        
        $totalCount = $db->fetchColumn(
            "SELECT COUNT(*) FROM payments p WHERE DATE(p.payment_date) >= ? AND DATE(p.payment_date) <= ?",
            [$from, $to]
        );
        $pagination = new \Core\Pagination($totalCount, $perPage, $page);
        
        $payments = $db->fetchAll(
            "SELECT p.*, i.invoice_number, c.full_name as customer_name
             FROM payments p
             LEFT JOIN invoices i ON p.invoice_id = i.id
             LEFT JOIN customers c ON i.customer_id = c.id
             WHERE DATE(p.payment_date) >= ? AND DATE(p.payment_date) <= ?
             ORDER BY p.payment_date DESC
             LIMIT {$pagination->getLimit()} OFFSET {$pagination->getOffset()}",
            [$from, $to]
        );
        
        $total = $db->fetchColumn(
            "SELECT SUM(amount) FROM payments WHERE DATE(payment_date) >= ? AND DATE(payment_date) <= ?",
            [$from, $to]
        );

        $this->view('reports/collections', [
            'title' => 'تقرير التحصيلات',
            'payments' => $payments,
            'from' => $from,
            'to' => $to,
            'total' => $total ?? 0,
            'pagination' => $pagination
        ]);
    }

    /**
     * تقرير المتأخرات
     */
    public function overdue(): void
    {
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = (int) ($_GET['per_page'] ?? 10);
        
        $db = \Core\Database::getInstance();
        
        // Include 'overdue' status since updateOverdueStatus() changes pending/partial to 'overdue'
        $totalCount = $db->fetchColumn(
            "SELECT COUNT(*) FROM installments inst WHERE inst.status IN ('pending', 'partial', 'overdue') AND inst.due_date < date('now')"
        );
        $pagination = new \Core\Pagination($totalCount, $perPage, $page);
        
        $installments = $db->fetchAll(
            "SELECT inst.*, i.invoice_number, c.full_name as customer_name, c.phone as customer_phone,
                    julianday('now') - julianday(inst.due_date) as days_overdue
             FROM installments inst
             JOIN invoices i ON inst.invoice_id = i.id
             JOIN customers c ON i.customer_id = c.id
             WHERE inst.status IN ('pending', 'partial', 'overdue') AND inst.due_date < date('now')
             ORDER BY inst.due_date ASC
             LIMIT {$pagination->getLimit()} OFFSET {$pagination->getOffset()}"
        );
        
        $total = $db->fetchColumn(
            "SELECT SUM(remaining_amount) FROM installments WHERE status IN ('pending', 'partial', 'overdue') AND due_date < date('now')"
        );

        $this->view('reports/overdue', [
            'title' => 'تقرير المتأخرات',
            'installments' => $installments,
            'total' => $total ?? 0,
            'pagination' => $pagination
        ]);
    }

    /**
     * تقرير العملاء
     */
    public function customers(): void
    {
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = (int) ($_GET['per_page'] ?? 10);
        $search = $_GET['q'] ?? '';
        
        $db = \Core\Database::getInstance();
        
        $where = $search ? "WHERE c.full_name LIKE '%{$search}%' OR c.phone LIKE '%{$search}%'" : '';
        
        $totalCount = $db->fetchColumn("SELECT COUNT(*) FROM customers c $where");
        $pagination = new \Core\Pagination($totalCount, $perPage, $page);
        
        $customers = $db->fetchAll(
            "SELECT c.*, 
                    COALESCE(SUM(CASE WHEN i.status = 'active' THEN i.remaining_amount ELSE 0 END), 0) as balance,
                    COUNT(CASE WHEN i.status = 'active' THEN 1 END) as active_invoices
             FROM customers c
             LEFT JOIN invoices i ON c.id = i.customer_id
             $where
             GROUP BY c.id
             ORDER BY balance DESC
             LIMIT {$pagination->getLimit()} OFFSET {$pagination->getOffset()}"
        );

        $this->view('reports/customers', [
            'title' => 'تقرير العملاء',
            'customers' => $customers,
            'pagination' => $pagination,
            'search' => $search
        ]);
    }

    /**
     * تقرير المخزون
     */
    public function inventory(): void
    {
        $perPage = (int) ($_GET['per_page'] ?? 15);
        $editedId = (int) ($_GET['edited'] ?? 0);
        
        $db = \Core\Database::getInstance();
        
        // إذا كان هناك منتج معدّل، احسب صفحته
        $page = (int) ($_GET['page'] ?? 1);
        if ($editedId && !isset($_GET['page'])) {
            // ابحث عن موقع المنتج في الترتيب
            $position = $db->fetchColumn(
                "SELECT COUNT(*) FROM products WHERE quantity < (SELECT quantity FROM products WHERE id = ?) 
                 OR (quantity = (SELECT quantity FROM products WHERE id = ?) AND id <= ?)",
                [$editedId, $editedId, $editedId]
            );
            if ($position) {
                $page = ceil($position / $perPage);
            }
        }
        
        $totalCount = $db->fetchColumn("SELECT COUNT(*) FROM products");
        $pagination = new \Core\Pagination($totalCount, $perPage, $page);
        
        $products = $db->fetchAll(
            "SELECT p.*, c.name as category_name 
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             ORDER BY p.quantity ASC
             LIMIT {$pagination->getLimit()} OFFSET {$pagination->getOffset()}"
        );
        
        // المنتجات منخفضة المخزون (للتنبيه فقط)
        $lowStock = $db->fetchAll(
            "SELECT * FROM products WHERE quantity <= min_quantity"
        );

        $this->view('reports/inventory', [
            'title' => 'تقرير المخزون',
            'products' => $products,
            'lowStock' => $lowStock,
            'pagination' => $pagination
        ]);
    }
}


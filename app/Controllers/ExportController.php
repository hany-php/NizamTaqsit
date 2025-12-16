<?php
namespace App\Controllers;

use Core\Controller;

/**
 * متحكم التصدير - PDF و Excel
 */
class ExportController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
    }
    
    /**
     * تصدير المنتجات
     */
    public function products(string $format): void
    {
        $search = $_GET['q'] ?? '';
        $categoryId = $_GET['category'] ?? '';
        $ids = $_GET['ids'] ?? '';
        
        $where = "WHERE 1=1";
        $params = [];
        
        // Filter by selected IDs
        if ($ids) {
            $idList = array_filter(explode(',', $ids), 'is_numeric');
            if (!empty($idList)) {
                $placeholders = implode(',', array_fill(0, count($idList), '?'));
                $where .= " AND p.id IN ($placeholders)";
                $params = array_merge($params, $idList);
            }
        } else {
            if ($search) {
                $where .= " AND (p.name LIKE ? OR p.barcode LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }
            
            if ($categoryId) {
                $where .= " AND p.category_id = ?";
                $params[] = $categoryId;
            }
        }
        
        $products = $this->db->fetchAll(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             $where 
             ORDER BY p.id DESC",
            $params
        );
        
        $headers = ['#', 'اسم المنتج', 'التصنيف', 'السعر نقدي', 'السعر تقسيط', 'الكمية', 'الحالة'];
        $rows = [];
        foreach ($products as $p) {
            $rows[] = [
                $p['id'],
                $p['name'],
                $p['category_name'] ?? '-',
                number_format($p['cash_price'], 2),
                number_format($p['installment_price'], 2),
                $p['quantity'],
                $p['is_active'] ? 'نشط' : 'معطل'
            ];
        }
        
        $this->export($format, 'المنتجات', $headers, $rows);
    }
    
    /**
     * تصدير التصنيفات
     */
    public function categories(string $format): void
    {
        $categories = $this->db->fetchAll(
            "SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as products_count 
             FROM categories c ORDER BY c.name"
        );
        
        $headers = ['#', 'الاسم', 'الوصف', 'عدد المنتجات'];
        $rows = [];
        foreach ($categories as $cat) {
            $rows[] = [
                $cat['id'],
                $cat['name'],
                $cat['description'] ?? '-',
                $cat['products_count']
            ];
        }
        
        $this->export($format, 'التصنيفات', $headers, $rows);
    }
    
    /**
     * تصدير العملاء
     */
    public function customers(string $format): void
    {
        $search = $_GET['q'] ?? '';
        
        $where = $search ? "WHERE full_name LIKE '%{$search}%' OR phone LIKE '%{$search}%'" : '';
        
        $customers = $this->db->fetchAll(
            "SELECT c.*, 
                    COALESCE((SELECT SUM(remaining_amount) FROM invoices WHERE customer_id = c.id), 0) as balance
             FROM customers c $where ORDER BY c.id DESC"
        );
        
        $headers = ['#', 'الاسم', 'الهاتف', 'رقم الهوية', 'المدينة', 'الرصيد المستحق'];
        $rows = [];
        foreach ($customers as $c) {
            $rows[] = [
                $c['id'],
                $c['full_name'],
                $c['phone'],
                $c['national_id'] ?? '-',
                $c['city'] ?? '-',
                number_format($c['balance'], 2)
            ];
        }
        
        $this->export($format, 'العملاء', $headers, $rows);
    }
    
    /**
     * تصدير المستخدمين
     */
    public function users(string $format): void
    {
        $ids = $_GET['ids'] ?? '';
        
        $where = '';
        $params = [];
        
        if ($ids) {
            $idList = array_filter(explode(',', $ids), 'is_numeric');
            if (!empty($idList)) {
                $placeholders = implode(',', array_fill(0, count($idList), '?'));
                $where = "WHERE id IN ($placeholders)";
                $params = $idList;
            }
        }
        
        $users = $this->db->fetchAll("SELECT * FROM users $where ORDER BY id DESC", $params);
        
        $headers = ['#', 'الاسم', 'اسم المستخدم', 'الهاتف', 'الدور', 'الحالة'];
        $rows = [];
        foreach ($users as $u) {
            $role = $u['role'] === 'admin' ? 'مدير' : ($u['role'] === 'cashier' ? 'كاشير' : 'موظف');
            $rows[] = [
                $u['id'],
                $u['full_name'],
                $u['username'],
                $u['phone'] ?? '-',
                $role,
                $u['is_active'] ? 'نشط' : 'معطل'
            ];
        }
        
        $this->export($format, 'المستخدمين', $headers, $rows);
    }
    
    /**
     * تصدير الفواتير
     */
    public function invoices(string $format): void
    {
        $search = $_GET['q'] ?? '';
        $type = $_GET['type'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $where = "WHERE 1=1";
        $params = [];
        
        if ($search) {
            $where .= " AND (i.invoice_number LIKE ? OR c.full_name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($type) {
            $where .= " AND i.invoice_type = ?";
            $params[] = $type;
        }
        if ($status) {
            $where .= " AND i.status = ?";
            $params[] = $status;
        }
        
        $invoices = $this->db->fetchAll(
            "SELECT i.*, c.full_name as customer_name
             FROM invoices i 
             LEFT JOIN customers c ON i.customer_id = c.id 
             $where ORDER BY i.id DESC",
            $params
        );
        
        $headers = ['رقم الفاتورة', 'العميل', 'النوع', 'الإجمالي', 'المدفوع', 'المتبقي', 'الحالة', 'التاريخ'];
        $rows = [];
        foreach ($invoices as $inv) {
            $rows[] = [
                $inv['invoice_number'],
                $inv['customer_name'] ?? 'زبون نقدي',
                $inv['invoice_type'] === 'cash' ? 'نقدي' : 'تقسيط',
                number_format($inv['total_amount'], 2),
                number_format($inv['paid_amount'], 2),
                number_format($inv['remaining_amount'], 2),
                $this->getStatusArabic($inv['status']),
                date('Y-m-d', strtotime($inv['created_at']))
            ];
        }
        
        $this->export($format, 'الفواتير', $headers, $rows);
    }
    
    /**
     * تصدير عقود الأقساط
     */
    public function installments(string $format): void
    {
        $search = $_GET['q'] ?? '';
        $status = $_GET['status'] ?? '';
        $ids = $_GET['ids'] ?? '';
        
        $where = "WHERE i.invoice_type = 'installment'";
        $params = [];
        
        if ($ids) {
            $idList = array_filter(explode(',', $ids), 'is_numeric');
            if (!empty($idList)) {
                $placeholders = implode(',', array_fill(0, count($idList), '?'));
                $where .= " AND i.id IN ($placeholders)";
                $params = array_merge($params, $idList);
            }
        } else {
            if ($search) {
                $where .= " AND (i.invoice_number LIKE ? OR c.full_name LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }
            if ($status) {
                $where .= " AND i.status = ?";
                $params[] = $status;
            }
        }
        
        $invoices = $this->db->fetchAll(
            "SELECT i.*, c.full_name as customer_name, c.phone as customer_phone
             FROM invoices i 
             JOIN customers c ON i.customer_id = c.id 
             $where ORDER BY i.id DESC",
            $params
        );
        
        $headers = ['رقم الفاتورة', 'العميل', 'الهاتف', 'الإجمالي', 'المدفوع', 'المتبقي', 'القسط الشهري', 'الحالة'];
        $rows = [];
        foreach ($invoices as $inv) {
            $rows[] = [
                $inv['invoice_number'],
                $inv['customer_name'],
                $inv['customer_phone'],
                number_format($inv['total_amount'], 2),
                number_format($inv['paid_amount'], 2),
                number_format($inv['remaining_amount'], 2),
                number_format($inv['monthly_installment'] ?? 0, 2),
                $this->getStatusArabic($inv['status'])
            ];
        }
        
        $this->export($format, 'عقود_الأقساط', $headers, $rows);
    }
    
    /**
     * تصدير أقساط اليوم
     */
    public function todayInstallments(string $format): void
    {
        $installments = $this->db->fetchAll(
            "SELECT inst.*, i.invoice_number, c.full_name as customer_name, c.phone as customer_phone
             FROM installments inst
             JOIN invoices i ON inst.invoice_id = i.id
             JOIN customers c ON i.customer_id = c.id
             WHERE inst.due_date = date('now') AND inst.status IN ('pending', 'partial')
             ORDER BY c.full_name"
        );
        
        $headers = ['العميل', 'الهاتف', 'رقم الفاتورة', 'رقم القسط', 'المبلغ', 'تاريخ الاستحقاق'];
        $rows = [];
        foreach ($installments as $inst) {
            $rows[] = [
                $inst['customer_name'],
                $inst['customer_phone'],
                $inst['invoice_number'],
                $inst['installment_number'],
                number_format($inst['remaining_amount'], 2),
                $inst['due_date']
            ];
        }
        
        $this->export($format, 'أقساط_اليوم', $headers, $rows);
    }
    
    /**
     * تصدير الأقساط المتأخرة
     */
    public function overdueInstallments(string $format): void
    {
        $installments = $this->db->fetchAll(
            "SELECT inst.*, i.invoice_number, c.full_name as customer_name, c.phone as customer_phone,
                    julianday('now') - julianday(inst.due_date) as days_overdue
             FROM installments inst
             JOIN invoices i ON inst.invoice_id = i.id
             JOIN customers c ON i.customer_id = c.id
             WHERE inst.status IN ('pending', 'partial') AND inst.due_date < date('now')
             ORDER BY inst.due_date ASC"
        );
        
        $headers = ['العميل', 'الهاتف', 'رقم الفاتورة', 'رقم القسط', 'المبلغ', 'تاريخ الاستحقاق', 'أيام التأخير'];
        $rows = [];
        foreach ($installments as $inst) {
            $rows[] = [
                $inst['customer_name'],
                $inst['customer_phone'],
                $inst['invoice_number'],
                $inst['installment_number'],
                number_format($inst['remaining_amount'], 2),
                $inst['due_date'],
                (int)$inst['days_overdue']
            ];
        }
        
        $this->export($format, 'الأقساط_المتأخرة', $headers, $rows);
    }
    
    /**
     * تصدير المدفوعات
     */
    public function payments(string $format): void
    {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');
        
        $payments = $this->db->fetchAll(
            "SELECT p.*, i.invoice_number, c.full_name as customer_name
             FROM payments p
             INNER JOIN invoices i ON p.invoice_id = i.id
             LEFT JOIN customers c ON i.customer_id = c.id
             WHERE DATE(p.payment_date) >= ? AND DATE(p.payment_date) <= ?
             ORDER BY p.payment_date DESC",
            [$from, $to]
        );
        
        $headers = ['رقم الإيصال', 'العميل', 'رقم الفاتورة', 'المبلغ', 'طريقة الدفع', 'التاريخ'];
        $rows = [];
        foreach ($payments as $p) {
            $rows[] = [
                $p['receipt_number'],
                $p['customer_name'] ?? '-',
                $p['invoice_number'],
                number_format($p['amount'], 2),
                $this->getPaymentMethodArabic($p['payment_method']),
                date('Y-m-d H:i', strtotime($p['payment_date']))
            ];
        }
        
        $this->export($format, 'المدفوعات', $headers, $rows);
    }
    
    /**
     * تصدير تقرير المبيعات
     */
    public function salesReport(string $format): void
    {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');
        $ids = $_GET['ids'] ?? '';
        
        $where = "WHERE 1=1";
        $params = [];
        
        // فلترة حسب الفواتير المحددة
        if ($ids) {
            $idList = array_filter(explode(',', $ids), 'is_numeric');
            if (!empty($idList)) {
                $placeholders = implode(',', array_fill(0, count($idList), '?'));
                $where .= " AND i.id IN ($placeholders)";
                $params = array_merge($params, $idList);
            }
        } else {
            // فلترة حسب التاريخ فقط إذا لم يتم تحديد فواتير
            $where .= " AND DATE(i.created_at) >= ? AND DATE(i.created_at) <= ?";
            $params[] = $from;
            $params[] = $to;
        }
        
        $invoices = $this->db->fetchAll(
            "SELECT i.*, c.full_name as customer_name 
             FROM invoices i 
             LEFT JOIN customers c ON i.customer_id = c.id 
             $where
             ORDER BY i.created_at DESC",
            $params
        );
        
        $headers = ['رقم الفاتورة', 'العميل', 'النوع', 'الإجمالي', 'التاريخ'];
        $rows = [];
        foreach ($invoices as $inv) {
            $rows[] = [
                $inv['invoice_number'],
                $inv['customer_name'] ?? 'زبون نقدي',
                $inv['invoice_type'] === 'cash' ? 'نقدي' : 'تقسيط',
                number_format($inv['total_amount'], 2),
                date('Y-m-d', strtotime($inv['created_at']))
            ];
        }
        
        $this->export($format, 'تقرير_المبيعات', $headers, $rows);
    }
    
    /**
     * تصدير تقرير التحصيلات
     */
    public function collectionsReport(string $format): void
    {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');
        $ids = $_GET['ids'] ?? '';
        
        $where = "WHERE 1=1";
        $params = [];
        
        // فلترة حسب المدفوعات المحددة
        if ($ids) {
            $idList = array_filter(explode(',', $ids), 'is_numeric');
            if (!empty($idList)) {
                $placeholders = implode(',', array_fill(0, count($idList), '?'));
                $where .= " AND p.id IN ($placeholders)";
                $params = array_merge($params, $idList);
            }
        } else {
            $where .= " AND DATE(p.payment_date) >= ? AND DATE(p.payment_date) <= ?";
            $params[] = $from;
            $params[] = $to;
        }
        
        $payments = $this->db->fetchAll(
            "SELECT p.*, i.invoice_number, c.full_name as customer_name
             FROM payments p
             LEFT JOIN invoices i ON p.invoice_id = i.id
             LEFT JOIN customers c ON i.customer_id = c.id
             $where
             ORDER BY p.payment_date DESC",
            $params
        );
        
        $headers = ['رقم الإيصال', 'العميل', 'الفاتورة', 'المبلغ', 'التاريخ'];
        $rows = [];
        foreach ($payments as $p) {
            $rows[] = [
                $p['receipt_number'],
                $p['customer_name'] ?? '-',
                $p['invoice_number'],
                number_format($p['amount'], 2),
                date('Y-m-d', strtotime($p['payment_date']))
            ];
        }
        
        $this->export($format, 'تقرير_التحصيلات', $headers, $rows);
    }
    
    /**
     * تصدير تقرير المتأخرات
     */
    public function overdueReport(string $format): void
    {
        $ids = $_GET['ids'] ?? '';
        
        $where = "WHERE inst.status IN ('pending', 'partial', 'overdue') AND inst.due_date < date('now')";
        $params = [];
        
        // فلترة حسب الأقساط المحددة
        if ($ids) {
            $idList = array_filter(explode(',', $ids), 'is_numeric');
            if (!empty($idList)) {
                $placeholders = implode(',', array_fill(0, count($idList), '?'));
                $where .= " AND inst.id IN ($placeholders)";
                $params = array_merge($params, $idList);
            }
        }
        
        $installments = $this->db->fetchAll(
            "SELECT inst.*, i.invoice_number, c.full_name as customer_name, c.phone as customer_phone,
                    julianday('now') - julianday(inst.due_date) as days_overdue
             FROM installments inst
             JOIN invoices i ON inst.invoice_id = i.id
             JOIN customers c ON i.customer_id = c.id
             $where
             ORDER BY inst.due_date ASC",
            $params
        );
        
        $headers = ['العميل', 'الهاتف', 'رقم الفاتورة', 'رقم القسط', 'المبلغ', 'تاريخ الاستحقاق', 'أيام التأخير'];
        $rows = [];
        foreach ($installments as $inst) {
            $rows[] = [
                $inst['customer_name'],
                $inst['customer_phone'],
                $inst['invoice_number'],
                $inst['installment_number'],
                number_format($inst['remaining_amount'], 2),
                $inst['due_date'],
                (int)$inst['days_overdue']
            ];
        }
        
        $this->export($format, 'تقرير_المتأخرات', $headers, $rows);
    }
    
    /**
     * تصدير تقرير العملاء
     */
    public function customersReport(string $format): void
    {
        $search = $_GET['q'] ?? '';
        $where = $search ? "WHERE c.full_name LIKE '%{$search}%' OR c.phone LIKE '%{$search}%'" : '';
        
        $customers = $this->db->fetchAll(
            "SELECT c.*, 
                    COALESCE(SUM(CASE WHEN i.status = 'active' THEN i.remaining_amount ELSE 0 END), 0) as balance,
                    COUNT(CASE WHEN i.status = 'active' THEN 1 END) as active_invoices
             FROM customers c
             LEFT JOIN invoices i ON c.id = i.customer_id
             $where
             GROUP BY c.id ORDER BY balance DESC"
        );
        
        $headers = ['الاسم', 'الهاتف', 'المدينة', 'الرصيد المستحق', 'العقود النشطة'];
        $rows = [];
        foreach ($customers as $c) {
            $rows[] = [
                $c['full_name'],
                $c['phone'],
                $c['city'] ?? '-',
                number_format($c['balance'] ?? 0, 2),
                $c['active_invoices'] ?? 0
            ];
        }
        
        $this->export($format, 'تقرير_العملاء', $headers, $rows);
    }
    
    /**
     * تصدير تقرير المخزون
     */
    public function inventoryReport(string $format): void
    {
        $ids = $_GET['ids'] ?? '';
        
        if (!empty($ids)) {
            // تصدير المنتجات المحددة فقط
            $idList = array_map('intval', explode(',', $ids));
            $placeholders = implode(',', array_fill(0, count($idList), '?'));
            $products = $this->db->fetchAll(
                "SELECT p.*, c.name as category_name 
                 FROM products p
                 LEFT JOIN categories c ON p.category_id = c.id
                 WHERE p.id IN ($placeholders)
                 ORDER BY p.quantity ASC",
                $idList
            );
        } else {
            // تصدير جميع المنتجات
            $products = $this->db->fetchAll(
                "SELECT p.*, c.name as category_name 
                 FROM products p
                 LEFT JOIN categories c ON p.category_id = c.id
                 ORDER BY p.quantity ASC"
            );
        }
        
        $headers = ['المنتج', 'التصنيف', 'الكمية', 'حد التنبيه', 'السعر النقدي', 'قيمة المخزون', 'الحالة'];
        $rows = [];
        foreach ($products as $p) {
            $status = $p['quantity'] <= 0 ? 'نفذ' : ($p['quantity'] <= $p['min_quantity'] ? 'منخفض' : 'متوفر');
            $rows[] = [
                $p['name'],
                $p['category_name'] ?? '-',
                $p['quantity'],
                $p['min_quantity'],
                number_format($p['cash_price'], 2),
                number_format($p['cash_price'] * $p['quantity'], 2),
                $status
            ];
        }
        
        $this->export($format, 'تقرير_المخزون', $headers, $rows);
    }
    
    /**
     * تصدير تقرير الأرباح
     */
    public function profitsReport(string $format): void
    {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');
        $ids = $_GET['ids'] ?? '';
        
        $where = "WHERE DATE(i.created_at) >= ? AND DATE(i.created_at) <= ?";
        $params = [$from, $to];
        
        // فلترة حسب المنتجات المحددة
        if ($ids) {
            $idList = array_filter(explode(',', $ids), 'is_numeric');
            if (!empty($idList)) {
                $placeholders = implode(',', array_fill(0, count($idList), '?'));
                $where .= " AND p.id IN ($placeholders)";
                $params = array_merge($params, $idList);
            }
        }
        
        $profits = $this->db->fetchAll(
            "SELECT p.name, p.id,
                    SUM(ii.quantity) as total_quantity,
                    SUM(ii.total_price) as total_revenue,
                    SUM(ii.quantity * p.cost_price) as total_cost,
                    SUM(ii.total_price) - SUM(ii.quantity * p.cost_price) as total_profit
             FROM invoice_items ii
             JOIN products p ON ii.product_id = p.id
             JOIN invoices i ON ii.invoice_id = i.id
             $where
             GROUP BY p.id ORDER BY total_profit DESC",
            $params
        );
        
        $headers = ['المنتج', 'الكمية', 'الإيرادات', 'التكلفة', 'الربح', 'الهامش %'];
        $rows = [];
        foreach ($profits as $item) {
            $margin = $item['total_revenue'] > 0 ? round(($item['total_profit'] / $item['total_revenue']) * 100, 1) : 0;
            $rows[] = [
                $item['name'],
                $item['total_quantity'],
                number_format($item['total_revenue'], 2),
                number_format($item['total_cost'], 2),
                number_format($item['total_profit'], 2),
                $margin . '%'
            ];
        }
        
        $this->export($format, 'تقرير_الأرباح', $headers, $rows);
    }
    
    /**
     * تصدير تقرير أداء الموظفين
     */
    public function employeesReport(string $format): void
    {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');
        
        $performance = $this->db->fetchAll(
            "SELECT u.full_name, 
                    COUNT(i.id) as invoices_count,
                    SUM(CASE WHEN i.invoice_type = 'cash' THEN 1 ELSE 0 END) as cash_sales,
                    SUM(CASE WHEN i.invoice_type = 'installment' THEN 1 ELSE 0 END) as installment_sales,
                    SUM(i.total_amount) as total_sales
             FROM users u
             LEFT JOIN invoices i ON u.id = i.user_id AND DATE(i.created_at) >= ? AND DATE(i.created_at) <= ?
             GROUP BY u.id ORDER BY total_sales DESC",
            [$from, $to]
        );
        
        $headers = ['الموظف', 'عدد الفواتير', 'مبيعات نقدية', 'مبيعات تقسيط', 'الإجمالي'];
        $rows = [];
        foreach ($performance as $emp) {
            $rows[] = [
                $emp['full_name'],
                $emp['invoices_count'],
                $emp['cash_sales'],
                $emp['installment_sales'],
                number_format($emp['total_sales'] ?? 0, 2)
            ];
        }
        
        $this->export($format, 'تقرير_أداء_الموظفين', $headers, $rows);
    }
    
    /**
     * تصدير تقرير التدفق النقدي
     */
    public function cashflowReport(string $format): void
    {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');
        $ids = $_GET['ids'] ?? '';
        
        if (!empty($ids)) {
            // تصدير التواريخ المحددة فقط
            $dates = explode(',', $ids);
            $placeholders = implode(',', array_fill(0, count($dates), '?'));
            $inflows = $this->db->fetchAll(
                "SELECT DATE(payment_date) as date, SUM(amount) as total
                 FROM payments
                 WHERE DATE(payment_date) IN ($placeholders)
                 GROUP BY DATE(payment_date) ORDER BY date DESC",
                $dates
            );
        } else {
            // تصدير كل التواريخ
            $inflows = $this->db->fetchAll(
                "SELECT DATE(payment_date) as date, SUM(amount) as total
                 FROM payments
                 WHERE DATE(payment_date) >= ? AND DATE(payment_date) <= ?
                 GROUP BY DATE(payment_date) ORDER BY date DESC",
                [$from, $to]
            );
        }
        
        $headers = ['التاريخ', 'المبلغ'];
        $rows = [];
        foreach ($inflows as $flow) {
            $rows[] = [
                $flow['date'],
                number_format($flow['total'], 2)
            ];
        }
        
        $this->export($format, 'تقرير_التدفق_النقدي', $headers, $rows);
    }
    
    /**
     * دالة التصدير الرئيسية
     */
    private function export(string $format, string $filename, array $headers, array $rows): void
    {
        // تطبيق حد التصدير إذا وجد
        $limit = $_GET['export_limit'] ?? 'all';
        if ($limit !== 'all' && is_numeric($limit)) {
            $rows = array_slice($rows, 0, (int)$limit);
        }
        
        if ($format === 'excel') {
            $this->exportExcel($filename, $headers, $rows);
        } else {
            $this->exportPdf($filename, $headers, $rows);
        }
    }
    
    /**
     * تصدير Excel (CSV)
     */
    private function exportExcel(string $filename, array $headers, array $rows): void
    {
        $filename = $filename . '_' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // BOM for UTF-8
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * تصدير PDF (HTML للطباعة)
     */
    private function exportPdf(string $filename, array $headers, array $rows): void
    {
        $settings = $this->getSettings();
        $companyName = $settings['company_name'] ?? 'نظام تقسيط';
        
        ?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title><?= $filename ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; direction: rtl; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .header h1 { font-size: 24px; margin-bottom: 5px; }
        .header p { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px 8px; text-align: right; font-size: 12px; }
        th { background: #f5f5f5; font-weight: bold; }
        tr:nth-child(even) { background: #fafafa; }
        .footer { margin-top: 30px; text-align: center; color: #999; font-size: 11px; }
        @media print { 
            body { padding: 0; } 
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?= $companyName ?></h1>
        <p><?= str_replace('_', ' ', $filename) ?> - <?= date('Y-m-d H:i') ?></p>
    </div>
    
    <button class="no-print" onclick="window.print()" style="padding:10px 20px;margin-bottom:20px;cursor:pointer">طباعة / حفظ PDF</button>
    
    <table>
        <thead>
            <tr>
                <?php foreach ($headers as $h): ?>
                <th><?= $h ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
            <tr>
                <?php foreach ($row as $cell): ?>
                <td><?= $cell ?></td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="footer">
        تم التصدير بواسطة <?= $companyName ?> - <?= date('Y-m-d H:i:s') ?>
    </div>
    
    <script>window.onload = function() { window.print(); }</script>
</body>
</html>
        <?php
        exit;
    }
    
    private function getStatusArabic(string $status): string
    {
        return match($status) {
            'active' => 'نشط',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            default => $status
        };
    }
    
    private function getPaymentMethodArabic(string $method): string
    {
        return match($method) {
            'cash' => 'نقدي',
            'card' => 'بطاقة',
            'bank' => 'تحويل بنكي',
            default => $method
        };
    }
}

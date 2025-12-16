<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Category;
use App\Models\InstallmentPlan;
use App\Models\Installment;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    نظام تقسيط - متحكم الفواتير                    ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
class InvoiceController extends Controller
{
    private Invoice $invoiceModel;
    private Product $productModel;
    private Customer $customerModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->invoiceModel = new Invoice();
        $this->productModel = new Product();
        $this->customerModel = new Customer();
    }
    
    /**
     * قائمة الفواتير
     */
    public function index(): void
    {
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = (int) ($_GET['per_page'] ?? 10);
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
        
        $totalCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM invoices i LEFT JOIN customers c ON i.customer_id = c.id $where", 
            $params
        );
        $pagination = new \Core\Pagination($totalCount, $perPage, $page);
        
        $sql = "SELECT i.*, c.full_name as customer_name, u.full_name as user_name
                FROM invoices i 
                LEFT JOIN customers c ON i.customer_id = c.id 
                LEFT JOIN users u ON i.user_id = u.id
                $where 
                ORDER BY i.id DESC 
                LIMIT {$pagination->getLimit()} OFFSET {$pagination->getOffset()}";
        
        $invoices = $this->db->fetchAll($sql, $params);
        
        $this->view('invoices/index', [
            'pageTitle' => 'الفواتير',
            'invoices' => $invoices,
            'pagination' => $pagination,
            'search' => $search,
            'type' => $type,
            'status' => $status
        ]);
    }
    
    /**
     * نقطة البيع - نقدي
     */
    public function pos(): void
    {
        $categoryModel = new Category();
        
        $products = $this->productModel->getActive();
        $categories = $categoryModel->getActive();
        $customers = $this->customerModel->all('full_name');
        
        $this->view('pos/index', [
            'pageTitle' => 'نقطة البيع',
            'products' => $products,
            'categories' => $categories,
            'customers' => $customers
        ]);
    }
    
    /**
     * نقطة البيع - تقسيط
     */
    public function posInstallment(): void
    {
        $categoryModel = new Category();
        $planModel = new InstallmentPlan();
        
        $products = $this->productModel->getActive();
        $categories = $categoryModel->getActive();
        $customers = $this->customerModel->all('full_name');
        $plans = $planModel->getActive();
        
        $this->view('pos/installment', [
            'pageTitle' => 'بيع بالتقسيط',
            'products' => $products,
            'categories' => $categories,
            'customers' => $customers,
            'plans' => $plans
        ]);
    }
    
    /**
     * حفظ فاتورة نقدي
     */
    public function storeCash(): void
    {
        $items = json_decode($this->input('items'), true);
        
        if (empty($items)) {
            $this->error('يجب إضافة منتجات للفاتورة');
            $this->redirect(url('/pos'));
            return;
        }
        
        $this->db->beginTransaction();
        
        try {
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            
            $discount = (float) $this->input('discount', 0);
            $total = $subtotal - $discount;
            
            // إنشاء الفاتورة
            $invoiceId = $this->invoiceModel->create([
                'invoice_number' => $this->invoiceModel->generateNumber(),
                'invoice_type' => 'cash',
                'customer_id' => $this->input('customer_id') ?: null,
                'user_id' => $_SESSION['user_id'],
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'paid_amount' => $total,
                'remaining_amount' => 0,
                'status' => 'completed',
                'notes' => $this->input('notes')
            ]);
            
            // إضافة البنود
            foreach ($items as $item) {
                $product = $this->productModel->find($item['id']);
                
                $this->db->insert('invoice_items', [
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['id'],
                    'product_name' => $product['name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['quantity'],
                    'serial_number' => $item['serial'] ?? null,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                // تحديث المخزون
                $this->productModel->updateQuantity($item['id'], -$item['quantity']);
            }
            
            // تسجيل الدفع
            $this->db->insert('payments', [
                'invoice_id' => $invoiceId,
                'amount' => $total,
                'payment_method' => $this->input('payment_method', 'cash'),
                'receipt_number' => generateReceiptNumber(),
                'user_id' => $_SESSION['user_id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->db->commit();
            
            $this->logActivity('create', 'invoice', $invoiceId, 'فاتورة نقدي جديدة');
            $this->success('تم إنشاء الفاتورة بنجاح');
            $this->redirect(url("/invoices/{$invoiceId}/print"));
            
        } catch (\Exception $e) {
            $this->db->rollback();
            $this->error('حدث خطأ: ' . $e->getMessage());
            $this->redirect(url('/pos'));
        }
    }
    
    /**
     * حفظ فاتورة تقسيط
     */
    public function storeInstallment(): void
    {
        $items = json_decode($this->input('items'), true);
        $customerId = $this->input('customer_id');
        $planId = $this->input('plan_id');
        $downPayment = (float) $this->input('down_payment', 0);
        
        if (empty($items)) {
            $this->error('يجب إضافة منتجات للفاتورة');
            $this->redirect(url('/pos/installment'));
            return;
        }
        
        if (!$customerId) {
            $this->error('يجب اختيار عميل للتقسيط');
            $this->redirect(url('/pos/installment'));
            return;
        }
        
        if (!$planId) {
            $this->error('يجب اختيار خطة تقسيط');
            $this->redirect(url('/pos/installment'));
            return;
        }
        
        $planModel = new InstallmentPlan();
        $plan = $planModel->find($planId);
        
        $this->db->beginTransaction();
        
        try {
            // حساب المجموع
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            
            // حساب سعر التقسيط
            $increasePercent = $plan['increase_percent'] / 100;
            $installmentTotal = $subtotal * (1 + $increasePercent);
            
            // حساب القسط الشهري
            $remaining = $installmentTotal - $downPayment;
            $monthlyInstallment = round($remaining / $plan['months'], 2);
            
            // تاريخ أول قسط
            $firstInstallmentDate = date('Y-m-d', strtotime('+1 month'));
            
            // إنشاء الفاتورة
            $invoiceId = $this->invoiceModel->create([
                'invoice_number' => $this->invoiceModel->generateNumber(),
                'invoice_type' => 'installment',
                'customer_id' => $customerId,
                'user_id' => $_SESSION['user_id'],
                'subtotal' => $subtotal,
                'total_amount' => $installmentTotal,
                'paid_amount' => $downPayment,
                'remaining_amount' => $remaining,
                'installment_plan_id' => $planId,
                'down_payment' => $downPayment,
                'monthly_installment' => $monthlyInstallment,
                'installments_count' => $plan['months'],
                'first_installment_date' => $firstInstallmentDate,
                'status' => 'active',
                'notes' => $this->input('notes')
            ]);
            
            // إضافة البنود
            foreach ($items as $item) {
                $product = $this->productModel->find($item['id']);
                
                $this->db->insert('invoice_items', [
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['id'],
                    'product_name' => $product['name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['quantity'],
                    'serial_number' => $item['serial'] ?? null,
                    'warranty_end_date' => $product['warranty_months'] ? date('Y-m-d', strtotime("+{$product['warranty_months']} months")) : null,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                // تحديث المخزون
                $this->productModel->updateQuantity($item['id'], -$item['quantity']);
            }
            
            // إنشاء جدول الأقساط
            for ($i = 1; $i <= $plan['months']; $i++) {
                $dueDate = date('Y-m-d', strtotime("+{$i} months"));
                
                $this->db->insert('installments', [
                    'invoice_id' => $invoiceId,
                    'installment_number' => $i,
                    'amount' => $monthlyInstallment,
                    'due_date' => $dueDate,
                    'remaining_amount' => $monthlyInstallment,
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            // تسجيل الدفعة المقدمة
            if ($downPayment > 0) {
                $this->db->insert('payments', [
                    'invoice_id' => $invoiceId,
                    'amount' => $downPayment,
                    'payment_method' => 'cash',
                    'receipt_number' => generateReceiptNumber(),
                    'user_id' => $_SESSION['user_id'],
                    'notes' => 'دفعة مقدمة',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            $this->db->commit();
            
            $this->logActivity('create', 'invoice', $invoiceId, 'فاتورة تقسيط جديدة');
            $this->success('تم إنشاء عقد التقسيط بنجاح');
            $this->redirect(url("/invoices/{$invoiceId}/contract"));
            
        } catch (\Exception $e) {
            $this->db->rollback();
            $this->error('حدث خطأ: ' . $e->getMessage());
            $this->redirect(url('/pos/installment'));
        }
    }
    
    /**
     * عرض فاتورة
     */
    public function show(int $id): void
    {
        $invoice = $this->invoiceModel->getWithDetails($id);
        
        if (!$invoice) {
            $this->error('الفاتورة غير موجودة');
            $this->redirect(url('/invoices'));
            return;
        }
        
        $this->view('invoices/show', [
            'pageTitle' => 'فاتورة رقم ' . $invoice['invoice_number'],
            'invoice' => $invoice
        ]);
    }
    
    /**
     * طباعة فاتورة
     */
    public function print(int $id): void
    {
        $invoice = $this->invoiceModel->getWithDetails($id);
        
        if (!$invoice) {
            $this->error('الفاتورة غير موجودة');
            $this->redirect(url('/invoices'));
            return;
        }
        
        $this->viewOnly('invoices/print', [
            'invoice' => $invoice,
            'settings' => $this->getSettings()
        ]);
    }
    
    /**
     * عقد التقسيط
     */
    public function contract(int $id): void
    {
        $invoice = $this->invoiceModel->getWithDetails($id);
        
        if (!$invoice || $invoice['invoice_type'] !== 'installment') {
            $this->error('العقد غير موجود');
            $this->redirect(url('/invoices'));
            return;
        }
        
        $this->viewOnly('invoices/contract', [
            'invoice' => $invoice,
            'settings' => $this->getSettings()
        ]);
    }
    
    /**
     * صفحة تعديل فاتورة
     */
    public function edit(int $id): void
    {
        $invoice = $this->invoiceModel->getWithDetails($id);
        
        if (!$invoice) {
            $this->error('الفاتورة غير موجودة');
            $this->redirect(url('/invoices'));
            return;
        }
        
        // لا يمكن تعديل الفواتير الملغية
        if ($invoice['status'] === 'cancelled') {
            $this->error('لا يمكن تعديل فاتورة ملغية');
            $this->redirect(url('/invoices/' . $id));
            return;
        }
        
        $categoryModel = new Category();
        
        $products = $this->productModel->getActive();
        $categories = $categoryModel->getActive();
        $customers = $this->customerModel->all('full_name');
        
        $this->view('invoices/edit', [
            'pageTitle' => 'تعديل فاتورة ' . $invoice['invoice_number'],
            'invoice' => $invoice,
            'products' => $products,
            'categories' => $categories,
            'customers' => $customers
        ]);
    }
    
    /**
     * تحديث فاتورة
     */
    public function update(int $id): void
    {
        $invoice = $this->invoiceModel->find($id);
        
        if (!$invoice) {
            $this->error('الفاتورة غير موجودة');
            $this->redirect(url('/invoices'));
            return;
        }
        
        // لا يمكن تعديل الفواتير الملغية
        if ($invoice['status'] === 'cancelled') {
            $this->error('لا يمكن تعديل فاتورة ملغية');
            $this->redirect(url('/invoices/' . $id));
            return;
        }
        
        $this->db->beginTransaction();
        
        try {
            // للفواتير النقدية: تعديل كامل
            if ($invoice['invoice_type'] === 'cash') {
                $items = json_decode($this->input('items'), true);
                
                if (empty($items)) {
                    $this->error('يجب إضافة منتجات للفاتورة');
                    $this->redirect(url('/invoices/' . $id . '/edit'));
                    return;
                }
                
                // إرجاع المخزون القديم
                $oldItems = $this->invoiceModel->getItems($id);
                foreach ($oldItems as $item) {
                    $this->productModel->updateQuantity($item['product_id'], $item['quantity']);
                }
                
                // حذف البنود القديمة
                $this->db->delete('invoice_items', 'invoice_id = ?', [$id]);
                
                // حساب المجموع الجديد
                $subtotal = 0;
                foreach ($items as $item) {
                    $subtotal += $item['price'] * $item['quantity'];
                }
                
                $discount = (float) $this->input('discount', 0);
                $total = $subtotal - $discount;
                
                // تحديث الفاتورة
                $this->invoiceModel->update($id, [
                    'customer_id' => $this->input('customer_id') ?: null,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'total_amount' => $total,
                    'paid_amount' => $total,
                    'notes' => $this->input('notes')
                ]);
                
                // إضافة البنود الجديدة
                foreach ($items as $item) {
                    $product = $this->productModel->find($item['id']);
                    
                    $this->db->insert('invoice_items', [
                        'invoice_id' => $id,
                        'product_id' => $item['id'],
                        'product_name' => $product['name'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'total_price' => $item['price'] * $item['quantity'],
                        'serial_number' => $item['serial'] ?? null,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    // خصم من المخزون
                    $this->productModel->updateQuantity($item['id'], -$item['quantity']);
                }
            } else {
                // فواتير التقسيط: تعديل الملاحظات فقط
                $this->invoiceModel->update($id, [
                    'notes' => $this->input('notes')
                ]);
            }
            
            $this->db->commit();
            
            $this->logActivity('update', 'invoice', $id, 'تعديل فاتورة');
            $this->success('تم تحديث الفاتورة بنجاح');
            $this->redirect(url('/invoices/' . $id));
            
        } catch (\Exception $e) {
            $this->db->rollback();
            $this->error('حدث خطأ: ' . $e->getMessage());
            $this->redirect(url('/invoices/' . $id . '/edit'));
        }
    }
    
    /**
     * إلغاء فاتورة
     */
    public function cancel(int $id): void
    {
        $this->requireRole(['admin']);
        
        $invoice = $this->invoiceModel->find($id);
        
        if (!$invoice) {
            $this->error('الفاتورة غير موجودة');
            $this->redirect(url('/invoices'));
            return;
        }
        
        $this->invoiceModel->update($id, ['status' => 'cancelled']);
        
        // إرجاع المخزون
        $items = $this->invoiceModel->getItems($id);
        foreach ($items as $item) {
            $this->productModel->updateQuantity($item['product_id'], $item['quantity']);
        }
        
        $this->logActivity('cancel', 'invoice', $id, 'إلغاء فاتورة');
        $this->success('تم إلغاء الفاتورة');
        $this->redirect(url('/invoices'));
    }
    
    /**
     * حساب التقسيط (AJAX)
     */
    public function calculateInstallment(): void
    {
        $total = (float) $this->input('total');
        $planId = (int) $this->input('plan_id');
        $downPayment = (float) $this->input('down_payment', 0);
        
        $planModel = new InstallmentPlan();
        $plan = $planModel->find($planId);
        
        if (!$plan) {
            $this->json(['error' => 'خطة التقسيط غير موجودة'], 404);
            return;
        }
        
        $increasePercent = $plan['increase_percent'] / 100;
        $installmentTotal = $total * (1 + $increasePercent);
        $minDownPayment = $installmentTotal * ($plan['min_down_payment_percent'] / 100);
        
        if ($downPayment < $minDownPayment) {
            $downPayment = $minDownPayment;
        }
        
        $remaining = $installmentTotal - $downPayment;
        $monthlyInstallment = round($remaining / $plan['months'], 2);
        
        $this->json([
            'cash_price' => $total,
            'installment_total' => round($installmentTotal, 2),
            'increase_amount' => round($installmentTotal - $total, 2),
            'min_down_payment' => round($minDownPayment, 2),
            'down_payment' => round($downPayment, 2),
            'remaining' => round($remaining, 2),
            'monthly_installment' => $monthlyInstallment,
            'months' => $plan['months'],
            'plan' => $plan
        ]);
    }
    
    /**
     * حذف فاتورة
     */
    public function destroy(int $id): void
    {
        $this->requireRole(['admin']);
        
        $invoice = $this->invoiceModel->find($id);
        
        if (!$invoice) {
            $this->error('الفاتورة غير موجودة');
            $this->redirect(url('/invoices'));
            return;
        }
        
        $this->db->beginTransaction();
        
        try {
            // إرجاع المخزون
            $items = $this->invoiceModel->getItems($id);
            foreach ($items as $item) {
                $this->productModel->updateQuantity($item['product_id'], $item['quantity']);
            }
            
            // حذف البيانات المرتبطة
            $this->db->delete('invoice_items', 'invoice_id = ?', [$id]);
            $this->db->delete('installments', 'invoice_id = ?', [$id]);
            $this->db->delete('payments', 'invoice_id = ?', [$id]);
            
            // حذف الفاتورة
            $this->invoiceModel->delete($id);
            
            $this->db->commit();
            
            $this->logActivity('delete', 'invoice', $id, 'حذف فاتورة: ' . $invoice['invoice_number']);
            $this->success('تم حذف الفاتورة بنجاح');
            
        } catch (\Exception $e) {
            $this->db->rollback();
            $this->error('حدث خطأ أثناء الحذف: ' . $e->getMessage());
        }
        
        $this->redirect(url('/invoices'));
    }
    
    /**
     * حذف متعدد للفواتير
     */
    public function bulkDelete(): void
    {
        $this->requireRole(['admin']);
        
        $ids = $this->input('ids', []);
        
        if (empty($ids)) {
            $this->json(['success' => false, 'message' => 'لم يتم تحديد أي فواتير']);
            return;
        }
        
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }
        
        $ids = array_map('intval', $ids);
        $deletedCount = 0;
        $errors = [];
        
        foreach ($ids as $id) {
            $invoice = $this->invoiceModel->find($id);
            if (!$invoice) {
                continue;
            }
            
            $this->db->beginTransaction();
            
            try {
                // إرجاع المخزون
                $items = $this->invoiceModel->getItems($id);
                foreach ($items as $item) {
                    $this->productModel->updateQuantity($item['product_id'], $item['quantity']);
                }
                
                // حذف البيانات المرتبطة
                $this->db->delete('invoice_items', 'invoice_id = ?', [$id]);
                $this->db->delete('installments', 'invoice_id = ?', [$id]);
                $this->db->delete('payments', 'invoice_id = ?', [$id]);
                
                // حذف الفاتورة
                $this->invoiceModel->delete($id);
                
                $this->db->commit();
                
                $this->logActivity('delete', 'invoice', $id, 'حذف فاتورة (حذف متعدد): ' . $invoice['invoice_number']);
                $deletedCount++;
                
            } catch (\Exception $e) {
                $this->db->rollback();
                $errors[] = $invoice['invoice_number'];
            }
        }
        
        if ($deletedCount > 0) {
            $message = "تم حذف {$deletedCount} فاتورة بنجاح";
            if (!empty($errors)) {
                $message .= " (فشل حذف: " . implode(', ', $errors) . ")";
            }
            $this->json(['success' => true, 'message' => $message]);
        } else {
            $this->json(['success' => false, 'message' => 'لم يتم حذف أي فاتورة']);
        }
    }
}

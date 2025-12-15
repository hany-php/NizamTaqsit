<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Customer;

/**
 * متحكم العملاء
 */
class CustomerController extends Controller
{
    private Customer $customerModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->customerModel = new Customer();
    }
    
    public function index(): void
    {
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = (int) ($_GET['per_page'] ?? 10);
        $search = $_GET['q'] ?? '';
        
        $where = "WHERE 1=1";
        $params = [];
        
        if ($search) {
            $where .= " AND (full_name LIKE ? OR phone LIKE ? OR national_id LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $totalCount = $this->db->fetchColumn("SELECT COUNT(*) FROM customers $where", $params);
        $pagination = new \Core\Pagination($totalCount, $perPage, $page);
        
        $sql = "SELECT c.*, 
                COALESCE((SELECT SUM(remaining_amount) FROM invoices WHERE customer_id = c.id), 0) as balance
                FROM customers c 
                $where 
                ORDER BY c.id DESC 
                LIMIT {$pagination->getLimit()} OFFSET {$pagination->getOffset()}";
        
        $customers = $this->db->fetchAll($sql, $params);
        
        $this->view('customers/index', [
            'pageTitle' => 'إدارة العملاء',
            'customers' => $customers,
            'pagination' => $pagination,
            'search' => $search
        ]);
    }
    
    public function create(): void
    {
        $this->view('customers/create', [
            'pageTitle' => 'إضافة عميل جديد'
        ]);
    }
    
    public function store(): void
    {
        $data = [
            'full_name' => $this->input('full_name'),
            'phone' => $this->input('phone'),
            'phone2' => $this->input('phone2'),
            'national_id' => $this->input('national_id'),
            'address' => $this->input('address'),
            'city' => $this->input('city'),
            'work_address' => $this->input('work_address'),
            'work_phone' => $this->input('work_phone'),
            'guarantor_name' => $this->input('guarantor_name'),
            'guarantor_phone' => $this->input('guarantor_phone'),
            'guarantor_national_id' => $this->input('guarantor_national_id'),
            'credit_limit' => (float) $this->input('credit_limit', 0),
            'notes' => $this->input('notes'),
            'is_active' => 1
        ];
        
        // رفع صورة الهوية
        if (!empty($_FILES['national_id_image']['name'])) {
            $data['national_id_image'] = $this->uploadDocument($_FILES['national_id_image']);
        }
        
        $id = $this->customerModel->create($data);
        
        $this->logActivity('create', 'customer', $id, 'إضافة عميل: ' . $data['full_name']);
        $this->success('تم إضافة العميل بنجاح');
        
        // التوجيه حسب الصفحة المصدر مع تمرير معرف العميل الجديد
        $returnTo = $this->input('return_to');
        if ($returnTo === 'pos') {
            $this->redirect(url('/pos?new_customer_id=' . $id));
        } elseif ($returnTo === 'installment') {
            $this->redirect(url('/pos/installment?new_customer_id=' . $id));
        } else {
            $this->redirect(url('/customers'));
        }
    }

    public function storeAjax(): void
    {
        try {
            $data = [
                'full_name' => $this->input('full_name'),
                'phone' => $this->input('phone'),
                'phone2' => $this->input('phone2'),
                'national_id' => $this->input('national_id'),
                'address' => $this->input('address'),
                'credit_limit' => (float) $this->input('credit_limit', 5000),
                'is_active' => 1
            ];
            
            if (empty($data['full_name']) || empty($data['phone'])) {
                $this->json(['success' => false, 'message' => 'الاسم ورقم الهاتف مطلوبان']);
                return;
            }
            
            $id = $this->customerModel->create($data);
            $this->logActivity('create', 'customer', $id, 'إضافة عميل (POS): ' . $data['full_name']);
            
            $this->json([
                'success' => true, 
                'customer' => [
                    'id' => $id,
                    'full_name' => $data['full_name'],
                    'phone' => $data['phone']
                ]
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    public function show(int $id): void
    {
        $customer = $this->customerModel->find($id);
        
        if (!$customer) {
            $this->error('العميل غير موجود');
            $this->redirect(url('/customers'));
            return;
        }
        
        $invoices = $this->customerModel->getInvoices($id);
        $payments = $this->customerModel->getPayments($id);
        $balance = $this->customerModel->getBalance($id);
        
        $this->view('customers/show', [
            'pageTitle' => 'ملف العميل: ' . $customer['full_name'],
            'customer' => $customer,
            'invoices' => $invoices,
            'payments' => $payments,
            'balance' => $balance
        ]);
    }
    
    public function edit(int $id): void
    {
        $customer = $this->customerModel->find($id);
        
        if (!$customer) {
            $this->error('العميل غير موجود');
            $this->redirect(url('/customers'));
            return;
        }
        
        $this->view('customers/edit', [
            'pageTitle' => 'تعديل: ' . $customer['full_name'],
            'customer' => $customer
        ]);
    }
    
    public function update(int $id): void
    {
        $customer = $this->customerModel->find($id);
        
        if (!$customer) {
            $this->error('العميل غير موجود');
            $this->redirect(url('/customers'));
            return;
        }
        
        $data = [
            'full_name' => $this->input('full_name'),
            'phone' => $this->input('phone'),
            'phone2' => $this->input('phone2'),
            'national_id' => $this->input('national_id'),
            'address' => $this->input('address'),
            'city' => $this->input('city'),
            'work_address' => $this->input('work_address'),
            'work_phone' => $this->input('work_phone'),
            'guarantor_name' => $this->input('guarantor_name'),
            'guarantor_phone' => $this->input('guarantor_phone'),
            'guarantor_national_id' => $this->input('guarantor_national_id'),
            'credit_limit' => (float) $this->input('credit_limit', 0),
            'notes' => $this->input('notes'),
            'is_active' => $this->input('is_active') ? 1 : 0
        ];
        
        if (!empty($_FILES['national_id_image']['name'])) {
            $data['national_id_image'] = $this->uploadDocument($_FILES['national_id_image']);
        }
        
        $this->customerModel->update($id, $data);
        
        $this->logActivity('update', 'customer', $id, 'تعديل عميل: ' . $data['full_name']);
        $this->success('تم تحديث بيانات العميل بنجاح');
        $this->redirect(url('/customers'));
    }
    
    public function destroy(int $id): void
    {
        $this->requireRole(['admin']);
        
        $customer = $this->customerModel->find($id);
        
        if (!$customer) {
            $this->error('العميل غير موجود');
            $this->redirect(url('/customers'));
            return;
        }
        
        $invoiceCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM invoices WHERE customer_id = ?",
            [$id]
        );
        
        if ($invoiceCount > 0) {
            $this->error('لا يمكن حذف العميل لوجود فواتير مرتبطة');
            $this->redirect(url('/customers'));
            return;
        }
        
        $this->customerModel->delete($id);
        
        $this->logActivity('delete', 'customer', $id, 'حذف عميل: ' . $customer['full_name']);
        $this->success('تم حذف العميل بنجاح');
        $this->redirect(url('/customers'));
    }
    
    public function search(): void
    {
        $query = $this->input('q', '');
        $customers = $this->customerModel->searchCustomers($query);
        $this->json($customers);
    }
    
    private function uploadDocument(array $file): ?string
    {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('doc_') . '.' . $ext;
        $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/customers/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            return $filename;
        }
        
        return null;
    }
}

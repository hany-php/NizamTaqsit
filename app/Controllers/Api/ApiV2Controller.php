<?php
namespace App\Controllers\Api;

use Core\BaseApiController;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    نظام تقسيط - API v2 Controller                 ║
 * ║              نقاط نهاية شاملة لجميع الموارد                       ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
class ApiV2Controller extends BaseApiController
{
    // ══════════════════════════════════════════════════════════════════
    // المنتجات
    // ══════════════════════════════════════════════════════════════════
    
    /**
     * قائمة المنتجات
     */
    public function products(): void
    {
        $pagination = $this->getPagination();
        $search = $_GET['q'] ?? '';
        $category = $_GET['category'] ?? '';
        
        $where = "WHERE p.is_active = 1";
        $params = [];
        
        if ($search) {
            $where .= " AND (p.name LIKE ? OR p.barcode LIKE ? OR p.brand LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if ($category) {
            $where .= " AND p.category_id = ?";
            $params[] = $category;
        }
        
        // العدد الإجمالي
        $total = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM products p {$where}",
            $params
        );
        
        // جلب البيانات
        $products = $this->db->fetchAll(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             {$where}
             ORDER BY p.id DESC
             LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}",
            $params
        );
        
        $this->paginated($products, $total, $pagination['page'], $pagination['per_page']);
    }
    
    /**
     * عرض منتج
     */
    public function product(int $id): void
    {
        $product = $this->db->fetch(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.id = ?",
            [$id]
        );
        
        if (!$product) {
            $this->notFound('المنتج غير موجود');
        }
        
        $this->success($product);
    }
    
    /**
     * إضافة منتج
     */
    public function createProduct(): void
    {
        $data = $this->getJsonInput();
        
        $errors = [];
        if (empty($data['name'])) {
            $errors['name'] = 'اسم المنتج مطلوب';
        }
        if (empty($data['cash_price']) || !is_numeric($data['cash_price'])) {
            $errors['cash_price'] = 'السعر النقدي مطلوب';
        }
        
        if (!empty($errors)) {
            $this->validationError($errors);
        }
        
        $productData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'barcode' => $data['barcode'] ?? null,
            'sku' => $data['sku'] ?? null,
            'cash_price' => $data['cash_price'],
            'installment_price' => $data['installment_price'] ?? $data['cash_price'],
            'cost_price' => $data['cost_price'] ?? null,
            'quantity' => $data['quantity'] ?? 0,
            'min_quantity' => $data['min_quantity'] ?? 5,
            'brand' => $data['brand'] ?? null,
            'model' => $data['model'] ?? null,
            'warranty_months' => $data['warranty_months'] ?? 0,
            'is_active' => $data['is_active'] ?? 1,
        ];
        
        $id = $this->db->insert('products', $productData);
        $this->logApiActivity('create_product', 'products', $id);
        
        $product = $this->db->fetch("SELECT * FROM products WHERE id = ?", [$id]);
        $this->success($product, 'تم إضافة المنتج بنجاح', 201);
    }
    
    /**
     * تعديل منتج
     */
    public function updateProduct(int $id): void
    {
        $product = $this->db->fetch("SELECT * FROM products WHERE id = ?", [$id]);
        if (!$product) {
            $this->notFound('المنتج غير موجود');
        }
        
        $data = $this->getJsonInput();
        $updateData = [];
        
        $fields = ['name', 'description', 'category_id', 'barcode', 'sku', 'cash_price', 
                   'installment_price', 'cost_price', 'quantity', 'min_quantity', 
                   'brand', 'model', 'warranty_months', 'is_active'];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (!empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            $this->db->update('products', $updateData, 'id = ?', [$id]);
            $this->logApiActivity('update_product', 'products', $id);
        }
        
        $product = $this->db->fetch("SELECT * FROM products WHERE id = ?", [$id]);
        $this->success($product, 'تم تحديث المنتج بنجاح');
    }
    
    /**
     * حذف منتج
     */
    public function deleteProduct(int $id): void
    {
        $product = $this->db->fetch("SELECT * FROM products WHERE id = ?", [$id]);
        if (!$product) {
            $this->notFound('المنتج غير موجود');
        }
        
        $this->db->delete('products', 'id = ?', [$id]);
        $this->logApiActivity('delete_product', 'products', $id);
        
        $this->success(null, 'تم حذف المنتج بنجاح');
    }
    
    // ══════════════════════════════════════════════════════════════════
    // التصنيفات
    // ══════════════════════════════════════════════════════════════════
    
    /**
     * قائمة التصنيفات
     */
    public function categories(): void
    {
        $categories = $this->db->fetchAll(
            "SELECT c.*, 
                    (SELECT COUNT(*) FROM products WHERE category_id = c.id) as products_count
             FROM categories c 
             ORDER BY c.sort_order, c.name"
        );
        
        $this->success($categories);
    }
    
    /**
     * عرض تصنيف
     */
    public function category(int $id): void
    {
        $category = $this->db->fetch(
            "SELECT c.*, 
                    (SELECT COUNT(*) FROM products WHERE category_id = c.id) as products_count
             FROM categories c WHERE c.id = ?",
            [$id]
        );
        
        if (!$category) {
            $this->notFound('التصنيف غير موجود');
        }
        
        // جلب المنتجات
        $category['products'] = $this->db->fetchAll(
            "SELECT * FROM products WHERE category_id = ? AND is_active = 1",
            [$id]
        );
        
        $this->success($category);
    }
    
    /**
     * إضافة تصنيف
     */
    public function createCategory(): void
    {
        $data = $this->getJsonInput();
        
        if (empty($data['name'])) {
            $this->validationError(['name' => 'اسم التصنيف مطلوب']);
        }
        
        $categoryData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'icon' => $data['icon'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? 1,
        ];
        
        $id = $this->db->insert('categories', $categoryData);
        $this->logApiActivity('create_category', 'categories', $id);
        
        $category = $this->db->fetch("SELECT * FROM categories WHERE id = ?", [$id]);
        $this->success($category, 'تم إضافة التصنيف بنجاح', 201);
    }
    
    /**
     * تعديل تصنيف
     */
    public function updateCategory(int $id): void
    {
        $category = $this->db->fetch("SELECT * FROM categories WHERE id = ?", [$id]);
        if (!$category) {
            $this->notFound('التصنيف غير موجود');
        }
        
        $data = $this->getJsonInput();
        $updateData = [];
        
        foreach (['name', 'description', 'parent_id', 'icon', 'sort_order', 'is_active'] as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (!empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            $this->db->update('categories', $updateData, 'id = ?', [$id]);
            $this->logApiActivity('update_category', 'categories', $id);
        }
        
        $category = $this->db->fetch("SELECT * FROM categories WHERE id = ?", [$id]);
        $this->success($category, 'تم تحديث التصنيف بنجاح');
    }
    
    /**
     * حذف تصنيف
     */
    public function deleteCategory(int $id): void
    {
        $category = $this->db->fetch("SELECT * FROM categories WHERE id = ?", [$id]);
        if (!$category) {
            $this->notFound('التصنيف غير موجود');
        }
        
        // التحقق من عدم وجود منتجات
        $count = $this->db->fetchColumn("SELECT COUNT(*) FROM products WHERE category_id = ?", [$id]);
        if ($count > 0) {
            $this->error('لا يمكن حذف التصنيف لأنه يحتوي على منتجات', 400);
        }
        
        $this->db->delete('categories', 'id = ?', [$id]);
        $this->logApiActivity('delete_category', 'categories', $id);
        
        $this->success(null, 'تم حذف التصنيف بنجاح');
    }
    
    // ══════════════════════════════════════════════════════════════════
    // العملاء
    // ══════════════════════════════════════════════════════════════════
    
    /**
     * قائمة العملاء
     */
    public function customers(): void
    {
        $pagination = $this->getPagination();
        $search = $_GET['q'] ?? '';
        
        $where = "WHERE 1=1";
        $params = [];
        
        if ($search) {
            $where .= " AND (full_name LIKE ? OR phone LIKE ? OR national_id LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $total = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM customers {$where}",
            $params
        );
        
        $customers = $this->db->fetchAll(
            "SELECT c.*, 
                    COALESCE((SELECT SUM(remaining_amount) FROM invoices WHERE customer_id = c.id AND status = 'active'), 0) as balance
             FROM customers c 
             {$where}
             ORDER BY c.id DESC
             LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}",
            $params
        );
        
        $this->paginated($customers, $total, $pagination['page'], $pagination['per_page']);
    }
    
    /**
     * عرض عميل
     */
    public function customer(int $id): void
    {
        $customer = $this->db->fetch("SELECT * FROM customers WHERE id = ?", [$id]);
        
        if (!$customer) {
            $this->notFound('العميل غير موجود');
        }
        
        // إضافة الرصيد
        $customer['balance'] = (float) $this->db->fetchColumn(
            "SELECT COALESCE(SUM(remaining_amount), 0) FROM invoices WHERE customer_id = ? AND status = 'active'",
            [$id]
        );
        
        // إضافة عدد الفواتير النشطة
        $customer['active_invoices_count'] = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM invoices WHERE customer_id = ? AND status = 'active'",
            [$id]
        );
        
        $this->success($customer);
    }
    
    /**
     * إضافة عميل
     */
    public function createCustomer(): void
    {
        $data = $this->getJsonInput();
        
        $errors = [];
        if (empty($data['full_name'])) {
            $errors['full_name'] = 'اسم العميل مطلوب';
        }
        if (empty($data['phone'])) {
            $errors['phone'] = 'رقم الهاتف مطلوب';
        }
        
        if (!empty($errors)) {
            $this->validationError($errors);
        }
        
        // التحقق من عدم تكرار الهاتف
        $existing = $this->db->fetch("SELECT id FROM customers WHERE phone = ?", [$data['phone']]);
        if ($existing) {
            $this->validationError(['phone' => 'رقم الهاتف مسجل مسبقاً']);
        }
        
        $customerData = [
            'full_name' => $data['full_name'],
            'phone' => $data['phone'],
            'phone2' => $data['phone2'] ?? null,
            'national_id' => $data['national_id'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'work_address' => $data['work_address'] ?? null,
            'work_phone' => $data['work_phone'] ?? null,
            'guarantor_name' => $data['guarantor_name'] ?? null,
            'guarantor_phone' => $data['guarantor_phone'] ?? null,
            'guarantor_national_id' => $data['guarantor_national_id'] ?? null,
            'credit_limit' => $data['credit_limit'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
        ];
        
        $id = $this->db->insert('customers', $customerData);
        $this->logApiActivity('create_customer', 'customers', $id);
        
        $customer = $this->db->fetch("SELECT * FROM customers WHERE id = ?", [$id]);
        $this->success($customer, 'تم إضافة العميل بنجاح', 201);
    }
    
    /**
     * تعديل عميل
     */
    public function updateCustomer(int $id): void
    {
        $customer = $this->db->fetch("SELECT * FROM customers WHERE id = ?", [$id]);
        if (!$customer) {
            $this->notFound('العميل غير موجود');
        }
        
        $data = $this->getJsonInput();
        $updateData = [];
        
        $fields = ['full_name', 'phone', 'phone2', 'national_id', 'address', 'city',
                   'work_address', 'work_phone', 'guarantor_name', 'guarantor_phone',
                   'guarantor_national_id', 'credit_limit', 'notes', 'is_active'];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (!empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            $this->db->update('customers', $updateData, 'id = ?', [$id]);
            $this->logApiActivity('update_customer', 'customers', $id);
        }
        
        $customer = $this->db->fetch("SELECT * FROM customers WHERE id = ?", [$id]);
        $this->success($customer, 'تم تحديث بيانات العميل بنجاح');
    }
    
    /**
     * حذف عميل
     */
    public function deleteCustomer(int $id): void
    {
        $customer = $this->db->fetch("SELECT * FROM customers WHERE id = ?", [$id]);
        if (!$customer) {
            $this->notFound('العميل غير موجود');
        }
        
        // التحقق من عدم وجود فواتير
        $count = $this->db->fetchColumn("SELECT COUNT(*) FROM invoices WHERE customer_id = ?", [$id]);
        if ($count > 0) {
            $this->error('لا يمكن حذف العميل لأنه مرتبط بفواتير', 400);
        }
        
        $this->db->delete('customers', 'id = ?', [$id]);
        $this->logApiActivity('delete_customer', 'customers', $id);
        
        $this->success(null, 'تم حذف العميل بنجاح');
    }
    
    // ══════════════════════════════════════════════════════════════════
    // المستخدمين
    // ══════════════════════════════════════════════════════════════════
    
    /**
     * قائمة المستخدمين
     */
    public function users(): void
    {
        $users = $this->db->fetchAll(
            "SELECT id, username, full_name, phone, email, role, is_active, last_login, created_at 
             FROM users ORDER BY id DESC"
        );
        
        $this->success($users);
    }
    
    /**
     * عرض مستخدم
     */
    public function user(int $id): void
    {
        $user = $this->db->fetch(
            "SELECT id, username, full_name, phone, email, role, is_active, last_login, created_at 
             FROM users WHERE id = ?",
            [$id]
        );
        
        if (!$user) {
            $this->notFound('المستخدم غير موجود');
        }
        
        $this->success($user);
    }
    
    /**
     * إضافة مستخدم
     */
    public function createUser(): void
    {
        $data = $this->getJsonInput();
        
        $errors = [];
        if (empty($data['username'])) {
            $errors['username'] = 'اسم المستخدم مطلوب';
        }
        if (empty($data['password'])) {
            $errors['password'] = 'كلمة المرور مطلوبة';
        }
        if (empty($data['full_name'])) {
            $errors['full_name'] = 'الاسم الكامل مطلوب';
        }
        
        if (!empty($errors)) {
            $this->validationError($errors);
        }
        
        // التحقق من عدم تكرار اسم المستخدم
        $existing = $this->db->fetch("SELECT id FROM users WHERE username = ?", [$data['username']]);
        if ($existing) {
            $this->validationError(['username' => 'اسم المستخدم موجود مسبقاً']);
        }
        
        $userData = [
            'username' => $data['username'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'full_name' => $data['full_name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'role' => $data['role'] ?? 'sales',
            'is_active' => $data['is_active'] ?? 1,
        ];
        
        $id = $this->db->insert('users', $userData);
        $this->logApiActivity('create_user', 'users', $id);
        
        $user = $this->db->fetch(
            "SELECT id, username, full_name, phone, email, role, is_active, created_at FROM users WHERE id = ?",
            [$id]
        );
        $this->success($user, 'تم إضافة المستخدم بنجاح', 201);
    }
    
    /**
     * تعديل مستخدم
     */
    public function updateUser(int $id): void
    {
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            $this->notFound('المستخدم غير موجود');
        }
        
        $data = $this->getJsonInput();
        $updateData = [];
        
        foreach (['full_name', 'phone', 'email', 'role', 'is_active'] as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (!empty($data['password'])) {
            $updateData['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (!empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            $this->db->update('users', $updateData, 'id = ?', [$id]);
            $this->logApiActivity('update_user', 'users', $id);
        }
        
        $user = $this->db->fetch(
            "SELECT id, username, full_name, phone, email, role, is_active, created_at FROM users WHERE id = ?",
            [$id]
        );
        $this->success($user, 'تم تحديث بيانات المستخدم بنجاح');
    }
    
    /**
     * حذف مستخدم
     */
    public function deleteUser(int $id): void
    {
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            $this->notFound('المستخدم غير موجود');
        }
        
        // لا يمكن حذف المستخدم الوحيد
        $count = $this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE is_active = 1");
        if ($count <= 1) {
            $this->error('لا يمكن حذف المستخدم الوحيد في النظام', 400);
        }
        
        $this->db->delete('users', 'id = ?', [$id]);
        $this->logApiActivity('delete_user', 'users', $id);
        
        $this->success(null, 'تم حذف المستخدم بنجاح');
    }
    
    // ══════════════════════════════════════════════════════════════════
    // الفواتير
    // ══════════════════════════════════════════════════════════════════
    
    /**
     * قائمة الفواتير
     */
    public function invoices(): void
    {
        $pagination = $this->getPagination();
        $type = $_GET['type'] ?? '';
        $status = $_GET['status'] ?? '';
        $customerId = $_GET['customer_id'] ?? '';
        
        $where = "WHERE 1=1";
        $params = [];
        
        if ($type) {
            $where .= " AND i.invoice_type = ?";
            $params[] = $type;
        }
        
        if ($status) {
            $where .= " AND i.status = ?";
            $params[] = $status;
        }
        
        if ($customerId) {
            $where .= " AND i.customer_id = ?";
            $params[] = $customerId;
        }
        
        $total = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM invoices i {$where}",
            $params
        );
        
        $invoices = $this->db->fetchAll(
            "SELECT i.*, c.full_name as customer_name, c.phone as customer_phone,
                    u.full_name as user_name
             FROM invoices i 
             LEFT JOIN customers c ON i.customer_id = c.id
             LEFT JOIN users u ON i.user_id = u.id
             {$where}
             ORDER BY i.id DESC
             LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}",
            $params
        );
        
        $this->paginated($invoices, $total, $pagination['page'], $pagination['per_page']);
    }
    
    /**
     * عرض فاتورة
     */
    public function invoice(int $id): void
    {
        $invoice = $this->db->fetch(
            "SELECT i.*, c.full_name as customer_name, c.phone as customer_phone,
                    u.full_name as user_name
             FROM invoices i 
             LEFT JOIN customers c ON i.customer_id = c.id
             LEFT JOIN users u ON i.user_id = u.id
             WHERE i.id = ?",
            [$id]
        );
        
        if (!$invoice) {
            $this->notFound('الفاتورة غير موجودة');
        }
        
        // جلب بنود الفاتورة
        $invoice['items'] = $this->db->fetchAll(
            "SELECT * FROM invoice_items WHERE invoice_id = ?",
            [$id]
        );
        
        // جلب الأقساط إذا كانت تقسيط
        if ($invoice['invoice_type'] === 'installment') {
            $invoice['installments'] = $this->db->fetchAll(
                "SELECT * FROM installments WHERE invoice_id = ? ORDER BY installment_number",
                [$id]
            );
        }
        
        // جلب المدفوعات
        $invoice['payments'] = $this->db->fetchAll(
            "SELECT * FROM payments WHERE invoice_id = ? ORDER BY payment_date DESC",
            [$id]
        );
        
        $this->success($invoice);
    }
    
    // ══════════════════════════════════════════════════════════════════
    // الأقساط
    // ══════════════════════════════════════════════════════════════════
    
    /**
     * قائمة الأقساط
     */
    public function installments(): void
    {
        $pagination = $this->getPagination();
        $status = $_GET['status'] ?? '';
        $customerId = $_GET['customer_id'] ?? '';
        
        $where = "WHERE 1=1";
        $params = [];
        
        if ($status) {
            $where .= " AND inst.status = ?";
            $params[] = $status;
        }
        
        if ($customerId) {
            $where .= " AND i.customer_id = ?";
            $params[] = $customerId;
        }
        
        $total = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM installments inst
             JOIN invoices i ON inst.invoice_id = i.id
             {$where}",
            $params
        );
        
        $installments = $this->db->fetchAll(
            "SELECT inst.*, i.invoice_number, i.customer_id,
                    c.full_name as customer_name, c.phone as customer_phone
             FROM installments inst
             JOIN invoices i ON inst.invoice_id = i.id
             LEFT JOIN customers c ON i.customer_id = c.id
             {$where}
             ORDER BY inst.due_date
             LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}",
            $params
        );
        
        $this->paginated($installments, $total, $pagination['page'], $pagination['per_page']);
    }
    
    /**
     * أقساط اليوم
     */
    public function installmentsToday(): void
    {
        $today = date('Y-m-d');
        
        $installments = $this->db->fetchAll(
            "SELECT inst.*, i.invoice_number, 
                    c.full_name as customer_name, c.phone as customer_phone
             FROM installments inst
             JOIN invoices i ON inst.invoice_id = i.id
             JOIN customers c ON i.customer_id = c.id
             WHERE inst.due_date = ? AND inst.status IN ('pending', 'partial')
             ORDER BY c.full_name",
            [$today]
        );
        
        $this->success($installments);
    }
    
    /**
     * الأقساط المتأخرة
     */
    public function installmentsOverdue(): void
    {
        $today = date('Y-m-d');
        
        $installments = $this->db->fetchAll(
            "SELECT inst.*, i.invoice_number, 
                    c.full_name as customer_name, c.phone as customer_phone,
                    JULIANDAY(?) - JULIANDAY(inst.due_date) as days_overdue
             FROM installments inst
             JOIN invoices i ON inst.invoice_id = i.id
             JOIN customers c ON i.customer_id = c.id
             WHERE inst.due_date < ? AND inst.status IN ('pending', 'partial', 'overdue')
             ORDER BY inst.due_date",
            [$today, $today]
        );
        
        $this->success($installments);
    }
    
    /**
     * دفع قسط
     */
    public function payInstallment(int $id): void
    {
        $installment = $this->db->fetch("SELECT * FROM installments WHERE id = ?", [$id]);
        if (!$installment) {
            $this->notFound('القسط غير موجود');
        }
        
        $data = $this->getJsonInput();
        $amount = (float) ($data['amount'] ?? 0);
        $method = $data['method'] ?? 'cash';
        
        if ($amount <= 0) {
            $this->validationError(['amount' => 'المبلغ يجب أن يكون أكبر من صفر']);
        }
        
        // إنشاء رقم إيصال
        $receiptNumber = 'RCP-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // تسجيل الدفعة
        $paymentId = $this->db->insert('payments', [
            'invoice_id' => $installment['invoice_id'],
            'installment_id' => $id,
            'amount' => $amount,
            'payment_method' => $method,
            'receipt_number' => $receiptNumber,
            'payment_date' => date('Y-m-d H:i:s'),
            'user_id' => $this->apiKey['created_by'] ?? 1,
            'notes' => $data['notes'] ?? null,
        ]);
        
        // تحديث القسط
        $paidAmount = $installment['paid_amount'] + $amount;
        $remainingAmount = $installment['amount'] - $paidAmount;
        $status = $paidAmount >= $installment['amount'] ? 'paid' : 'partial';
        
        $this->db->update('installments', [
            'paid_amount' => $paidAmount,
            'remaining_amount' => max(0, $remainingAmount),
            'status' => $status,
            'paid_date' => $status === 'paid' ? date('Y-m-d') : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
        
        // تحديث الفاتورة
        $this->db->query(
            "UPDATE invoices SET 
             paid_amount = paid_amount + ?,
             remaining_amount = remaining_amount - ?,
             updated_at = ?
             WHERE id = ?",
            [$amount, $amount, date('Y-m-d H:i:s'), $installment['invoice_id']]
        );
        
        $this->logApiActivity('pay_installment', 'installments', $id);
        
        $this->success([
            'payment_id' => $paymentId,
            'receipt_number' => $receiptNumber,
            'status' => $status,
        ], 'تم تسجيل الدفعة بنجاح');
    }
    
    // ══════════════════════════════════════════════════════════════════
    // المدفوعات
    // ══════════════════════════════════════════════════════════════════
    
    /**
     * قائمة المدفوعات
     */
    public function payments(): void
    {
        $pagination = $this->getPagination();
        $invoiceId = $_GET['invoice_id'] ?? '';
        $customerId = $_GET['customer_id'] ?? '';
        
        $where = "WHERE 1=1";
        $params = [];
        
        if ($invoiceId) {
            $where .= " AND p.invoice_id = ?";
            $params[] = $invoiceId;
        }
        
        if ($customerId) {
            $where .= " AND i.customer_id = ?";
            $params[] = $customerId;
        }
        
        $total = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM payments p
             JOIN invoices i ON p.invoice_id = i.id
             {$where}",
            $params
        );
        
        $payments = $this->db->fetchAll(
            "SELECT p.*, i.invoice_number, c.full_name as customer_name
             FROM payments p
             JOIN invoices i ON p.invoice_id = i.id
             LEFT JOIN customers c ON i.customer_id = c.id
             {$where}
             ORDER BY p.payment_date DESC
             LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}",
            $params
        );
        
        $this->paginated($payments, $total, $pagination['page'], $pagination['per_page']);
    }
    
    /**
     * عرض مدفوعة
     */
    public function payment(int $id): void
    {
        $payment = $this->db->fetch(
            "SELECT p.*, i.invoice_number, c.full_name as customer_name
             FROM payments p
             JOIN invoices i ON p.invoice_id = i.id
             LEFT JOIN customers c ON i.customer_id = c.id
             WHERE p.id = ?",
            [$id]
        );
        
        if (!$payment) {
            $this->notFound('المدفوعة غير موجودة');
        }
        
        $this->success($payment);
    }
    
    // ══════════════════════════════════════════════════════════════════
    // لوحة التحكم
    // ══════════════════════════════════════════════════════════════════
    
    /**
     * إحصائيات لوحة التحكم
     */
    public function dashboardStats(): void
    {
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');
        
        $stats = [
            // إحصائيات عامة
            'total_customers' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM customers WHERE is_active = 1"),
            'total_products' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM products WHERE is_active = 1"),
            'total_invoices' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM invoices"),
            
            // المبيعات
            'total_sales' => (float) $this->db->fetchColumn("SELECT COALESCE(SUM(total_amount), 0) FROM invoices WHERE status != 'cancelled'"),
            'month_sales' => (float) $this->db->fetchColumn(
                "SELECT COALESCE(SUM(total_amount), 0) FROM invoices WHERE status != 'cancelled' AND DATE(created_at) >= ?",
                [$monthStart]
            ),
            'today_sales' => (float) $this->db->fetchColumn(
                "SELECT COALESCE(SUM(total_amount), 0) FROM invoices WHERE status != 'cancelled' AND DATE(created_at) = ?",
                [$today]
            ),
            
            // التحصيلات
            'total_collected' => (float) $this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM payments"),
            'month_collected' => (float) $this->db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE DATE(payment_date) >= ?",
                [$monthStart]
            ),
            'today_collected' => (float) $this->db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE DATE(payment_date) = ?",
                [$today]
            ),
            
            // الأقساط
            'total_remaining' => (float) $this->db->fetchColumn(
                "SELECT COALESCE(SUM(remaining_amount), 0) FROM invoices WHERE status = 'active'"
            ),
            'overdue_installments' => (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM installments WHERE status IN ('pending', 'partial', 'overdue') AND due_date < ?",
                [$today]
            ),
            'today_installments' => (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM installments WHERE status IN ('pending', 'partial') AND due_date = ?",
                [$today]
            ),
            
            // المخزون
            'low_stock_products' => (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM products WHERE quantity <= min_quantity AND is_active = 1"
            ),
        ];
        
        $this->success($stats);
    }
    
    // ══════════════════════════════════════════════════════════════════
    // المصادقة
    // ══════════════════════════════════════════════════════════════════
    
    /**
     * تسجيل الدخول (بدون API Key)
     */
    public function login(): void
    {
        // هذه الدالة لا تحتاج API Key
    }
}

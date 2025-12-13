<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Product;
use App\Models\Category;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    نظام تقسيط - متحكم المنتجات                    ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
class ProductController extends Controller
{
    private Product $productModel;
    private Category $categoryModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }
    
    /**
     * قائمة المنتجات
     */
    public function index(): void
    {
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = (int) ($_GET['per_page'] ?? 10);
        $search = $_GET['q'] ?? '';
        $categoryId = $_GET['category'] ?? '';
        
        // بناء الاستعلام
        $where = "WHERE 1=1";
        $params = [];
        
        if ($search) {
            $where .= " AND (p.name LIKE ? OR p.barcode LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if ($categoryId) {
            $where .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }
        
        // عدد المنتجات الإجمالي
        $totalCount = $this->db->fetchColumn("SELECT COUNT(*) FROM products p $where", $params);
        
        // إنشاء Pagination
        $pagination = new \Core\Pagination($totalCount, $perPage, $page);
        
        // جلب المنتجات مع الـ Limit
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                $where 
                ORDER BY p.id DESC 
                LIMIT {$pagination->getLimit()} OFFSET {$pagination->getOffset()}";
        
        $products = $this->db->fetchAll($sql, $params);
        $categories = $this->categoryModel->getActive();
        
        $this->view('products/index', [
            'pageTitle' => 'إدارة المنتجات',
            'products' => $products,
            'categories' => $categories,
            'pagination' => $pagination,
            'search' => $search,
            'categoryId' => $categoryId
        ]);
    }
    
    /**
     * صفحة إضافة منتج
     */
    public function create(): void
    {
        $this->requireRole(['admin']);
        
        $categories = $this->categoryModel->getActive();
        
        $this->view('products/create', [
            'pageTitle' => 'إضافة منتج جديد',
            'categories' => $categories
        ]);
    }
    
    /**
     * حفظ منتج جديد
     */
    public function store(): void
    {
        $this->requireRole(['admin']);
        
        $data = [
            'name' => $this->input('name'),
            'description' => $this->input('description'),
            'category_id' => $this->input('category_id') ?: null,
            'barcode' => $this->input('barcode') ?: null,
            'sku' => $this->input('sku') ?: null,
            'cash_price' => (float) $this->input('cash_price'),
            'installment_price' => (float) $this->input('installment_price') ?: null,
            'cost_price' => (float) $this->input('cost_price') ?: null,
            'quantity' => (int) $this->input('quantity', 0),
            'min_quantity' => (int) $this->input('min_quantity', 5),
            'brand' => $this->input('brand'),
            'model' => $this->input('model'),
            'warranty_months' => (int) $this->input('warranty_months', 0),
            'is_active' => $this->input('is_active') ? 1 : 0
        ];
        
        // رفع الصورة
        if (!empty($_FILES['image']['name'])) {
            $data['image'] = $this->uploadImage($_FILES['image']);
        }
        
        // حساب سعر التقسيط التلقائي إن لم يُحدد
        if (empty($data['installment_price'])) {
            $data['installment_price'] = $data['cash_price'] * 1.15; // زيادة 15%
        }
        
        $id = $this->productModel->create($data);
        
        $this->logActivity('create', 'product', $id, 'إضافة منتج: ' . $data['name']);
        $this->success('تم إضافة المنتج بنجاح');
        $this->redirect(url('/products'));
    }
    
    /**
     * عرض منتج
     */
    public function show(int $id): void
    {
        $product = $this->productModel->find($id);
        
        if (!$product) {
            $this->error('المنتج غير موجود');
            $this->redirect(url('/products'));
            return;
        }
        
        // جلب التصنيف
        if ($product['category_id']) {
            $product['category'] = $this->categoryModel->find($product['category_id']);
        }
        
        $this->view('products/show', [
            'pageTitle' => $product['name'],
            'product' => $product
        ]);
    }
    
    /**
     * صفحة تعديل منتج
     */
    public function edit(int $id): void
    {
        $this->requireRole(['admin']);
        
        $product = $this->productModel->find($id);
        
        if (!$product) {
            $this->error('المنتج غير موجود');
            $this->redirect(url('/products'));
            return;
        }
        
        $categories = $this->categoryModel->getActive();
        
        $this->view('products/edit', [
            'pageTitle' => 'تعديل: ' . $product['name'],
            'product' => $product,
            'categories' => $categories
        ]);
    }
    
    /**
     * تحديث منتج
     */
    public function update(int $id): void
    {
        $this->requireRole(['admin']);
        
        $product = $this->productModel->find($id);
        
        if (!$product) {
            $this->error('المنتج غير موجود');
            $this->redirect(url('/products'));
            return;
        }
        
        $data = [
            'name' => $this->input('name'),
            'description' => $this->input('description'),
            'category_id' => $this->input('category_id') ?: null,
            'barcode' => $this->input('barcode') ?: null,
            'sku' => $this->input('sku') ?: null,
            'cash_price' => (float) $this->input('cash_price'),
            'installment_price' => (float) $this->input('installment_price') ?: null,
            'cost_price' => (float) $this->input('cost_price') ?: null,
            'quantity' => (int) $this->input('quantity', 0),
            'min_quantity' => (int) $this->input('min_quantity', 5),
            'brand' => $this->input('brand'),
            'model' => $this->input('model'),
            'warranty_months' => (int) $this->input('warranty_months', 0),
            'is_active' => $this->input('is_active') ? 1 : 0
        ];
        
        // رفع صورة جديدة
        if (!empty($_FILES['image']['name'])) {
            $data['image'] = $this->uploadImage($_FILES['image']);
            // حذف الصورة القديمة
            if ($product['image']) {
                $oldPath = dirname(dirname(__DIR__)) . '/public/uploads/products/' . $product['image'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
        }
        
        $this->productModel->update($id, $data);
        
        $this->logActivity('update', 'product', $id, 'تعديل منتج: ' . $data['name']);
        $this->success('تم تحديث المنتج بنجاح');
        $this->redirect(url('/products'));
    }
    
    /**
     * حذف منتج
     */
    public function destroy(int $id): void
    {
        $this->requireRole(['admin']);
        
        $product = $this->productModel->find($id);
        
        if (!$product) {
            $this->error('المنتج غير موجود');
            $this->redirect(url('/products'));
            return;
        }
        
        // التحقق من عدم وجود فواتير مرتبطة
        $invoiceCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM invoice_items WHERE product_id = ?",
            [$id]
        );
        
        if ($invoiceCount > 0) {
            $this->error('لا يمكن حذف المنتج لوجود فواتير مرتبطة به');
            $this->redirect(url('/products'));
            return;
        }
        
        $this->productModel->delete($id);
        
        $this->logActivity('delete', 'product', $id, 'حذف منتج: ' . $product['name']);
        $this->success('تم حذف المنتج بنجاح');
        $this->redirect(url('/products'));
    }
    
    /**
     * بحث في المنتجات
     */
    public function search(): void
    {
        $query = $this->input('q', '');
        $products = $this->productModel->searchProducts($query);
        $this->json($products);
    }
    
    /**
     * البحث بالباركود
     */
    public function findByBarcode(string $code): void
    {
        $product = $this->productModel->findByBarcode($code);
        
        if ($product) {
            $this->json(['success' => true, 'product' => $product]);
        } else {
            $this->json(['success' => false, 'message' => 'المنتج غير موجود'], 404);
        }
    }
    
    /**
     * رفع صورة المنتج
     */
    private function uploadImage(array $file): ?string
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('product_') . '.' . $ext;
        $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/products/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            return $filename;
        }
        
        return null;
    }
}

<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Category;

/**
 * متحكم التصنيفات
 */
class CategoryController extends Controller
{
    private Category $categoryModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->categoryModel = new Category();
    }
    
    public function index(): void
    {
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = (int) ($_GET['per_page'] ?? 10);
        $search = $_GET['q'] ?? '';
        
        // بناء الاستعلام
        $where = "WHERE 1=1";
        $params = [];
        
        if ($search) {
            $where .= " AND (c.name LIKE ? OR c.description LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        // عدد التصنيفات الإجمالي
        $totalCount = $this->db->fetchColumn("SELECT COUNT(*) FROM categories c $where", $params);
        
        // إنشاء Pagination
        $pagination = new \Core\Pagination($totalCount, $perPage, $page);
        
        // جلب التصنيفات مع الـ Limit
        $sql = "SELECT c.*, COUNT(p.id) as products_count 
                FROM categories c 
                LEFT JOIN products p ON c.id = p.category_id 
                $where 
                GROUP BY c.id 
                ORDER BY c.id DESC 
                LIMIT {$pagination->getLimit()} OFFSET {$pagination->getOffset()}";
        
        $categories = $this->db->fetchAll($sql, $params);
        
        // جلب جميع التصنيفات للـ modal
        $allCategories = $this->categoryModel->getAllWithProductCount();
        
        $this->view('categories/index', [
            'pageTitle' => 'إدارة التصنيفات',
            'categories' => $categories,
            'allCategories' => $allCategories,
            'pagination' => $pagination,
            'search' => $search,
            'totalCount' => $totalCount
        ]);
    }
    
    public function store(): void
    {
        $this->requireRole(['admin']);
        
        $data = [
            'name' => $this->input('name'),
            'description' => $this->input('description'),
            'parent_id' => $this->input('parent_id') ?: null,
            'icon' => $this->input('icon'),
            'color' => $this->input('color') ?: '#1e88e5',
            'sort_order' => (int) $this->input('sort_order', 0),
            'is_active' => 1
        ];
        
        if (empty($data['name'])) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'اسم التصنيف مطلوب']);
                return;
            }
            $this->error('اسم التصنيف مطلوب');
            $this->redirect(url('/categories'));
            return;
        }
        
        $id = $this->categoryModel->create($data);
        
        $this->logActivity('create', 'category', $id, 'إضافة تصنيف: ' . $data['name']);
        
        if ($this->isAjax()) {
            $category = $this->categoryModel->find($id);
            $category['products_count'] = 0;
            $this->json([
                'success' => true, 
                'message' => 'تم إضافة التصنيف بنجاح',
                'category' => $category
            ]);
            return;
        }
        
        $this->success('تم إضافة التصنيف بنجاح');
        $this->redirect(url('/categories'));
    }
    
    public function update(int $id): void
    {
        $this->requireRole(['admin']);
        
        $category = $this->categoryModel->find($id);
        if (!$category) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'التصنيف غير موجود']);
                return;
            }
            $this->error('التصنيف غير موجود');
            $this->redirect(url('/categories'));
            return;
        }
        
        $data = [
            'name' => $this->input('name'),
            'description' => $this->input('description'),
            'parent_id' => $this->input('parent_id') ?: null,
            'icon' => $this->input('icon'),
            'color' => $this->input('color') ?: '#1e88e5',
            'sort_order' => (int) $this->input('sort_order', 0),
            'is_active' => $this->input('is_active') !== null ? ($this->input('is_active') ? 1 : 0) : 1
        ];
        
        if (empty($data['name'])) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'اسم التصنيف مطلوب']);
                return;
            }
            $this->error('اسم التصنيف مطلوب');
            $this->redirect(url('/categories'));
            return;
        }
        
        $this->categoryModel->update($id, $data);
        
        $this->logActivity('update', 'category', $id, 'تعديل تصنيف: ' . $data['name']);
        
        if ($this->isAjax()) {
            $updatedCategory = $this->categoryModel->find($id);
            $updatedCategory['products_count'] = $this->categoryModel->getProductCount($id);
            $this->json([
                'success' => true, 
                'message' => 'تم تحديث التصنيف بنجاح',
                'category' => $updatedCategory
            ]);
            return;
        }
        
        $this->success('تم تحديث التصنيف بنجاح');
        $this->redirect(url('/categories'));
    }
    
    public function destroy(int $id): void
    {
        $this->requireRole(['admin']);
        
        $category = $this->categoryModel->find($id);
        
        if (!$category) {
            $this->error('التصنيف غير موجود');
            $this->redirect(url('/categories'));
            return;
        }
        
        // التحقق من التأكيد على حذف المنتجات
        $confirmDelete = $this->input('confirm_delete');
        $moveToCategory = $this->input('move_to_category');
        
        // عدد المنتجات
        $productCount = $this->categoryModel->getProductCount($id);
        
        if ($productCount > 0 && !$confirmDelete) {
            // إذا لم يتم التأكيد، أظهر رسالة خطأ
            $this->error("لا يمكن حذف التصنيف لوجود {$productCount} منتج مرتبط به. استخدم خيار الحذف مع التأكيد.");
            $this->redirect(url('/categories'));
            return;
        }
        
        // إذا تم التأكيد، قم بمعالجة المنتجات
        if ($productCount > 0 && $confirmDelete) {
            // جلب المنتجات المرتبطة بهذا التصنيف
            $products = $this->db->fetchAll(
                "SELECT p.id, p.name FROM products p WHERE p.category_id = ?",
                [$id]
            );
            
            $deletedProducts = 0;
            $movedProducts = 0;
            
            foreach ($products as $product) {
                // التحقق مما إذا كان المنتج مرتبط بفاتورة
                $hasInvoice = $this->db->fetchColumn(
                    "SELECT COUNT(*) FROM invoice_items WHERE product_id = ?",
                    [$product['id']]
                );
                
                if ($hasInvoice > 0) {
                    // نقل المنتج لتصنيف آخر إذا تم تحديده
                    if ($moveToCategory && $moveToCategory != $id) {
                        $this->db->query(
                            "UPDATE products SET category_id = ? WHERE id = ?",
                            [$moveToCategory, $product['id']]
                        );
                        $movedProducts++;
                    } else {
                        // نقل لـ NULL (بدون تصنيف)
                        $this->db->query(
                            "UPDATE products SET category_id = NULL WHERE id = ?",
                            [$product['id']]
                        );
                        $movedProducts++;
                    }
                } else {
                    // حذف المنتج لأنه غير مرتبط بفاتورة
                    $this->db->delete('products', 'id = ?', [$product['id']]);
                    $deletedProducts++;
                }
            }
            
            $this->logActivity('delete', 'category', $id, 
                "حذف تصنيف: {$category['name']} (تم حذف {$deletedProducts} منتج، نقل {$movedProducts} منتج)"
            );
        }
        
        // حذف التصنيف
        $this->categoryModel->delete($id);
        
        $this->logActivity('delete', 'category', $id, 'حذف تصنيف: ' . $category['name']);
        
        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'message' => 'تم حذف التصنيف بنجاح'
            ]);
            return;
        }
        
        $this->success('تم حذف التصنيف بنجاح');
        $this->redirect(url('/categories'));
    }
    
    /**
     * عرض منتجات التصنيف
     */
    public function show(int $id): void
    {
        $category = $this->categoryModel->find($id);
        
        if (!$category) {
            $this->error('التصنيف غير موجود');
            $this->redirect(url('/categories'));
            return;
        }
        
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = (int) ($_GET['per_page'] ?? 10);
        
        // عدد المنتجات الإجمالي في هذا التصنيف
        $totalCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM products WHERE category_id = ?",
            [$id]
        );
        
        // إنشاء Pagination
        $pagination = new \Core\Pagination($totalCount, $perPage, $page);
        
        // جلب منتجات هذا التصنيف مع pagination
        $products = $this->db->fetchAll(
            "SELECT p.*, 
                    (SELECT COUNT(*) FROM invoice_items WHERE product_id = p.id) as invoice_count
             FROM products p 
             WHERE p.category_id = ? 
             ORDER BY p.id DESC
             LIMIT {$pagination->getLimit()} OFFSET {$pagination->getOffset()}",
            [$id]
        );
        
        // جلب جميع التصنيفات
        $categories = $this->categoryModel->getAllWithProductCount();
        
        $this->view('categories/show', [
            'pageTitle' => 'منتجات التصنيف: ' . $category['name'],
            'category' => $category,
            'products' => $products,
            'categories' => $categories,
            'pagination' => $pagination,
            'totalCount' => $totalCount
        ]);
    }
    
    /**
     * نقل جميع منتجات التصنيف إلى تصنيف آخر
     */
    public function moveAllProducts(int $id): void
    {
        $this->requireRole(['admin']);
        
        $category = $this->categoryModel->find($id);
        if (!$category) {
            $this->error('التصنيف غير موجود');
            $this->redirect(url('/categories'));
            return;
        }
        
        $newCategoryId = $this->input('new_category_id');
        
        if ($newCategoryId == $id) {
            $this->error('لا يمكن نقل المنتجات لنفس التصنيف');
            $this->redirect(url('/categories/' . $id));
            return;
        }
        
        // نقل جميع المنتجات
        $this->db->query(
            "UPDATE products SET category_id = ? WHERE category_id = ?",
            [$newCategoryId ?: null, $id]
        );
        
        $newCategory = $newCategoryId ? $this->categoryModel->find($newCategoryId) : null;
        $this->logActivity('update', 'category', $id, 
            'نقل جميع منتجات التصنيف "' . $category['name'] . '" إلى "' . ($newCategory['name'] ?? 'بدون تصنيف') . '"'
        );
        
        $this->success('تم نقل جميع المنتجات بنجاح');
        $this->redirect(url('/categories/' . $id));
    }
    
    /**
     * نقل منتج واحد لتصنيف آخر
     */
    public function moveProduct(int $categoryId): void
    {
        try {
            // التحقق من الصلاحية بطريقة AJAX-friendly
            $userRole = $_SESSION['user_role'] ?? '';
            if (!in_array($userRole, ['admin'])) {
                $this->json(['success' => false, 'message' => 'ليس لديك صلاحية لهذا الإجراء']);
                return;
            }
            
            $productId = $this->input('product_id');
            $newCategoryId = $this->input('new_category_id');
            
            if (!$productId) {
                $this->json(['success' => false, 'message' => 'معرف المنتج مطلوب']);
                return;
            }
            
            $product = $this->db->fetch("SELECT * FROM products WHERE id = ?", [$productId]);
            
            if (!$product) {
                $this->json(['success' => false, 'message' => 'المنتج غير موجود']);
                return;
            }
            
            $this->db->query(
                "UPDATE products SET category_id = ? WHERE id = ?",
                [$newCategoryId ?: null, $productId]
            );
            
            $this->logActivity('update', 'product', $productId, 
                'نقل المنتج "' . $product['name'] . '" إلى تصنيف جديد'
            );
            
            $this->json(['success' => true, 'message' => 'تم نقل المنتج بنجاح']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
        }
    }
    
    /**
     * حذف منتج من صفحة التصنيف
     */
    public function deleteProduct(int $categoryId): void
    {
        // التحقق من الصلاحية بطريقة AJAX-friendly
        $userRole = $_SESSION['user_role'] ?? '';
        if (!in_array($userRole, ['admin'])) {
            $this->json(['success' => false, 'message' => 'ليس لديك صلاحية لهذا الإجراء']);
            return;
        }
        
        $productId = $this->input('product_id');
        
        $product = $this->db->fetch("SELECT * FROM products WHERE id = ?", [$productId]);
        
        if (!$product) {
            $this->json(['success' => false, 'message' => 'المنتج غير موجود']);
            return;
        }
        
        // التحقق من وجود فواتير مرتبطة
        $invoiceCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM invoice_items WHERE product_id = ?",
            [$productId]
        );
        
        if ($invoiceCount > 0) {
            $this->json(['success' => false, 'message' => 'لا يمكن حذف المنتج لوجود فواتير مرتبطة']);
            return;
        }
        
        $this->db->delete('products', 'id = ?', [$productId]);
        
        $this->logActivity('delete', 'product', $productId, 'حذف منتج: ' . $product['name']);
        
        $this->json(['success' => true, 'message' => 'تم حذف المنتج بنجاح']);
    }
}

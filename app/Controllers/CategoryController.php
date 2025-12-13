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
        $categories = $this->categoryModel->getAllWithProductCount();
        
        $this->view('categories/index', [
            'pageTitle' => 'إدارة التصنيفات',
            'categories' => $categories
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
            'sort_order' => (int) $this->input('sort_order', 0),
            'is_active' => 1
        ];
        
        $id = $this->categoryModel->create($data);
        
        $this->logActivity('create', 'category', $id, 'إضافة تصنيف: ' . $data['name']);
        $this->success('تم إضافة التصنيف بنجاح');
        $this->redirect(url('/categories'));
    }
    
    public function update(int $id): void
    {
        $this->requireRole(['admin']);
        
        $data = [
            'name' => $this->input('name'),
            'description' => $this->input('description'),
            'parent_id' => $this->input('parent_id') ?: null,
            'icon' => $this->input('icon'),
            'sort_order' => (int) $this->input('sort_order', 0),
            'is_active' => $this->input('is_active') ? 1 : 0
        ];
        
        $this->categoryModel->update($id, $data);
        
        $this->logActivity('update', 'category', $id, 'تعديل تصنيف: ' . $data['name']);
        $this->success('تم تحديث التصنيف بنجاح');
        $this->redirect(url('/categories'));
    }
    
    public function destroy(int $id): void
    {
        $this->requireRole(['admin']);
        
        $category = $this->categoryModel->find($id);
        
        if ($this->categoryModel->getProductCount($id) > 0) {
            $this->error('لا يمكن حذف التصنيف لوجود منتجات مرتبطة');
            $this->redirect(url('/categories'));
            return;
        }
        
        $this->categoryModel->delete($id);
        
        $this->logActivity('delete', 'category', $id, 'حذف تصنيف: ' . $category['name']);
        $this->success('تم حذف التصنيف بنجاح');
        $this->redirect(url('/categories'));
    }
}

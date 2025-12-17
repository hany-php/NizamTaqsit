<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\User;

class UserController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        // التحقق من تسجيل الدخول وصلاحية المدير
        $this->requireRole('admin');
        $this->userModel = new User();
    }

    /**
     * عرض قائمة المستخدمين
     */
    public function index(): void
    {
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = (int) ($_GET['per_page'] ?? 15);
        
        $totalCount = $this->db->fetchColumn("SELECT COUNT(*) FROM users");
        $pagination = new \Core\Pagination($totalCount, $perPage, $page);
        
        $users = $this->db->fetchAll(
            "SELECT * FROM users ORDER BY id DESC LIMIT {$pagination->getLimit()} OFFSET {$pagination->getOffset()}"
        );
        
        $this->view('users/index', [
            'title' => 'إدارة المستخدمين',
            'users' => $users,
            'pagination' => $pagination
        ]);
    }

    /**
     * صفحة إضافة مستخدم
     */
    public function create(): void
    {
        $this->view('users/create', [
            'title' => 'إضافة مستخدم'
        ]);
    }

    /**
     * حفظ مستخدم جديد
     */
    public function store(): void
    {
        $data = [
            'username' => $_POST['username'],
            'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'full_name' => $_POST['full_name'],
            'phone' => $_POST['phone'] ?? null,
            'role' => $_POST['role'] ?? 'sales',
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        $this->userModel->create($data);
        
        $_SESSION['flash']['success'] = 'تم إضافة المستخدم بنجاح';
        header('Location: ' . url('/users'));
        exit;
    }

    /**
     * صفحة تعديل مستخدم
     */
    public function edit(int $id): void
    {
        $user = $this->userModel->find($id);
        
        if (!$user) {
            header('Location: ' . url('/users'));
            exit;
        }

        $this->view('users/edit', [
            'title' => 'تعديل مستخدم',
            'user' => $user
        ]);
    }

    /**
     * تحديث مستخدم
     */
    public function update(int $id): void
    {
        $data = [
            'username' => $_POST['username'],
            'full_name' => $_POST['full_name'],
            'phone' => $_POST['phone'] ?? null,
            'role' => $_POST['role'] ?? 'sales',
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // تحديث كلمة المرور إذا تم إدخالها
        if (!empty($_POST['password'])) {
            $data['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $data);
        
        $_SESSION['flash']['success'] = 'تم تحديث المستخدم بنجاح';
        header('Location: ' . url('/users'));
        exit;
    }

    /**
     * حذف مستخدم
     */
    public function destroy(int $id): void
    {
        // منع حذف المستخدم الحالي
        if ($id == $_SESSION['user_id']) {
            $_SESSION['flash']['error'] = 'لا يمكنك حذف حسابك الحالي';
            header('Location: ' . url('/users'));
            exit;
        }

        $this->userModel->delete($id);
        
        $_SESSION['flash']['success'] = 'تم حذف المستخدم بنجاح';
        header('Location: ' . url('/users'));
        exit;
    }
}

<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\User;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    نظام تقسيط - متحكم المصادقة                    ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
class AuthController extends Controller
{
    private User $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }
    
    /**
     * عرض صفحة تسجيل الدخول
     */
    public function showLogin(): void
    {
        // إذا كان مسجل الدخول، توجيهه للوحة التحكم
        if (isset($_SESSION['user_id'])) {
            $this->redirect(url('/dashboard'));
        }
        
        $this->viewOnly('auth/login', [
            'pageTitle' => 'تسجيل الدخول'
        ]);
    }
    
    /**
     * معالجة تسجيل الدخول
     */
    public function login(): void
    {
        $username = $this->input('username');
        $password = $this->input('password');
        
        // التحقق من البيانات
        if (empty($username) || empty($password)) {
            $this->error('يرجى إدخال اسم المستخدم وكلمة المرور');
            $this->redirect(url('/login'));
            return;
        }
        
        // البحث عن المستخدم
        $user = $this->userModel->findByUsername($username);
        
        if (!$user) {
            $this->error('اسم المستخدم غير صحيح');
            $this->redirect(url('/login'));
            return;
        }
        
        // التحقق من كلمة المرور
        if (!$this->userModel->verifyPassword($password, $user['password_hash'])) {
            $this->error('كلمة المرور غير صحيحة');
            $this->redirect(url('/login'));
            return;
        }
        
        // التحقق من أن الحساب نشط
        if (!$user['is_active']) {
            $this->error('هذا الحساب معطل، يرجى التواصل مع المدير');
            $this->redirect(url('/login'));
            return;
        }
        
        // تسجيل الدخول
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];
        
        // تحديث آخر دخول
        $this->userModel->updateLastLogin($user['id']);
        
        // تسجيل النشاط
        $this->logActivity('login', 'user', $user['id'], 'تسجيل دخول');
        
        $this->success('مرحباً بك ' . $user['full_name']);
        $this->redirect(url('/dashboard'));
    }
    
    /**
     * تسجيل الخروج
     */
    public function logout(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->logActivity('logout', 'user', $_SESSION['user_id'], 'تسجيل خروج');
        }
        
        // مسح جميع بيانات الجلسة
        $_SESSION = [];
        
        // حذف كوكي الجلسة من المتصفح
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // تدمير الجلسة
        session_destroy();
        
        $this->redirect(url('/login'));
    }
}

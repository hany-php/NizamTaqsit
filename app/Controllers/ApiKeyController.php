<?php
namespace App\Controllers;

use Core\Controller;
use Core\ApiMiddleware;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    نظام تقسيط - إدارة API Keys                    ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
class ApiKeyController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->requireRole(['admin']);
    }
    
    /**
     * صفحة إدارة API Keys
     */
    public function index(): void
    {
        $apiKeys = $this->db->fetchAll(
            "SELECT ak.*, u.full_name as created_by_name 
             FROM api_keys ak 
             LEFT JOIN users u ON ak.created_by = u.id 
             ORDER BY ak.created_at DESC"
        );
        
        $this->view('settings/api', [
            'pageTitle' => 'إدارة API Keys',
            'apiKeys' => $apiKeys
        ]);
    }
    
    /**
     * إنشاء مفتاح جديد
     */
    public function generate(): void
    {
        $name = $this->input('name', 'مفتاح API');
        $expiresAt = $this->input('expires_at');
        
        $apiKey = ApiMiddleware::generateApiKey();
        
        $id = $this->db->insert('api_keys', [
            'name' => $name,
            'api_key' => $apiKey,
            'permissions' => null,
            'is_active' => 1,
            'expires_at' => $expiresAt ?: null,
            'created_by' => $_SESSION['user_id'],
        ]);
        
        $this->logActivity('create', 'api_keys', $id, 'إنشاء مفتاح API جديد');
        
        // إرجاع JSON للـ AJAX
        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'api_key' => $apiKey,
                'message' => 'تم إنشاء المفتاح بنجاح. احفظه في مكان آمن!'
            ]);
        }
        
        $this->success('تم إنشاء مفتاح API بنجاح');
        $this->redirect(url('/settings/api'));
    }
    
    /**
     * تفعيل/إيقاف مفتاح
     */
    public function toggle(int $id): void
    {
        $key = $this->db->fetch("SELECT * FROM api_keys WHERE id = ?", [$id]);
        
        if (!$key) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'المفتاح غير موجود'], 404);
            }
            $this->error('المفتاح غير موجود');
            $this->redirect(url('/settings/api'));
            return;
        }
        
        $newStatus = $key['is_active'] ? 0 : 1;
        
        $this->db->update('api_keys', [
            'is_active' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
        
        $this->logActivity('update', 'api_keys', $id, $newStatus ? 'تفعيل مفتاح API' : 'إيقاف مفتاح API');
        
        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'is_active' => $newStatus,
                'message' => $newStatus ? 'تم تفعيل المفتاح' : 'تم إيقاف المفتاح'
            ]);
            return;
        }
        
        $this->success($newStatus ? 'تم تفعيل المفتاح' : 'تم إيقاف المفتاح');
        $this->redirect(url('/settings/api'));
    }
    
    /**
     * حذف مفتاح
     */
    public function delete(int $id): void
    {
        $key = $this->db->fetch("SELECT * FROM api_keys WHERE id = ?", [$id]);
        
        if (!$key) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'المفتاح غير موجود'], 404);
            }
            $this->error('المفتاح غير موجود');
            $this->redirect(url('/settings/api'));
            return;
        }
        
        $this->db->delete('api_keys', 'id = ?', [$id]);
        $this->logActivity('delete', 'api_keys', $id, 'حذف مفتاح API');
        
        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'تم حذف المفتاح بنجاح']);
            return;
        }
        
        $this->success('تم حذف المفتاح بنجاح');
        $this->redirect(url('/settings/api'));
    }
    
    /**
     * تحديث بيانات المفتاح
     */
    public function update(int $id): void
    {
        $key = $this->db->fetch("SELECT * FROM api_keys WHERE id = ?", [$id]);
        
        if (!$key) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'المفتاح غير موجود'], 404);
            }
            $this->error('المفتاح غير موجود');
            $this->redirect(url('/settings/api'));
            return;
        }
        
        $name = $this->input('name');
        $expiresAt = $this->input('expires_at');
        
        if (empty($name)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'اسم المفتاح مطلوب'], 400);
            }
            $this->error('اسم المفتاح مطلوب');
            $this->redirect(url('/settings/api'));
            return;
        }
        
        $this->db->update('api_keys', [
            'name' => $name,
            'expires_at' => $expiresAt ?: null,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
        
        $this->logActivity('update', 'api_keys', $id, 'تحديث مفتاح API: ' . $name);
        
        if ($this->isAjax()) {
            $updatedKey = $this->db->fetch("SELECT * FROM api_keys WHERE id = ?", [$id]);
            $this->json([
                'success' => true, 
                'message' => 'تم تحديث المفتاح بنجاح',
                'key' => $updatedKey
            ]);
            return;
        }
        
        $this->success('تم تحديث المفتاح بنجاح');
        $this->redirect(url('/settings/api'));
    }
}

<?php

namespace App\Controllers;

use Core\Controller;

class NotificationController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * عرض التنبيهات
     */
    public function index(): void
    {
        $db = \Core\Database::getInstance();
        
        // الأقساط المتأخرة
        $overdueCount = $db->fetchColumn(
            "SELECT COUNT(*) FROM installments 
             WHERE status IN ('pending', 'partial') AND due_date < date('now')"
        );
        
        // أقساط اليوم
        $todayCount = $db->fetchColumn(
            "SELECT COUNT(*) FROM installments 
             WHERE status IN ('pending', 'partial') AND due_date = date('now')"
        );
        
        // منتجات منخفضة المخزون
        $lowStockCount = $db->fetchColumn(
            "SELECT COUNT(*) FROM products WHERE quantity <= min_quantity AND is_active = 1"
        );

        $notifications = [];
        
        if ($overdueCount > 0) {
            $notifications[] = [
                'type' => 'danger',
                'icon' => 'warning',
                'title' => 'أقساط متأخرة',
                'message' => "يوجد {$overdueCount} قسط متأخر عن السداد",
                'link' => url('/installments/overdue')
            ];
        }
        
        if ($todayCount > 0) {
            $notifications[] = [
                'type' => 'primary',
                'icon' => 'event',
                'title' => 'أقساط اليوم',
                'message' => "يوجد {$todayCount} قسط مستحق اليوم",
                'link' => url('/installments/today')
            ];
        }
        
        if ($lowStockCount > 0) {
            $notifications[] = [
                'type' => 'warning',
                'icon' => 'inventory',
                'title' => 'مخزون منخفض',
                'message' => "يوجد {$lowStockCount} منتج بمخزون منخفض",
                'link' => url('/reports/inventory')
            ];
        }

        $this->view('notifications/index', [
            'title' => 'التنبيهات',
            'notifications' => $notifications
        ]);
    }

    /**
     * تحديد كمقروء
     */
    public function markAsRead(int $id): void
    {
        header('Location: ' . url('/notifications'));
        exit;
    }

    /**
     * تحديد الكل كمقروء
     */
    public function markAllAsRead(): void
    {
        $_SESSION['flash']['success'] = 'تم تحديد جميع التنبيهات كمقروءة';
        header('Location: ' . url('/notifications'));
        exit;
    }
}

<?php
/**
 * متحكم SMS
 */

namespace App\Controllers;

use Core\Controller;
use App\Services\SmsService;

class SmsController extends Controller
{
    private SmsService $smsService;

    public function __construct()
    {
        parent::__construct();
        $this->smsService = new SmsService();
    }

    /**
     * صفحة إعدادات SMS
     */
    public function settings(): void
    {
        $db = \Core\Database::getInstance();
        $settings = [];
        
        $rows = $db->fetchAll("SELECT key_name, value FROM settings WHERE key_name LIKE 'sms_%'");
        foreach ($rows as $row) {
            $settings[$row['key_name']] = $row['value'];
        }
        
        // سجل الرسائل
        $logs = $db->fetchAll("SELECT * FROM sms_logs ORDER BY created_at DESC LIMIT 50");

        $this->view('settings/sms', [
            'title' => 'إعدادات الرسائل القصيرة',
            'settings' => $settings,
            'logs' => $logs
        ]);
    }

    /**
     * حفظ إعدادات SMS
     */
    public function saveSettings(): void
    {
        $db = \Core\Database::getInstance();
        
        $fields = [
            'sms_provider', 'sms_api_key', 'sms_auth_token', 
            'sms_username', 'sms_sender_id',
            'sms_template_reminder', 'sms_template_overdue'
        ];

        foreach ($fields as $field) {
            $value = $_POST[$field] ?? '';
            $db->query(
                "INSERT OR REPLACE INTO settings (key_name, value) VALUES (?, ?)",
                [$field, $value]
            );
        }

        $_SESSION['flash']['success'] = 'تم حفظ إعدادات SMS بنجاح';
        header('Location: ' . url('/settings/sms'));
        exit;
    }

    /**
     * إرسال رسالة تجريبية
     */
    public function sendTest(): void
    {
        $phone = $_POST['phone'] ?? '';
        $message = $_POST['message'] ?? 'هذه رسالة تجريبية من نظام تقسيط';

        $result = $this->smsService->send($phone, $message);

        if ($result['success']) {
            $_SESSION['flash']['success'] = 'تم إرسال الرسالة بنجاح';
        } else {
            $_SESSION['flash']['error'] = 'فشل الإرسال: ' . ($result['error'] ?? 'خطأ غير معروف');
        }

        header('Location: ' . url('/settings/sms'));
        exit;
    }

    /**
     * إرسال تذكيرات للأقساط المستحقة غداً
     */
    public function sendReminders(): void
    {
        $db = \Core\Database::getInstance();
        
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        
        $installments = $db->fetchAll(
            "SELECT inst.*, i.invoice_number, c.full_name as customer_name, c.phone as customer_phone
             FROM installments inst
             JOIN invoices i ON inst.invoice_id = i.id
             JOIN customers c ON i.customer_id = c.id
             WHERE inst.status IN ('pending', 'partial') AND inst.due_date = ?",
            [$tomorrow]
        );

        $sent = 0;
        foreach ($installments as $inst) {
            if (!empty($inst['customer_phone'])) {
                $result = $this->smsService->sendInstallmentReminder($inst);
                if ($result['success']) $sent++;
            }
        }

        $_SESSION['flash']['success'] = "تم إرسال {$sent} تذكير من أصل " . count($installments);
        header('Location: ' . url('/settings/sms'));
        exit;
    }

    /**
     * إرسال تنبيهات للأقساط المتأخرة
     */
    public function sendOverdueAlerts(): void
    {
        $db = \Core\Database::getInstance();
        
        $installments = $db->fetchAll(
            "SELECT inst.*, i.invoice_number, c.full_name as customer_name, c.phone as customer_phone,
                    julianday('now') - julianday(inst.due_date) as days_overdue
             FROM installments inst
             JOIN invoices i ON inst.invoice_id = i.id
             JOIN customers c ON i.customer_id = c.id
             WHERE inst.status IN ('pending', 'partial') AND inst.due_date < date('now')"
        );

        $sent = 0;
        foreach ($installments as $inst) {
            if (!empty($inst['customer_phone'])) {
                $result = $this->smsService->sendOverdueAlert($inst);
                if ($result['success']) $sent++;
            }
        }

        $_SESSION['flash']['success'] = "تم إرسال {$sent} تنبيه تأخر من أصل " . count($installments);
        header('Location: ' . url('/settings/sms'));
        exit;
    }
}

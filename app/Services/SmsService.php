<?php
/**
 * نظام إشعارات SMS
 */

namespace App\Services;

class SmsService
{
    private string $provider;
    private string $apiKey;
    private string $senderId;
    private $db;

    public function __construct()
    {
        $this->db = \Core\Database::getInstance();
        $this->loadSettings();
    }

    /**
     * تحميل إعدادات SMS
     */
    private function loadSettings(): void
    {
        $settings = $this->db->fetchAll("SELECT key_name, value FROM settings WHERE key_name LIKE 'sms_%'");
        
        foreach ($settings as $setting) {
            switch ($setting['key_name']) {
                case 'sms_provider': $this->provider = $setting['value']; break;
                case 'sms_api_key': $this->apiKey = $setting['value']; break;
                case 'sms_sender_id': $this->senderId = $setting['value']; break;
            }
        }
    }

    /**
     * إرسال رسالة
     */
    public function send(string $phone, string $message): array
    {
        // تنسيق رقم الهاتف
        $phone = $this->formatPhone($phone);
        
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'لم يتم تكوين إعدادات SMS'];
        }

        // تسجيل الرسالة
        $logId = $this->logMessage($phone, $message, 'pending');

        try {
            $result = $this->sendViaProvider($phone, $message);
            
            // تحديث حالة الرسالة
            $this->updateLogStatus($logId, $result['success'] ? 'sent' : 'failed');
            
            return $result;
        } catch (\Exception $e) {
            $this->updateLogStatus($logId, 'failed');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * إرسال عبر المزود
     */
    private function sendViaProvider(string $phone, string $message): array
    {
        switch ($this->provider) {
            case 'twilio':
                return $this->sendTwilio($phone, $message);
            case 'msegat':
                return $this->sendMsegat($phone, $message);
            default:
                // وضع تجريبي - فقط يسجل الرسالة
                return ['success' => true, 'message' => 'تم إرسال الرسالة (وضع تجريبي)'];
        }
    }

    /**
     * إرسال عبر Twilio
     */
    private function sendTwilio(string $phone, string $message): array
    {
        // Twilio API implementation
        $accountSid = $this->apiKey;
        $authToken = $this->db->fetchColumn("SELECT value FROM settings WHERE key_name = 'sms_auth_token'");
        
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'To' => $phone,
                'From' => $this->senderId,
                'Body' => $message
            ]),
            CURLOPT_USERPWD => "{$accountSid}:{$authToken}",
            CURLOPT_RETURNTRANSFER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'response' => json_decode($response, true)
        ];
    }

    /**
     * إرسال عبر Msegat (مزود عربي)
     */
    private function sendMsegat(string $phone, string $message): array
    {
        $username = $this->db->fetchColumn("SELECT value FROM settings WHERE key_name = 'sms_username'");
        
        $url = "https://www.msegat.com/gw/sendsms.php";
        
        $data = [
            'userName' => $username,
            'apiKey' => $this->apiKey,
            'numbers' => $phone,
            'userSender' => $this->senderId,
            'msg' => $message,
            'msgEncoding' => 'UTF8'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        return [
            'success' => isset($result['code']) && $result['code'] == '1',
            'response' => $result
        ];
    }

    /**
     * تنسيق رقم الهاتف
     */
    private function formatPhone(string $phone): string
    {
        // إزالة الفراغات والشرطات
        $phone = preg_replace('/[\s\-]/', '', $phone);
        
        // إذا بدأ بـ 0، استبدل بـ +20 (مصر)
        if (str_starts_with($phone, '0')) {
            $phone = '+20' . substr($phone, 1);
        }
        
        // إذا لم يبدأ بـ +، أضفها
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        
        return $phone;
    }

    /**
     * تسجيل الرسالة
     */
    private function logMessage(string $phone, string $message, string $status): int
    {
        return $this->db->insert('sms_logs', [
            'phone' => $phone,
            'message' => $message,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * تحديث حالة الرسالة
     */
    private function updateLogStatus(int $id, string $status): void
    {
        $this->db->update('sms_logs', ['status' => $status], 'id = ?', [$id]);
    }

    /**
     * إرسال تذكير قسط
     */
    public function sendInstallmentReminder(array $installment): array
    {
        $template = $this->db->fetchColumn("SELECT value FROM settings WHERE key_name = 'sms_template_reminder'");
        
        if (!$template) {
            $template = "عزيزي {customer}، نذكرك بموعد استحقاق القسط رقم {number} بمبلغ {amount} بتاريخ {date}. شكراً لتعاملكم معنا.";
        }
        
        $message = str_replace(
            ['{customer}', '{number}', '{amount}', '{date}'],
            [
                $installment['customer_name'],
                $installment['installment_number'],
                formatMoney($installment['remaining_amount']),
                formatDate($installment['due_date'])
            ],
            $template
        );
        
        return $this->send($installment['customer_phone'], $message);
    }

    /**
     * إرسال تنبيه تأخر
     */
    public function sendOverdueAlert(array $installment): array
    {
        $template = $this->db->fetchColumn("SELECT value FROM settings WHERE key_name = 'sms_template_overdue'");
        
        if (!$template) {
            $template = "عزيزي {customer}، القسط رقم {number} بمبلغ {amount} متأخر عن السداد. يرجى المبادرة بالسداد.";
        }
        
        $message = str_replace(
            ['{customer}', '{number}', '{amount}', '{days}'],
            [
                $installment['customer_name'],
                $installment['installment_number'],
                formatMoney($installment['remaining_amount']),
                $installment['days_overdue'] ?? 0
            ],
            $template
        );
        
        return $this->send($installment['customer_phone'], $message);
    }
}

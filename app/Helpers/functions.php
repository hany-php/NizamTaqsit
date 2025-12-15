<?php
/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    نظام تقسيط - الدوال المساعدة                   ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */

/**
 * الحصول على URL أساسي
 */
function url(string $path = ''): string
{
    // للسيرفر المدمج في PHP استخدم مسار فارغ
    // لـ Apache/htdocs استخدم '/nizam-taqsit/public'
    $baseUrl = '';
    return $baseUrl . '/' . ltrim($path, '/');
}

/**
 * الحصول على مسار الأصول مع دعم cache busting
 */
function asset(string $path): string
{
    $assetPath = 'assets/' . ltrim($path, '/');
    $fullPath = BASE_PATH . '/public/' . $assetPath;
    
    // إضافة version query string بناءً على وقت تعديل الملف
    $version = '';
    if (file_exists($fullPath)) {
        $version = '?v=' . filemtime($fullPath);
    }
    
    return url($assetPath) . $version;
}

/**
 * الحصول على مسار الرفع
 */
function upload(string $path): string
{
    return url('uploads/' . ltrim($path, '/'));
}

/**
 * تنسيق المبلغ
 */
function formatMoney(float $amount, string $currency = 'ج.م'): string
{
    return number_format($amount, 2) . ' ' . $currency;
}

/**
 * تنسيق التاريخ بالعربي
 */
function formatDate(string $date, string $format = 'd/m/Y'): string
{
    return date($format, strtotime($date));
}

/**
 * تنسيق التاريخ والوقت بالعربي
 */
function formatDateTime(string $datetime): string
{
    return date('d/m/Y h:i A', strtotime($datetime));
}

/**
 * الحصول على التاريخ بالهجري (تقريبي)
 */
function hijriDate(string $date = null): string
{
    $timestamp = $date ? strtotime($date) : time();
    // تحويل تقريبي - يمكن استخدام مكتبة متخصصة
    return date('d/m/Y', $timestamp);
}

/**
 * تحويل الأرقام للعربية
 */
function arabicNumber($number): string
{
    $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($english, $arabic, (string)$number);
}

/**
 * تنسيق رقم الهاتف
 */
function formatPhone(string $phone): string
{
    // إزالة أي أحرف غير رقمية
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // تنسيق رقم مصري
    if (strlen($phone) === 11 && substr($phone, 0, 2) === '01') {
        return substr($phone, 0, 4) . '-' . substr($phone, 4, 3) . '-' . substr($phone, 7);
    }
    
    return $phone;
}

/**
 * اختصار النص
 */
function truncate(string $text, int $length = 50): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

/**
 * إنشاء رقم فاتورة فريد
 */
function generateInvoiceNumber(string $prefix = 'INV'): string
{
    return $prefix . '-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

/**
 * إنشاء رقم إيصال فريد
 */
function generateReceiptNumber(): string
{
    return 'RCP-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

/**
 * حالة القسط بالعربي
 */
function installmentStatus(string $status): string
{
    return match ($status) {
        'pending' => 'قيد الانتظار',
        'partial' => 'مدفوع جزئياً',
        'paid' => 'مدفوع',
        'overdue' => 'متأخر',
        default => $status
    };
}

/**
 * لون حالة القسط
 */
function installmentStatusColor(string $status): string
{
    return match ($status) {
        'pending' => 'warning',
        'partial' => 'info',
        'paid' => 'success',
        'overdue' => 'danger',
        default => 'secondary'
    };
}

/**
 * حالة الفاتورة بالعربي
 */
function invoiceStatus(string $status): string
{
    return match ($status) {
        'pending' => 'معلقة',
        'active' => 'نشطة',
        'completed' => 'مكتملة',
        'cancelled' => 'ملغاة',
        default => $status
    };
}

/**
 * نوع الفاتورة بالعربي
 */
function invoiceType(string $type): string
{
    return match ($type) {
        'cash' => 'نقدي',
        'installment' => 'تقسيط',
        default => $type
    };
}

/**
 * دور المستخدم بالعربي
 */
function userRole(string $role): string
{
    return match ($role) {
        'admin' => 'مدير',
        'sales' => 'موظف مبيعات',
        'accountant' => 'محاسب',
        default => $role
    };
}

/**
 * طريقة الدفع بالعربي
 */
function paymentMethod(string $method): string
{
    return match ($method) {
        'cash' => 'نقداً',
        'card' => 'بطاقة',
        'transfer' => 'تحويل',
        'other' => 'أخرى',
        default => $method
    };
}

/**
 * تنظيف المدخلات
 */
function clean(mixed $data): mixed
{
    if (is_array($data)) {
        return array_map('clean', $data);
    }
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * قيمة قديمة من الطلب
 */
function old(string $key, mixed $default = ''): mixed
{
    $value = $_SESSION['old'][$key] ?? $default;
    unset($_SESSION['old'][$key]);
    return $value;
}

/**
 * خطأ في حقل معين
 */
function error(string $key): ?string
{
    $error = $_SESSION['errors'][$key] ?? null;
    unset($_SESSION['errors'][$key]);
    return $error;
}

/**
 * رسالة Flash
 */
function flash(string $key): ?string
{
    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $message;
}

/**
 * التحقق من تسجيل الدخول
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * التحقق من الصلاحية
 */
function hasRole(string|array $roles): bool
{
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = $_SESSION['user_role'] ?? '';
    $roles = is_array($roles) ? $roles : [$roles];
    
    return in_array($userRole, $roles);
}

/**
 * الحصول على إعداد
 */
function setting(string $key, mixed $default = null): mixed
{
    static $settings = null;
    
    if ($settings === null) {
        $db = \Core\Database::getInstance();
        $rows = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    
    return $settings[$key] ?? $default;
}

/**
 * رقم CSRF Token
 */
function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * حقل CSRF المخفي
 */
function csrfField(): string
{
    return '<input type="hidden" name="_token" value="' . csrfToken() . '">';
}

/**
 * التحقق من CSRF
 */
function verifyCsrf(): bool
{
    $token = $_POST['_token'] ?? '';
    return $token === ($_SESSION['csrf_token'] ?? '');
}

/**
 * حساب الفرق بين تاريخين بالأيام
 */
function daysDiff(string $date1, string $date2 = null): int
{
    $d1 = new DateTime($date1);
    $d2 = $date2 ? new DateTime($date2) : new DateTime();
    return $d1->diff($d2)->days;
}

/**
 * هل التاريخ متأخر
 */
function isOverdue(string $date): bool
{
    return strtotime($date) < strtotime('today');
}

/**
 * هل التاريخ اليوم
 */
function isToday(string $date): bool
{
    return date('Y-m-d', strtotime($date)) === date('Y-m-d');
}

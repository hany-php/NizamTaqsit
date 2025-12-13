<?php
/**
 * Middleware للتحقق من الصلاحيات
 */

namespace Core;

class Permission
{
    private static array $permissions = [
        'admin' => ['*'],
        'accountant' => [
            'dashboard', 'pos', 'invoices', 'payments', 'installments',
            'customers.view', 'products.view', 'reports'
        ],
        'sales' => [
            'dashboard', 'pos', 'invoices.create', 'customers.create',
            'products.view', 'installments.view'
        ]
    ];

    /**
     * التحقق من صلاحية
     */
    public static function check(string $permission): bool
    {
        $role = $_SESSION['user_role'] ?? 'guest';
        
        if (!isset(self::$permissions[$role])) {
            return false;
        }

        $userPermissions = self::$permissions[$role];
        
        // المدير لديه كل الصلاحيات
        if (in_array('*', $userPermissions)) {
            return true;
        }

        // التحقق من الصلاحية المحددة
        if (in_array($permission, $userPermissions)) {
            return true;
        }

        // التحقق من الصلاحية الأساسية (مثل products من products.view)
        $basePerm = explode('.', $permission)[0];
        if (in_array($basePerm, $userPermissions)) {
            return true;
        }

        return false;
    }

    /**
     * التحقق والتوجيه إذا لم تتوفر الصلاحية
     */
    public static function require(string $permission): void
    {
        if (!self::check($permission)) {
            $_SESSION['flash']['error'] = 'ليس لديك صلاحية لهذا الإجراء';
            header('Location: ' . url('/'));
            exit;
        }
    }

    /**
     * الحصول على قائمة الصلاحيات للدور
     */
    public static function getForRole(string $role): array
    {
        return self::$permissions[$role] ?? [];
    }

    /**
     * تحديث صلاحيات دور
     */
    public static function setForRole(string $role, array $permissions): void
    {
        self::$permissions[$role] = $permissions;
    }

    /**
     * قائمة كل الصلاحيات المتاحة
     */
    public static function allAvailable(): array
    {
        return [
            'dashboard' => 'لوحة التحكم',
            'pos' => 'نقطة البيع',
            'products' => 'إدارة المنتجات',
            'products.view' => 'عرض المنتجات',
            'products.create' => 'إضافة منتجات',
            'products.edit' => 'تعديل منتجات',
            'products.delete' => 'حذف منتجات',
            'customers' => 'إدارة العملاء',
            'customers.view' => 'عرض العملاء',
            'customers.create' => 'إضافة عملاء',
            'invoices' => 'الفواتير',
            'invoices.create' => 'إنشاء فواتير',
            'installments' => 'الأقساط',
            'installments.view' => 'عرض الأقساط',
            'installments.pay' => 'تسجيل الدفعات',
            'payments' => 'المدفوعات',
            'reports' => 'التقارير',
            'users' => 'إدارة المستخدمين',
            'settings' => 'الإعدادات'
        ];
    }
}

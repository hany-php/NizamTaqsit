<?php
/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║                    نظام تقسيط - المسارات                          ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */

use Core\Router;

$router = new Router();

// ══════════════════════════════════════════════════════════════════
// المصادقة
// ══════════════════════════════════════════════════════════════════
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// ══════════════════════════════════════════════════════════════════
// لوحة التحكم
// ══════════════════════════════════════════════════════════════════
$router->get('/', 'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');
$router->get('/dashboard/stats', 'DashboardController@stats');

// ══════════════════════════════════════════════════════════════════
// المنتجات
// ══════════════════════════════════════════════════════════════════
$router->get('/products', 'ProductController@index');
$router->get('/products/create', 'ProductController@create');
$router->post('/products', 'ProductController@store');
$router->get('/products/{id}', 'ProductController@show');
$router->get('/products/{id}/edit', 'ProductController@edit');
$router->post('/products/{id}', 'ProductController@update');
$router->post('/products/{id}/delete', 'ProductController@destroy');
$router->get('/products/search', 'ProductController@search');
$router->get('/products/barcode/{code}', 'ProductController@findByBarcode');

// ══════════════════════════════════════════════════════════════════
// التصنيفات
// ══════════════════════════════════════════════════════════════════
$router->get('/categories', 'CategoryController@index');
$router->post('/categories', 'CategoryController@store');
$router->post('/categories/{id}', 'CategoryController@update');
$router->post('/categories/{id}/delete', 'CategoryController@destroy');

// ══════════════════════════════════════════════════════════════════
// العملاء
// ══════════════════════════════════════════════════════════════════
$router->get('/customers', 'CustomerController@index');
$router->get('/customers/create', 'CustomerController@create');
$router->post('/customers', 'CustomerController@store');
$router->get('/customers/{id}', 'CustomerController@show');
$router->get('/customers/{id}/edit', 'CustomerController@edit');
$router->post('/customers/{id}', 'CustomerController@update');
$router->post('/customers/{id}/delete', 'CustomerController@destroy');
$router->get('/customers/search', 'CustomerController@search');

// ══════════════════════════════════════════════════════════════════
// نقطة البيع
// ══════════════════════════════════════════════════════════════════
$router->get('/pos', 'InvoiceController@pos');
$router->post('/pos/cash', 'InvoiceController@storeCash');
$router->get('/pos/installment', 'InvoiceController@posInstallment');
$router->post('/pos/installment', 'InvoiceController@storeInstallment');
$router->get('/pos/calculate', 'InvoiceController@calculateInstallment');

// ══════════════════════════════════════════════════════════════════
// الفواتير
// ══════════════════════════════════════════════════════════════════
$router->get('/invoices', 'InvoiceController@index');
$router->get('/invoices/{id}', 'InvoiceController@show');
$router->get('/invoices/{id}/print', 'InvoiceController@print');
$router->get('/invoices/{id}/contract', 'InvoiceController@contract');
$router->post('/invoices/{id}/cancel', 'InvoiceController@cancel');

// ══════════════════════════════════════════════════════════════════
// الأقساط
// ══════════════════════════════════════════════════════════════════
$router->get('/installments', 'InstallmentController@index');
$router->get('/installments/today', 'InstallmentController@today');
$router->get('/installments/overdue', 'InstallmentController@overdue');
$router->get('/installments/upcoming', 'InstallmentController@upcoming');
$router->get('/installments/{id}', 'InstallmentController@show');
$router->post('/installments/{id}/pay', 'InstallmentController@pay');

// ══════════════════════════════════════════════════════════════════
// المدفوعات
// ══════════════════════════════════════════════════════════════════
$router->get('/payments', 'PaymentController@index');
$router->get('/payments/{id}/receipt', 'PaymentController@receipt');

// ══════════════════════════════════════════════════════════════════
// التقارير
// ══════════════════════════════════════════════════════════════════
$router->get('/reports', 'ReportController@index');
$router->get('/reports/sales', 'ReportController@sales');
$router->get('/reports/collections', 'ReportController@collections');
$router->get('/reports/overdue', 'ReportController@overdue');
$router->get('/reports/customers', 'ReportController@customers');
$router->get('/reports/inventory', 'ReportController@inventory');
// التقارير المتقدمة
$router->get('/reports/profits', 'AdvancedReportController@profits');
$router->get('/reports/employees', 'AdvancedReportController@employees');
$router->get('/reports/cashflow', 'AdvancedReportController@cashflow');
$router->get('/reports/chart/sales', 'AdvancedReportController@salesChart');

// ══════════════════════════════════════════════════════════════════
// المستخدمين
// ══════════════════════════════════════════════════════════════════
$router->get('/users', 'UserController@index');
$router->get('/users/create', 'UserController@create');
$router->post('/users', 'UserController@store');
$router->get('/users/{id}/edit', 'UserController@edit');
$router->post('/users/{id}', 'UserController@update');
$router->post('/users/{id}/delete', 'UserController@destroy');

// ══════════════════════════════════════════════════════════════════
// الإعدادات
// ══════════════════════════════════════════════════════════════════
$router->get('/settings', 'SettingController@index');
$router->post('/settings', 'SettingController@update');
$router->get('/settings/installment', 'SettingController@installmentPlans');
$router->post('/settings/installment', 'SettingController@updateInstallmentPlan');
// إعدادات القائمة
$router->post('/settings/menu-config', 'SettingController@saveMenuConfig');
$router->get('/settings/menu-config', 'SettingController@fetchMenuConfig');
// إعدادات المظهر
$router->post('/settings/theme-config', 'SettingController@saveThemeConfig');
// النسخ الاحتياطي
$router->get('/settings/backup', 'BackupController@index');
$router->post('/settings/backup/create', 'BackupController@create');
$router->get('/settings/backup/download/{filename}', 'BackupController@download');
$router->post('/settings/backup/delete/{filename}', 'BackupController@delete');
$router->post('/settings/backup/restore', 'BackupController@restore');
// الرسائل القصيرة
$router->get('/settings/sms', 'SmsController@settings');
$router->post('/settings/sms/save', 'SmsController@saveSettings');
$router->post('/settings/sms/test', 'SmsController@sendTest');
$router->post('/settings/sms/reminders', 'SmsController@sendReminders');
$router->post('/settings/sms/overdue', 'SmsController@sendOverdueAlerts');

// ══════════════════════════════════════════════════════════════════
// التنبيهات
// ══════════════════════════════════════════════════════════════════
$router->get('/notifications', 'NotificationController@index');
$router->post('/notifications/{id}/read', 'NotificationController@markAsRead');
$router->post('/notifications/read-all', 'NotificationController@markAllAsRead');

// ══════════════════════════════════════════════════════════════════
// API
// ══════════════════════════════════════════════════════════════════
$router->get('/api/products', 'ApiController@products');
$router->get('/api/products/{id}', 'ApiController@product');
$router->get('/api/customers', 'ApiController@customers');
$router->get('/api/customers/{id}', 'ApiController@customer');
$router->get('/api/invoices', 'ApiController@invoices');
$router->get('/api/installments', 'ApiController@installments');
$router->get('/api/installments/due', 'ApiController@dueInstallments');
$router->post('/api/auth/login', 'ApiController@login');
$router->post('/api/payments', 'ApiController@storePayment');

// ══════════════════════════════════════════════════════════════════
// التصدير PDF/Excel
// ══════════════════════════════════════════════════════════════════
$router->get('/export/products/{format}', 'ExportController@products');
$router->get('/export/customers/{format}', 'ExportController@customers');
$router->get('/export/invoices/{format}', 'ExportController@invoices');
$router->get('/export/installments/overdue/{format}', 'ExportController@overdueInstallments');
$router->get('/export/installments/today/{format}', 'ExportController@todayInstallments');
$router->get('/export/installments/{format}', 'ExportController@installments');
$router->get('/export/payments/{format}', 'ExportController@payments');
$router->get('/export/reports/sales/{format}', 'ExportController@salesReport');
$router->get('/export/reports/collections/{format}', 'ExportController@collectionsReport');
$router->get('/export/reports/overdue/{format}', 'ExportController@overdueReport');
$router->get('/export/reports/customers/{format}', 'ExportController@customersReport');
$router->get('/export/reports/inventory/{format}', 'ExportController@inventoryReport');
$router->get('/export/reports/profits/{format}', 'ExportController@profitsReport');
$router->get('/export/reports/employees/{format}', 'ExportController@employeesReport');
$router->get('/export/reports/cashflow/{format}', 'ExportController@cashflowReport');
$router->get('/export/users/{format}', 'ExportController@users');
$router->get('/export/categories/{format}', 'ExportController@categories');

return $router;

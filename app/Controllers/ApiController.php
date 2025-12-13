<?php

namespace App\Controllers;

use Core\Controller;

class ApiController extends Controller
{
    public function __construct()
    {
        $this->db = \Core\Database::getInstance();
        header('Content-Type: application/json; charset=utf-8');
    }

    /**
     * قائمة المنتجات
     */
    public function products(): void
    {
        $search = $_GET['q'] ?? '';
        $category = $_GET['category'] ?? '';
        
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.is_active = 1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (p.name LIKE ? OR p.barcode LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if ($category) {
            $sql .= " AND p.category_id = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY p.name LIMIT 50";
        
        $products = $this->db->fetchAll($sql, $params);
        echo json_encode(['success' => true, 'data' => $products], JSON_UNESCAPED_UNICODE);
    }

    /**
     * منتج واحد
     */
    public function product(int $id): void
    {
        $product = $this->db->fetch(
            "SELECT p.*, c.name as category_name FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.id = ?", [$id]
        );
        
        if ($product) {
            echo json_encode(['success' => true, 'data' => $product], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'المنتج غير موجود'], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * قائمة العملاء
     */
    public function customers(): void
    {
        $search = $_GET['q'] ?? '';
        
        $sql = "SELECT * FROM customers WHERE 1=1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (full_name LIKE ? OR phone LIKE ? OR national_id LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $sql .= " ORDER BY full_name LIMIT 50";
        
        $customers = $this->db->fetchAll($sql, $params);
        echo json_encode(['success' => true, 'data' => $customers], JSON_UNESCAPED_UNICODE);
    }

    /**
     * عميل واحد
     */
    public function customer(int $id): void
    {
        $customer = $this->db->fetch("SELECT * FROM customers WHERE id = ?", [$id]);
        
        if ($customer) {
            echo json_encode(['success' => true, 'data' => $customer], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'العميل غير موجود'], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * قائمة الفواتير
     */
    public function invoices(): void
    {
        $invoices = $this->db->fetchAll(
            "SELECT i.*, c.full_name as customer_name 
             FROM invoices i 
             LEFT JOIN customers c ON i.customer_id = c.id 
             ORDER BY i.created_at DESC LIMIT 100"
        );
        echo json_encode(['success' => true, 'data' => $invoices], JSON_UNESCAPED_UNICODE);
    }

    /**
     * قائمة الأقساط
     */
    public function installments(): void
    {
        $installments = $this->db->fetchAll(
            "SELECT inst.*, i.invoice_number, c.full_name as customer_name 
             FROM installments inst
             JOIN invoices i ON inst.invoice_id = i.id
             LEFT JOIN customers c ON i.customer_id = c.id
             WHERE inst.status IN ('pending', 'partial')
             ORDER BY inst.due_date LIMIT 100"
        );
        echo json_encode(['success' => true, 'data' => $installments], JSON_UNESCAPED_UNICODE);
    }

    /**
     * الأقساط المستحقة
     */
    public function dueInstallments(): void
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $installments = $this->db->fetchAll(
            "SELECT inst.*, i.invoice_number, c.full_name as customer_name, c.phone as customer_phone
             FROM installments inst
             JOIN invoices i ON inst.invoice_id = i.id
             JOIN customers c ON i.customer_id = c.id
             WHERE inst.status IN ('pending', 'partial') AND inst.due_date <= ?
             ORDER BY inst.due_date",
            [$date]
        );
        echo json_encode(['success' => true, 'data' => $installments], JSON_UNESCAPED_UNICODE);
    }

    /**
     * تسجيل الدخول API
     */
    public function login(): void
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE username = ? AND is_active = 1",
            [$username]
        );
        
        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']);
            echo json_encode(['success' => true, 'data' => $user], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'بيانات الدخول غير صحيحة'], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * تسجيل دفعة
     */
    public function storePayment(): void
    {
        $installmentId = $_POST['installment_id'] ?? 0;
        $amount = $_POST['amount'] ?? 0;
        $method = $_POST['method'] ?? 'cash';
        
        if (!$installmentId || !$amount) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'بيانات ناقصة'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        $installment = $this->db->fetch("SELECT * FROM installments WHERE id = ?", [$installmentId]);
        
        if (!$installment) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'القسط غير موجود'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // تسجيل الدفعة
        $receiptNumber = 'RCP-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $this->db->insert('payments', [
            'invoice_id' => $installment['invoice_id'],
            'installment_id' => $installmentId,
            'amount' => $amount,
            'payment_method' => $method,
            'receipt_number' => $receiptNumber,
            'payment_date' => date('Y-m-d H:i:s'),
            'created_by' => $_SESSION['user_id'] ?? 1
        ]);
        
        // تحديث القسط
        $paidAmount = $installment['paid_amount'] + $amount;
        $status = $paidAmount >= $installment['amount'] ? 'paid' : 'partial';
        
        $this->db->update('installments', [
            'paid_amount' => $paidAmount,
            'status' => $status
        ], 'id = ?', [$installmentId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'تم تسجيل الدفعة بنجاح',
            'receipt_number' => $receiptNumber
        ], JSON_UNESCAPED_UNICODE);
    }
}

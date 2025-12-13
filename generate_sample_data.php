<?php
/**
 * سكريبت إضافة بيانات تجريبية كثيرة
 */

$dbPath = __DIR__ . '/database/database.sqlite';
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "بدء إضافة البيانات التجريبية...\n";

// أسماء عربية للتوليد العشوائي
$firstNames = ['محمد', 'أحمد', 'علي', 'حسن', 'إبراهيم', 'عمر', 'خالد', 'يوسف', 'عبدالله', 'مصطفى', 'كريم', 'طارق', 'سامي', 'فهد', 'ناصر', 'عادل', 'سعيد', 'رامي', 'هاني', 'وليد'];
$lastNames = ['محمود', 'عبدالرحمن', 'السيد', 'حسين', 'العلي', 'الأحمد', 'المصري', 'الشريف', 'البدر', 'النجار', 'الحداد', 'الخطيب', 'السالم', 'الفهد', 'العمري'];
$cities = ['القاهرة', 'الجيزة', 'الإسكندرية', 'أسوان', 'الأقصر', 'المنصورة', 'طنطا', 'الزقازيق', 'دمياط', 'بورسعيد'];

$productNames = [
    'تلفزيون سامسونج 55 بوصة', 'تلفزيون LG 50 بوصة', 'تلفزيون سوني 65 بوصة',
    'ثلاجة توشيبا 18 قدم', 'ثلاجة شارب 16 قدم', 'ثلاجة ال جي 20 قدم',
    'غسالة سامسونج 8 كيلو', 'غسالة ال جي 10 كيلو', 'غسالة توشيبا 7 كيلو',
    'تكييف شارب 1.5 حصان', 'تكييف كاريير 2.25 حصان', 'تكييف يونيون اير 3 حصان',
    'بوتاجاز يونيفرسال 5 شعلة', 'بوتاجاز فريش 4 شعلة', 'بوتاجاز اي كوك 5 شعلة',
    'سخان غاز اوليمبيك', 'سخان كهرباء تورنيدو', 'سخان غاز فريش',
    'ميكروويف سامسونج', 'ميكروويف ال جي', 'ميكروويف شارب',
    'خلاط براون', 'خلاط مولينكس', 'خلاط تورنيدو',
    'شاشة كمبيوتر HP 24', 'شاشة كمبيوتر Dell 27', 'لابتوب Lenovo',
    'لابتوب HP Pavilion', 'لابتوب Dell Inspiron', 'لابتوب ASUS',
    'آيفون 15 Pro', 'سامسونج S24', 'شاومي 14', 'أوبو رينو',
    'ديب فريزر كريازي', 'ديب فريزر الاسكا', 'مكنسة كهربائية باناسونيك'
];

// ========== إضافة تصنيفات ==========
echo "إضافة التصنيفات...\n";
$categories = ['تلفزيونات', 'ثلاجات', 'غسالات', 'تكييفات', 'بوتاجازات', 'سخانات', 'ميكروويف', 'أجهزة منزلية', 'موبايلات', 'لابتوبات'];

foreach ($categories as $cat) {
    $pdo->exec("INSERT OR IGNORE INTO categories (name) VALUES ('$cat')");
}
echo "تم إضافة " . count($categories) . " تصنيف\n";

// ========== إضافة منتجات ==========
echo "إضافة المنتجات...\n";
$productCount = 0;
foreach ($productNames as $name) {
    $categoryId = rand(1, 10);
    $cashPrice = rand(3000, 50000);
    $installmentPrice = $cashPrice * 1.15;
    $costPrice = $cashPrice * 0.75;
    $quantity = rand(5, 100);
    $barcode = '10' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, category_id, cash_price, installment_price, cost_price, quantity, min_quantity, barcode, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$name, $categoryId, $cashPrice, $installmentPrice, $costPrice, $quantity, 5, $barcode]);
        $productCount++;
    } catch (Exception $e) {
        // تجاهل المنتجات المكررة
    }
}
echo "تم إضافة $productCount منتج\n";

// ========== إضافة عملاء ==========
echo "إضافة العملاء...\n";
$customerCount = 0;
for ($i = 1; $i <= 200; $i++) {
    $name = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    $phone = '01' . rand(0, 2) . str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT);
    $nationalId = rand(20000000000000, 39999999999999);
    $city = $cities[array_rand($cities)];
    $address = 'شارع ' . rand(1, 100) . '، ' . $city;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO customers (full_name, phone, national_id, city, address, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([$name, $phone, $nationalId, $city, $address]);
        $customerCount++;
    } catch (Exception $e) {
        // تجاهل
    }
}
echo "تم إضافة $customerCount عميل\n";

// ========== إضافة فواتير وأقساط ==========
echo "إضافة الفواتير والأقساط...\n";
$invoiceCount = 0;
$installmentCount = 0;
$paymentCount = 0;

// فواتير نقدية (100 فاتورة)
for ($i = 1; $i <= 100; $i++) {
    $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);
    $customerId = rand(1, 200);
    $productId = rand(1, 35);
    $daysAgo = rand(0, 90);
    $createdAt = date('Y-m-d H:i:s', strtotime("-$daysAgo days"));
    
    $product = $pdo->query("SELECT * FROM products WHERE id = $productId")->fetch(PDO::FETCH_ASSOC);
    if (!$product) continue;
    
    $quantity = rand(1, 3);
    $totalAmount = $product['cash_price'] * $quantity;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO invoices (invoice_number, invoice_type, customer_id, user_id, subtotal, total_amount, paid_amount, remaining_amount, status, created_at) VALUES (?, 'cash', ?, 1, ?, ?, ?, 0, 'completed', ?)");
        $stmt->execute([$invoiceNumber, $customerId, $totalAmount, $totalAmount, $totalAmount, $createdAt]);
        $invoiceId = $pdo->lastInsertId();
        
        // إضافة بند الفاتورة
        $stmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, product_id, product_name, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$invoiceId, $productId, $product['name'], $quantity, $product['cash_price'], $totalAmount]);
        
        // إضافة دفعة
        $receiptNumber = 'RCP-' . date('Ymd', strtotime($createdAt)) . '-' . str_pad($paymentCount + 1, 4, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("INSERT INTO payments (invoice_id, amount, payment_method, receipt_number, user_id, payment_date) VALUES (?, ?, 'cash', ?, 1, ?)");
        $stmt->execute([$invoiceId, $totalAmount, $receiptNumber, $createdAt]);
        $paymentCount++;
        
        $invoiceCount++;
    } catch (Exception $e) {
        // تجاهل
    }
}

// فواتير تقسيط (150 فاتورة)
for ($i = 1; $i <= 150; $i++) {
    $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(100 + $i, 4, '0', STR_PAD_LEFT);
    $customerId = rand(1, 200);
    $productId = rand(1, 35);
    $daysAgo = rand(0, 180);
    $createdAt = date('Y-m-d H:i:s', strtotime("-$daysAgo days"));
    
    $product = $pdo->query("SELECT * FROM products WHERE id = $productId")->fetch(PDO::FETCH_ASSOC);
    if (!$product) continue;
    
    $quantity = rand(1, 2);
    $totalAmount = $product['installment_price'] * $quantity;
    $downPayment = $totalAmount * 0.2;
    $remainingAmount = $totalAmount - $downPayment;
    $months = [6, 12, 18, 24][rand(0, 3)];
    $monthlyInstallment = $remainingAmount / $months;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO invoices (invoice_number, invoice_type, customer_id, user_id, subtotal, total_amount, paid_amount, remaining_amount, down_payment, monthly_installment, installments_count, first_installment_date, status, created_at) VALUES (?, 'installment', ?, 1, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)");
        $firstInstDate = date('Y-m-d', strtotime($createdAt . ' +1 month'));
        $stmt->execute([$invoiceNumber, $customerId, $totalAmount, $totalAmount, $downPayment, $remainingAmount, $downPayment, $monthlyInstallment, $months, $firstInstDate, $createdAt]);
        $invoiceId = $pdo->lastInsertId();
        
        // إضافة بند الفاتورة
        $stmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, product_id, product_name, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$invoiceId, $productId, $product['name'], $quantity, $product['installment_price'], $totalAmount]);
        
        // إضافة الأقساط
        for ($m = 1; $m <= $months; $m++) {
            $dueDate = date('Y-m-d', strtotime($firstInstDate . ' +' . ($m - 1) . ' months'));
            $status = 'pending';
            $paidAmount = 0;
            
            // بعض الأقساط مدفوعة
            if (strtotime($dueDate) < time() && rand(0, 100) < 70) {
                $status = 'paid';
                $paidAmount = $monthlyInstallment;
            } else if (strtotime($dueDate) < time() && rand(0, 100) < 50) {
                $status = 'partial';
                $paidAmount = $monthlyInstallment * 0.5;
            }
            
            $stmt = $pdo->prepare("INSERT INTO installments (invoice_id, installment_number, amount, due_date, paid_amount, remaining_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$invoiceId, $m, $monthlyInstallment, $dueDate, $paidAmount, $monthlyInstallment - $paidAmount, $status]);
            $installmentCount++;
            
            // إضافة دفعة إذا مدفوع
            if ($paidAmount > 0) {
                $receiptNumber = 'RCP-' . date('Ymd', strtotime($dueDate)) . '-' . str_pad($paymentCount + 1, 4, '0', STR_PAD_LEFT);
                $stmt = $pdo->prepare("INSERT INTO payments (invoice_id, installment_id, amount, payment_method, receipt_number, user_id, payment_date) VALUES (?, ?, ?, 'cash', ?, 1, ?)");
                $instId = $pdo->lastInsertId();
                $stmt->execute([$invoiceId, $instId, $paidAmount, $receiptNumber, $dueDate]);
                $paymentCount++;
            }
        }
        
        $invoiceCount++;
    } catch (Exception $e) {
        // تجاهل
    }
}

echo "تم إضافة $invoiceCount فاتورة\n";
echo "تم إضافة $installmentCount قسط\n";
echo "تم إضافة $paymentCount دفعة\n";

echo "\n✅ اكتمل إضافة البيانات التجريبية بنجاح!\n";
echo "الملخص:\n";
echo "- " . count($categories) . " تصنيف\n";
echo "- $productCount منتج\n";
echo "- $customerCount عميل\n";
echo "- $invoiceCount فاتورة\n";
echo "- $installmentCount قسط\n";
echo "- $paymentCount دفعة\n";

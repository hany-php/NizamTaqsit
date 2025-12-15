<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة <?= $invoice['invoice_number'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Cairo', sans-serif; background: #fff; color: #333; padding: 20px; }
        .invoice { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #1a237e; }
        .header h1 { color: #1a237e; font-size: 24px; margin-bottom: 5px; }
        .header p { color: #666; }
        .invoice-info { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .invoice-info div { flex: 1; }
        .invoice-info h3 { font-size: 14px; color: #999; margin-bottom: 10px; }
        .invoice-info p { margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { padding: 12px; text-align: right; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; font-weight: 600; }
        tfoot td { font-weight: 600; }
        tfoot tr:last-child { font-size: 18px; background: #1a237e; color: white; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px; }
        .badge { display: inline-block; padding: 5px 15px; border-radius: 15px; font-size: 12px; }
        .badge-cash { background: #e8f5e9; color: #2e7d32; }
        .badge-installment { background: #e3f2fd; color: #1565c0; }
        @media print { body { padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="invoice">
        <div class="header">
            <?php if (!empty($settings['store_logo'])): ?>
                <img src="<?= asset('images/' . $settings['store_logo']) ?>" alt="الشعار" style="max-width: auto; max-height: 195px; margin-bottom: 10px;">
            <?php else: ?>
                <img src="<?= asset('images/logo.png') ?>" alt="الشعار" style="max-width: auto; max-height: 195px; margin-bottom: 10px;">
            <?php endif; ?>
            <!--<h1><?= $settings['store_name'] ?? 'نظام تقسيط' ?></h1>
            <p><?= $settings['store_address'] ?? '' ?></p>
            <p>هاتف: <?= $settings['store_phone'] ?? '' ?></p>-->
        </div>
        
        <div class="invoice-info">
            <div>
                <h3>بيانات الفاتورة</h3>
                <p><strong>رقم الفاتورة:</strong> <?= $invoice['invoice_number'] ?></p>
                <p><strong>التاريخ:</strong> <?= formatDateTime($invoice['created_at']) ?></p>
                <p><strong>النوع:</strong> <span class="badge badge-<?= $invoice['invoice_type'] ?>"><?= invoiceType($invoice['invoice_type']) ?></span></p>
            </div>
            <div>
                <h3>بيانات العميل</h3>
                <?php if ($invoice['customer_name']): ?>
                <p><strong>الاسم:</strong> <?= $invoice['customer_name'] ?></p>
                <p><strong>الهاتف:</strong> <?= $invoice['customer_phone'] ?></p>
                <?php else: ?>
                <p>زبون نقدي</p>
                <?php endif; ?>
            </div>
        </div>
        
        <table>
            <thead>
                <tr><th>#</th><th>المنتج</th><th>الكمية</th><th>السعر</th><th>الإجمالي</th></tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($invoice['items'] as $item): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= $item['product_name'] ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= formatMoney($item['unit_price']) ?></td>
                    <td><?= formatMoney($item['total_price']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><td colspan="4">المجموع الفرعي:</td><td><?= formatMoney($invoice['subtotal']) ?></td></tr>
                <?php if ($invoice['discount_amount'] > 0): ?>
                <tr><td colspan="4">الخصم:</td><td>-<?= formatMoney($invoice['discount_amount']) ?></td></tr>
                <?php endif; ?>
                <tr><td colspan="4">الإجمالي:</td><td><?= formatMoney($invoice['total_amount']) ?></td></tr>
            </tfoot>
        </table>
        
        <?php if ($invoice['invoice_type'] === 'installment'): ?>
        <div style="background:#f5f5f5;padding:15px;border-radius:8px;margin-bottom:20px">
            <p><strong>الدفعة المقدمة:</strong> <?= formatMoney($invoice['down_payment']) ?></p>
            <p><strong>القسط الشهري:</strong> <?= formatMoney($invoice['monthly_installment']) ?></p>
            <p><strong>عدد الأقساط:</strong> <?= $invoice['installments_count'] ?> شهر</p>
        </div>
        <?php endif; ?>
        
        <div class="footer">
            <p><?= $settings['invoice_footer'] ?? 'شكراً لتعاملكم معنا' ?></p>
            <p style="margin-top:10px">تم الطباعة: <?= date('Y-m-d H:i') ?></p>
        </div>
    </div>
    
    <div class="no-print" style="text-align:center;margin-top:30px">
        <button onclick="window.print()" style="padding:10px 30px;font-size:16px;cursor:pointer">طباعة</button>
    </div>
</body>
</html>

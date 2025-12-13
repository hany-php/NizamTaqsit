<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إيصال دفع - <?= $payment['receipt_number'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Cairo', sans-serif; background: #fff; color: #333; padding: 20px; }
        .receipt { max-width: 400px; margin: 0 auto; border: 1px solid #ddd; padding: 25px; }
        .header { text-align: center; border-bottom: 2px dashed #ddd; padding-bottom: 15px; margin-bottom: 15px; }
        .header h1 { font-size: 18px; margin-bottom: 5px; }
        .header p { font-size: 12px; color: #666; }
        .receipt-number { text-align: center; background: #f5f5f5; padding: 10px; border-radius: 8px; margin-bottom: 15px; }
        .receipt-number strong { font-size: 18px; color: #1a237e; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dotted #ddd; }
        .info-row:last-of-type { border: none; }
        .amount-box { text-align: center; background: #e8f5e9; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .amount-box span { display: block; font-size: 12px; color: #666; }
        .amount-box strong { font-size: 28px; color: #2e7d32; }
        .footer { text-align: center; margin-top: 20px; padding-top: 15px; border-top: 2px dashed #ddd; }
        .footer p { font-size: 11px; color: #666; margin-bottom: 5px; }
        @media print { body { padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1><?= $settings['store_name'] ?? 'نظام تقسيط' ?></h1>
            <p><?= $settings['store_address'] ?? '' ?></p>
            <p>هاتف: <?= $settings['store_phone'] ?? '' ?></p>
        </div>
        
        <div class="receipt-number">
            <span>رقم الإيصال</span>
            <strong><?= $payment['receipt_number'] ?></strong>
        </div>
        
        <div class="info-row"><span>التاريخ:</span><span><?= formatDateTime($payment['payment_date']) ?></span></div>
        <div class="info-row"><span>رقم الفاتورة:</span><span><?= $payment['invoice_number'] ?></span></div>
        <?php if ($payment['customer_name']): ?>
        <div class="info-row"><span>العميل:</span><span><?= $payment['customer_name'] ?></span></div>
        <?php endif; ?>
        <div class="info-row"><span>طريقة الدفع:</span><span><?= paymentMethod($payment['payment_method']) ?></span></div>
        <?php if ($payment['installment_number']): ?>
        <div class="info-row"><span>القسط:</span><span>القسط رقم <?= $payment['installment_number'] ?></span></div>
        <?php endif; ?>
        
        <div class="amount-box">
            <span>المبلغ المدفوع</span>
            <strong><?= formatMoney($payment['amount']) ?></strong>
        </div>
        
        <?php if ($payment['notes']): ?>
        <p style="font-size:12px;color:#666;margin-bottom:15px">ملاحظات: <?= $payment['notes'] ?></p>
        <?php endif; ?>
        
        <div class="footer">
            <p>شكراً لتعاملكم معنا</p>
            <p>تم الطباعة: <?= date('Y-m-d H:i') ?></p>
        </div>
    </div>
    
    <div class="no-print" style="text-align:center;margin-top:30px">
        <button onclick="window.print()" style="padding:10px 30px;font-size:16px;cursor:pointer">طباعة</button>
    </div>
</body>
</html>

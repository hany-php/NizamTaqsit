<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>عقد تقسيط - <?= $invoice['invoice_number'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Cairo', sans-serif; background: #fff; color: #333; padding: 30px; font-size: 14px; line-height: 1.8; }
        .contract { max-width: 800px; margin: 0 auto; border: 2px solid #1a237e; padding: 30px; }
        .header { text-align: center; border-bottom: 2px solid #1a237e; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { color: #1a237e; font-size: 24px; margin-bottom: 5px; }
        .header h2 { font-size: 18px; color: #666; }
        .header p { color: #666; font-size: 12px; }
        .contract-info { display: flex; justify-content: space-between; margin-bottom: 25px; padding: 15px; background: #f5f5f5; border-radius: 8px; }
        .contract-info div { text-align: center; }
        .contract-info strong { display: block; font-size: 16px; color: #1a237e; }
        .section { margin-bottom: 25px; }
        .section-title { background: #1a237e; color: white; padding: 8px 15px; border-radius: 5px; margin-bottom: 15px; }
        .party-info { padding: 15px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 15px; }
        .party-info h4 { color: #1a237e; margin-bottom: 10px; }
        .party-info p { margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: right; }
        th { background: #f5f5f5; }
        .summary-box { background: #e3f2fd; padding: 20px; border-radius: 8px; }
        .summary-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(0,0,0,0.1); }
        .summary-row:last-child { border: none; font-size: 18px; font-weight: 700; color: #1a237e; }
        .installments-table th { background: #1a237e; color: white; }
        .terms { padding: 15px; background: #fff3e0; border-radius: 8px; font-size: 13px; }
        .terms h4 { margin-bottom: 10px; color: #e65100; }
        .terms ol { padding-right: 20px; }
        .terms li { margin-bottom: 8px; }
        .signatures { display: flex; justify-content: space-between; margin-top: 40px; padding-top: 20px; }
        .signature { text-align: center; width: 200px; }
        .signature-line { border-top: 1px solid #333; margin-top: 60px; padding-top: 10px; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
        @media print { body { padding: 0; } .no-print { display: none; } .contract { border: none; } }
    </style>
</head>
<body>
    <div class="contract">
        <div class="header">
            <h1><?= $settings['store_name'] ?? 'نظام تقسيط' ?></h1>
            <h2>عقد بيع بالتقسيط</h2>
            <p><?= $settings['store_address'] ?? '' ?> | هاتف: <?= $settings['store_phone'] ?? '' ?></p>
        </div>
        
        <div class="contract-info">
            <div><span>رقم العقد</span><strong><?= $invoice['invoice_number'] ?></strong></div>
            <div><span>تاريخ العقد</span><strong><?= formatDate($invoice['created_at']) ?></strong></div>
            <div><span>تاريخ أول قسط</span><strong><?= formatDate($invoice['first_installment_date']) ?></strong></div>
        </div>
        
        <div class="section">
            <div class="party-info">
                <h4>الطرف الأول (البائع)</h4>
                <p><strong><?= $settings['store_name'] ?? 'نظام تقسيط' ?></strong></p>
                <p>العنوان: <?= $settings['store_address'] ?? '' ?></p>
                <p>الهاتف: <?= $settings['store_phone'] ?? '' ?></p>
            </div>
            <div class="party-info">
                <h4>الطرف الثاني (المشتري)</h4>
                <p><strong><?= $invoice['customer_name'] ?></strong></p>
                <p>رقم الهوية: <?= $invoice['customer_id'] ?></p>
                <p>الهاتف: <?= $invoice['customer_phone'] ?></p>
            </div>
        </div>
        
        <div class="section">
            <h3 class="section-title">البضاعة المباعة</h3>
            <table>
                <thead><tr><th>#</th><th>الصنف</th><th>الكمية</th><th>السعر</th><th>الإجمالي</th></tr></thead>
                <tbody>
                    <?php $i = 1; foreach ($invoice['items'] as $item): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= $item['product_name'] ?><?php if ($item['serial_number']): ?><br><small>السيريال: <?= $item['serial_number'] ?></small><?php endif; ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= formatMoney($item['unit_price']) ?></td>
                        <td><?= formatMoney($item['total_price']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <h3 class="section-title">ملخص الأسعار</h3>
            <div class="summary-box">
                <div class="summary-row"><span>السعر النقدي:</span><span><?= formatMoney($invoice['subtotal']) ?></span></div>
                <div class="summary-row"><span>سعر التقسيط:</span><span><?= formatMoney($invoice['total_amount']) ?></span></div>
                <div class="summary-row"><span>الدفعة المقدمة:</span><span><?= formatMoney($invoice['down_payment']) ?></span></div>
                <div class="summary-row"><span>المبلغ المتبقي:</span><span><?= formatMoney($invoice['remaining_amount']) ?></span></div>
                <div class="summary-row"><span>عدد الأقساط:</span><span><?= $invoice['installments_count'] ?> شهر</span></div>
                <div class="summary-row"><span>القسط الشهري:</span><span><?= formatMoney($invoice['monthly_installment']) ?></span></div>
            </div>
        </div>
        
        <div class="section">
            <h3 class="section-title">جدول الأقساط</h3>
            <table class="installments-table">
                <thead><tr><th>#</th><th>تاريخ الاستحقاق</th><th>المبلغ</th><th>الحالة</th></tr></thead>
                <tbody>
                    <?php foreach ($invoice['installments'] as $inst): ?>
                    <tr>
                        <td>القسط <?= $inst['installment_number'] ?></td>
                        <td><?= formatDate($inst['due_date']) ?></td>
                        <td><?= formatMoney($inst['amount']) ?></td>
                        <td><?= installmentStatus($inst['status']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="section terms">
            <h4>الشروط والأحكام</h4>
            <ol>
                <li>يلتزم الطرف الثاني بسداد الأقساط في مواعيدها المحددة.</li>
                <li>في حالة التأخر عن سداد قسطين متتاليين يحق للطرف الأول استرداد البضاعة.</li>
                <li>تبقى ملكية البضاعة للطرف الأول حتى سداد كامل المبلغ.</li>
                <li>لا يحق للطرف الثاني بيع أو رهن البضاعة قبل السداد الكامل.</li>
                <li>الضامن ملتزم بالسداد في حالة تعثر الطرف الثاني.</li>
            </ol>
        </div>
        
        <div class="signatures">
            <div class="signature"><div class="signature-line">توقيع البائع</div></div>
            <div class="signature"><div class="signature-line">توقيع المشتري</div></div>
            <div class="signature"><div class="signature-line">توقيع الضامن</div></div>
        </div>
        
        <div class="footer">
            <p>تم إنشاء هذا العقد بتاريخ <?= formatDateTime($invoice['created_at']) ?></p>
        </div>
    </div>
    
    <div class="no-print" style="text-align:center;margin-top:30px">
        <button onclick="window.print()" style="padding:12px 40px;font-size:16px;cursor:pointer;background:#1a237e;color:white;border:none;border-radius:8px">طباعة العقد</button>
    </div>
</body>
</html>

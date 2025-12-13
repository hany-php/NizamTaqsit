<div class="page-header">
    <h2><span class="material-icons-round">receipt</span> فاتورة رقم <?= $invoice['invoice_number'] ?></h2>
    <div class="header-actions">
        <a href="<?= url('/invoices/' . $invoice['id'] . '/print') ?>" class="btn btn-primary" target="_blank"><span class="material-icons-round">print</span> طباعة</a>
        <?php if ($invoice['invoice_type'] === 'installment'): ?>
        <a href="<?= url('/invoices/' . $invoice['id'] . '/contract') ?>" class="btn btn-success" target="_blank"><span class="material-icons-round">description</span> العقد</a>
        <?php endif; ?>
        <a href="<?= url('/invoices') ?>" class="btn btn-secondary"><span class="material-icons-round">arrow_forward</span> رجوع</a>
    </div>
</div>

<div class="invoice-details">
    <div class="invoice-main">
        <div class="card">
            <div class="card-header">
                <h3>تفاصيل الفاتورة</h3>
                <span class="badge badge-<?= $invoice['status'] === 'completed' ? 'success' : ($invoice['status'] === 'active' ? 'warning' : 'secondary') ?> badge-lg"><?= invoiceStatus($invoice['status']) ?></span>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div><strong>النوع:</strong> <?= invoiceType($invoice['invoice_type']) ?></div>
                    <div><strong>التاريخ:</strong> <?= formatDateTime($invoice['created_at']) ?></div>
                    <div><strong>البائع:</strong> <?= $invoice['user_name'] ?></div>
                    <?php if ($invoice['invoice_type'] === 'installment'): ?>
                    <div><strong>خطة التقسيط:</strong> <?= $invoice['plan_name'] ?></div>
                    <div><strong>عدد الأقساط:</strong> <?= $invoice['installments_count'] ?> شهر</div>
                    <div><strong>القسط الشهري:</strong> <?= formatMoney($invoice['monthly_installment']) ?></div>
                    <?php endif; ?>
                </div>
                
                <h4 class="section-title">المنتجات</h4>
                <table class="table">
                    <thead>
                        <tr><th>المنتج</th><th>الكمية</th><th>السعر</th><th>الإجمالي</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoice['items'] as $item): ?>
                        <tr>
                            <td><?= $item['product_name'] ?><?php if ($item['serial_number']): ?><br><small class="text-muted">السيريال: <?= $item['serial_number'] ?></small><?php endif; ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= formatMoney($item['unit_price']) ?></td>
                            <td><strong><?= formatMoney($item['total_price']) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3" class="text-left">المجموع الفرعي:</td><td><?= formatMoney($invoice['subtotal']) ?></td></tr>
                        <?php if ($invoice['discount_amount'] > 0): ?>
                        <tr><td colspan="3" class="text-left">الخصم:</td><td class="text-danger">-<?= formatMoney($invoice['discount_amount']) ?></td></tr>
                        <?php endif; ?>
                        <tr class="total-row"><td colspan="3" class="text-left">الإجمالي:</td><td><strong><?= formatMoney($invoice['total_amount']) ?></strong></td></tr>
                        <?php if ($invoice['invoice_type'] === 'installment'): ?>
                        <tr><td colspan="3" class="text-left">الدفعة المقدمة:</td><td><?= formatMoney($invoice['down_payment']) ?></td></tr>
                        <tr><td colspan="3" class="text-left">المدفوع:</td><td class="text-success"><?= formatMoney($invoice['paid_amount']) ?></td></tr>
                        <tr><td colspan="3" class="text-left">المتبقي:</td><td class="text-danger"><?= formatMoney($invoice['remaining_amount']) ?></td></tr>
                        <?php endif; ?>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <?php if ($invoice['invoice_type'] === 'installment' && !empty($invoice['installments'])): ?>
        <div class="card">
            <div class="card-header"><h3><span class="material-icons-round">event</span> جدول الأقساط</h3></div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>#</th><th>تاريخ الاستحقاق</th><th>المبلغ</th><th>المدفوع</th><th>المتبقي</th><th>الحالة</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($invoice['installments'] as $inst): ?>
                        <tr>
                            <td>القسط <?= $inst['installment_number'] ?></td>
                            <td><?= formatDate($inst['due_date']) ?></td>
                            <td><?= formatMoney($inst['amount']) ?></td>
                            <td class="text-success"><?= formatMoney($inst['paid_amount']) ?></td>
                            <td class="text-danger"><?= formatMoney($inst['remaining_amount']) ?></td>
                            <td><span class="badge badge-<?= $inst['status'] === 'paid' ? 'success' : ($inst['status'] === 'overdue' ? 'danger' : 'warning') ?>"><?= installmentStatus($inst['status']) ?></span></td>
                            <td>
                                <?php if ($inst['status'] !== 'paid'): ?>
                                <button class="btn btn-sm btn-primary" onclick="payInstallment(<?= $inst['id'] ?>, <?= $inst['remaining_amount'] ?>)">تحصيل</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="invoice-sidebar">
        <?php if ($invoice['customer_name']): ?>
        <div class="card">
            <div class="card-header"><h3><span class="material-icons-round">person</span> العميل</h3></div>
            <div class="card-body">
                <p><strong><?= $invoice['customer_name'] ?></strong></p>
                <p><a href="tel:<?= $invoice['customer_phone'] ?>"><?= $invoice['customer_phone'] ?></a></p>
                <a href="<?= url('/customers/' . $invoice['customer_id']) ?>" class="btn btn-outline btn-block">عرض ملف العميل</a>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($invoice['notes']): ?>
        <div class="card">
            <div class="card-header"><h3>ملاحظات</h3></div>
            <div class="card-body"><p><?= nl2br($invoice['notes']) ?></p></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal" id="payModal">
    <div class="modal-content">
        <div class="modal-header"><h3>تحصيل قسط</h3><button class="close-btn" onclick="closeModal('payModal')">&times;</button></div>
        <div class="modal-body">
            <form id="payForm">
                <input type="hidden" id="payInstallmentId">
                <div class="form-group"><label>المبلغ المستحق</label><input type="text" id="dueAmount" readonly class="form-control"></div>
                <div class="form-group"><label>المبلغ المدفوع</label><input type="number" id="payAmount" step="0.01" class="form-control" required></div>
                <button type="submit" class="btn btn-primary btn-block">تأكيد الدفع</button>
            </form>
        </div>
    </div>
</div>

<style>
.invoice-details { display: grid; grid-template-columns: 1fr 300px; gap: 25px; }
.info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px; }
.section-title { margin: 25px 0 15px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color); }
.total-row { background: var(--bg-main); font-size: 16px; }
.badge-lg { font-size: 14px; padding: 8px 16px; }
@media (max-width: 1024px) { .invoice-details { grid-template-columns: 1fr; } }
</style>

<script>
function payInstallment(id, amount) {
    document.getElementById('payInstallmentId').value = id;
    document.getElementById('dueAmount').value = amount.toFixed(2);
    document.getElementById('payAmount').value = amount.toFixed(2);
    document.getElementById('payModal').classList.add('show');
}
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
document.getElementById('payForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('payInstallmentId').value;
    const amount = document.getElementById('payAmount').value;
    const formData = new FormData();
    formData.append('amount', amount);
    const response = await fetch('<?= url('/installments/') ?>' + id + '/pay', { method: 'POST', body: formData });
    const result = await response.json();
    if (result.success) { alert('تم تسجيل الدفعة'); location.reload(); } else { alert(result.message); }
});
</script>

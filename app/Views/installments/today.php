<?php 
$exportUrl = url('/export/installments/today');
$todayTotal = array_sum(array_column($installments, 'remaining_amount'));
?>
<div class="page-header">
    <h2><span class="material-icons-round">today</span> أقساط اليوم - <?= formatDate(date('Y-m-d')) ?></h2>
    <div class="header-actions">
        <?php include dirname(__DIR__) . '/partials/export_buttons.php'; ?>
        <a href="<?= url('/installments') ?>" class="btn btn-secondary"><span class="material-icons-round">arrow_forward</span> رجوع</a>
    </div>
</div>

<?php if (empty($installments)): ?>
<div class="card">
    <div class="card-body">
        <p class="empty-message success-message"><span class="material-icons-round">check_circle</span> لا توجد أقساط مستحقة اليوم</p>
    </div>
</div>
<?php else: ?>

<div class="summary-bar">
    <div class="summary-item">
        <span class="material-icons-round" style="color:var(--primary)">event</span>
        <div><strong><?= count($installments) ?></strong><span>قسط مستحق</span></div>
    </div>
    <div class="summary-item">
        <span class="material-icons-round" style="color:var(--success)">payments</span>
        <div><strong><?= formatMoney($todayTotal) ?></strong><span>إجمالي المستحق</span></div>
    </div>
</div>
<div class="installments-grid">
    <?php foreach ($installments as $inst): ?>
    <div class="installment-card">
        <div class="card-top">
            <div class="customer-avatar"><span class="material-icons-round">account_circle</span></div>
            <div class="customer-info">
                <h4><?= $inst['customer_name'] ?></h4>
                <a href="tel:<?= $inst['customer_phone'] ?>"><?= $inst['customer_phone'] ?></a>
            </div>
            <div class="amount">
                <strong><?= formatMoney($inst['remaining_amount']) ?></strong>
                <small>القسط <?= $inst['installment_number'] ?></small>
            </div>
        </div>
        <div class="card-bottom">
            <span class="invoice-num">فاتورة: <?= $inst['invoice_number'] ?></span>
            <div class="actions">
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $inst['customer_phone']) ?>" class="btn btn-sm btn-success" target="_blank" title="واتساب"><span class="material-icons-round">chat</span></a>
                <button class="btn btn-sm btn-primary" onclick="payInstallment(<?= $inst['id'] ?>, <?= $inst['remaining_amount'] ?>, '<?= $inst['customer_name'] ?>')">تحصيل</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="modal" id="payModal">
    <div class="modal-content">
        <div class="modal-header"><h3>تحصيل قسط</h3><button class="close-btn" onclick="closeModal('payModal')">&times;</button></div>
        <div class="modal-body">
            <p id="customerName" style="margin-bottom:15px;font-weight:600"></p>
            <form id="payForm">
                <input type="hidden" id="payInstallmentId">
                <div class="form-group"><label>المبلغ المستحق</label><input type="text" id="dueAmount" readonly class="form-control"></div>
                <div class="form-group"><label>المبلغ المدفوع</label><input type="number" id="payAmount" step="0.01" class="form-control" required></div>
                <button type="submit" class="btn btn-primary btn-block"><span class="material-icons-round">check</span> تأكيد الدفع</button>
            </form>
        </div>
    </div>
</div>

<style>
.summary-bar { display: flex; gap: 20px; margin-bottom: 25px; }
.summary-item { background: var(--bg-card); border-radius: var(--radius); padding: 20px 25px; display: flex; align-items: center; gap: 15px; box-shadow: var(--shadow); }
.summary-item .material-icons-round { font-size: 36px; }
.summary-item strong { display: block; font-size: 24px; }
.summary-item span { font-size: 13px; color: var(--text-muted); }
.installments-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; }
.installment-card { background: var(--bg-card); border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow); }
.card-top { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color); }
.customer-avatar { width: 50px; height: 50px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.customer-avatar .material-icons-round { font-size: 30px; color: white; }
.customer-info { flex: 1; }
.customer-info h4 { margin-bottom: 3px; }
.customer-info a { color: var(--text-muted); font-size: 14px; }
.amount { text-align: left; }
.amount strong { display: block; font-size: 20px; color: var(--primary); }
.amount small { color: var(--text-muted); }
.card-bottom { display: flex; justify-content: space-between; align-items: center; }
.invoice-num { font-size: 13px; color: var(--text-muted); }
.actions { display: flex; gap: 8px; }
</style>

<script>
function payInstallment(id, amount, name) {
    document.getElementById('payInstallmentId').value = id;
    document.getElementById('customerName').textContent = name;
    document.getElementById('dueAmount').value = amount.toFixed(2) + ' <?= $settings['currency'] ?? 'ج.م' ?>';
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
    if (result.success) { alert('تم تسجيل الدفعة بنجاح\nرقم الإيصال: ' + result.receipt_number); location.reload(); } else { alert(result.message); }
});
</script>

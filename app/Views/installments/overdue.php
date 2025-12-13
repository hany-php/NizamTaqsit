<?php $exportUrl = url('/export/installments/overdue'); ?>
<div class="page-header">
    <h2><span class="material-icons-round">warning</span> الأقساط المتأخرة</h2>
    <div class="header-actions">
        <?php include dirname(__DIR__) . '/partials/export_buttons.php'; ?>
        <a href="<?= url('/installments') ?>" class="btn btn-secondary"><span class="material-icons-round">arrow_forward</span> رجوع</a>
    </div>
</div>

<?php if (empty($installments)): ?>
<div class="card">
    <div class="card-body">
        <p class="empty-message success-message"><span class="material-icons-round">check_circle</span> لا توجد أقساط متأخرة - ممتاز!</p>
    </div>
</div>
<?php else: ?>

<div class="summary-bar">
    <div class="summary-item">
        <span class="material-icons-round">error</span>
        <div><strong><?= $pagination->getTotalItems() ?></strong><span>قسط متأخر</span></div>
    </div>
    <div class="summary-item">
        <span class="material-icons-round">payments</span>
        <div><strong><?= formatMoney($total) ?></strong><span>إجمالي المتأخرات</span></div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php $selectionType = 'installments/overdue'; $allowDelete = false; include dirname(__DIR__) . '/partials/table_selection.php'; ?>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th class="select-checkbox"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
                        <th>العميل</th>
                        <th>الهاتف</th>
                        <th>رقم الفاتورة</th>
                        <th>القسط</th>
                        <th>المبلغ</th>
                        <th>تاريخ الاستحقاق</th>
                        <th>أيام التأخير</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($installments as $inst): ?>
                    <tr class="overdue-row">
                        <td class="select-checkbox"><input type="checkbox" class="row-checkbox" data-id="<?= $inst['id'] ?>" onclick="toggleRowSelect(this)"></td>
                        <td><strong><?= $inst['customer_name'] ?></strong></td>
                        <td><a href="tel:<?= $inst['customer_phone'] ?>"><?= $inst['customer_phone'] ?></a></td>
                        <td><a href="<?= url('/invoices/' . $inst['invoice_id']) ?>"><?= $inst['invoice_number'] ?></a></td>
                        <td>القسط <?= $inst['installment_number'] ?></td>
                        <td><strong class="text-danger"><?= formatMoney($inst['remaining_amount']) ?></strong></td>
                        <td><?= formatDate($inst['due_date']) ?></td>
                        <td><span class="badge badge-danger"><?= (int)$inst['days_overdue'] ?> يوم</span></td>
                        <td>
                            <div class="actions">
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $inst['customer_phone']) ?>?text=<?= urlencode('السلام عليكم، نذكركم بقسط متأخر بمبلغ ' . formatMoney($inst['remaining_amount'])) ?>" class="btn btn-sm btn-success" target="_blank" title="تذكير واتساب"><span class="material-icons-round">chat</span></a>
                                <button class="btn btn-sm btn-primary" onclick="payInstallment(<?= $inst['id'] ?>, <?= $inst['remaining_amount'] ?>, '<?= $inst['customer_name'] ?>')">تحصيل</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php include dirname(__DIR__) . '/partials/pagination.php'; ?>
    </div>
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
                <button type="submit" class="btn btn-primary btn-block">تأكيد الدفع</button>
            </form>
        </div>
    </div>
</div>

<style>
.summary-bar { display: flex; gap: 20px; margin-bottom: 25px; }
.summary-item { background: var(--bg-card); border-radius: var(--radius); padding: 20px 25px; display: flex; align-items: center; gap: 15px; box-shadow: var(--shadow); }
.summary-item .material-icons-round { font-size: 36px; color: var(--danger); }
.summary-item strong { display: block; font-size: 24px; }
.summary-item span { font-size: 13px; color: var(--text-muted); }
.overdue-row { background: rgba(229, 57, 53, 0.05); }
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
    if (result.success) { alert('تم تسجيل الدفعة'); location.reload(); } else { alert(result.message); }
});
</script>

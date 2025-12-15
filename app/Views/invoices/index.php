<?php $exportUrl = url('/export/invoices'); ?>
<div class="page-header">
    <h2><span class="material-icons-round">receipt_long</span> الفواتير</h2>
    <div class="header-actions">
        <?php include dirname(__DIR__) . '/partials/export_buttons.php'; ?>
        <a href="<?= url('/pos') ?>" class="btn btn-success"><span class="material-icons-round">add</span> فاتورة نقدي</a>
        <a href="<?= url('/pos/installment') ?>" class="btn btn-primary"><span class="material-icons-round">credit_score</span> فاتورة تقسيط</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="filters">
            <select name="type" class="form-control" onchange="this.form.submit()">
                <option value="">كل الأنواع</option>
                <option value="cash" <?= $type === 'cash' ? 'selected' : '' ?>>نقدي</option>
                <option value="installment" <?= $type === 'installment' ? 'selected' : '' ?>>تقسيط</option>
            </select>
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="">كل الحالات</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>نشط</option>
                <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>مكتمل</option>
                <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>ملغي</option>
            </select>
            <input type="text" name="q" class="form-control" placeholder="بحث..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary"><span class="material-icons-round">search</span></button>
            <?php if ($search || $type || $status): ?>
            <a href="<?= url('/invoices') ?>" class="btn btn-secondary"><span class="material-icons-round">clear</span></a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body">
        <?php $selectionType = 'invoices'; $allowDelete = false; include dirname(__DIR__) . '/partials/table_selection.php'; ?>
        
        <div class="table-responsive">
            <table class="table" id="invoicesTable">
                <thead>
                    <tr>
                        <th class="select-checkbox"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
                        <th>رقم الفاتورة</th>
                        <th>العميل</th>
                        <th>النوع</th>
                        <th>الإجمالي</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td class="select-checkbox"><input type="checkbox" class="row-checkbox" data-id="<?= $inv['id'] ?>" onclick="toggleRowSelect(this)"></td>
                        <td><a href="<?= url('/invoices/' . $inv['id']) ?>"><?= $inv['invoice_number'] ?></a></td>
                        <td><?= $inv['customer_name'] ?? 'زبون نقدي' ?></td>
                        <td><span class="badge badge-<?= $inv['invoice_type'] === 'cash' ? 'success' : 'primary' ?>"><?= invoiceType($inv['invoice_type']) ?></span></td>
                        <td><strong><?= formatMoney($inv['total_amount']) ?></strong></td>
                        <td class="text-success"><?= formatMoney($inv['paid_amount']) ?></td>
                        <td class="<?= $inv['remaining_amount'] > 0 ? 'text-danger' : '' ?>"><?= formatMoney($inv['remaining_amount']) ?></td>
                        <td><span class="badge badge-<?= $inv['status'] === 'completed' ? 'success' : ($inv['status'] === 'active' ? 'warning' : 'secondary') ?>"><?= invoiceStatus($inv['status']) ?></span></td>
                        <td><?= formatDate($inv['created_at']) ?></td>
                        <td>
                            <div class="actions">
                                <a href="<?= url('/invoices/' . $inv['id']) ?>" class="btn btn-sm btn-secondary" title="عرض"><span class="material-icons-round">visibility</span></a>
                                <?php if ($inv['status'] !== 'cancelled'): ?>
                                <a href="<?= url('/invoices/' . $inv['id'] . '/edit') ?>" class="btn btn-sm btn-warning" title="تعديل"><span class="material-icons-round">edit</span></a>
                                <?php endif; ?>
                                <a href="<?= url('/invoices/' . $inv['id'] . '/print') ?>" class="btn btn-sm btn-primary" title="طباعة" target="_blank"><span class="material-icons-round">print</span></a>
                                <?php if ($inv['invoice_type'] === 'installment'): ?>
                                <a href="<?= url('/invoices/' . $inv['id'] . '/contract') ?>" class="btn btn-sm btn-success" title="العقد" target="_blank"><span class="material-icons-round">description</span></a>
                                <?php endif; ?>
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

<style>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
.header-actions { display: flex; gap: 10px; }
.filters { display: flex; gap: 15px; }
.filters select, .filters input { width: 180px; }
.actions { display: flex; gap: 5px; }
.actions .btn { padding: 5px 8px; }
</style>

<script>
function filterInvoices() {
    const type = document.getElementById('typeFilter').value;
    const status = document.getElementById('statusFilter').value;
    const search = document.getElementById('searchInput').value.toLowerCase();
    
    document.querySelectorAll('#invoicesTable tbody tr').forEach(row => {
        const matchType = !type || row.dataset.type === type;
        const matchStatus = !status || row.dataset.status === status;
        const matchSearch = !search || row.textContent.toLowerCase().includes(search);
        row.style.display = matchType && matchStatus && matchSearch ? '' : 'none';
    });
}
</script>

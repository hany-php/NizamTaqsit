<?php $exportUrl = url('/export/installments'); ?>
<div class="page-header">
    <h2><span class="material-icons-round">payments</span> إدارة الأقساط</h2>
    <div class="header-actions">
        <?php include dirname(__DIR__) . '/partials/export_buttons.php'; ?>
        <a href="<?= url('/installments/today') ?>" class="btn btn-primary"><span class="material-icons-round">today</span> أقساط اليوم</a>
        <a href="<?= url('/installments/overdue') ?>" class="btn btn-danger"><span class="material-icons-round">warning</span> المتأخرات</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>عقود التقسيط</h3>
        <form method="GET" class="filters">
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="">كل الحالات</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>نشط</option>
                <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>مكتمل</option>
            </select>
            <input type="text" name="q" class="form-control" style="width:250px" placeholder="بحث..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary"><span class="material-icons-round">search</span></button>
            <?php if ($search || $status): ?>
            <a href="<?= url('/installments') ?>" class="btn btn-secondary"><span class="material-icons-round">clear</span></a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body">
        <?php $selectionType = 'installments'; $allowDelete = false; include dirname(__DIR__) . '/partials/table_selection.php'; ?>
        
        <div class="table-responsive">
            <table class="table" id="dataTable">
                <thead>
                    <tr>
                        <th class="select-checkbox"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
                        <th>رقم الفاتورة</th>
                        <th>العميل</th>
                        <th>الإجمالي</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                        <th>القسط الشهري</th>
                        <th>الأقساط</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td class="select-checkbox"><input type="checkbox" class="row-checkbox" data-id="<?= $inv['id'] ?>" onclick="toggleRowSelect(this)"></td>
                        <td><a href="<?= url('/invoices/' . $inv['id']) ?>"><?= $inv['invoice_number'] ?></a></td>
                        <td><strong><?= $inv['customer_name'] ?></strong></td>
                        <td><?= formatMoney($inv['total_amount']) ?></td>
                        <td class="text-success"><?= formatMoney($inv['paid_amount']) ?></td>
                        <td class="text-danger"><?= formatMoney($inv['remaining_amount']) ?></td>
                        <td><?= formatMoney($inv['monthly_installment']) ?></td>
                        <td><span class="badge badge-success"><?= $inv['paid_count'] ?? 0 ?></span> / <span class="badge badge-warning"><?= $inv['pending_count'] ?? 0 ?></span></td>
                        <td><a href="<?= url('/invoices/' . $inv['id']) ?>" class="btn btn-sm btn-secondary">عرض</a></td>
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
.quick-links { display: flex; gap: 10px; }
</style>

<script>
function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#dataTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(search) ? '' : 'none';
    });
}
</script>

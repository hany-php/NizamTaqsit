<?php $exportUrl = url('/export/customers'); ?>
<div class="page-header">
    <h2><span class="material-icons-round">people</span> إدارة العملاء</h2>
    <div class="header-actions">
        <?php include dirname(__DIR__) . '/partials/export_buttons.php'; ?>
        <a href="<?= url('/customers/create') ?>" class="btn btn-primary">
            <span class="material-icons-round">person_add</span> إضافة عميل
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="filters">
            <input type="text" name="q" class="form-control" style="width:300px" placeholder="بحث بالاسم أو الهاتف..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary"><span class="material-icons-round">search</span></button>
            <?php if ($search): ?>
            <a href="<?= url('/customers') ?>" class="btn btn-secondary"><span class="material-icons-round">clear</span></a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body">
        <?php $selectionType = 'customers'; $allowDelete = true; include dirname(__DIR__) . '/partials/table_selection.php'; ?>
        
        <div class="table-responsive">
            <table class="table" id="customersTable">
                <thead>
                    <tr>
                        <th class="select-checkbox"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
                        <th>#</th>
                        <th>اسم العميل</th>
                        <th>الهاتف</th>
                        <th>رقم الهوية</th>
                        <th>المدينة</th>
                        <th>الرصيد المستحق</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td class="select-checkbox"><input type="checkbox" class="row-checkbox" data-id="<?= $customer['id'] ?>" onclick="toggleRowSelect(this)"></td>
                        <td><?= $customer['id'] ?></td>
                        <td><strong><?= $customer['full_name'] ?></strong></td>
                        <td><a href="tel:<?= $customer['phone'] ?>"><?= $customer['phone'] ?></a></td>
                        <td><?= $customer['national_id'] ?></td>
                        <td><?= $customer['city'] ?? '-' ?></td>
                        <td>
                            <strong class="<?= $customer['balance'] > 0 ? 'text-danger' : 'text-success' ?>">
                                <?= formatMoney($customer['balance'] ?? 0) ?>
                            </strong>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="<?= url('/customers/' . $customer['id']) ?>" class="btn btn-sm btn-secondary" title="عرض">
                                    <span class="material-icons-round">visibility</span>
                                </a>
                                <a href="<?= url('/customers/' . $customer['id'] . '/edit') ?>" class="btn btn-sm btn-primary" title="تعديل">
                                    <span class="material-icons-round">edit</span>
                                </a>
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
.page-header h2 { display: flex; align-items: center; gap: 10px; font-size: 22px; }
.actions { display: flex; gap: 5px; }
.actions .btn { padding: 5px 8px; }
.actions .material-icons-round { font-size: 18px; }
</style>

<script>
function filterCustomers() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#customersTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(search) ? '' : 'none';
    });
}
</script>

<?php $exportUrl = url('/export/reports/customers'); ?>
<div class="page-header">
    <h2><span class="material-icons-round">people</span> تقرير العملاء</h2>
    <div class="header-actions">
        <?php include dirname(__DIR__) . '/partials/export_buttons.php'; ?>
        <a href="<?= url('/reports') ?>" class="btn btn-secondary"><span class="material-icons-round">arrow_forward</span> رجوع</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="filters">
            <input type="text" name="q" class="form-control" placeholder="بحث بالاسم أو الهاتف..." value="<?= htmlspecialchars($search ?? '') ?>" style="width:300px">
            <button type="submit" class="btn btn-primary"><span class="material-icons-round">search</span></button>
            <?php if (!empty($search)): ?>
            <a href="<?= url('/reports/customers') ?>" class="btn btn-secondary"><span class="material-icons-round">clear</span></a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body">
        <?php $selectionType = 'reports/customers'; $allowDelete = false; include dirname(__DIR__) . '/partials/table_selection.php'; ?>
        
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th class="select-checkbox"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th><th>العميل</th><th>الهاتف</th><th>المدينة</th><th>الرصيد المستحق</th><th>العقود النشطة</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($customers as $cust): ?>
                    <tr>
                        <td class="select-checkbox"><input type="checkbox" class="row-checkbox" data-id="<?= $cust['id'] ?>" onclick="toggleRowSelect(this)"></td>
                        <td><strong><?= $cust['full_name'] ?></strong></td>
                        <td><a href="tel:<?= $cust['phone'] ?>"><?= $cust['phone'] ?></a></td>
                        <td><?= $cust['city'] ?? '-' ?></td>
                        <td><strong class="<?= ($cust['balance'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?>"><?= formatMoney($cust['balance'] ?? 0) ?></strong></td>
                        <td><?= $cust['active_invoices'] ?? 0 ?></td>
                        <td><a href="<?= url('/customers/' . $cust['id']) ?>" class="btn btn-sm btn-secondary">عرض</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php include dirname(__DIR__) . '/partials/pagination.php'; ?>
    </div>
</div>

<style>.filters { display: flex; gap: 15px; align-items: center; }</style>


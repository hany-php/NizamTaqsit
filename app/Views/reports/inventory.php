<?php $exportUrl = url('/export/reports/inventory'); ?>
<div class="page-header">
    <h2><span class="material-icons-round">inventory</span> تقرير المخزون</h2>
    <div class="header-actions">
        <?php include dirname(__DIR__) . '/partials/export_buttons.php'; ?>
        <a href="<?= url('/reports') ?>" class="btn btn-secondary"><span class="material-icons-round">arrow_forward</span> رجوع</a>
    </div>
</div>

<?php if (!empty($lowStock)): ?>
<div class="alert alert-warning">
    <span class="material-icons-round">warning</span>
    يوجد <?= count($lowStock) ?> منتج بمخزون منخفض!
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php $selectionType = 'reports/inventory'; $allowDelete = false; include dirname(__DIR__) . '/partials/table_selection.php'; ?>
        
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th class="select-checkbox"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th><th>المنتج</th><th>التصنيف</th><th>الكمية</th><th>حد التنبيه</th><th>السعر النقدي</th><th>قيمة المخزون</th><th>الحالة</th></tr></thead>
                <tbody>
                    <?php foreach ($products as $prod): ?>
                    <tr class="<?= $prod['quantity'] <= $prod['min_quantity'] ? 'overdue-row' : '' ?>">
                        <td class="select-checkbox"><input type="checkbox" class="row-checkbox" data-id="<?= $prod['id'] ?>" onclick="toggleRowSelect(this)"></td>
                        <td><strong><?= $prod['name'] ?></strong></td>
                        <td><?= $prod['category_name'] ?? '-' ?></td>
                        <td><?= $prod['quantity'] ?></td>
                        <td><?= $prod['min_quantity'] ?></td>
                        <td><?= formatMoney($prod['cash_price']) ?></td>
                        <td><?= formatMoney($prod['cash_price'] * $prod['quantity']) ?></td>
                        <td>
                            <?php if ($prod['quantity'] <= 0): ?>
                            <span class="badge badge-danger">نفذ</span>
                            <?php elseif ($prod['quantity'] <= $prod['min_quantity']): ?>
                            <span class="badge badge-warning">منخفض</span>
                            <?php else: ?>
                            <span class="badge badge-success">متوفر</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>.overdue-row { background: rgba(255, 152, 0, 0.1); }</style>

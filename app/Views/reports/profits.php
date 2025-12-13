<?php $exportUrl = url('/export/reports/profits'); ?>
<div class="page-header">
    <h2><span class="material-icons-round">trending_up</span> تقرير الأرباح</h2>
    <div class="header-actions">
        <?php include dirname(__DIR__) . '/partials/export_buttons.php'; ?>
        <a href="<?= url('/reports') ?>" class="btn btn-secondary"><span class="material-icons-round">arrow_forward</span> رجوع</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="filters">
            <div class="form-group" style="margin:0"><label>من</label><input type="date" name="from" class="form-control" value="<?= $from ?>"></div>
            <div class="form-group" style="margin:0"><label>إلى</label><input type="date" name="to" class="form-control" value="<?= $to ?>"></div>
            <button type="submit" class="btn btn-primary" style="align-self:flex-end">عرض</button>
        </form>
    </div>
    <div class="card-body">
        <div class="stats-grid small">
            <div class="stat-card-mini"><span class="material-icons-round" style="color:#1e88e5">payments</span><div><strong><?= formatMoney($totalRevenue) ?></strong><span>إجمالي المبيعات</span></div></div>
            <div class="stat-card-mini"><span class="material-icons-round" style="color:#ff9800">shopping_cart</span><div><strong><?= formatMoney($totalCost) ?></strong><span>تكلفة البضاعة</span></div></div>
            <div class="stat-card-mini"><span class="material-icons-round" style="color:#43a047">trending_up</span><div><strong><?= formatMoney($totalProfit) ?></strong><span>صافي الربح</span></div></div>
            <div class="stat-card-mini"><span class="material-icons-round" style="color:#9c27b0">percent</span><div><strong><?= $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 1) : 0 ?>%</strong><span>هامش الربح</span></div></div>
        </div>
        
        <?php $selectionType = 'reports/profits'; $allowDelete = false; include dirname(__DIR__) . '/partials/table_selection.php'; ?>
        
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th class="select-checkbox"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th><th>المنتج</th><th>الكمية</th><th>الإيرادات</th><th>التكلفة</th><th>الربح</th><th>الهامش</th></tr></thead>
                <tbody>
                    <?php foreach ($profits as $item): ?>
                    <tr>
                        <td class="select-checkbox"><input type="checkbox" class="row-checkbox" data-id="<?= $item['product_id'] ?? $item['name'] ?>" onclick="toggleRowSelect(this)"></td>
                        <td><strong><?= $item['name'] ?></strong></td>
                        <td><?= $item['total_quantity'] ?></td>
                        <td><?= formatMoney($item['total_revenue']) ?></td>
                        <td><?= formatMoney($item['total_cost']) ?></td>
                        <td><strong class="text-success"><?= formatMoney($item['total_profit']) ?></strong></td>
                        <td><?= $item['total_revenue'] > 0 ? round(($item['total_profit'] / $item['total_revenue']) * 100, 1) : 0 ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>.filters { display: flex; gap: 20px; align-items: flex-end; }</style>

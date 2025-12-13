<?php $exportUrl = url('/export/reports/cashflow'); ?>
<div class="page-header">
    <h2><span class="material-icons-round">account_balance</span> تقرير التدفق النقدي</h2>
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
            <div class="stat-card-mini"><span class="material-icons-round" style="color:#43a047">payments</span><div><strong><?= formatMoney($summary['cash_sales'] ?? 0) ?></strong><span>مبيعات نقدية</span></div></div>
            <div class="stat-card-mini"><span class="material-icons-round" style="color:#1e88e5">credit_score</span><div><strong><?= formatMoney($summary['down_payments'] ?? 0) ?></strong><span>دفعات مقدمة</span></div></div>
            <div class="stat-card-mini"><span class="material-icons-round" style="color:#ff9800">receipt</span><div><strong><?= formatMoney($totalPayments) ?></strong><span>تحصيلات أقساط</span></div></div>
            <div class="stat-card-mini"><span class="material-icons-round" style="color:#9c27b0">account_balance_wallet</span><div><strong><?= formatMoney(($summary['cash_sales'] ?? 0) + $totalPayments) ?></strong><span>إجمالي النقد</span></div></div>
        </div>
        
        <h4 style="margin:25px 0 15px">التدفق اليومي</h4>
        <div class="chart-container">
            <canvas id="cashflowChart"></canvas>
        </div>
        
        <?php $selectionType = 'reports/cashflow'; $allowDelete = false; include dirname(__DIR__) . '/partials/table_selection.php'; ?>
        
        <div class="table-responsive" style="margin-top:25px">
            <table class="table">
                <thead><tr><th class="select-checkbox"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th><th>التاريخ</th><th>المبلغ</th></tr></thead>
                <tbody>
                    <?php foreach ($inflows as $flow): ?>
                    <tr>
                        <td class="select-checkbox"><input type="checkbox" class="row-checkbox" data-id="<?= $flow['date'] ?>" onclick="toggleRowSelect(this)"></td>
                        <td><?= formatDate($flow['date']) ?></td>
                        <td><strong class="text-success"><?= formatMoney($flow['total']) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('cashflowChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($inflows, 'date')) ?>,
        datasets: [{
            label: 'التحصيلات',
            data: <?= json_encode(array_column($inflows, 'total')) ?>,
            backgroundColor: 'rgba(30, 136, 229, 0.8)',
            borderRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>

<style>
.filters { display: flex; gap: 20px; align-items: flex-end; }
.chart-container { height: 300px; background: var(--bg-main); border-radius: var(--radius); padding: 20px; }
</style>

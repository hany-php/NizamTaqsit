<?php $exportUrl = url('/export/reports/sales'); ?>
<div class="page-header">
    <h2><span class="material-icons-round">point_of_sale</span> تقرير المبيعات</h2>
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
            <div class="stat-card-mini"><span class="material-icons-round" style="color:#43a047">payments</span><div><strong><?= formatMoney($totalCash) ?></strong><span>مبيعات نقدية</span></div></div>
            <div class="stat-card-mini"><span class="material-icons-round" style="color:#1e88e5">credit_score</span><div><strong><?= formatMoney($totalInstallment) ?></strong><span>مبيعات تقسيط</span></div></div>
            <div class="stat-card-mini"><span class="material-icons-round" style="color:#ff9800">receipt</span><div><strong><?= count($invoices) ?></strong><span>عدد الفواتير</span></div></div>
            <div class="stat-card-mini"><span class="material-icons-round" style="color:#9c27b0">account_balance</span><div><strong><?= formatMoney($total) ?></strong><span>الإجمالي</span></div></div>
        </div>
        
        <?php $selectionType = 'reports/sales'; $allowDelete = false; include dirname(__DIR__) . '/partials/table_selection.php'; ?>
        
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th class="select-checkbox"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th><th>رقم الفاتورة</th><th>العميل</th><th>النوع</th><th>الإجمالي</th><th>التاريخ</th></tr></thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td class="select-checkbox"><input type="checkbox" class="row-checkbox" data-id="<?= $inv['id'] ?>" onclick="toggleRowSelect(this)"></td>
                        <td><a href="<?= url('/invoices/' . $inv['id']) ?>"><?= $inv['invoice_number'] ?></a></td>
                        <td><?= $inv['customer_name'] ?? 'زبون نقدي' ?></td>
                        <td><span class="badge badge-<?= $inv['invoice_type'] === 'cash' ? 'success' : 'primary' ?>"><?= invoiceType($inv['invoice_type']) ?></span></td>
                        <td><strong><?= formatMoney($inv['total_amount']) ?></strong></td>
                        <td><?= formatDate($inv['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php include dirname(__DIR__) . '/partials/pagination.php'; ?>
    </div>
</div>

<style>.filters { display: flex; gap: 20px; align-items: flex-end; }</style>

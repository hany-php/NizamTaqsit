<?php $exportUrl = url('/export/reports/collections'); ?>
<div class="page-header">
    <h2><span class="material-icons-round">payments</span> تقرير التحصيلات</h2>
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
        <div class="summary-bar">
            <div class="summary-item"><span class="material-icons-round" style="color:#43a047">payments</span><div><strong><?= formatMoney($total) ?></strong><span>إجمالي التحصيلات</span></div></div>
            <div class="summary-item"><span class="material-icons-round" style="color:#1e88e5">receipt</span><div><strong><?= count($payments) ?></strong><span>عدد العمليات</span></div></div>
        </div>
        
        <?php $selectionType = 'reports/collections'; $allowDelete = false; include dirname(__DIR__) . '/partials/table_selection.php'; ?>
        
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th class="select-checkbox"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th><th>رقم الإيصال</th><th>العميل</th><th>الفاتورة</th><th>المبلغ</th><th>التاريخ</th></tr></thead>
                <tbody>
                    <?php foreach ($payments as $pay): ?>
                    <tr>
                        <td class="select-checkbox"><input type="checkbox" class="row-checkbox" data-id="<?= $pay['id'] ?>" onclick="toggleRowSelect(this)"></td>
                        <td><?= $pay['receipt_number'] ?></td>
                        <td><?= $pay['customer_name'] ?? '-' ?></td>
                        <td><?= $pay['invoice_number'] ?></td>
                        <td><strong class="text-success"><?= formatMoney($pay['amount']) ?></strong></td>
                        <td><?= formatDateTime($pay['payment_date']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php include dirname(__DIR__) . '/partials/pagination.php'; ?>
    </div>
</div>

<style>
.filters { display: flex; gap: 20px; align-items: flex-end; }
.summary-bar { display: flex; gap: 20px; margin-bottom: 25px; }
.summary-item { background: var(--bg-main); border-radius: var(--radius-sm); padding: 15px 20px; display: flex; align-items: center; gap: 15px; }
.summary-item strong { display: block; font-size: 20px; }
.summary-item span { font-size: 12px; color: var(--text-muted); }
</style>

<?php $exportUrl = url('/export/payments'); ?>
<div class="page-header">
    <h2><span class="material-icons-round">سجل المدفوعات</span> سجل المدفوعات</h2>
    <div class="header-actions">
        <?php include dirname(__DIR__) . '/partials/export_buttons.php'; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="filters">
            <div class="form-group" style="margin:0">
                <label>من تاريخ</label>
                <input type="date" name="from" class="form-control" value="<?= $from ?>">
            </div>
            <div class="form-group" style="margin:0">
                <label>إلى تاريخ</label>
                <input type="date" name="to" class="form-control" value="<?= $to ?>">
            </div>
            <button type="submit" class="btn btn-primary" style="align-self:flex-end">بحث</button>
        </form>
    </div>
    <div class="card-body">
        <div class="summary-bar">
            <div class="summary-item">
                <span class="material-icons-round">payments</span>
                <div><strong><?= formatMoney($total) ?></strong><span>إجمالي المدفوعات</span></div>
            </div>
            <div class="summary-item">
                <span class="material-icons-round">receipt</span>
                <div><strong><?= count($payments) ?></strong><span>عدد العمليات</span></div>
            </div>
        </div>
        
        <?php $selectionType = 'payments'; $allowDelete = false; include dirname(__DIR__) . '/partials/table_selection.php'; ?>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th class="select-checkbox"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
                        <th>رقم الإيصال</th>
                        <th>العميل</th>
                        <th>الفاتورة</th>
                        <th>المبلغ</th>
                        <th>طريقة الدفع</th>
                        <th>التاريخ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $pay): ?>
                    <tr>
                        <td class="select-checkbox"><input type="checkbox" class="row-checkbox" data-id="<?= $pay['id'] ?>" onclick="toggleRowSelect(this)"></td>
                        <td><?= $pay['receipt_number'] ?></td>
                        <td><?= $pay['customer_name'] ?? '-' ?></td>
                        <td><a href="<?= url('/invoices/' . $pay['invoice_id']) ?>"><?= $pay['invoice_number'] ?></a></td>
                        <td><strong class="text-success"><?= formatMoney($pay['amount']) ?></strong></td>
                        <td><?= paymentMethod($pay['payment_method']) ?></td>
                        <td><?= formatDateTime($pay['payment_date']) ?></td>
                        <td><a href="<?= url('/payments/' . $pay['id'] . '/receipt') ?>" class="btn btn-sm btn-secondary" target="_blank"><span class="material-icons-round">print</span></a></td>
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
.summary-item .material-icons-round { font-size: 30px; color: var(--success); }
.summary-item strong { display: block; font-size: 20px; }
.summary-item span { font-size: 12px; color: var(--text-muted); }
</style>

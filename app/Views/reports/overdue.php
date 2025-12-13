<?php $exportUrl = url('/export/reports/overdue'); ?>
<div class="page-header">
    <h2><span class="material-icons-round">warning</span> تقرير المتأخرات</h2>
    <div class="header-actions">
        <?php include dirname(__DIR__) . '/partials/export_buttons.php'; ?>
        <a href="<?= url('/reports') ?>" class="btn btn-secondary"><span class="material-icons-round">arrow_forward</span> رجوع</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="summary-bar">
            <div class="summary-item" style="background:#ffebee"><span class="material-icons-round" style="color:#e53935">error</span><div><strong><?= count($installments) ?></strong><span>عدد الأقساط المتأخرة</span></div></div>
            <div class="summary-item" style="background:#ffebee"><span class="material-icons-round" style="color:#e53935">payments</span><div><strong><?= formatMoney($total) ?></strong><span>إجمالي المتأخرات</span></div></div>
        </div>
        
        <?php if (empty($installments)): ?>
        <p class="empty-message success-message"><span class="material-icons-round">check_circle</span> لا توجد أقساط متأخرة</p>
        <?php else: ?>
        <?php $selectionType = 'reports/overdue'; $allowDelete = false; include dirname(__DIR__) . '/partials/table_selection.php'; ?>
        
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th class="select-checkbox"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th><th>العميل</th><th>الهاتف</th><th>الفاتورة</th><th>القسط</th><th>المبلغ</th><th>تاريخ الاستحقاق</th><th>أيام التأخير</th></tr></thead>
                <tbody>
                    <?php foreach ($installments as $inst): ?>
                    <tr class="overdue-row">
                        <td class="select-checkbox"><input type="checkbox" class="row-checkbox" data-id="<?= $inst['id'] ?>" onclick="toggleRowSelect(this)"></td>
                        <td><strong><?= $inst['customer_name'] ?></strong></td>
                        <td><a href="tel:<?= $inst['customer_phone'] ?>"><?= $inst['customer_phone'] ?></a></td>
                        <td><a href="<?= url('/invoices/' . $inst['invoice_id']) ?>"><?= $inst['invoice_number'] ?></a></td>
                        <td>القسط <?= $inst['installment_number'] ?></td>
                        <td><strong class="text-danger"><?= formatMoney($inst['remaining_amount']) ?></strong></td>
                        <td><?= formatDate($inst['due_date']) ?></td>
                        <td><span class="badge badge-danger"><?= (int)$inst['days_overdue'] ?> يوم</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php include dirname(__DIR__) . '/partials/pagination.php'; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.summary-bar { display: flex; gap: 20px; margin-bottom: 25px; }
.summary-item { border-radius: var(--radius-sm); padding: 15px 20px; display: flex; align-items: center; gap: 15px; }
.summary-item strong { display: block; font-size: 20px; }
.summary-item span { font-size: 12px; color: var(--text-muted); }
.overdue-row { background: rgba(229, 57, 53, 0.05); }
</style>

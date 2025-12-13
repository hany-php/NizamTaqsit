<?php $exportUrl = url('/export/reports/employees'); ?>
<div class="page-header">
    <h2><span class="material-icons-round">groups</span> تقرير أداء الموظفين</h2>
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
        <?php if (empty($performance)): ?>
        <p class="empty-message">لا توجد بيانات في هذه الفترة</p>
        <?php else: ?>
        <?php $selectionType = 'reports/employees'; $allowDelete = false; include dirname(__DIR__) . '/partials/table_selection.php'; ?>
        
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th class="select-checkbox"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th><th>الموظف</th><th>عدد الفواتير</th><th>مبيعات نقدية</th><th>مبيعات تقسيط</th><th>الإجمالي</th></tr></thead>
                <tbody>
                    <?php foreach ($performance as $emp): ?>
                    <tr>
                        <td class="select-checkbox"><input type="checkbox" class="row-checkbox" data-id="<?= $emp['user_id'] ?? $emp['full_name'] ?>" onclick="toggleRowSelect(this)"></td>
                        <td><strong><?= $emp['full_name'] ?></strong></td>
                        <td><?= $emp['invoices_count'] ?></td>
                        <td><?= $emp['cash_sales'] ?></td>
                        <td><?= $emp['installment_sales'] ?></td>
                        <td><strong class="text-primary"><?= formatMoney($emp['total_sales']) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>.filters { display: flex; gap: 20px; align-items: flex-end; }</style>

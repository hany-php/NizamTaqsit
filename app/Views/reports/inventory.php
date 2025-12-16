<?php 
$exportUrl = url('/export/reports/inventory'); 
$editedId = isset($_GET['edited']) ? (int)$_GET['edited'] : 0;
?>
<div class="page-header">
    <h2><span class="material-icons-round">inventory</span> تقرير المخزون</h2>
    <div class="header-actions">
        <?php include dirname(__DIR__) . '/partials/export_buttons.php'; ?>
        <a href="<?= url('/products/create') ?>" class="btn btn-primary"><span class="material-icons-round">add</span> إضافة منتج</a>
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
        <?php $selectionType = 'products'; $allowDelete = true; include dirname(__DIR__) . '/partials/table_selection.php'; ?>
        
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th class="select-checkbox"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th><th>المنتج</th><th>التصنيف</th><th>الكمية</th><th>حد التنبيه</th><th>السعر النقدي</th><th>قيمة المخزون</th><th>الحالة</th><th>الإجراءات</th></tr></thead>
                <tbody>
                    <?php foreach ($products as $prod): 
                        $isEdited = ($prod['id'] == $editedId);
                        $rowClass = $prod['quantity'] <= $prod['min_quantity'] ? 'overdue-row' : '';
                        if ($isEdited) $rowClass .= ' edited-row';
                    ?>
                    <tr class="<?= trim($rowClass) ?>" <?= $isEdited ? 'id="edited-product"' : '' ?>>
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
                        <td>
                            <a href="<?= url('/products/' . $prod['id'] . '/edit') ?>?return=inventory" class="btn btn-sm btn-secondary" title="تعديل"><span class="material-icons-round">edit</span></a>
                            <button type="button" class="btn btn-sm btn-danger" title="حذف" onclick="deleteSingleProduct(<?= $prod['id'] ?>, this)"><span class="material-icons-round">delete</span></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php include dirname(__DIR__) . '/partials/pagination.php'; ?>
    </div>
</div>

<script>
function deleteSingleProduct(id, btn) {
    if (!confirm('هل أنت متأكد من حذف هذا المنتج؟')) {
        return;
    }
    
    fetch('<?= url('/products/bulk-delete') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'ids=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            const row = btn.closest('tr');
            if (row) {
                row.style.transition = 'opacity 0.3s, transform 0.3s';
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                setTimeout(() => row.remove(), 300);
            }
        } else {
            showToast(data.message || 'حدث خطأ أثناء الحذف', 'error');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        showToast('حدث خطأ في الاتصال بالخادم', 'error');
    });
}
</script>

<style>
/* تمييز المنتج المعدّل */
.edited-row {
    animation: highlightPulse 2s ease-in-out 3;
    background-color: rgba(67, 160, 71, 0.15) !important;
}

@keyframes highlightPulse {
    0%, 100% { background-color: rgba(67, 160, 71, 0.15); }
    50% { background-color: rgba(67, 160, 71, 0.35); }
}

.edited-row td:first-child::before {
    content: '✓';
    display: inline-block;
    background: var(--success);
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    text-align: center;
    line-height: 20px;
    font-size: 12px;
    margin-left: 5px;
}
</style>

<script>
// التمرير التلقائي للمنتج المعدّل
document.addEventListener('DOMContentLoaded', function() {
    const editedRow = document.getElementById('edited-product');
    if (editedRow) {
        setTimeout(() => {
            editedRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 300);
    }
});
</script>


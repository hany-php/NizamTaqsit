<?php 
/**
 * Checkbox Selection Partial
 * Include this in table views to enable row selection
 * Required: $selectionType = 'products' | 'customers' | 'invoices' | 'payments' etc.
 */
?>

<style>
.selection-bar {
    display: none;
    background: var(--primary);
    color: white;
    padding: 10px 20px;
    border-radius: var(--radius-sm);
    margin-bottom: 15px;
    align-items: center;
    justify-content: space-between;
    animation: slideDown 0.2s ease;
}
.selection-bar.show { display: flex; }
.selection-bar .selected-count { font-weight: 600; }
.selection-bar .selection-actions { display: flex; gap: 10px; }
.selection-bar .btn { background: rgba(255,255,255,0.2); color: white; border: none; }
.selection-bar .btn:hover { background: rgba(255,255,255,0.3); }
.selection-bar .btn-danger { background: var(--danger); }

.table .select-checkbox {
    width: 40px;
    text-align: center;
}
.table .select-checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}
.table tbody tr.selected { background: rgba(var(--primary-rgb), 0.1) !important; }

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="selection-bar" id="selectionBar">
    <div>
        <span class="selected-count"><span id="selectedCount">0</span> عنصر محدد</span>
    </div>
    <div class="selection-actions">
        <button class="btn btn-sm" onclick="exportSelected('pdf')">
            <span class="material-icons-round">picture_as_pdf</span> تصدير PDF
        </button>
        <button class="btn btn-sm" onclick="exportSelected('excel')">
            <span class="material-icons-round">table_chart</span> تصدير Excel
        </button>
        <?php if (isset($allowDelete) && $allowDelete): ?>
        <button class="btn btn-sm btn-danger" onclick="deleteSelected()">
            <span class="material-icons-round">delete</span> حذف المحدد
        </button>
        <?php endif; ?>
        <button class="btn btn-sm" onclick="clearSelection()">
            <span class="material-icons-round">close</span> إلغاء
        </button>
    </div>
</div>

<script>
const selectionType = '<?= $selectionType ?? 'items' ?>';
const exportBaseUrl = '<?= $exportUrl ?? '' ?>';
let selectedIds = [];

function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        updateRowSelection(cb);
    });
    updateSelectionBar();
}

function toggleRowSelect(checkbox) {
    updateRowSelection(checkbox);
    updateSelectAllCheckbox();
    updateSelectionBar();
}

function updateRowSelection(checkbox) {
    const id = checkbox.dataset.id;
    const row = checkbox.closest('tr');
    
    if (checkbox.checked) {
        if (!selectedIds.includes(id)) {
            selectedIds.push(id);
        }
        row.classList.add('selected');
    } else {
        selectedIds = selectedIds.filter(i => i !== id);
        row.classList.remove('selected');
    }
}

function updateSelectAllCheckbox() {
    const allCheckbox = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
    
    if (allCheckbox) {
        allCheckbox.checked = checkedCount === checkboxes.length && checkboxes.length > 0;
        allCheckbox.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
    }
}

function updateSelectionBar() {
    const bar = document.getElementById('selectionBar');
    const countEl = document.getElementById('selectedCount');
    
    countEl.textContent = selectedIds.length;
    
    if (selectedIds.length > 0) {
        bar.classList.add('show');
    } else {
        bar.classList.remove('show');
    }
}

function clearSelection() {
    selectedIds = [];
    document.querySelectorAll('.row-checkbox, #selectAll').forEach(cb => cb.checked = false);
    document.querySelectorAll('tr.selected').forEach(tr => tr.classList.remove('selected'));
    updateSelectionBar();
}

function exportSelected(format) {
    if (selectedIds.length === 0) {
        alert('لم يتم تحديد أي عناصر');
        return;
    }
    
    const ids = selectedIds.join(',');
    let url = exportBaseUrl + '/' + format + '?ids=' + ids;
    window.open(url, '_blank');
}

function deleteSelected() {
    if (selectedIds.length === 0) {
        alert('لم يتم تحديد أي عناصر');
        return;
    }
    
    if (!confirm('هل أنت متأكد من حذف ' + selectedIds.length + ' عنصر؟')) {
        return;
    }
    
    // Submit delete form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= url('/' . ($selectionType ?? 'items') . '/bulk-delete') ?>';
    
    selectedIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}
</script>

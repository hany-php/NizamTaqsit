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

/* Toast Notifications */
.bulk-toast {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(-100px);
    padding: 15px 25px;
    border-radius: var(--radius-sm);
    color: white;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 9999;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    transition: transform 0.3s ease, opacity 0.3s ease;
    opacity: 0;
}
.bulk-toast.show {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
}
.bulk-toast-success { background: var(--success, #28a745); }
.bulk-toast-error { background: var(--danger, #dc3545); }
.bulk-toast-warning { background: var(--warning, #ffc107); color: #333; }
.bulk-toast-info { background: var(--primary, #007bff); }
.bulk-toast .material-icons-round { font-size: 20px; }
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
        showToast('لم يتم تحديد أي عناصر', 'warning');
        return;
    }
    
    if (!confirm('هل أنت متأكد من حذف ' + selectedIds.length + ' عنصر؟')) {
        return;
    }
    
    // Use AJAX to delete and stay on same page
    const deleteUrl = '<?= url('/' . ($selectionType ?? 'items') . '/bulk-delete') ?>';
    
    fetch(deleteUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'ids=' + selectedIds.join(',')
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Remove deleted rows from table
            selectedIds.forEach(id => {
                const checkbox = document.querySelector('.row-checkbox[data-id="' + id + '"]');
                if (checkbox) {
                    const row = checkbox.closest('tr');
                    if (row) {
                        row.style.transition = 'opacity 0.3s, transform 0.3s';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(-20px)';
                        setTimeout(() => row.remove(), 300);
                    }
                }
            });
            clearSelection();
        } else {
            showToast(data.message || 'حدث خطأ أثناء الحذف', 'error');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        showToast('حدث خطأ في الاتصال بالخادم', 'error');
    });
}

// Toast notification function
function showToast(message, type = 'info') {
    // Remove existing toast
    const existingToast = document.querySelector('.bulk-toast');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.className = 'bulk-toast bulk-toast-' + type;
    
    const icons = {
        success: 'check_circle',
        error: 'error',
        warning: 'warning',
        info: 'info'
    };
    
    toast.innerHTML = '<span class="material-icons-round">' + (icons[type] || 'info') + '</span> ' + message;
    document.body.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Auto-remove after 4 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}
</script>

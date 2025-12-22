<?php $exportUrl = url('/export/categories'); ?>
<div class="page-header">
    <h2><span class="material-icons-round">category</span> إدارة التصنيفات</h2>
    <div class="header-actions">
        <?php include dirname(__DIR__) . '/partials/export_buttons.php'; ?>
        <button class="btn btn-primary" onclick="showAddModal()"><span class="material-icons-round">add</span> إضافة تصنيف</button>
    </div>
</div>

<!-- شريط البحث -->
<div class="filters-bar" style="margin-bottom: 20px;">
    <form method="GET" class="search-form" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <div class="search-box" style="flex: 1; min-width: 200px;">
            <input type="text" name="q" class="form-control" placeholder="ابحث بالاسم أو الوصف..." value="<?= htmlspecialchars($search ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-primary"><span class="material-icons-round">search</span> بحث</button>
        <?php if (!empty($search)): ?>
        <a href="<?= url('/categories') ?>" class="btn btn-secondary"><span class="material-icons-round">clear</span> مسح</a>
        <?php endif; ?>
    </form>
</div>

<div class="summary-bar">
    <div class="summary-item">
        <span class="material-icons-round" style="color:var(--primary)">category</span>
        <div><strong><?= $totalCount ?? count($categories) ?></strong><span>تصنيف</span></div>
    </div>
    <div class="summary-item">
        <span class="material-icons-round" style="color:var(--success)">inventory_2</span>
        <div><strong><?= array_sum(array_column($allCategories ?? $categories, 'products_count')) ?></strong><span>منتج</span></div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table" id="categoriesTable">
                <thead>
                    <tr>
                        <th style="width:50px; cursor:pointer" onclick="sortTable(0, 'num')"># <span class="sort-icon">↕</span></th>
                        <th>الأيقونة</th>
                        <th>اللون</th>
                        <th style="cursor:pointer" onclick="sortTable(3, 'text')">اسم التصنيف <span class="sort-icon">↕</span></th>
                        <th>الوصف</th>
                        <th style="cursor:pointer" onclick="sortTable(5, 'num')">عدد المنتجات <span class="sort-icon">↕</span></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?= $cat['id'] ?></td>
                        <td>
                            <div class="category-icon-sm" style="background:<?= $cat['color'] ?? '#1e88e5' ?>">
                                <span class="material-icons-round"><?= $cat['icon'] ?: 'category' ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="color-box" style="background:<?= $cat['color'] ?? '#1e88e5' ?>"></div>
                            <small class="text-muted"><?= $cat['color'] ?? '#1e88e5' ?></small>
                        </td>
                        <td><strong><?= $cat['name'] ?></strong></td>
                        <td><small class="text-muted"><?= $cat['description'] ?? '-' ?></small></td>
                        <td>
                            <span class="badge badge-<?= ($cat['products_count'] ?? 0) > 0 ? 'primary' : 'secondary' ?>">
                                <?= $cat['products_count'] ?? 0 ?> منتج
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="<?= url('/categories/' . $cat['id']) ?>" class="btn btn-sm btn-primary" title="عرض المنتجات"><span class="material-icons-round">visibility</span></a>
                                <button class="btn btn-sm btn-secondary" onclick="editCategory(<?= htmlspecialchars(json_encode($cat)) ?>)" title="تعديل"><span class="material-icons-round">edit</span></button>
                                <button class="btn btn-sm btn-danger" onclick="showDeleteModal(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>', <?= $cat['products_count'] ?? 0 ?>)" title="حذف"><span class="material-icons-round">delete</span></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php include dirname(__DIR__) . '/partials/pagination.php'; ?>
    </div>
</div>

<!-- Modal تأكيد الحذف -->
<div class="modal" id="deleteModal">
    <div class="modal-content" style="max-width:450px">
        <div class="modal-header">
            <h3>تأكيد حذف التصنيف</h3>
            <button class="close-btn" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="deleteForm" method="POST">
                <input type="hidden" name="confirm_delete" value="1">
                
                <div class="alert alert-warning" id="deleteWarning" style="display:none;">
                    <span class="material-icons-round">warning</span>
                    <span id="deleteWarningText"></span>
                </div>
                
                <p id="deleteMessage"></p>
                
                <div class="form-group" id="moveToCategoryGroup" style="display:none;">
                    <label>نقل المنتجات المرتبطة بفواتير إلى:</label>
                    <select name="move_to_category" id="moveToCategory" class="form-control">
                        <option value="">-- بدون تصنيف --</option>
                        <?php foreach ($allCategories ?? $categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" data-cat-id="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">سيتم حذف المنتجات غير المرتبطة بفواتير ونقل الباقي</small>
                </div>
                
                <div class="btn-group" style="display:flex; gap:10px; margin-top:20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">إلغاء</button>
                    <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">حذف</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="categoryModal">
    <div class="modal-content" style="max-width:400px">
        <div class="modal-header">
            <h3 id="modalTitle">إضافة تصنيف</h3>
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="categoryForm" method="POST">
                <input type="hidden" id="categoryId" name="id">
                <div class="form-group">
                    <label>اسم التصنيف *</label>
                    <input type="text" id="categoryName" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>الوصف</label>
                    <textarea id="categoryDesc" name="description" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>الأيقونة</label>
                    <input type="text" id="categoryIcon" name="icon" class="form-control" placeholder="مثال: smartphone">
                    <small>أيقونات <a href="https://fonts.google.com/icons" target="_blank">Material Icons</a></small>
                </div>
                <div class="form-group">
                    <label>اللون</label>
                    <input type="color" id="categoryColor" name="color" class="form-control" value="#1e88e5" style="height:45px">
                </div>
                <button type="submit" class="btn btn-primary btn-block">حفظ</button>
            </form>
        </div>
    </div>
</div>

<style>
.summary-bar { display: flex; gap: 20px; margin-bottom: 25px; }
.summary-item { background: var(--bg-card); border-radius: var(--radius); padding: 20px 25px; display: flex; align-items: center; gap: 15px; box-shadow: var(--shadow); }
.summary-item .material-icons-round { font-size: 36px; }
.summary-item strong { display: block; font-size: 24px; }
.summary-item span { font-size: 13px; color: var(--text-muted); }
.category-icon-sm { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
.category-icon-sm .material-icons-round { color: white; font-size: 18px; }
.color-box { width: 24px; height: 24px; border-radius: 4px; display: inline-block; vertical-align: middle; margin-left: 8px; border: 1px solid rgba(0,0,0,0.1); }
.actions { display: flex; gap: 5px; }
.actions .btn { padding: 5px 8px; }
.actions .material-icons-round { font-size: 18px; }
th[onclick] { user-select: none; }
th[onclick]:hover { background: var(--bg-main); }
.sort-icon { font-size: 12px; color: var(--text-muted); margin-right: 5px; }
</style>

<script>
let currentCategoryId = null;

function showAddModal() {
    document.getElementById('modalTitle').textContent = 'إضافة تصنيف';
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryDesc').value = '';
    document.getElementById('categoryIcon').value = '';
    document.getElementById('categoryColor').value = '#1e88e5';
    currentCategoryId = null;
    document.getElementById('categoryModal').classList.add('show');
}

function editCategory(cat) {
    document.getElementById('modalTitle').textContent = 'تعديل تصنيف';
    document.getElementById('categoryId').value = cat.id;
    document.getElementById('categoryName').value = cat.name;
    document.getElementById('categoryDesc').value = cat.description || '';
    document.getElementById('categoryIcon').value = cat.icon || '';
    document.getElementById('categoryColor').value = cat.color || '#1e88e5';
    currentCategoryId = cat.id;
    document.getElementById('categoryModal').classList.add('show');
}

function closeModal() {
    document.getElementById('categoryModal').classList.remove('show');
}

// معالجة النموذج بـ AJAX
document.getElementById('categoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const id = document.getElementById('categoryId').value;
    const isEdit = id ? true : false;
    const url = isEdit ? '<?= url('/categories/') ?>' + id : '<?= url('/categories') ?>';
    
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'جاري الحفظ...';
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error('HTTP ' + response.status + ': ' + text);
            });
        }
        return response.json();
    })
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'حفظ';
        
        if (data.success) {
            closeModal();
            showToast(data.message);
            
            if (isEdit) {
                // تحديث الصف في الجدول
                updateTableRow(data.category);
            } else {
                // إضافة صف جديد
                addTableRow(data.category);
                updateCounts(1);
            }
        } else {
            alert(data.message || 'حدث خطأ');
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'حفظ';
        console.error('Error:', error);
        alert('حدث خطأ: ' + error.message);
    });
});

function showDeleteModal(id, name, productsCount) {
    currentCategoryId = id;
    
    const warning = document.getElementById('deleteWarning');
    const warningText = document.getElementById('deleteWarningText');
    const message = document.getElementById('deleteMessage');
    const moveToCategoryGroup = document.getElementById('moveToCategoryGroup');
    const moveToCategory = document.getElementById('moveToCategory');
    
    if (productsCount > 0) {
        warning.style.display = 'flex';
        warningText.textContent = 'هذا التصنيف يحتوي على ' + productsCount + ' منتج';
        message.innerHTML = '<strong>عند الحذف:</strong><br>• المنتجات غير المرتبطة بفواتير سيتم حذفها<br>• المنتجات المرتبطة بفواتير سيتم نقلها';
        moveToCategoryGroup.style.display = 'block';
        
        // إخفاء التصنيف الحالي من القائمة
        const options = moveToCategory.querySelectorAll('option');
        options.forEach(opt => {
            if (opt.dataset.catId == id) {
                opt.style.display = 'none';
            } else {
                opt.style.display = '';
            }
        });
    } else {
        warning.style.display = 'none';
        message.textContent = 'هل أنت متأكد من حذف تصنيف "' + name + '"؟';
        moveToCategoryGroup.style.display = 'none';
    }
    
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

// معالجة الحذف بـ AJAX
document.getElementById('deleteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!currentCategoryId) return;
    
    const formData = new FormData(this);
    const submitBtn = document.getElementById('confirmDeleteBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'جاري الحذف...';
    
    fetch('<?= url('/categories/') ?>' + currentCategoryId + '/delete', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error('HTTP ' + response.status + ': ' + text);
            });
        }
        return response.json();
    })
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'حذف';
        
        if (data.success) {
            closeDeleteModal();
            showToast(data.message);
            
            // حذف الصف من الجدول
            const row = document.querySelector(`tr td:first-child`);
            const rows = document.querySelectorAll('#categoriesTable tbody tr');
            rows.forEach(r => {
                if (r.cells[0].textContent.trim() == currentCategoryId) {
                    r.remove();
                }
            });
            updateCounts(-1);
        } else {
            alert(data.message || 'حدث خطأ');
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'حذف';
        console.error('Error:', error);
        alert('حدث خطأ: ' + error.message);
    });
});

function addTableRow(cat) {
    const tbody = document.querySelector('#categoriesTable tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>${cat.id}</td>
        <td>
            <div class="category-icon-sm" style="background:${cat.color || '#1e88e5'}">
                <span class="material-icons-round">${cat.icon || 'category'}</span>
            </div>
        </td>
        <td>
            <div class="color-box" style="background:${cat.color || '#1e88e5'}"></div>
            <small class="text-muted">${cat.color || '#1e88e5'}</small>
        </td>
        <td><strong>${escapeHtml(cat.name)}</strong></td>
        <td><small class="text-muted">${cat.description || '-'}</small></td>
        <td><span class="badge badge-secondary">0 منتج</span></td>
        <td>
            <div class="actions">
                <a href="<?= url('/categories/') ?>${cat.id}" class="btn btn-sm btn-primary" title="عرض المنتجات"><span class="material-icons-round">visibility</span></a>
                <button class="btn btn-sm btn-secondary" onclick="editCategory(${JSON.stringify(cat).replace(/"/g, '&quot;')})" title="تعديل"><span class="material-icons-round">edit</span></button>
                <button class="btn btn-sm btn-danger" onclick="showDeleteModal(${cat.id}, '${escapeHtml(cat.name)}', 0)" title="حذف"><span class="material-icons-round">delete</span></button>
            </div>
        </td>
    `;
    tbody.insertBefore(tr, tbody.firstChild);
}

function updateTableRow(cat) {
    const rows = document.querySelectorAll('#categoriesTable tbody tr');
    rows.forEach(r => {
        if (r.cells[0].textContent.trim() == cat.id) {
            r.cells[1].innerHTML = `<div class="category-icon-sm" style="background:${cat.color || '#1e88e5'}"><span class="material-icons-round">${cat.icon || 'category'}</span></div>`;
            r.cells[2].innerHTML = `<div class="color-box" style="background:${cat.color || '#1e88e5'}"></div><small class="text-muted">${cat.color || '#1e88e5'}</small>`;
            r.cells[3].innerHTML = `<strong>${escapeHtml(cat.name)}</strong>`;
            r.cells[4].innerHTML = `<small class="text-muted">${cat.description || '-'}</small>`;
        }
    });
}

function updateCounts(delta) {
    const countEl = document.querySelector('.summary-item strong');
    if (countEl) {
        const current = parseInt(countEl.textContent) || 0;
        countEl.textContent = current + delta;
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    toast.style.cssText = 'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#333;color:#fff;padding:12px 24px;border-radius:8px;z-index:9999;';
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// ترتيب الجدول
let sortDirection = {};
function sortTable(columnIndex, type) {
    const table = document.getElementById('categoriesTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // تبديل اتجاه الترتيب
    sortDirection[columnIndex] = !sortDirection[columnIndex];
    const isAsc = sortDirection[columnIndex];
    
    rows.sort((a, b) => {
        let aVal = a.cells[columnIndex].textContent.trim();
        let bVal = b.cells[columnIndex].textContent.trim();
        
        if (type === 'num') {
            aVal = parseInt(aVal) || 0;
            bVal = parseInt(bVal) || 0;
            return isAsc ? aVal - bVal : bVal - aVal;
        } else {
            return isAsc ? aVal.localeCompare(bVal, 'ar') : bVal.localeCompare(aVal, 'ar');
        }
    });
    
    // إعادة ترتيب الصفوف
    rows.forEach(row => tbody.appendChild(row));
    
    // تحديث أيقونات الترتيب
    document.querySelectorAll('.sort-icon').forEach(icon => icon.textContent = '↕');
    const clickedIcon = table.querySelectorAll('th')[columnIndex].querySelector('.sort-icon');
    if (clickedIcon) {
        clickedIcon.textContent = isAsc ? '↑' : '↓';
    }
}
</script>

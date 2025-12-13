<?php $exportUrl = url('/export/categories'); ?>
<div class="page-header">
    <h2><span class="material-icons-round">category</span> إدارة التصنيفات</h2>
    <div class="header-actions">
        <?php include dirname(__DIR__) . '/partials/export_buttons.php'; ?>
        <button class="btn btn-primary" onclick="showAddModal()"><span class="material-icons-round">add</span> إضافة تصنيف</button>
    </div>
</div>

<div class="summary-bar">
    <div class="summary-item">
        <span class="material-icons-round" style="color:var(--primary)">category</span>
        <div><strong><?= count($categories) ?></strong><span>تصنيف</span></div>
    </div>
    <div class="summary-item">
        <span class="material-icons-round" style="color:var(--success)">inventory_2</span>
        <div><strong><?= array_sum(array_column($categories, 'products_count')) ?></strong><span>منتج</span></div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
            <div class="category-card">
                <div class="category-icon" style="background:<?= $cat['color'] ?? '#1e88e5' ?>">
                    <span class="material-icons-round"><?= $cat['icon'] ?: 'category' ?></span>
                </div>
                <div class="category-info">
                    <h4><?= $cat['name'] ?></h4>
                    <small><?= $cat['products_count'] ?? 0 ?> منتج</small>
                </div>
                <div class="category-actions">
                    <button class="btn btn-sm btn-secondary" onclick="editCategory(<?= htmlspecialchars(json_encode($cat)) ?>)"><span class="material-icons-round">edit</span></button>
                    <?php if (($cat['products_count'] ?? 0) == 0): ?>
                    <form method="POST" action="<?= url('/categories/' . $cat['id'] . '/delete') ?>" style="display:inline" onsubmit="return confirm('هل أنت متأكد؟')">
                        <button type="submit" class="btn btn-sm btn-danger"><span class="material-icons-round">delete</span></button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
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
.categories-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
.category-card { display: flex; align-items: center; gap: 15px; padding: 20px; background: var(--bg-main); border-radius: var(--radius); }
.category-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.category-icon .material-icons-round { color: white; font-size: 24px; }
.category-info { flex: 1; }
.category-info h4 { margin-bottom: 3px; }
.category-info small { color: var(--text-muted); }
.category-actions { display: flex; gap: 5px; }
.category-actions .btn { padding: 5px 8px; }
</style>

<script>
function showAddModal() {
    document.getElementById('modalTitle').textContent = 'إضافة تصنيف';
    document.getElementById('categoryForm').action = '<?= url('/categories') ?>';
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryDesc').value = '';
    document.getElementById('categoryIcon').value = '';
    document.getElementById('categoryColor').value = '#1e88e5';
    document.getElementById('categoryModal').classList.add('show');
}

function editCategory(cat) {
    document.getElementById('modalTitle').textContent = 'تعديل تصنيف';
    document.getElementById('categoryForm').action = '<?= url('/categories/') ?>' + cat.id;
    document.getElementById('categoryId').value = cat.id;
    document.getElementById('categoryName').value = cat.name;
    document.getElementById('categoryDesc').value = cat.description || '';
    document.getElementById('categoryIcon').value = cat.icon || '';
    document.getElementById('categoryColor').value = cat.color || '#1e88e5';
    document.getElementById('categoryModal').classList.add('show');
}

function closeModal() {
    document.getElementById('categoryModal').classList.remove('show');
}
</script>

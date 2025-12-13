<?php $exportUrl = url('/export/products'); ?>
<div class="page-header">
    <h2><span class="material-icons-round">inventory_2</span> إدارة المنتجات</h2>
    <div class="header-actions">
        <?php include dirname(__DIR__) . '/partials/export_buttons.php'; ?>
        <a href="<?= url('/products/create') ?>" class="btn btn-primary">
            <span class="material-icons-round">add</span> إضافة منتج
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="filters">
            <select name="category" class="form-control" style="width:200px" onchange="this.form.submit()">
                <option value="">كل التصنيفات</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>><?= $cat['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="q" class="form-control" style="width:250px" placeholder="بحث..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary"><span class="material-icons-round">search</span></button>
            <?php if ($search || $categoryId): ?>
            <a href="<?= url('/products') ?>" class="btn btn-secondary"><span class="material-icons-round">clear</span></a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body">
        <?php $selectionType = 'products'; $allowDelete = true; include dirname(__DIR__) . '/partials/table_selection.php'; ?>
        
        <div class="table-responsive">
            <table class="table" id="productsTable">
                <thead>
                    <tr>
                        <th class="select-checkbox"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
                        <th>#</th>
                        <th>الصورة</th>
                        <th>اسم المنتج</th>
                        <th>التصنيف</th>
                        <th>السعر نقدي</th>
                        <th>السعر تقسيط</th>
                        <th>الكمية</th>
                        <th>الحالة</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td class="select-checkbox"><input type="checkbox" class="row-checkbox" data-id="<?= $product['id'] ?>" onclick="toggleRowSelect(this)"></td>
                        <td><?= $product['id'] ?></td>
                        <td>
                            <div class="product-thumb">
                                <?php if ($product['image']): ?>
                                    <img src="<?= upload('products/' . $product['image']) ?>" alt="">
                                <?php else: ?>
                                    <span class="material-icons-round">inventory_2</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <strong><?= $product['name'] ?></strong>
                            <?php if ($product['barcode']): ?>
                            <br><small class="text-muted"><?= $product['barcode'] ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= $product['category_name'] ?? '-' ?></td>
                        <td><?= formatMoney($product['cash_price']) ?></td>
                        <td><?= formatMoney($product['installment_price']) ?></td>
                        <td>
                            <span class="<?= $product['quantity'] <= $product['min_quantity'] ? 'text-danger' : '' ?>">
                                <?= $product['quantity'] ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?= $product['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $product['is_active'] ? 'نشط' : 'معطل' ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="<?= url('/products/' . $product['id']) ?>" class="btn btn-sm btn-secondary" title="عرض">
                                    <span class="material-icons-round">visibility</span>
                                </a>
                                <a href="<?= url('/products/' . $product['id'] . '/edit') ?>" class="btn btn-sm btn-primary" title="تعديل">
                                    <span class="material-icons-round">edit</span>
                                </a>
                                <form method="POST" action="<?= url('/products/' . $product['id'] . '/delete') ?>" style="display:inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                    <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </form>
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

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}
.page-header h2 {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 22px;
}
.filters {
    display: flex;
    gap: 15px;
}
.product-thumb {
    width: 50px;
    height: 50px;
    background: var(--bg-main);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.product-thumb img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
.product-thumb .material-icons-round {
    color: var(--text-muted);
}
.actions {
    display: flex;
    gap: 5px;
}
.actions .btn {
    padding: 5px 8px;
}
.actions .material-icons-round {
    font-size: 18px;
}
</style>

<script>
function filterProducts() {
    const category = document.getElementById('categoryFilter').value;
    const search = document.getElementById('searchInput').value.toLowerCase();
    
    document.querySelectorAll('#productsTable tbody tr').forEach(row => {
        const matchCategory = !category || row.dataset.category === category;
        const text = row.textContent.toLowerCase();
        const matchSearch = !search || text.includes(search);
        
        row.style.display = matchCategory && matchSearch ? '' : 'none';
    });
}
</script>

<?php $exportUrl = url('/export/products?category=' . $category['id']); ?>
<div class="page-header">
    <h2><span class="material-icons-round">category</span> منتجات التصنيف: <?= htmlspecialchars($category['name']) ?></h2>
    <div class="header-actions">
        <a href="<?= url('/categories') ?>" class="btn btn-secondary"><span class="material-icons-round">arrow_back</span> رجوع</a>
    </div>
</div>

<!-- شريط التصنيف -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="POST" action="<?= url('/categories/' . $category['id'] . '/move-all') ?>" class="move-all-form">
            <div class="form-row" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <label style="white-space: nowrap; font-weight: 600;"><span class="material-icons-round" style="vertical-align: middle;">swap_horiz</span> نقل جميع المنتجات (<?= $totalCount ?? count($products) ?>) إلى:</label>
                    <select name="new_category_id" class="form-control" style="width: 200px;">
                        <option value="">-- بدون تصنيف --</option>
                        <?php foreach ($categories as $cat): ?>
                        <?php if ($cat['id'] != $category['id']): ?>
                        <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?> (<?= $cat['products_count'] ?>)</option>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" onclick="return confirm('هل أنت متأكد من نقل جميع المنتجات؟')">
                    <span class="material-icons-round">check</span> نقل الكل
                </button>
            </div>
        </form>
    </div>
</div>

<!-- قائمة المنتجات -->
<div class="card">
    <div class="card-header">
        <h3>المنتجات (<?= $totalCount ?? count($products) ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($products)): ?>
        <div class="empty-state">
            <span class="material-icons-round" style="font-size: 48px; color: var(--text-muted);">inventory_2</span>
            <p>لا توجد منتجات في هذا التصنيف</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table" id="productsTable">
                <thead>
                    <tr>
                        <th>اسم المنتج</th>
                        <th>السعر النقدي</th>
                        <th>سعر التقسيط</th>
                        <th>الكمية</th>
                        <th>الفواتير</th>
                        <th>التصنيف</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr id="product-row-<?= $product['id'] ?>">
                        <td>
                            <strong><?= htmlspecialchars($product['name']) ?></strong>
                            <?php if ($product['barcode']): ?>
                            <br><small class="text-muted"><?= $product['barcode'] ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= formatMoney($product['cash_price']) ?></td>
                        <td><?= formatMoney($product['installment_price']) ?></td>
                        <td>
                            <span class="badge badge-<?= $product['quantity'] <= $product['min_quantity'] ? 'danger' : 'success' ?>">
                                <?= $product['quantity'] ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($product['invoice_count'] > 0): ?>
                            <span class="badge badge-warning" title="مرتبط بفواتير"><?= $product['invoice_count'] ?></span>
                            <?php else: ?>
                            <span class="badge badge-secondary">0</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <select class="form-control form-control-sm category-select" 
                                    data-product-id="<?= $product['id'] ?>" 
                                    data-original-value="<?= $category['id'] ?>"
                                    onchange="moveProduct(this)">
                                <option value="">-- بدون تصنيف --</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $category['id'] ? 'selected' : '' ?>><?= $cat['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="<?= url('/products/' . $product['id'] . '/edit') ?>" class="btn btn-sm btn-warning" title="تعديل">
                                    <span class="material-icons-round">edit</span>
                                </a>
                                <?php if ($product['invoice_count'] == 0): ?>
                                <button type="button" class="btn btn-sm btn-danger" title="حذف" onclick="deleteProduct(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>')">
                                    <span class="material-icons-round">delete</span>
                                </button>
                                <?php else: ?>
                                <button type="button" class="btn btn-sm btn-secondary" title="لا يمكن الحذف - مرتبط بفواتير" disabled>
                                    <span class="material-icons-round">lock</span>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
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
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
.page-header h2 { display: flex; align-items: center; gap: 10px; font-size: 22px; }
.actions { display: flex; gap: 5px; }
.actions .btn { padding: 5px 8px; }
.actions .material-icons-round { font-size: 18px; }
.empty-state { text-align: center; padding: 40px; color: var(--text-muted); }
.category-select { min-width: 150px; }
.form-control-sm { padding: 5px 10px; font-size: 13px; }
</style>

<script>
function moveProduct(select) {
    const productId = select.dataset.productId;
    const newCategoryId = select.value;
    const originalValue = select.dataset.originalValue;
    
    if (newCategoryId === originalValue) {
        return;
    }
    
    fetch('<?= url('/categories/' . $category['id'] . '/move-product') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'product_id=' + productId + '&new_category_id=' + newCategoryId
    })
    .then(response => {
        // التحقق من حالة الـ response
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        return response.text();
    })
    .then(text => {
        // محاولة تحويل النص إلى JSON
        if (!text || text.trim() === '') {
            throw new Error('Empty response');
        }
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Response text:', text);
            throw new Error('Invalid JSON response');
        }
    })
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // إزالة الصف من الجدول
            const row = document.getElementById('product-row-' + productId);
            row.style.opacity = '0.5';
            setTimeout(() => {
                row.remove();
                updateProductCount();
            }, 500);
        } else {
            showToast(data.message || 'حدث خطأ', 'error');
            select.value = originalValue;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('حدث خطأ: ' + error.message, 'error');
        select.value = originalValue;
    });
}

function deleteProduct(productId, productName) {
    if (!confirm('هل أنت متأكد من حذف المنتج "' + productName + '"؟')) {
        return;
    }
    
    fetch('<?= url('/categories/' . $category['id'] . '/delete-product') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // إزالة الصف
            const row = document.getElementById('product-row-' + productId);
            row.style.opacity = '0.5';
            setTimeout(() => {
                row.remove();
                updateProductCount();
            }, 500);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('حدث خطأ في الاتصال', 'error');
    });
}

function updateProductCount() {
    const count = document.querySelectorAll('#productsTable tbody tr').length;
    document.querySelector('.card-header h3').textContent = 'المنتجات (' + count + ')';
}

// دالة إظهار التنبيهات
function showToast(message, type = 'success') {
    // إزالة أي توست سابق
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.className = 'toast-notification toast-' + type;
    toast.innerHTML = '<span class="material-icons-round">' + (type === 'success' ? 'check_circle' : 'error') + '</span> ' + message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>

<style>
.toast-notification {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(-100px);
    padding: 15px 25px;
    border-radius: 8px;
    color: white;
    font-weight: 500;
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    transition: transform 0.3s ease;
}
.toast-notification.show { transform: translateX(-50%) translateY(0); }
.toast-success { background: linear-gradient(135deg, #28a745, #20c997); }
.toast-error { background: linear-gradient(135deg, #dc3545, #e74c3c); }
</style>

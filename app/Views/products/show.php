<div class="page-header">
    <h2><span class="material-icons-round">inventory_2</span> <?= $product['name'] ?></h2>
    <div class="header-actions">
        <a href="<?= url('/products/' . $product['id'] . '/edit') ?>" class="btn btn-primary"><span class="material-icons-round">edit</span> تعديل</a>
        <a href="<?= url('/products') ?>" class="btn btn-secondary"><span class="material-icons-round">arrow_forward</span> رجوع</a>
    </div>
</div>

<div class="product-details">
    <div class="product-image-large">
        <?php if ($product['image']): ?>
            <img src="<?= upload('products/' . $product['image']) ?>" alt="<?= $product['name'] ?>">
        <?php else: ?>
            <span class="material-icons-round">inventory_2</span>
        <?php endif; ?>
    </div>
    
    <div class="product-info-card card">
        <div class="card-body">
            <h3><?= $product['name'] ?></h3>
            <p class="category-badge"><span class="badge badge-primary"><?= $product['category_name'] ?? 'بدون تصنيف' ?></span></p>
            
            <div class="price-box">
                <div class="price cash-price">
                    <span>السعر النقدي</span>
                    <strong><?= formatMoney($product['cash_price']) ?></strong>
                </div>
                <div class="price installment-price">
                    <span>سعر التقسيط</span>
                    <strong><?= formatMoney($product['installment_price']) ?></strong>
                </div>
            </div>
            
            <div class="info-list">
                <div class="info-item"><span>الباركود:</span><strong><?= $product['barcode'] ?: '-' ?></strong></div>
                <div class="info-item"><span>رقم الصنف:</span><strong><?= $product['sku'] ?: '-' ?></strong></div>
                <div class="info-item"><span>الماركة:</span><strong><?= $product['brand'] ?: '-' ?></strong></div>
                <div class="info-item"><span>الموديل:</span><strong><?= $product['model'] ?: '-' ?></strong></div>
                <div class="info-item"><span>الضمان:</span><strong><?= $product['warranty_months'] ? $product['warranty_months'] . ' شهر' : 'بدون ضمان' ?></strong></div>
                <div class="info-item"><span>سعر التكلفة:</span><strong><?= formatMoney($product['cost_price']) ?></strong></div>
            </div>
            
            <div class="stock-info">
                <div class="stock-current <?= $product['quantity'] <= $product['min_quantity'] ? 'low' : '' ?>">
                    <span>الكمية المتاحة</span>
                    <strong><?= $product['quantity'] ?></strong>
                </div>
                <div class="stock-min">
                    <span>حد التنبيه</span>
                    <strong><?= $product['min_quantity'] ?></strong>
                </div>
            </div>
            
            <?php if ($product['description']): ?>
            <div class="description">
                <h4>الوصف</h4>
                <p><?= nl2br($product['description']) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.product-details { display: grid; grid-template-columns: 400px 1fr; gap: 30px; }
.product-image-large { background: var(--bg-card); border-radius: var(--radius); padding: 30px; text-align: center; height: fit-content; }
.product-image-large img { max-width: 100%; max-height: 350px; object-fit: contain; }
.product-image-large .material-icons-round { font-size: 120px; color: var(--text-muted); }
.product-info-card h3 { font-size: 24px; margin-bottom: 10px; }
.category-badge { margin-bottom: 20px; }
.price-box { display: flex; gap: 20px; margin-bottom: 25px; }
.price { flex: 1; padding: 20px; border-radius: 12px; text-align: center; }
.cash-price { background: #e8f5e9; }
.cash-price strong { color: #2e7d32; }
.installment-price { background: #e3f2fd; }
.installment-price strong { color: #1565c0; }
.price span { display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 5px; }
.price strong { font-size: 24px; }
.info-list { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 25px; }
.info-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color); }
.info-item span { color: var(--text-muted); }
.stock-info { display: flex; gap: 20px; margin-bottom: 25px; }
.stock-current, .stock-min { flex: 1; padding: 15px; background: var(--bg-main); border-radius: 10px; text-align: center; }
.stock-current span, .stock-min span { display: block; font-size: 12px; color: var(--text-muted); }
.stock-current strong, .stock-min strong { font-size: 28px; }
.stock-current.low { background: #ffebee; }
.stock-current.low strong { color: var(--danger); }
.description h4 { margin-bottom: 10px; color: var(--text-muted); }
@media (max-width: 900px) { .product-details { grid-template-columns: 1fr; } }
</style>

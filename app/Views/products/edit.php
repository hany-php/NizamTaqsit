<div class="page-header">
    <h2><span class="material-icons-round">edit</span> تعديل: <?= $product['name'] ?></h2>
    <a href="<?= url('/products') ?>" class="btn btn-secondary">
        <span class="material-icons-round">arrow_forward</span> رجوع
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= url('/products/' . $product['id']) ?>" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label>اسم المنتج *</label>
                    <input type="text" name="name" class="form-control" value="<?= $product['name'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label>التصنيف</label>
                    <select name="category_id" class="form-control">
                        <option value="">اختر التصنيف</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>><?= $cat['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>الباركود</label>
                    <input type="text" name="barcode" class="form-control" value="<?= $product['barcode'] ?>">
                </div>
                
                <div class="form-group">
                    <label>رقم الصنف (SKU)</label>
                    <input type="text" name="sku" class="form-control" value="<?= $product['sku'] ?>">
                </div>
                
                <div class="form-group">
                    <label>السعر النقدي *</label>
                    <input type="number" name="cash_price" class="form-control" step="0.01" value="<?= $product['cash_price'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label>سعر التقسيط</label>
                    <input type="number" name="installment_price" class="form-control" step="0.01" value="<?= $product['installment_price'] ?>">
                </div>
                
                <div class="form-group">
                    <label>سعر التكلفة</label>
                    <input type="number" name="cost_price" class="form-control" step="0.01" value="<?= $product['cost_price'] ?>">
                </div>
                
                <div class="form-group">
                    <label>الكمية</label>
                    <input type="number" name="quantity" class="form-control" value="<?= $product['quantity'] ?>">
                </div>
                
                <div class="form-group">
                    <label>الحد الأدنى للتنبيه</label>
                    <input type="number" name="min_quantity" class="form-control" value="<?= $product['min_quantity'] ?>">
                </div>
                
                <div class="form-group">
                    <label>الماركة</label>
                    <input type="text" name="brand" class="form-control" value="<?= $product['brand'] ?>">
                </div>
                
                <div class="form-group">
                    <label>الموديل</label>
                    <input type="text" name="model" class="form-control" value="<?= $product['model'] ?>">
                </div>
                
                <div class="form-group">
                    <label>مدة الضمان (شهر)</label>
                    <input type="number" name="warranty_months" class="form-control" value="<?= $product['warranty_months'] ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>الوصف</label>
                <textarea name="description" class="form-control" rows="3"><?= $product['description'] ?></textarea>
            </div>
            
            <div class="form-group">
                <label>صورة المنتج</label>
                <?php if ($product['image']): ?>
                <div class="current-image">
                    <img src="<?= upload('products/' . $product['image']) ?>" alt="" style="max-height:100px;border-radius:8px">
                </div>
                <?php endif; ?>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1" <?= $product['is_active'] ? 'checked' : '' ?>>
                    منتج نشط
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons-round">save</span> حفظ التعديلات
                </button>
                <a href="<?= url('/products') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid var(--border-color);
}
.current-image {
    margin-bottom: 10px;
}
@media (max-width: 768px) {
    .form-grid { grid-template-columns: 1fr; }
}
</style>

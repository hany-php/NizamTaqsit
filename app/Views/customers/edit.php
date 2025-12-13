<div class="page-header">
    <h2><span class="material-icons-round">edit</span> تعديل بيانات: <?= $customer['full_name'] ?></h2>
    <a href="<?= url('/customers/' . $customer['id']) ?>" class="btn btn-secondary"><span class="material-icons-round">arrow_forward</span> رجوع</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= url('/customers/' . $customer['id']) ?>" enctype="multipart/form-data">
            <h4 class="section-title">البيانات الأساسية</h4>
            <div class="form-grid">
                <div class="form-group">
                    <label>الاسم الكامل *</label>
                    <input type="text" name="full_name" class="form-control" value="<?= $customer['full_name'] ?>" required>
                </div>
                <div class="form-group">
                    <label>رقم الهاتف *</label>
                    <input type="tel" name="phone" class="form-control" value="<?= $customer['phone'] ?>" required>
                </div>
                <div class="form-group">
                    <label>هاتف إضافي</label>
                    <input type="tel" name="phone2" class="form-control" value="<?= $customer['phone2'] ?>">
                </div>
                <div class="form-group">
                    <label>رقم الهوية *</label>
                    <input type="text" name="national_id" class="form-control" value="<?= $customer['national_id'] ?>" required>
                </div>
                <div class="form-group">
                    <label>صورة الهوية</label>
                    <?php if ($customer['national_id_image']): ?>
                    <div style="margin-bottom:10px"><img src="<?= upload('customers/' . $customer['national_id_image']) ?>" alt="" style="max-height:80px;border-radius:8px"></div>
                    <?php endif; ?>
                    <input type="file" name="national_id_image" class="form-control" accept="image/*">
                </div>
                <div class="form-group">
                    <label>الحد الائتماني</label>
                    <input type="number" name="credit_limit" class="form-control" value="<?= $customer['credit_limit'] ?>" step="0.01">
                </div>
            </div>

            <h4 class="section-title">العنوان</h4>
            <div class="form-grid">
                <div class="form-group" style="grid-column: span 2">
                    <label>العنوان</label>
                    <input type="text" name="address" class="form-control" value="<?= $customer['address'] ?>">
                </div>
                <div class="form-group">
                    <label>المدينة</label>
                    <input type="text" name="city" class="form-control" value="<?= $customer['city'] ?>">
                </div>
            </div>

            <h4 class="section-title">بيانات العمل</h4>
            <div class="form-grid">
                <div class="form-group">
                    <label>عنوان العمل</label>
                    <input type="text" name="work_address" class="form-control" value="<?= $customer['work_address'] ?>">
                </div>
                <div class="form-group">
                    <label>هاتف العمل</label>
                    <input type="tel" name="work_phone" class="form-control" value="<?= $customer['work_phone'] ?>">
                </div>
            </div>

            <h4 class="section-title">بيانات الضامن</h4>
            <div class="form-grid">
                <div class="form-group">
                    <label>اسم الضامن</label>
                    <input type="text" name="guarantor_name" class="form-control" value="<?= $customer['guarantor_name'] ?>">
                </div>
                <div class="form-group">
                    <label>هاتف الضامن</label>
                    <input type="tel" name="guarantor_phone" class="form-control" value="<?= $customer['guarantor_phone'] ?>">
                </div>
                <div class="form-group">
                    <label>هوية الضامن</label>
                    <input type="text" name="guarantor_national_id" class="form-control" value="<?= $customer['guarantor_national_id'] ?>">
                </div>
            </div>

            <div class="form-group">
                <label>ملاحظات</label>
                <textarea name="notes" class="form-control" rows="3"><?= $customer['notes'] ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><span class="material-icons-round">save</span> حفظ التعديلات</button>
                <a href="<?= url('/customers/' . $customer['id']) ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<style>
.section-title { margin: 25px 0 15px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color); color: var(--primary); }
.form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.form-actions { display: flex; gap: 15px; margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color); }
@media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }
</style>

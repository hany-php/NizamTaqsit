<div class="page-header">
    <h2><span class="material-icons-round">edit</span> تعديل مستخدم: <?= $user['full_name'] ?></h2>
    <a href="<?= url('/users') ?>" class="btn btn-secondary"><span class="material-icons-round">arrow_forward</span> رجوع</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= url('/users/' . $user['id']) ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label>الاسم الكامل *</label>
                    <input type="text" name="full_name" class="form-control" value="<?= $user['full_name'] ?>" required>
                </div>
                <div class="form-group">
                    <label>اسم المستخدم *</label>
                    <input type="text" name="username" class="form-control" value="<?= $user['username'] ?>" required>
                </div>
                <div class="form-group">
                    <label>كلمة مرور جديدة</label>
                    <input type="password" name="password" class="form-control" minlength="6" placeholder="اتركه فارغاً للإبقاء على الحالية">
                </div>
                <div class="form-group">
                    <label>رقم الهاتف</label>
                    <input type="tel" name="phone" class="form-control" value="<?= $user['phone'] ?>">
                </div>
                <div class="form-group">
                    <label>الدور</label>
                    <select name="role" class="form-control">
                        <option value="sales" <?= $user['role'] === 'sales' ? 'selected' : '' ?>>موظف مبيعات</option>
                        <option value="accountant" <?= $user['role'] === 'accountant' ? 'selected' : '' ?>>محاسب</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>مدير</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" <?= $user['is_active'] ? 'checked' : '' ?>>
                        حساب نشط
                    </label>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><span class="material-icons-round">save</span> حفظ التعديلات</button>
                <a href="<?= url('/users') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
.form-actions { margin-top: 25px; display: flex; gap: 15px; }
.checkbox-label { display: flex; align-items: center; gap: 10px; cursor: pointer; margin-top: 30px; }
.checkbox-label input { width: 18px; height: 18px; }
</style>

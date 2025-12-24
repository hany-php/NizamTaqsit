<div class="page-header">
    <h2><span class="material-icons-round">credit_score</span> خطط التقسيط</h2>
    <div>
        <button class="btn btn-primary" onclick="showAddModal()"><span class="material-icons-round">add</span> إضافة خطة</button>
        <a href="<?= url('/settings') ?>" class="btn btn-secondary"><span class="material-icons-round">arrow_forward</span> رجوع</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>اسم الخطة</th>
                        <th>عدد الأشهر</th>
                        <th>نسبة الزيادة</th>
                        <th>الحد الأدنى للمقدم</th>
                        <th>الحالة</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plans as $plan): ?>
                    <tr>
                        <td><strong><?= $plan['name'] ?></strong></td>
                        <td><?= $plan['months'] ?> شهر</td>
                        <td><?= $plan['increase_percent'] ?>%</td>
                        <td><?= $plan['min_down_payment_percent'] ?>%</td>
                        <td><span class="badge badge-<?= $plan['is_active'] ? 'success' : 'secondary' ?>"><?= $plan['is_active'] ? 'نشط' : 'معطل' ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick='editPlan(<?= json_encode($plan) ?>)'><span class="material-icons-round">edit</span></button>
                            <button class="btn btn-sm btn-danger" onclick='deletePlan(<?= $plan["id"] ?>, "<?= htmlspecialchars($plan["name"], ENT_QUOTES) ?>")'><span class="material-icons-round">delete</span></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal" id="planModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">إضافة خطة تقسيط</h3>
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="planForm" method="POST">
                <input type="hidden" id="planId" name="id">
                <div class="form-group">
                    <label>اسم الخطة *</label>
                    <input type="text" id="planName" name="name" class="form-control" required placeholder="مثال: 12 شهر">
                </div>
                <div class="form-group">
                    <label>عدد الأشهر *</label>
                    <input type="number" id="planMonths" name="months" class="form-control" min="1" max="60" required>
                </div>
                <div class="form-group">
                    <label>نسبة الزيادة % *</label>
                    <input type="number" id="planIncrease" name="increase_percent" class="form-control" step="0.01" min="0" required>
                    <small>مثال: 15 يعني 15% زيادة على السعر النقدي</small>
                </div>
                <div class="form-group">
                    <label>الحد الأدنى للدفعة المقدمة %</label>
                    <input type="number" id="planMinDown" name="min_down_payment_percent" class="form-control" step="0.01" min="0" value="0">
                    <small>مثال: 20 يعني 20% كحد أدنى للمقدم</small>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="planActive" name="is_active" value="1" checked>
                        خطة نشطة
                    </label>
                </div>
                <button type="submit" class="btn btn-primary btn-block">حفظ</button>
            </form>
        </div>
    </div>
</div>

<style>.checkbox-label { display: flex; align-items: center; gap: 10px; cursor: pointer; } .checkbox-label input { width: 18px; height: 18px; }</style>

<script>
function showAddModal() {
    document.getElementById('modalTitle').textContent = 'إضافة خطة تقسيط';
    document.getElementById('planForm').action = '<?= url('/settings/installment') ?>';
    document.getElementById('planId').value = '';
    document.getElementById('planName').value = '';
    document.getElementById('planMonths').value = '';
    document.getElementById('planIncrease').value = '';
    document.getElementById('planMinDown').value = '0';
    document.getElementById('planActive').checked = true;
    document.getElementById('planModal').classList.add('show');
}

function editPlan(plan) {
    document.getElementById('modalTitle').textContent = 'تعديل خطة تقسيط';
    document.getElementById('planForm').action = '<?= url('/settings/installment/') ?>' + plan.id;
    document.getElementById('planId').value = plan.id;
    document.getElementById('planName').value = plan.name;
    document.getElementById('planMonths').value = plan.months;
    document.getElementById('planIncrease').value = plan.increase_percent;
    document.getElementById('planMinDown').value = plan.min_down_payment_percent;
    document.getElementById('planActive').checked = plan.is_active == 1;
    document.getElementById('planModal').classList.add('show');
}

function closeModal() { document.getElementById('planModal').classList.remove('show'); }

function deletePlan(id, name) {
    if (confirm('هل أنت متأكد من حذف خطة التقسيط "' + name + '"؟')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= url('/settings/installment/') ?>' + id + '/delete';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

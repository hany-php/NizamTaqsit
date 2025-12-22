<div class="page-header">
    <h2><span class="material-icons-round">api</span> إدارة مفاتيح API</h2>
    <button class="btn btn-primary" onclick="showAddModal()">
        <span class="material-icons-round">add</span> إنشاء مفتاح جديد
    </button>
</div>

<!-- معلومات عن API -->
<div class="card" style="margin-bottom: 25px;">
    <div class="card-header"><h3><span class="material-icons-round">info</span> معلومات استخدام API</h3></div>
    <div class="card-body">
        <div class="api-info">
            <?php 
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
            ?>
            <p><strong>رابط API:</strong> <code><?= $scheme . '://' . $host ?>/api/v2/</code></p>
            <p><strong>طريقة المصادقة:</strong> أضف المفتاح في Header الطلب:</p>
            <pre style="background: var(--bg-main); padding: 15px; border-radius: 8px; direction: ltr; text-align: left;">X-API-KEY: your_api_key_here</pre>
            
            <div style="margin-top: 20px;">
                <h4 style="margin-bottom: 10px;">نقاط النهاية المتاحة:</h4>
                <div class="endpoints-grid">
                    <div class="endpoint-group">
                        <h5>المنتجات</h5>
                        <code>GET /api/v2/products</code>
                        <code>POST /api/v2/products</code>
                        <code>PUT /api/v2/products/{id}</code>
                        <code>DELETE /api/v2/products/{id}</code>
                    </div>
                    <div class="endpoint-group">
                        <h5>التصنيفات</h5>
                        <code>GET /api/v2/categories</code>
                        <code>POST /api/v2/categories</code>
                        <code>PUT /api/v2/categories/{id}</code>
                        <code>DELETE /api/v2/categories/{id}</code>
                    </div>
                    <div class="endpoint-group">
                        <h5>العملاء</h5>
                        <code>GET /api/v2/customers</code>
                        <code>POST /api/v2/customers</code>
                        <code>PUT /api/v2/customers/{id}</code>
                        <code>DELETE /api/v2/customers/{id}</code>
                    </div>
                    <div class="endpoint-group">
                        <h5>الفواتير والأقساط</h5>
                        <code>GET /api/v2/invoices</code>
                        <code>GET /api/v2/installments</code>
                        <code>GET /api/v2/installments/today</code>
                        <code>POST /api/v2/installments/{id}/pay</code>
                    </div>
                    <div class="endpoint-group">
                        <h5>لوحة التحكم</h5>
                        <code>GET /api/v2/dashboard/stats</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- قائمة المفاتيح -->
<div class="card">
    <div class="card-header"><h3><span class="material-icons-round">vpn_key</span> مفاتيح API الحالية</h3></div>
    <div class="card-body">
        <?php if (empty($apiKeys)): ?>
            <div class="empty-state">
                <span class="material-icons-round" style="font-size: 64px; color: var(--text-muted);">key_off</span>
                <p>لا توجد مفاتيح API حالياً</p>
                <button class="btn btn-primary" onclick="showAddModal()">إنشاء مفتاح جديد</button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" id="apiKeysTable">
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>المفتاح</th>
                            <th>الحالة</th>
                            <th>تاريخ الانتهاء</th>
                            <th>آخر استخدام</th>
                            <th>تاريخ الإنشاء</th>
                            <th>أنشأه</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($apiKeys as $key): ?>
                        <tr id="key-row-<?= $key['id'] ?>" data-key='<?= htmlspecialchars(json_encode($key), ENT_QUOTES) ?>'>
                            <td>
                                <span class="key-name"><?= htmlspecialchars($key['name']) ?></span>
                            </td>
                            <td>
                                <div class="api-key-display">
                                    <code class="api-key-masked"><?= substr($key['api_key'], 0, 8) ?>...<?= substr($key['api_key'], -4) ?></code>
                                    <button class="btn btn-sm btn-outline" onclick="copyApiKey('<?= $key['api_key'] ?>')" title="نسخ">
                                        <span class="material-icons-round">content_copy</span>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $key['is_active'] ? 'badge-success' : 'badge-danger' ?>" id="status-<?= $key['id'] ?>">
                                    <?= $key['is_active'] ? 'مفعّل' : 'موقوف' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($key['expires_at']): ?>
                                    <?php 
                                    $expiresDate = strtotime($key['expires_at']);
                                    $isExpired = $expiresDate < time();
                                    ?>
                                    <span class="<?= $isExpired ? 'text-danger' : '' ?>">
                                        <?= date('Y-m-d', $expiresDate) ?>
                                        <?= $isExpired ? '<small>(منتهي)</small>' : '' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">غير محدد</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $key['last_used_at'] ? date('Y-m-d H:i', strtotime($key['last_used_at'])) : 'لم يُستخدم' ?></td>
                            <td><?= date('Y-m-d', strtotime($key['created_at'])) ?></td>
                            <td><?= htmlspecialchars($key['created_by_name'] ?? '-') ?></td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-secondary" onclick="editKey(<?= $key['id'] ?>)" title="تعديل">
                                        <span class="material-icons-round">edit</span>
                                    </button>
                                    <button class="btn btn-sm <?= $key['is_active'] ? 'btn-warning' : 'btn-success' ?>" 
                                            onclick="toggleKey(<?= $key['id'] ?>)"
                                            id="toggle-btn-<?= $key['id'] ?>"
                                            title="<?= $key['is_active'] ? 'إيقاف' : 'تفعيل' ?>">
                                        <span class="material-icons-round"><?= $key['is_active'] ? 'pause' : 'play_arrow' ?></span>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="showDeleteModal(<?= $key['id'] ?>, '<?= htmlspecialchars($key['name'], ENT_QUOTES) ?>')" title="حذف">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal إضافة/تعديل مفتاح -->
<div id="keyModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle"><span class="material-icons-round">add</span> إنشاء مفتاح API جديد</h3>
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="keyForm">
                <input type="hidden" id="keyId" name="id">
                <div class="form-group">
                    <label>اسم المفتاح *</label>
                    <input type="text" id="keyName" name="name" class="form-control" placeholder="مثال: تطبيق الموبايل" required>
                </div>
                <div class="form-group" id="apiKeyDisplayGroup" style="display: none;">
                    <label>مفتاح API</label>
                    <div class="api-key-edit-display">
                        <input type="password" id="editApiKey" class="form-control" readonly style="direction: ltr; font-family: monospace;">
                        <button type="button" class="btn btn-outline" onclick="toggleApiKeyVisibility()" title="إظهار/إخفاء">
                            <span class="material-icons-round" id="toggleKeyIcon">visibility</span>
                        </button>
                        <button type="button" class="btn btn-primary" onclick="copyEditApiKey()" title="نسخ">
                            <span class="material-icons-round">content_copy</span>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label>تاريخ انتهاء الصلاحية (اختياري)</label>
                    <input type="date" id="keyExpires" name="expires_at" class="form-control">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal()">إلغاء</button>
            <button class="btn btn-primary" id="submitBtn" onclick="submitForm()">
                <span class="material-icons-round">save</span> حفظ
            </button>
        </div>
    </div>
</div>

<!-- Modal عرض المفتاح الجديد -->
<div id="newKeyModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><span class="material-icons-round">check_circle</span> تم إنشاء المفتاح بنجاح</h3>
        </div>
        <div class="modal-body">
            <div class="alert alert-warning">
                <span class="material-icons-round">warning</span>
                <strong>تنبيه:</strong> هذه هي المرة الوحيدة التي سترى فيها المفتاح كاملاً. احفظه في مكان آمن!
            </div>
            <div class="form-group">
                <label>مفتاح API</label>
                <div class="input-with-copy">
                    <input type="text" id="newApiKey" class="form-control" readonly style="direction: ltr;">
                    <button class="btn btn-primary" onclick="copyNewKey()">
                        <span class="material-icons-round">content_copy</span> نسخ
                    </button>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" onclick="closeNewKeyModal()">تم</button>
        </div>
    </div>
</div>

<!-- Modal تأكيد الحذف -->
<div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width:400px">
        <div class="modal-header">
            <h3><span class="material-icons-round">warning</span> تأكيد الحذف</h3>
            <button class="close-btn" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p id="deleteMessage">هل أنت متأكد من حذف هذا المفتاح؟</p>
            <div class="alert alert-danger" style="margin-top:15px">
                <span class="material-icons-round">info</span>
                <span>التطبيقات التي تستخدم هذا المفتاح ستتوقف عن العمل</span>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeDeleteModal()">إلغاء</button>
            <button class="btn btn-danger" id="confirmDeleteBtn" onclick="confirmDeleteApiKey()">
                <span class="material-icons-round">delete</span> حذف
            </button>
        </div>
    </div>
</div>

<style>
.api-info code {
    background: var(--bg-main);
    padding: 4px 8px;
    border-radius: 4px;
    font-family: monospace;
}

.endpoints-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.endpoint-group {
    background: var(--bg-main);
    padding: 12px;
    border-radius: 8px;
}

.endpoint-group h5 {
    margin-bottom: 8px;
    color: var(--primary);
    font-size: 14px;
}

.endpoint-group code {
    display: block;
    margin: 4px 0;
    font-size: 11px;
    direction: ltr;
    text-align: left;
}

.api-key-display {
    display: flex;
    align-items: center;
    gap: 8px;
}

.api-key-masked {
    font-family: monospace;
    font-size: 12px;
}

.api-key-edit-display {
    display: flex;
    gap: 8px;
    align-items: center;
}

.api-key-edit-display input {
    flex: 1;
}

.input-with-copy {
    display: flex;
    gap: 10px;
}

.input-with-copy input {
    flex: 1;
    font-family: monospace;
}

.empty-state {
    text-align: center;
    padding: 40px;
}

.btn-group {
    display: flex;
    gap: 5px;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.modal.show {
    display: flex;
    opacity: 1;
}

.modal-content {
    background: var(--bg-card);
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: var(--shadow-lg);
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
}

.modal-header h3 {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}

.close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--text-muted);
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 20px;
    border-top: 1px solid var(--border-color);
}

.alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    border-radius: 8px;
}

.alert-warning {
    background: rgba(255, 152, 0, 0.1);
    color: #f57c00;
}

.alert-danger {
    background: rgba(244, 67, 54, 0.1);
    color: #d32f2f;
}
</style>

<script>
let currentKeyId = null;
let isEditMode = false;

function showAddModal() {
    document.getElementById('modalTitle').innerHTML = '<span class="material-icons-round">add</span> إنشاء مفتاح API جديد';
    document.getElementById('keyId').value = '';
    document.getElementById('keyName').value = '';
    document.getElementById('keyExpires').value = '';
    document.getElementById('submitBtn').innerHTML = '<span class="material-icons-round">vpn_key</span> إنشاء المفتاح';
    // إخفاء حقل المفتاح عند الإضافة
    document.getElementById('apiKeyDisplayGroup').style.display = 'none';
    currentKeyId = null;
    isEditMode = false;
    document.getElementById('keyModal').classList.add('show');
}

function editKey(id) {
    const row = document.getElementById('key-row-' + id);
    const keyData = JSON.parse(row.dataset.key);
    
    document.getElementById('modalTitle').innerHTML = '<span class="material-icons-round">edit</span> تعديل مفتاح API';
    document.getElementById('keyId').value = keyData.id;
    document.getElementById('keyName').value = keyData.name;
    document.getElementById('keyExpires').value = keyData.expires_at || '';
    document.getElementById('submitBtn').innerHTML = '<span class="material-icons-round">save</span> حفظ التعديلات';
    
    // إظهار حقل المفتاح عند التعديل
    document.getElementById('apiKeyDisplayGroup').style.display = 'block';
    document.getElementById('editApiKey').value = keyData.api_key;
    document.getElementById('editApiKey').type = 'password'; // مخفي افتراضياً
    document.getElementById('toggleKeyIcon').textContent = 'visibility';
    
    currentKeyId = id;
    isEditMode = true;
    document.getElementById('keyModal').classList.add('show');
}

function toggleApiKeyVisibility() {
    const input = document.getElementById('editApiKey');
    const icon = document.getElementById('toggleKeyIcon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = 'visibility_off';
    } else {
        input.type = 'password';
        icon.textContent = 'visibility';
    }
}

function copyEditApiKey() {
    const key = document.getElementById('editApiKey').value;
    navigator.clipboard.writeText(key).then(() => {
        showToast('تم نسخ المفتاح');
    });
}

function closeModal() {
    document.getElementById('keyModal').classList.remove('show');
    // إعادة إخفاء المفتاح عند إغلاق النافذة
    document.getElementById('editApiKey').type = 'password';
    document.getElementById('toggleKeyIcon').textContent = 'visibility';
}

function closeNewKeyModal() {
    document.getElementById('newKeyModal').classList.remove('show');
    location.reload(); // تحديث الصفحة لإظهار المفتاح الجديد
}

function submitForm() {
    const form = document.getElementById('keyForm');
    const formData = new FormData(form);
    const btn = document.getElementById('submitBtn');
    
    const name = document.getElementById('keyName').value.trim();
    if (!name) {
        alert('اسم المفتاح مطلوب');
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<span class="material-icons-round">hourglass_empty</span> جاري الحفظ...';
    
    const url = isEditMode 
        ? '<?= url('/settings/api/') ?>' + currentKeyId + '/update'
        : '<?= url('/settings/api/generate') ?>';
    
    console.log('Submit URL:', url);
    console.log('Edit mode:', isEditMode);
    console.log('Current Key ID:', currentKeyId);
    
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
        btn.disabled = false;
        btn.innerHTML = isEditMode 
            ? '<span class="material-icons-round">save</span> حفظ التعديلات'
            : '<span class="material-icons-round">vpn_key</span> إنشاء المفتاح';
        
        if (data.success) {
            closeModal();
            
            if (isEditMode) {
                // تحديث بعد التعديل
                showToast('تم تحديث المفتاح بنجاح');
                setTimeout(() => location.reload(), 500);
            } else {
                // عرض المفتاح الجديد
                document.getElementById('newApiKey').value = data.api_key;
                document.getElementById('newKeyModal').classList.add('show');
            }
        } else {
            alert(data.message || 'حدث خطأ');
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = isEditMode 
            ? '<span class="material-icons-round">save</span> حفظ التعديلات'
            : '<span class="material-icons-round">vpn_key</span> إنشاء المفتاح';
        console.error('Error:', error);
        alert('حدث خطأ: ' + error.message);
    });
}

function showDeleteModal(id, name) {
    currentKeyId = id;
    document.getElementById('deleteMessage').textContent = 'هل أنت متأكد من حذف مفتاح "' + name + '"؟';
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

function confirmDeleteApiKey() {
    if (!currentKeyId) {
        console.error('No key ID set');
        return;
    }
    
    const btn = document.getElementById('confirmDeleteBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-icons-round">hourglass_empty</span> جاري الحذف...';
    
    const deleteUrl = '<?= url('/settings/api/') ?>' + currentKeyId + '/delete';
    console.log('Delete URL:', deleteUrl);
    
    fetch(deleteUrl, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error response:', text);
                throw new Error('HTTP ' + response.status);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            closeDeleteModal();
            showToast('تم حذف المفتاح بنجاح');
            setTimeout(() => location.reload(), 500);
        } else {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-icons-round">delete</span> حذف';
            alert(data.message || 'حدث خطأ');
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-icons-round">delete</span> حذف';
        console.error('Fetch Error:', error);
        alert('حدث خطأ: ' + error.message);
    });
}

function toggleKey(id) {
    const btn = document.getElementById('toggle-btn-' + id);
    if (!btn) {
        console.error('Toggle button not found for id:', id);
        return;
    }
    btn.disabled = true;
    
    const toggleUrl = '<?= url('/settings/api/') ?>' + id + '/toggle';
    console.log('Toggle URL:', toggleUrl);
    
    fetch(toggleUrl, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => {
        console.log('Toggle response status:', response.status);
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Toggle error response:', text);
                throw new Error('HTTP ' + response.status);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Toggle response data:', data);
        btn.disabled = false;
        
        if (data.success) {
            const statusBadge = document.getElementById('status-' + id);
            
            if (data.is_active) {
                statusBadge.className = 'badge badge-success';
                statusBadge.textContent = 'مفعّل';
                btn.className = 'btn btn-sm btn-warning';
                btn.title = 'إيقاف';
                btn.innerHTML = '<span class="material-icons-round">pause</span>';
            } else {
                statusBadge.className = 'badge badge-danger';
                statusBadge.textContent = 'موقوف';
                btn.className = 'btn btn-sm btn-success';
                btn.title = 'تفعيل';
                btn.innerHTML = '<span class="material-icons-round">play_arrow</span>';
            }
            
            showToast(data.message);
        } else {
            alert(data.message || 'حدث خطأ');
        }
    })
    .catch(error => {
        btn.disabled = false;
        console.error('Toggle Fetch Error:', error);
        alert('حدث خطأ: ' + error.message);
    });
}

function updateTableRow(key) {
    const row = document.getElementById('key-row-' + key.id);
    if (row) {
        row.querySelector('.key-name').textContent = key.name;
        // تحديث data attribute
        const oldData = JSON.parse(row.dataset.key);
        oldData.name = key.name;
        if (key.expires_at) oldData.expires_at = key.expires_at;
        row.dataset.key = JSON.stringify(oldData);
    }
}

function copyApiKey(key) {
    navigator.clipboard.writeText(key).then(() => {
        showToast('تم نسخ المفتاح');
    });
}

function copyNewKey() {
    const key = document.getElementById('newApiKey').value;
    navigator.clipboard.writeText(key).then(() => {
        showToast('تم نسخ المفتاح');
    });
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    toast.style.cssText = 'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#333;color:#fff;padding:12px 24px;border-radius:8px;z-index:9999;';
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>

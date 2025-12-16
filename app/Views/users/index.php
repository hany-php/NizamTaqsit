<?php $exportUrl = url('/export/users'); ?>
<div class="page-header">
    <h2><span class="material-icons-round">people</span> إدارة المستخدمين</h2>
    <div class="header-actions">
        <?php include dirname(__DIR__) . '/partials/export_buttons.php'; ?>
        <a href="<?= url('/users/create') ?>" class="btn btn-primary"><span class="material-icons-round">add</span> إضافة مستخدم</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php $selectionType = 'users'; $allowDelete = true; include dirname(__DIR__) . '/partials/table_selection.php'; ?>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th class="select-checkbox"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
                        <th>الاسم</th>
                        <th>اسم المستخدم</th>
                        <th>الهاتف</th>
                        <th>الدور</th>
                        <th>الحالة</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="select-checkbox"><input type="checkbox" class="row-checkbox" data-id="<?= $user['id'] ?>" onclick="toggleRowSelect(this)"></td>
                        <td><strong><?= $user['full_name'] ?></strong></td>
                        <td><?= $user['username'] ?></td>
                        <td><?= $user['phone'] ?? '-' ?></td>
                        <td><span class="badge badge-<?= $user['role'] === 'admin' ? 'primary' : 'secondary' ?>"><?= userRole($user['role']) ?></span></td>
                        <td><span class="badge badge-<?= $user['is_active'] ? 'success' : 'danger' ?>"><?= $user['is_active'] ? 'نشط' : 'معطل' ?></span></td>
                        <td>
                            <a href="<?= url('/users/' . $user['id'] . '/edit') ?>" class="btn btn-sm btn-secondary"><span class="material-icons-round">edit</span></a>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" action="<?= url('/users/' . $user['id'] . '/delete') ?>" style="display:inline" onsubmit="return confirm('هل أنت متأكد؟')">
                                <button type="submit" class="btn btn-sm btn-danger"><span class="material-icons-round">delete</span></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php include dirname(__DIR__) . '/partials/pagination.php'; ?>
    </div>
</div>


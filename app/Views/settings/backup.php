<div class="page-header">
    <h2><span class="material-icons-round">backup</span> النسخ الاحتياطي</h2>
    <a href="<?= url('/settings') ?>" class="btn btn-secondary"><span class="material-icons-round">arrow_forward</span> رجوع</a>
</div>

<div class="backup-actions">
    <form method="POST" action="<?= url('/settings/backup/create') ?>" style="display:inline">
        <button type="submit" class="btn btn-primary btn-lg">
            <span class="material-icons-round">cloud_upload</span>
            إنشاء نسخة احتياطية
        </button>
    </form>
</div>

<div class="card" style="margin-top:25px">
    <div class="card-header">
        <h3><span class="material-icons-round">history</span> النسخ الاحتياطية المحفوظة</h3>
    </div>
    <div class="card-body">
        <?php if (empty($backups)): ?>
        <p class="empty-message">لا توجد نسخ احتياطية محفوظة</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>الملف</th><th>الحجم</th><th>التاريخ</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($backups as $backup): ?>
                    <tr>
                        <td><strong><?= $backup['filename'] ?></strong></td>
                        <td><?= number_format($backup['size'] / 1024, 2) ?> KB</td>
                        <td><?= date('Y-m-d H:i', $backup['date']) ?></td>
                        <td>
                            <a href="<?= url('/settings/backup/download/' . $backup['filename']) ?>" class="btn btn-sm btn-primary"><span class="material-icons-round">download</span></a>
                            <form method="POST" action="<?= url('/settings/backup/delete/' . $backup['filename']) ?>" style="display:inline" onsubmit="return confirm('هل أنت متأكد؟')">
                                <button type="submit" class="btn btn-sm btn-danger"><span class="material-icons-round">delete</span></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-top:25px">
    <div class="card-header">
        <h3><span class="material-icons-round">restore</span> استعادة نسخة احتياطية</h3>
    </div>
    <div class="card-body">
        <div class="alert alert-warning">
            <span class="material-icons-round">warning</span>
            تحذير: استعادة نسخة احتياطية ستستبدل جميع البيانات الحالية!
        </div>
        <form method="POST" action="<?= url('/settings/backup/restore') ?>" enctype="multipart/form-data">
            <div class="form-group">
                <label>اختر ملف النسخة الاحتياطية</label>
                <input type="file" name="backup" class="form-control" accept=".sqlite" required>
            </div>
            <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من استعادة هذه النسخة؟ سيتم استبدال جميع البيانات الحالية.')">
                <span class="material-icons-round">restore</span> استعادة
            </button>
        </form>
    </div>
</div>

<style>
.backup-actions { text-align: center; padding: 30px; background: var(--bg-card); border-radius: var(--radius); }
.btn-lg { padding: 15px 30px; font-size: 16px; }
</style>

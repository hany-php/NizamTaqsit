<div class="page-header">
    <h2><span class="material-icons-round">sms</span> إعدادات الرسائل القصيرة</h2>
    <a href="<?= url('/settings') ?>" class="btn btn-secondary"><span class="material-icons-round">arrow_forward</span> رجوع</a>
</div>

<div class="sms-actions">
    <form method="POST" action="<?= url('/settings/sms/reminders') ?>" style="display:inline">
        <button type="submit" class="btn btn-primary"><span class="material-icons-round">schedule_send</span> إرسال تذكيرات الغد</button>
    </form>
    <form method="POST" action="<?= url('/settings/sms/overdue') ?>" style="display:inline">
        <button type="submit" class="btn btn-danger"><span class="material-icons-round">warning</span> إرسال تنبيهات التأخر</button>
    </form>
</div>

<div class="card">
    <div class="card-header"><h3>إعدادات المزود</h3></div>
    <div class="card-body">
        <form method="POST" action="<?= url('/settings/sms/save') ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label>مزود الخدمة</label>
                    <select name="sms_provider" class="form-control">
                        <option value="">تجريبي (لا يرسل)</option>
                        <option value="twilio" <?= ($settings['sms_provider'] ?? '') === 'twilio' ? 'selected' : '' ?>>Twilio</option>
                        <option value="msegat" <?= ($settings['sms_provider'] ?? '') === 'msegat' ? 'selected' : '' ?>>Msegat (عربي)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>API Key</label>
                    <input type="text" name="sms_api_key" class="form-control" value="<?= $settings['sms_api_key'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Auth Token (للـ Twilio)</label>
                    <input type="text" name="sms_auth_token" class="form-control" value="<?= $settings['sms_auth_token'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Username (للـ Msegat)</label>
                    <input type="text" name="sms_username" class="form-control" value="<?= $settings['sms_username'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>معرف المرسل (Sender ID)</label>
                    <input type="text" name="sms_sender_id" class="form-control" value="<?= $settings['sms_sender_id'] ?? '' ?>">
                </div>
            </div>

            <h4 style="margin:25px 0 15px">قوالب الرسائل</h4>
            <div class="form-group">
                <label>قالب التذكير (المتغيرات: {customer}, {number}, {amount}, {date})</label>
                <textarea name="sms_template_reminder" class="form-control" rows="3"><?= $settings['sms_template_reminder'] ?? 'عزيزي {customer}، نذكرك بموعد استحقاق القسط رقم {number} بمبلغ {amount} بتاريخ {date}.' ?></textarea>
            </div>
            <div class="form-group">
                <label>قالب التأخر (المتغيرات: {customer}, {number}, {amount}, {days})</label>
                <textarea name="sms_template_overdue" class="form-control" rows="3"><?= $settings['sms_template_overdue'] ?? 'عزيزي {customer}، القسط رقم {number} بمبلغ {amount} متأخر عن السداد. يرجى المبادرة بالسداد.' ?></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary"><span class="material-icons-round">save</span> حفظ الإعدادات</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>إرسال رسالة تجريبية</h3></div>
    <div class="card-body">
        <form method="POST" action="<?= url('/settings/sms/test') ?>" class="test-form">
            <div class="form-group">
                <label>رقم الهاتف</label>
                <input type="tel" name="phone" class="form-control" placeholder="01xxxxxxxxx" required>
            </div>
            <div class="form-group">
                <label>الرسالة</label>
                <textarea name="message" class="form-control" rows="2">هذه رسالة تجريبية من نظام تقسيط</textarea>
            </div>
            <button type="submit" class="btn btn-secondary"><span class="material-icons-round">send</span> إرسال تجريبي</button>
        </form>
    </div>
</div>

<?php if (!empty($logs)): ?>
<div class="card">
    <div class="card-header"><h3>سجل الرسائل</h3></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>الهاتف</th><th>الرسالة</th><th>الحالة</th><th>التاريخ</th></tr></thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= $log['phone'] ?></td>
                        <td><small><?= mb_substr($log['message'], 0, 50) ?>...</small></td>
                        <td><span class="badge badge-<?= $log['status'] === 'sent' ? 'success' : ($log['status'] === 'pending' ? 'warning' : 'danger') ?>"><?= $log['status'] ?></span></td>
                        <td><?= $log['created_at'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.sms-actions { display: flex; gap: 15px; margin-bottom: 25px; }
.form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
.test-form { max-width: 500px; }
</style>

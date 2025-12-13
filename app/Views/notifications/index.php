<div class="page-header">
    <h2><span class="material-icons-round">notifications</span> التنبيهات</h2>
</div>

<?php if (empty($notifications)): ?>
<div class="empty-state">
    <span class="material-icons-round">notifications_off</span>
    <h3>لا توجد تنبيهات</h3>
    <p>كل شيء على ما يرام!</p>
</div>
<?php else: ?>
<div class="notifications-list">
    <?php foreach ($notifications as $notif): ?>
    <a href="<?= $notif['link'] ?>" class="notification-item notification-<?= $notif['type'] ?>">
        <div class="notification-icon">
            <span class="material-icons-round"><?= $notif['icon'] ?></span>
        </div>
        <div class="notification-content">
            <h4><?= $notif['title'] ?></h4>
            <p><?= $notif['message'] ?></p>
        </div>
        <span class="material-icons-round">arrow_back</span>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<style>
.empty-state { text-align: center; padding: 80px 20px; background: var(--bg-card); border-radius: var(--radius); }
.empty-state .material-icons-round { font-size: 80px; color: var(--text-muted); opacity: 0.5; }
.empty-state h3 { margin: 20px 0 10px; color: var(--text-muted); }
.notifications-list { display: flex; flex-direction: column; gap: 15px; }
.notification-item { display: flex; align-items: center; gap: 20px; padding: 20px 25px; background: var(--bg-card); border-radius: var(--radius); text-decoration: none; color: inherit; transition: all 0.2s; }
.notification-item:hover { transform: translateX(-5px); box-shadow: var(--shadow); }
.notification-icon { width: 50px; height: 50px; border-radius: 12px; display: flex;align-items: center; justify-content: center; }
.notification-icon .material-icons-round { font-size: 24px; color: white; }
.notification-danger .notification-icon { background: linear-gradient(135deg, #e53935, #c62828); }
.notification-warning .notification-icon { background: linear-gradient(135deg, #ff9800, #f57c00); }
.notification-primary .notification-icon { background: linear-gradient(135deg, #1e88e5, #1565c0); }
.notification-content { flex: 1; }
.notification-content h4 { margin-bottom: 5px; }
.notification-content p { color: var(--text-muted); font-size: 14px; }
</style>

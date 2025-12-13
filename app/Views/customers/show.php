<div class="page-header">
    <h2><span class="material-icons-round">person</span> ملف العميل: <?= $customer['full_name'] ?></h2>
    <div class="header-actions">
        <a href="<?= url('/customers/' . $customer['id'] . '/edit') ?>" class="btn btn-primary">
            <span class="material-icons-round">edit</span> تعديل
        </a>
        <a href="<?= url('/customers') ?>" class="btn btn-secondary">
            <span class="material-icons-round">arrow_forward</span> رجوع
        </a>
    </div>
</div>

<div class="customer-profile">
    <div class="profile-sidebar">
        <div class="profile-card">
            <div class="profile-avatar">
                <span class="material-icons-round">account_circle</span>
            </div>
            <h3><?= $customer['full_name'] ?></h3>
            <p><a href="tel:<?= $customer['phone'] ?>"><?= $customer['phone'] ?></a></p>
            
            <div class="profile-stats">
                <div class="stat">
                    <strong class="<?= $balance > 0 ? 'text-danger' : 'text-success' ?>"><?= formatMoney($balance) ?></strong>
                    <span>الرصيد المستحق</span>
                </div>
                <div class="stat">
                    <strong><?= count($invoices) ?></strong>
                    <span>الفواتير</span>
                </div>
            </div>
        </div>
        
        <div class="profile-info">
            <h4>معلومات العميل</h4>
            <ul>
                <li><strong>رقم الهوية:</strong> <?= $customer['national_id'] ?></li>
                <li><strong>هاتف إضافي:</strong> <?= $customer['phone2'] ?: '-' ?></li>
                <li><strong>العنوان:</strong> <?= $customer['address'] ?: '-' ?></li>
                <li><strong>المدينة:</strong> <?= $customer['city'] ?: '-' ?></li>
                <li><strong>عنوان العمل:</strong> <?= $customer['work_address'] ?: '-' ?></li>
                <li><strong>هاتف العمل:</strong> <?= $customer['work_phone'] ?: '-' ?></li>
            </ul>
            
            <?php if ($customer['guarantor_name']): ?>
            <h4>بيانات الضامن</h4>
            <ul>
                <li><strong>الاسم:</strong> <?= $customer['guarantor_name'] ?></li>
                <li><strong>الهاتف:</strong> <?= $customer['guarantor_phone'] ?></li>
                <li><strong>الهوية:</strong> <?= $customer['guarantor_national_id'] ?></li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="profile-content">
        <div class="card">
            <div class="card-header">
                <h3><span class="material-icons-round">receipt_long</span> الفواتير</h3>
            </div>
            <div class="card-body">
                <?php if (empty($invoices)): ?>
                <p class="empty-message">لا توجد فواتير</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>رقم الفاتورة</th>
                                <th>النوع</th>
                                <th>الإجمالي</th>
                                <th>المتبقي</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td><?= $inv['invoice_number'] ?></td>
                                <td><span class="badge badge-<?= $inv['invoice_type'] === 'cash' ? 'success' : 'primary' ?>"><?= invoiceType($inv['invoice_type']) ?></span></td>
                                <td><?= formatMoney($inv['total_amount']) ?></td>
                                <td><?= formatMoney($inv['remaining_amount']) ?></td>
                                <td><span class="badge badge-<?= $inv['status'] === 'completed' ? 'success' : ($inv['status'] === 'active' ? 'warning' : 'secondary') ?>"><?= invoiceStatus($inv['status']) ?></span></td>
                                <td><?= formatDate($inv['created_at']) ?></td>
                                <td><a href="<?= url('/invoices/' . $inv['id']) ?>" class="btn btn-sm btn-secondary">عرض</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3><span class="material-icons-round">payments</span> سجل المدفوعات</h3>
            </div>
            <div class="card-body">
                <?php if (empty($payments)): ?>
                <p class="empty-message">لا توجد مدفوعات</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>رقم الإيصال</th>
                                <th>الفاتورة</th>
                                <th>المبلغ</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $pay): ?>
                            <tr>
                                <td><?= $pay['receipt_number'] ?></td>
                                <td><?= $pay['invoice_number'] ?></td>
                                <td><strong class="text-success"><?= formatMoney($pay['amount']) ?></strong></td>
                                <td><?= formatDateTime($pay['payment_date']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
.header-actions { display: flex; gap: 10px; }
.customer-profile { display: grid; grid-template-columns: 320px 1fr; gap: 25px; }
.profile-sidebar { display: flex; flex-direction: column; gap: 20px; }
.profile-card { background: var(--bg-card); border-radius: var(--radius); padding: 30px; text-align: center; }
.profile-avatar { width: 80px; height: 80px; background: var(--primary); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; }
.profile-avatar .material-icons-round { font-size: 48px; color: white; }
.profile-card h3 { margin-bottom: 5px; }
.profile-stats { display: flex; gap: 20px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color); }
.profile-stats .stat { flex: 1; text-align: center; }
.profile-stats .stat strong { display: block; font-size: 18px; }
.profile-stats .stat span { font-size: 12px; color: var(--text-muted); }
.profile-info { background: var(--bg-card); border-radius: var(--radius); padding: 20px; }
.profile-info h4 { margin-bottom: 15px; font-size: 14px; color: var(--text-muted); }
.profile-info ul { list-style: none; }
.profile-info li { padding: 8px 0; border-bottom: 1px solid var(--border-color); }
.profile-info li:last-child { border: none; }
.profile-info li strong { color: var(--text-muted); display: inline-block; width: 100px; }
@media (max-width: 1024px) { .customer-profile { grid-template-columns: 1fr; } }
</style>

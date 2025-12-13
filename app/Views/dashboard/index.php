<div class="dashboard">
    <!-- إحصائيات سريعة -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">
                <span class="material-icons-round">shopping_cart</span>
            </div>
            <div class="stat-info">
                <h3>مبيعات اليوم النقدية</h3>
                <p class="stat-value"><?= formatMoney($todayStats['cash_total'] ?? 0) ?></p>
                <small><?= $todayStats['cash_count'] ?? 0 ?> فاتورة</small>
            </div>
        </div>
        
        <div class="stat-card success">
            <div class="stat-icon">
                <span class="material-icons-round">credit_score</span>
            </div>
            <div class="stat-info">
                <h3>مبيعات اليوم بالتقسيط</h3>
                <p class="stat-value"><?= formatMoney($todayStats['installment_total'] ?? 0) ?></p>
                <small><?= $todayStats['installment_count'] ?? 0 ?> عقد</small>
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-icon">
                <span class="material-icons-round">payments</span>
            </div>
            <div class="stat-info">
                <h3>تحصيلات اليوم</h3>
                <p class="stat-value"><?= formatMoney($todayPayments ?? 0) ?></p>
                <small>من الأقساط</small>
            </div>
        </div>
        
        <div class="stat-card danger">
            <div class="stat-icon">
                <span class="material-icons-round">warning</span>
            </div>
            <div class="stat-info">
                <h3>أقساط متأخرة</h3>
                <p class="stat-value"><?= formatMoney($installmentStats['overdue_amount'] ?? 0) ?></p>
                <small><?= $installmentStats['overdue_count'] ?? 0 ?> قسط</small>
            </div>
        </div>
    </div>
    
    <!-- صف ثاني -->
    <div class="stats-grid small">
        <div class="stat-card-mini">
            <span class="material-icons-round">inventory_2</span>
            <div>
                <strong><?= $stats['products_count'] ?? 0 ?></strong>
                <span>منتج</span>
            </div>
        </div>
        <div class="stat-card-mini">
            <span class="material-icons-round">people</span>
            <div>
                <strong><?= $stats['customers_count'] ?? 0 ?></strong>
                <span>عميل</span>
            </div>
        </div>
        <div class="stat-card-mini">
            <span class="material-icons-round">receipt_long</span>
            <div>
                <strong><?= $stats['active_installments'] ?? 0 ?></strong>
                <span>عقد نشط</span>
            </div>
        </div>
        <div class="stat-card-mini <?= ($stats['low_stock_count'] ?? 0) > 0 ? 'alert' : '' ?>">
            <span class="material-icons-round">inventory</span>
            <div>
                <strong><?= $stats['low_stock_count'] ?? 0 ?></strong>
                <span>منتج منخفض</span>
            </div>
        </div>
    </div>
    
    <div class="dashboard-grid">
        <!-- أقساط اليوم -->
        <div class="card">
            <div class="card-header">
                <h3><span class="material-icons-round">today</span> أقساط اليوم</h3>
                <a href="<?= url('/installments/today') ?>" class="btn-link">عرض الكل</a>
            </div>
            <div class="card-body">
                <?php if (empty($todayInstallments)): ?>
                    <p class="empty-message">لا توجد أقساط مستحقة اليوم</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>العميل</th>
                                    <th>القسط</th>
                                    <th>المبلغ</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($todayInstallments as $inst): ?>
                                <tr>
                                    <td><?= $inst['customer_name'] ?></td>
                                    <td>القسط <?= $inst['installment_number'] ?></td>
                                    <td><strong><?= formatMoney($inst['remaining_amount']) ?></strong></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="payInstallment(<?= $inst['id'] ?>, <?= $inst['remaining_amount'] ?>)">
                                            تحصيل
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- الأقساط المتأخرة -->
        <div class="card">
            <div class="card-header danger">
                <h3><span class="material-icons-round">warning</span> أقساط متأخرة</h3>
                <a href="<?= url('/installments/overdue') ?>" class="btn-link">عرض الكل</a>
            </div>
            <div class="card-body">
                <?php if (empty($overdueInstallments)): ?>
                    <p class="empty-message success-message">
                        <span class="material-icons-round">check_circle</span>
                        لا توجد أقساط متأخرة
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>العميل</th>
                                    <th>الهاتف</th>
                                    <th>المبلغ</th>
                                    <th>التأخير</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($overdueInstallments as $inst): ?>
                                <tr class="overdue-row">
                                    <td><?= $inst['customer_name'] ?></td>
                                    <td><a href="tel:<?= $inst['customer_phone'] ?>"><?= $inst['customer_phone'] ?></a></td>
                                    <td><strong class="text-danger"><?= formatMoney($inst['remaining_amount']) ?></strong></td>
                                    <td><span class="badge badge-danger"><?= (int)$inst['days_overdue'] ?> يوم</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- آخر الفواتير -->
    <div class="card">
        <div class="card-header">
            <h3><span class="material-icons-round">receipt_long</span> آخر الفواتير</h3>
            <a href="<?= url('/invoices') ?>" class="btn-link">عرض الكل</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>العميل</th>
                            <th>النوع</th>
                            <th>الإجمالي</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentInvoices as $invoice): ?>
                        <tr>
                            <td><a href="<?= url('/invoices/' . $invoice['id']) ?>"><?= $invoice['invoice_number'] ?></a></td>
                            <td><?= $invoice['customer_name'] ?? 'زبون نقدي' ?></td>
                            <td>
                                <span class="badge badge-<?= $invoice['invoice_type'] === 'cash' ? 'success' : 'primary' ?>">
                                    <?= invoiceType($invoice['invoice_type']) ?>
                                </span>
                            </td>
                            <td><strong><?= formatMoney($invoice['total_amount']) ?></strong></td>
                            <td><span class="badge badge-<?= $invoice['status'] === 'completed' ? 'success' : 'warning' ?>"><?= invoiceStatus($invoice['status']) ?></span></td>
                            <td><?= formatDateTime($invoice['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- نافذة الدفع -->
<div class="modal" id="payModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>تحصيل قسط</h3>
            <button class="close-btn" onclick="closeModal('payModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="payForm">
                <input type="hidden" id="payInstallmentId">
                <div class="form-group">
                    <label>المبلغ المستحق</label>
                    <input type="text" id="dueAmount" readonly class="form-control">
                </div>
                <div class="form-group">
                    <label>المبلغ المدفوع</label>
                    <input type="number" id="payAmount" step="0.01" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <span class="material-icons-round">check</span>
                    تأكيد الدفع
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function payInstallment(id, amount) {
    document.getElementById('payInstallmentId').value = id;
    document.getElementById('dueAmount').value = amount.toFixed(2) + ' <?= $settings['currency'] ?? 'ج.م' ?>';
    document.getElementById('payAmount').value = amount.toFixed(2);
    document.getElementById('payAmount').max = amount;
    document.getElementById('payModal').classList.add('show');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}

document.getElementById('payForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const id = document.getElementById('payInstallmentId').value;
    const amount = document.getElementById('payAmount').value;
    
    const formData = new FormData();
    formData.append('amount', amount);
    
    try {
        const response = await fetch('<?= url('/installments/') ?>' + id + '/pay', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('تم تسجيل الدفعة بنجاح\nرقم الإيصال: ' + result.receipt_number);
            location.reload();
        } else {
            alert(result.message || 'حدث خطأ');
        }
    } catch (error) {
        alert('حدث خطأ في الاتصال');
    }
});
</script>

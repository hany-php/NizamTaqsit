<div class="page-header">
    <h2><span class="material-icons-round">analytics</span> التقارير</h2>
</div>

<div class="reports-grid">
    <a href="<?= url('/reports/sales') ?>" class="report-card">
        <div class="report-icon" style="background: linear-gradient(135deg, #1e88e5, #1565c0)">
            <span class="material-icons-round">point_of_sale</span>
        </div>
        <div class="report-info">
            <h3>تقرير المبيعات</h3>
            <p>عرض إجماليات المبيعات النقدية والتقسيط</p>
        </div>
    </a>
    
    <a href="<?= url('/reports/profits') ?>" class="report-card">
        <div class="report-icon" style="background: linear-gradient(135deg, #00bcd4, #0097a7)">
            <span class="material-icons-round">trending_up</span>
        </div>
        <div class="report-info">
            <h3>تقرير الأرباح</h3>
            <p>عرض أرباح المنتجات وهوامش الربح</p>
        </div>
    </a>
    
    <a href="<?= url('/reports/collections') ?>" class="report-card">
        <div class="report-icon" style="background: linear-gradient(135deg, #43a047, #2e7d32)">
            <span class="material-icons-round">payments</span>
        </div>
        <div class="report-info">
            <h3>تقرير التحصيلات</h3>
            <p>عرض المدفوعات والتحصيلات اليومية</p>
        </div>
    </a>
    
    <a href="<?= url('/reports/cashflow') ?>" class="report-card">
        <div class="report-icon" style="background: linear-gradient(135deg, #673ab7, #512da8)">
            <span class="material-icons-round">account_balance</span>
        </div>
        <div class="report-info">
            <h3>التدفق النقدي</h3>
            <p>عرض تحليل التدفقات النقدية</p>
        </div>
    </a>
    
    <a href="<?= url('/reports/overdue') ?>" class="report-card">
        <div class="report-icon" style="background: linear-gradient(135deg, #e53935, #c62828)">
            <span class="material-icons-round">warning</span>
        </div>
        <div class="report-info">
            <h3>تقرير المتأخرات</h3>
            <p>عرض الأقساط المتأخرة والمبالغ المستحقة</p>
        </div>
    </a>
    
    <a href="<?= url('/reports/employees') ?>" class="report-card">
        <div class="report-icon" style="background: linear-gradient(135deg, #3f51b5, #303f9f)">
            <span class="material-icons-round">groups</span>
        </div>
        <div class="report-info">
            <h3>أداء الموظفين</h3>
            <p>عرض مبيعات وإنجازات الموظفين</p>
        </div>
    </a>
    
    <a href="<?= url('/reports/customers') ?>" class="report-card">
        <div class="report-icon" style="background: linear-gradient(135deg, #ff9800, #f57c00)">
            <span class="material-icons-round">people</span>
        </div>
        <div class="report-info">
            <h3>تقرير العملاء</h3>
            <p>عرض أرصدة العملاء والمستحقات</p>
        </div>
    </a>
    
    <a href="<?= url('/reports/inventory') ?>" class="report-card">
        <div class="report-icon" style="background: linear-gradient(135deg, #9c27b0, #7b1fa2)">
            <span class="material-icons-round">inventory</span>
        </div>
        <div class="report-info">
            <h3>تقرير المخزون</h3>
            <p>عرض حالة المخزون والمنتجات</p>
        </div>
    </a>
</div>

<style>
.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
}
.report-card {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 25px;
    background: var(--bg-card);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    text-decoration: none;
    color: inherit;
    transition: all 0.3s;
}
.report-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}
.report-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.report-icon .material-icons-round {
    font-size: 28px;
    color: white;
}
.report-info h3 {
    margin-bottom: 5px;
    font-size: 16px;
}
.report-info p {
    font-size: 13px;
    color: var(--text-muted);
}
</style>

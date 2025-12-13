<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <?php if (!empty($settings['store_logo'])): ?>
                <img src="<?= asset('images/' . $settings['store_logo']) ?>" alt="الشعار">
            <?php else: ?>
                <span class="material-icons-round">store</span>
            <?php endif; ?>
        </div>
        <h1><?= $settings['store_name'] ?? 'نظام تقسيط' ?></h1>
    </div>
    
    <nav class="sidebar-nav" id="sidebarNav">
        <!-- الإدارة -->
        <div class="nav-section open" data-section="admin">
            <div class="nav-section-header" onclick="toggleSection(this)">
                <span class="nav-section-title">الإدارة</span>
                <span class="material-icons-round section-arrow">expand_more</span>
            </div>
            <div class="nav-section-content">
                <a href="<?= url('/dashboard') ?>" data-item="dashboard" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false ? 'active' : '' ?>">
                    <span class="material-icons-round">dashboard</span>
                    <span>لوحة التحكم</span>
                </a>
                
                <a href="<?= url('/pos') ?>" data-item="pos" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/pos') !== false && strpos($_SERVER['REQUEST_URI'], 'installment') === false ? 'active' : '' ?>">
                    <span class="material-icons-round">point_of_sale</span>
                    <span>نقطة البيع</span>
                </a>
                
                <a href="<?= url('/pos/installment') ?>" data-item="pos-installment" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/pos/installment') !== false ? 'active' : '' ?>">
                    <span class="material-icons-round">credit_score</span>
                    <span>بيع بالتقسيط</span>
                </a>
            </div>
        </div>
        
        <!-- المخزون -->
        <div class="nav-section" data-section="inventory">
            <div class="nav-section-header" onclick="toggleSection(this)">
                <span class="nav-section-title">المخزون</span>
                <span class="material-icons-round section-arrow">expand_more</span>
            </div>
            <div class="nav-section-content">
                <a href="<?= url('/products') ?>" data-item="products" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/products') !== false ? 'active' : '' ?>">
                    <span class="material-icons-round">inventory_2</span>
                    <span>المنتجات</span>
                </a>
                
                <a href="<?= url('/categories') ?>" data-item="categories" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/categories') !== false ? 'active' : '' ?>">
                    <span class="material-icons-round">category</span>
                    <span>التصنيفات</span>
                </a>
                
                <a href="<?= url('/customers') ?>" data-item="customers" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/customers') !== false ? 'active' : '' ?>">
                    <span class="material-icons-round">people</span>
                    <span>العملاء</span>
                </a>
            </div>
        </div>
        
        <!-- المالية -->
        <div class="nav-section" data-section="finance">
            <div class="nav-section-header" onclick="toggleSection(this)">
                <span class="nav-section-title">المالية</span>
                <span class="material-icons-round section-arrow">expand_more</span>
            </div>
            <div class="nav-section-content">
                <a href="<?= url('/invoices') ?>" data-item="invoices" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/invoices') !== false ? 'active' : '' ?>">
                    <span class="material-icons-round">receipt_long</span>
                    <span>الفواتير</span>
                </a>
                
                <a href="<?= url('/installments') ?>" data-item="installments" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/installments') !== false && strpos($_SERVER['REQUEST_URI'], 'today') === false && strpos($_SERVER['REQUEST_URI'], 'overdue') === false ? 'active' : '' ?>">
                    <span class="material-icons-round">payments</span>
                    <span>الأقساط</span>
                </a>
                
                <a href="<?= url('/installments/today') ?>" data-item="installments-today" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'today') !== false ? 'active' : '' ?>">
                    <span class="material-icons-round">today</span>
                    <span>أقساط اليوم</span>
                </a>
                
                <a href="<?= url('/installments/overdue') ?>" data-item="installments-overdue" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'overdue') !== false ? 'active' : '' ?>">
                    <span class="material-icons-round">warning</span>
                    <span>متأخرات</span>
                </a>
                
                <a href="<?= url('/payments') ?>" data-item="payments" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/payments') !== false ? 'active' : '' ?>">
                    <span class="material-icons-round">account_balance_wallet</span>
                    <span>المدفوعات</span>
                </a>
            </div>
        </div>
        
        <?php if (hasRole('admin')): ?>
        <!-- النظام -->
        <div class="nav-section" data-section="system">
            <div class="nav-section-header" onclick="toggleSection(this)">
                <span class="nav-section-title">النظام</span>
                <span class="material-icons-round section-arrow">expand_more</span>
            </div>
            <div class="nav-section-content">
                <a href="<?= url('/reports') ?>" data-item="reports" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/reports') !== false ? 'active' : '' ?>">
                    <span class="material-icons-round">analytics</span>
                    <span>التقارير</span>
                </a>
                
                <a href="<?= url('/users') ?>" data-item="users" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/users') !== false ? 'active' : '' ?>">
                    <span class="material-icons-round">manage_accounts</span>
                    <span>المستخدمين</span>
                </a>
                
                <a href="<?= url('/settings') ?>" data-item="settings" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/settings') !== false ? 'active' : '' ?>">
                    <span class="material-icons-round">settings</span>
                    <span>الإعدادات</span>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </nav>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <span class="material-icons-round">account_circle</span>
            <div>
                <strong><?= $user['full_name'] ?? 'مستخدم' ?></strong>
                <small><?= userRole($user['role'] ?? '') ?></small>
            </div>
        </div>
        <a href="<?= url('/logout') ?>" class="logout-btn" title="تسجيل خروج">
            <span class="material-icons-round">logout</span>
        </a>
    </div>
</aside>

<script>
function toggleSection(header) {
    const section = header.parentElement;
    section.classList.toggle('open');
    
    // Save state to localStorage
    const sectionName = section.dataset.section;
    const openSections = JSON.parse(localStorage.getItem('openSections') || '["admin"]');
    
    if (section.classList.contains('open')) {
        if (!openSections.includes(sectionName)) {
            openSections.push(sectionName);
        }
    } else {
        const index = openSections.indexOf(sectionName);
        if (index > -1) {
            openSections.splice(index, 1);
        }
    }
    
    localStorage.setItem('openSections', JSON.stringify(openSections));
}

// Initialize sidebar on page load
document.addEventListener('DOMContentLoaded', function() {
    const nav = document.getElementById('sidebarNav');
    
    // Load menu config from PHP (database) - this is per-user from the server
    const config = <?= json_encode($menuConfig ?? null, JSON_UNESCAPED_UNICODE) ?> || {};
    
    console.log('Sidebar loading menuConfig from server:', config);
    
    // Apply section order from new config
    if (config.sectionOrder && config.sectionOrder.length > 0) {
        console.log('Applying section order:', config.sectionOrder);
        config.sectionOrder.forEach(sectionName => {
            const section = nav.querySelector(`[data-section="${sectionName}"]`);
            if (section) nav.appendChild(section);
        });
    }
    
    // Apply item order within each section (move items from any section to target section)
    if (config.itemOrder) {
        console.log('Applying item order:', config.itemOrder);
        Object.keys(config.itemOrder).forEach(sectionId => {
            const section = nav.querySelector(`[data-section="${sectionId}"]`);
            const itemsContainer = section?.querySelector('.nav-section-content');
            if (itemsContainer && config.itemOrder[sectionId]) {
                config.itemOrder[sectionId].forEach(itemId => {
                    // Search for item in the ENTIRE nav, not just current section
                    const item = nav.querySelector(`[data-item="${itemId}"]`);
                    if (item) {
                        console.log(`Moving item ${itemId} to section ${sectionId}`);
                        itemsContainer.appendChild(item);
                    }
                });
            }
        });
    }
    
    // Hide hidden sections
    if (config.hidden?.sections) {
        config.hidden.sections.forEach(sectionId => {
            const section = nav.querySelector(`[data-section="${sectionId}"]`);
            if (section) section.style.display = 'none';
        });
    }
    
    // Hide hidden items
    if (config.hidden?.items) {
        config.hidden.items.forEach(itemId => {
            const item = nav.querySelector(`[data-item="${itemId}"]`);
            if (item) item.style.display = 'none';
        });
    }
    
    // Restore open/closed states
    const openSections = JSON.parse(localStorage.getItem('openSections') || '["admin"]');
    nav.querySelectorAll('.nav-section').forEach(section => {
        const sectionName = section.dataset.section;
        if (openSections.includes(sectionName)) {
            section.classList.add('open');
        } else {
            section.classList.remove('open');
        }
    });
    
    // Auto-open section containing active item
    const activeItem = nav.querySelector('.nav-item.active');
    if (activeItem) {
        const parentSection = activeItem.closest('.nav-section');
        if (parentSection && !parentSection.classList.contains('open')) {
            parentSection.classList.add('open');
        }
    }
});
</script>

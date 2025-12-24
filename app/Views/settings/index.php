<div class="page-header">
    <h2><span class="material-icons-round">settings</span> إعدادات النظام</h2>
</div>

<div class="settings-grid">
    <div class="card">
        <div class="card-header"><h3><span class="material-icons-round">store</span> بيانات المتجر</h3></div>
        <div class="card-body">
            <form method="POST" action="<?= url('/settings') ?>" enctype="multipart/form-data">
                <div class="form-group">
                    <label>اسم المتجر</label>
                    <input type="text" name="store_name" class="form-control" value="<?= $settings['store_name'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>رقم الهاتف</label>
                    <input type="tel" name="store_phone" class="form-control" value="<?= $settings['store_phone'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>هاتف إضافي</label>
                    <input type="tel" name="store_phone2" class="form-control" value="<?= $settings['store_phone2'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>العنوان</label>
                    <textarea name="store_address" class="form-control" rows="2"><?= $settings['store_address'] ?? '' ?></textarea>
                </div>
                <div class="form-group">
                    <label>شعار المتجر</label>
                    <?php if (!empty($settings['store_logo'])): ?>
                    <div style="margin-bottom:10px"><img src="<?= asset('images/' . $settings['store_logo']) ?>" alt="" style="max-height:60px"></div>
                    <?php endif; ?>
                    <input type="file" name="store_logo" class="form-control" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary"><span class="material-icons-round">save</span> حفظ</button>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h3><span class="material-icons-round">receipt</span> إعدادات الفواتير</h3></div>
        <div class="card-body">
            <form method="POST" action="<?= url('/settings') ?>">
                <div class="form-group">
                    <label>العملة</label>
                    <input type="text" name="currency" class="form-control" value="<?= $settings['currency'] ?? 'ج.م' ?>">
                </div>
                <div class="form-group">
                    <label>نسبة الضريبة %</label>
                    <input type="number" name="tax_rate" class="form-control" step="0.01" value="<?= $settings['tax_rate'] ?? 0 ?>">
                </div>
                <div class="form-group">
                    <label>بادئة الفاتورة</label>
                    <input type="text" name="invoice_prefix" class="form-control" value="<?= $settings['invoice_prefix'] ?? 'INV' ?>">
                </div>
                <div class="form-group">
                    <label>بادئة الإيصال</label>
                    <input type="text" name="receipt_prefix" class="form-control" value="<?= $settings['receipt_prefix'] ?? 'RCP' ?>">
                </div>
                <div class="form-group">
                    <label>تذييل الفاتورة</label>
                    <textarea name="invoice_footer" class="form-control" rows="2"><?= $settings['invoice_footer'] ?? 'شكراً لتعاملكم معنا' ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><span class="material-icons-round">save</span> حفظ</button>
            </form>
        </div>
    </div>
    
    <!-- المظهر الأساسي (للنظام - يحفظه المشرف) -->
    <div class="card">
        <div class="card-header"><h3><span class="material-icons-round">palette</span> المظهر الأساسي للنظام</h3></div>
        <div class="card-body">
            <p style="color: var(--text-muted); margin-bottom: 15px; font-size: 14px;">
                <span class="material-icons-round" style="vertical-align: middle; font-size: 18px;">info</span>
                هذه الإعدادات تُطبق كإفتراضي على جميع الموظفين. يمكن لكل موظف تخصيص مظهره الخاص من قسم "تخصيص المظهر" أدناه.
            </p>
            <form method="POST" action="<?= url('/settings') ?>">
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="dark_mode" value="1" <?= ($settings['dark_mode'] ?? 0) ? 'checked' : '' ?>>
                        الوضع الليلي
                    </label>
                </div>
                <div class="form-group">
                    <label>اللون الرئيسي</label>
                    <input type="color" name="primary_color" class="form-control" value="<?= $settings['primary_color'] ?? '#1e88e5' ?>" style="height:45px">
                </div>
                <div class="form-group">
                    <label>لون القائمة الجانبية</label>
                    <input type="color" name="sidebar_color" class="form-control" value="<?= $settings['sidebar_color'] ?? '#1a237e' ?>" style="height:45px">
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary"><span class="material-icons-round">save</span> حفظ الإفتراضي</button>
                    <button type="button" class="btn btn-outline" onclick="resetSystemAppearance()"><span class="material-icons-round">restart_alt</span> إعادة للأصل</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- تخصيص المظهر (لكل موظف) -->
    <div class="card">
        <div class="card-header"><h3><span class="material-icons-round">brush</span> تخصيص المظهر (لك فقط)</h3></div>
        <div class="card-body" id="themeEditor">
            <p style="color: var(--text-muted); margin-bottom: 15px; font-size: 14px;">
                <span class="material-icons-round" style="vertical-align: middle; font-size: 18px;">person</span>
                خصص مظهر النظام حسب رغبتك. هذه التغييرات تُطبق عليك فقط ولا تؤثر على الموظفين الآخرين.
            </p>
            
            <!-- اختيار الوضع -->
            <div class="theme-mode-selector" style="display: flex; gap: 15px; margin-bottom: 25px;">
                <label class="mode-option" style="flex: 1; cursor: pointer;">
                    <input type="radio" name="themeMode" value="light" <?= !($themeConfig['dark_mode'] ?? false) ? 'checked' : '' ?> onchange="switchThemeMode('light')">
                    <div class="mode-card" style="border: 2px solid var(--border-color); border-radius: 12px; padding: 20px; text-align: center; transition: all 0.3s; background: #f5f7fa;">
                        <span class="material-icons-round" style="font-size: 48px; color: #ff9800;">light_mode</span>
                        <h4 style="margin: 10px 0 5px; color: #212121;">الوضع النهاري</h4>
                        <small style="color: #666;">ألوان فاتحة ومريحة للعين</small>
                    </div>
                </label>
                <label class="mode-option" style="flex: 1; cursor: pointer;">
                    <input type="radio" name="themeMode" value="dark" <?= ($themeConfig['dark_mode'] ?? false) ? 'checked' : '' ?> onchange="switchThemeMode('dark')">
                    <div class="mode-card" style="border: 2px solid var(--border-color); border-radius: 12px; padding: 20px; text-align: center; transition: all 0.3s; background: #1e1e1e;">
                        <span class="material-icons-round" style="font-size: 48px; color: #64b5f6;">dark_mode</span>
                        <h4 style="margin: 10px 0 5px; color: #ffffff;">الوضع الليلي</h4>
                        <small style="color: #b0b0b0;">ألوان داكنة لراحة العين ليلاً</small>
                    </div>
                </label>
            </div>
            
            <input type="hidden" id="themeDarkMode" value="<?= ($themeConfig['dark_mode'] ?? false) ? '1' : '0' ?>">
            
            <!-- ألوان الوضع النهاري -->
            <div id="lightModeColors" class="theme-section" style="<?= ($themeConfig['dark_mode'] ?? false) ? 'display:none;' : '' ?>">
                <h4 style="margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                    <span class="material-icons-round" style="color: #ff9800;">light_mode</span>
                    ألوان الوضع النهاري
                </h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>اللون الرئيسي</label>
                        <input type="color" id="themePrimaryColor" class="form-control" 
                               value="<?= $themeConfig['primary_color'] ?? ($settings['primary_color'] ?? '#1e88e5') ?>" 
                               style="height:45px">
                    </div>
                    <div class="form-group">
                        <label>لون القائمة الجانبية</label>
                        <input type="color" id="themeSidebarColor" class="form-control" 
                               value="<?= $themeConfig['sidebar_color'] ?? ($settings['sidebar_color'] ?? '#1a237e') ?>" 
                               style="height:45px">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>لون نص القائمة</label>
                        <input type="color" id="themeSidebarTextColor" class="form-control" 
                               value="<?= $themeConfig['sidebar_text_color'] ?? '#ffffff' ?>" 
                               style="height:45px">
                    </div>
                    <div class="form-group">
                        <label>لون الخلفية</label>
                        <input type="color" id="themeBgColor" class="form-control" 
                               value="<?= $themeConfig['bg_color'] ?? '#f5f7fa' ?>" 
                               style="height:45px">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>لون البطاقات</label>
                        <input type="color" id="themeCardColor" class="form-control" 
                               value="<?= $themeConfig['card_color'] ?? '#ffffff' ?>" 
                               style="height:45px">
                    </div>
                    <div class="form-group">
                        <label>لون النص</label>
                        <input type="color" id="themeTextColor" class="form-control" 
                               value="<?= $themeConfig['text_color'] ?? '#212121' ?>" 
                               style="height:45px">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>لون رأس الأقسام</label>
                        <input type="color" id="themeSectionHeaderColor" class="form-control" 
                               value="<?= $themeConfig['section_header_color'] ?? '#1565c0' ?>" 
                               style="height:45px">
                    </div>
                    <div class="form-group">
                        <label>لون الأزرار الثانوية</label>
                        <input type="color" id="themeSecondaryColor" class="form-control" 
                               value="<?= $themeConfig['secondary_color'] ?? '#546e7a' ?>" 
                               style="height:45px">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>لون الحدود</label>
                        <input type="color" id="themeBorderColor" class="form-control" 
                               value="<?= $themeConfig['border_color'] ?? '#e0e0e0' ?>" 
                               style="height:45px">
                    </div>
                    <div class="form-group">
                        <label>لون النص الثانوي</label>
                        <input type="color" id="themeTextSecondaryColor" class="form-control" 
                               value="<?= $themeConfig['text_secondary_color'] ?? '#666666' ?>" 
                               style="height:45px">
                    </div>
                </div>
            </div>
            
            <!-- ألوان الوضع الليلي -->
            <div id="darkModeColors" class="theme-section" style="<?= !($themeConfig['dark_mode'] ?? false) ? 'display:none;' : '' ?>">
                <h4 style="margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                    <span class="material-icons-round" style="color: #64b5f6;">dark_mode</span>
                    ألوان الوضع الليلي
                </h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>اللون الرئيسي</label>
                        <input type="color" id="themeDarkPrimaryColor" class="form-control" 
                               value="<?= $themeConfig['dark_primary_color'] ?? '#64b5f6' ?>" 
                               style="height:45px">
                    </div>
                    <div class="form-group">
                        <label>لون القائمة الجانبية</label>
                        <input type="color" id="themeDarkSidebarColor" class="form-control" 
                               value="<?= $themeConfig['dark_sidebar_color'] ?? '#121212' ?>" 
                               style="height:45px">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>لون نص القائمة</label>
                        <input type="color" id="themeDarkSidebarTextColor" class="form-control" 
                               value="<?= $themeConfig['dark_sidebar_text_color'] ?? '#ffffff' ?>" 
                               style="height:45px">
                    </div>
                    <div class="form-group">
                        <label>لون الخلفية</label>
                        <input type="color" id="themeDarkBgColor" class="form-control" 
                               value="<?= $themeConfig['dark_bg_color'] ?? '#121212' ?>" 
                               style="height:45px">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>لون البطاقات</label>
                        <input type="color" id="themeDarkCardColor" class="form-control" 
                               value="<?= $themeConfig['dark_card_color'] ?? '#1e1e1e' ?>" 
                               style="height:45px">
                    </div>
                    <div class="form-group">
                        <label>لون النص</label>
                        <input type="color" id="themeDarkTextColor" class="form-control" 
                               value="<?= $themeConfig['dark_text_color'] ?? '#ffffff' ?>" 
                               style="height:45px">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>لون رأس الأقسام</label>
                        <input type="color" id="themeDarkSectionHeaderColor" class="form-control" 
                               value="<?= $themeConfig['dark_section_header_color'] ?? '#64b5f6' ?>" 
                               style="height:45px">
                    </div>
                    <div class="form-group">
                        <label>لون الأزرار الثانوية</label>
                        <input type="color" id="themeDarkSecondaryColor" class="form-control" 
                               value="<?= $themeConfig['dark_secondary_color'] ?? '#78909c' ?>" 
                               style="height:45px">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>لون الحدود</label>
                        <input type="color" id="themeDarkBorderColor" class="form-control" 
                               value="<?= $themeConfig['dark_border_color'] ?? '#333333' ?>" 
                               style="height:45px">
                    </div>
                    <div class="form-group">
                        <label>لون النص الثانوي</label>
                        <input type="color" id="themeDarkTextSecondaryColor" class="form-control" 
                               value="<?= $themeConfig['dark_text_secondary_color'] ?? '#b0b0b0' ?>" 
                               style="height:45px">
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-primary" onclick="saveThemeConfig()">
                    <span class="material-icons-round">save</span> حفظ المظهر
                </button>
                <button type="button" class="btn btn-outline" onclick="resetThemeConfig()">
                    <span class="material-icons-round">restart_alt</span> إعادة للإفتراضي
                </button>
                <button type="button" class="btn btn-outline" onclick="previewTheme()">
                    <span class="material-icons-round">visibility</span> معاينة
                </button>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h3><span class="material-icons-round">credit_score</span> خطط التقسيط</h3></div>
        <div class="card-body">
            <a href="<?= url('/settings/installment') ?>" class="btn btn-outline btn-block">إدارة خطط التقسيط</a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h3><span class="material-icons-round">backup</span> النسخ الاحتياطي</h3></div>
        <div class="card-body">
            <a href="<?= url('/settings/backup') ?>" class="btn btn-outline btn-block">إدارة النسخ الاحتياطي</a>
        </div>
    </div>
    
    
    <div class="card">
        <div class="card-header"><h3><span class="material-icons-round">api</span> واجهة برمجة التطبيقات</h3></div>
        <div class="card-body">
            <p style="color: var(--text-muted); margin-bottom: 15px; font-size: 14px;">
                إدارة مفاتيح API للتكامل مع تطبيقات الموبايل والأنظمة الخارجية
            </p>
            <a href="<?= url('/settings/api') ?>" class="btn btn-outline btn-block">
                <span class="material-icons-round">vpn_key</span> إدارة API Keys
            </a>
        </div>
    </div>
    <div class="card" style="grid-column: span 2">
        <div class="card-header"><h3><span class="material-icons-round">menu</span> ترتيب القائمة الجانبية</h3></div>
        <div class="card-body">
            <p style="color:var(--text-muted);margin-bottom:20px;font-size:14px">
                <span class="material-icons-round" style="font-size:16px;vertical-align:middle">info</span>
                اسحب وأفلت لترتيب الأقسام والعناصر كما تريد. استخدم مفاتيح التبديل للإظهار/الإخفاء
            </p>
            
            <div id="menuEditor" class="menu-editor">
                <!-- الإدارة -->
                <div class="menu-section" data-section="admin" draggable="true">
                    <div class="menu-section-header">
                        <span class="material-icons-round drag-handle">drag_indicator</span>
                        <span class="section-name">الإدارة</span>
                        <label class="toggle-switch">
                            <input type="checkbox" checked data-type="section" data-id="admin">
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="material-icons-round expand-btn" onclick="toggleExpand(this)">expand_more</span>
                    </div>
                    <div class="menu-section-items">
                        <div class="menu-item" data-item="dashboard" draggable="true">
                            <span class="material-icons-round drag-handle">drag_indicator</span>
                            <span class="material-icons-round item-icon">dashboard</span>
                            <span>لوحة التحكم</span>
                            <label class="toggle-switch small">
                                <input type="checkbox" checked data-type="item" data-id="dashboard">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="menu-item" data-item="pos" draggable="true">
                            <span class="material-icons-round drag-handle">drag_indicator</span>
                            <span class="material-icons-round item-icon">point_of_sale</span>
                            <span>نقطة البيع</span>
                            <label class="toggle-switch small">
                                <input type="checkbox" checked data-type="item" data-id="pos">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="menu-item" data-item="pos-installment" draggable="true">
                            <span class="material-icons-round drag-handle">drag_indicator</span>
                            <span class="material-icons-round item-icon">credit_score</span>
                            <span>بيع بالتقسيط</span>
                            <label class="toggle-switch small">
                                <input type="checkbox" checked data-type="item" data-id="pos-installment">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- المخزون -->
                <div class="menu-section" data-section="inventory" draggable="true">
                    <div class="menu-section-header">
                        <span class="material-icons-round drag-handle">drag_indicator</span>
                        <span class="section-name">المخزون</span>
                        <label class="toggle-switch">
                            <input type="checkbox" checked data-type="section" data-id="inventory">
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="material-icons-round expand-btn" onclick="toggleExpand(this)">expand_more</span>
                    </div>
                    <div class="menu-section-items">
                        <div class="menu-item" data-item="products" draggable="true">
                            <span class="material-icons-round drag-handle">drag_indicator</span>
                            <span class="material-icons-round item-icon">inventory_2</span>
                            <span>المنتجات</span>
                            <label class="toggle-switch small">
                                <input type="checkbox" checked data-type="item" data-id="products">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="menu-item" data-item="categories" draggable="true">
                            <span class="material-icons-round drag-handle">drag_indicator</span>
                            <span class="material-icons-round item-icon">category</span>
                            <span>التصنيفات</span>
                            <label class="toggle-switch small">
                                <input type="checkbox" checked data-type="item" data-id="categories">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="menu-item" data-item="customers" draggable="true">
                            <span class="material-icons-round drag-handle">drag_indicator</span>
                            <span class="material-icons-round item-icon">people</span>
                            <span>العملاء</span>
                            <label class="toggle-switch small">
                                <input type="checkbox" checked data-type="item" data-id="customers">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- المالية -->
                <div class="menu-section" data-section="finance" draggable="true">
                    <div class="menu-section-header">
                        <span class="material-icons-round drag-handle">drag_indicator</span>
                        <span class="section-name">المالية</span>
                        <label class="toggle-switch">
                            <input type="checkbox" checked data-type="section" data-id="finance">
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="material-icons-round expand-btn" onclick="toggleExpand(this)">expand_more</span>
                    </div>
                    <div class="menu-section-items">
                        <div class="menu-item" data-item="invoices" draggable="true">
                            <span class="material-icons-round drag-handle">drag_indicator</span>
                            <span class="material-icons-round item-icon">receipt_long</span>
                            <span>الفواتير</span>
                            <label class="toggle-switch small">
                                <input type="checkbox" checked data-type="item" data-id="invoices">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="menu-item" data-item="installments" draggable="true">
                            <span class="material-icons-round drag-handle">drag_indicator</span>
                            <span class="material-icons-round item-icon">payments</span>
                            <span>الأقساط</span>
                            <label class="toggle-switch small">
                                <input type="checkbox" checked data-type="item" data-id="installments">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="menu-item" data-item="installments-today" draggable="true">
                            <span class="material-icons-round drag-handle">drag_indicator</span>
                            <span class="material-icons-round item-icon">today</span>
                            <span>أقساط اليوم</span>
                            <label class="toggle-switch small">
                                <input type="checkbox" checked data-type="item" data-id="installments-today">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="menu-item" data-item="installments-overdue" draggable="true">
                            <span class="material-icons-round drag-handle">drag_indicator</span>
                            <span class="material-icons-round item-icon">warning</span>
                            <span>متأخرات</span>
                            <label class="toggle-switch small">
                                <input type="checkbox" checked data-type="item" data-id="installments-overdue">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="menu-item" data-item="payments" draggable="true">
                            <span class="material-icons-round drag-handle">drag_indicator</span>
                            <span class="material-icons-round item-icon">account_balance_wallet</span>
                            <span>المدفوعات</span>
                            <label class="toggle-switch small">
                                <input type="checkbox" checked data-type="item" data-id="payments">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- النظام -->
                <div class="menu-section" data-section="system" draggable="true">
                    <div class="menu-section-header">
                        <span class="material-icons-round drag-handle">drag_indicator</span>
                        <span class="section-name">النظام</span>
                        <label class="toggle-switch">
                            <input type="checkbox" checked data-type="section" data-id="system">
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="material-icons-round expand-btn" onclick="toggleExpand(this)">expand_more</span>
                    </div>
                    <div class="menu-section-items">
                        <div class="menu-item" data-item="reports" draggable="true">
                            <span class="material-icons-round drag-handle">drag_indicator</span>
                            <span class="material-icons-round item-icon">analytics</span>
                            <span>التقارير</span>
                            <label class="toggle-switch small">
                                <input type="checkbox" checked data-type="item" data-id="reports">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="menu-item" data-item="users" draggable="true">
                            <span class="material-icons-round drag-handle">drag_indicator</span>
                            <span class="material-icons-round item-icon">manage_accounts</span>
                            <span>المستخدمين</span>
                            <label class="toggle-switch small">
                                <input type="checkbox" checked data-type="item" data-id="users">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="menu-item" data-item="settings" draggable="true">
                            <span class="material-icons-round drag-handle">drag_indicator</span>
                            <span class="material-icons-round item-icon">settings</span>
                            <span>الإعدادات</span>
                            <label class="toggle-switch small">
                                <input type="checkbox" checked data-type="item" data-id="settings">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top:20px;display:flex;gap:10px">
                <button onclick="saveFullMenuConfig()" class="btn btn-primary">
                    <span class="material-icons-round">save</span> حفظ التغييرات
                </button>
                <button onclick="resetFullMenuConfig()" class="btn btn-secondary">
                    <span class="material-icons-round">restart_alt</span> إعادة تعيين
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.settings-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; }
.checkbox-label { display: flex; align-items: center; gap: 10px; cursor: pointer; }
.checkbox-label input { width: 20px; height: 20px; }
@media (max-width: 1024px) { 
    .settings-grid { grid-template-columns: 1fr; }
    .settings-grid .card[style*="grid-column: span 2"] { grid-column: span 1 !important; }
}

/* Menu Order Styles */
.menu-order-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.menu-order-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    background: var(--bg-main);
    border-radius: var(--radius-sm);
    cursor: grab;
    transition: all 0.2s;
    border: 2px solid transparent;
}

.menu-order-item:hover {
    background: var(--bg-card);
    border-color: var(--primary);
}

.menu-order-item.dragging {
    opacity: 0.5;
    border-color: var(--primary);
    cursor: grabbing;
}

.menu-order-item .drag-handle {
    color: var(--text-muted);
}

.menu-order-item span:nth-child(2) {
    flex: 1;
    font-weight: 600;
}

/* Toggle Switch */
.toggle-switch {
    position: relative;
    width: 44px;
    height: 24px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background: var(--secondary);
    border-radius: 24px;
    transition: 0.3s;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    right: 3px;
    bottom: 3px;
    background: white;
    border-radius: 50%;
    transition: 0.3s;
}

.toggle-switch input:checked + .toggle-slider {
    background: var(--success);
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(-20px);
}

/* Menu Editor Styles */
.menu-editor {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.menu-section {
    background: var(--bg-main);
    border-radius: var(--radius);
    overflow: hidden;
    border: 2px solid transparent;
    transition: all 0.2s;
}

.menu-section.dragging {
    opacity: 0.5;
    border-color: var(--primary);
}

.menu-section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    background: var(--bg-main);
    cursor: grab;
}

.menu-section-header:hover {
    background: rgba(30, 136, 229, 0.05);
}

.menu-section-header .section-name {
    flex: 1;
    font-weight: 700;
    font-size: 15px;
}

.menu-section-header .expand-btn {
    cursor: pointer;
    transition: transform 0.3s;
    color: var(--text-muted);
}

.menu-section.expanded .expand-btn {
    transform: rotate(180deg);
}

.menu-section-items {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    background: var(--bg-card);
}

.menu-section.expanded .menu-section-items {
    max-height: 500px;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px 12px 40px;
    border-top: 1px solid var(--border-color);
    cursor: grab;
    transition: all 0.2s;
}

.menu-item:hover {
    background: rgba(30, 136, 229, 0.05);
}

.menu-item.dragging {
    opacity: 0.5;
    background: rgba(30, 136, 229, 0.1);
}

.menu-item .item-icon {
    color: var(--primary);
    font-size: 20px;
}

.menu-item span:nth-child(3) {
    flex: 1;
    font-weight: 500;
}

.menu-item .drag-handle {
    color: var(--text-muted);
    font-size: 18px;
}

.toggle-switch.small {
    width: 36px;
    height: 20px;
}

.toggle-switch.small .toggle-slider:before {
    height: 14px;
    width: 14px;
}

.toggle-switch.small input:checked + .toggle-slider:before {
    transform: translateX(-16px);
}
</style>

<script>
let draggedElement = null;
let dragType = null;
const menuEditor = document.getElementById('menuEditor');

// Toggle section expand/collapse
function toggleExpand(btn) {
    const section = btn.closest('.menu-section');
    section.classList.toggle('expanded');
}

// Setup drag for sections
document.querySelectorAll('.menu-section').forEach(section => {
    section.addEventListener('dragstart', function(e) {
        if (e.target.classList.contains('menu-item')) return;
        draggedElement = this;
        dragType = 'section';
        this.classList.add('dragging');
        e.stopPropagation();
    });
    
    section.addEventListener('dragend', function() {
        this.classList.remove('dragging');
        draggedElement = null;
        dragType = null;
    });
    
    section.addEventListener('dragover', function(e) {
        e.preventDefault();
        if (dragType === 'section' && draggedElement && draggedElement !== this) {
            const sections = [...menuEditor.querySelectorAll('.menu-section')];
            const draggedIdx = sections.indexOf(draggedElement);
            const targetIdx = sections.indexOf(this);
            
            if (draggedIdx < targetIdx) {
                this.after(draggedElement);
            } else {
                this.before(draggedElement);
            }
        }
    });
});

// Setup drag for items
document.querySelectorAll('.menu-item').forEach(item => {
    item.addEventListener('dragstart', function(e) {
        draggedElement = this;
        dragType = 'item';
        this.classList.add('dragging');
        e.stopPropagation();
    });
    
    item.addEventListener('dragend', function() {
        this.classList.remove('dragging');
        draggedElement = null;
        dragType = null;
    });
    
    item.addEventListener('dragover', function(e) {
        e.preventDefault();
        if (dragType === 'item' && draggedElement && draggedElement !== this) {
            const container = this.closest('.menu-section-items');
            const items = [...container.querySelectorAll('.menu-item')];
            const draggedIdx = items.indexOf(draggedElement);
            const targetIdx = items.indexOf(this);
            
            if (draggedIdx < targetIdx) {
                this.after(draggedElement);
            } else {
                this.before(draggedElement);
            }
        }
    });
});

// Load saved config on page load and apply to editor
document.addEventListener('DOMContentLoaded', function() {
    // Load menu config from PHP (database) - this is per-user from the server
    const config = <?= json_encode($menuConfig ?? null, JSON_UNESCAPED_UNICODE) ?> || {};
    
    console.log('Settings editor loading config from server:', config);
    
    // Apply section order
    if (config.sectionOrder && config.sectionOrder.length > 0) {
        config.sectionOrder.forEach(sectionId => {
            const section = menuEditor.querySelector(`[data-section="${sectionId}"]`);
            if (section) menuEditor.appendChild(section);
        });
    }
    
    // Apply item order per section (move items from any section to target section)
    if (config.itemOrder) {
        Object.keys(config.itemOrder).forEach(sectionId => {
            const section = menuEditor.querySelector(`[data-section="${sectionId}"]`);
            const itemsContainer = section?.querySelector('.menu-section-items');
            if (itemsContainer && config.itemOrder[sectionId]) {
                config.itemOrder[sectionId].forEach(itemId => {
                    // Search in entire editor, not just current section
                    const item = menuEditor.querySelector(`[data-item="${itemId}"]`);
                    if (item) itemsContainer.appendChild(item);
                });
            }
        });
    }
    
    // Apply visibility (uncheck hidden items)
    if (config.hidden) {
        config.hidden.sections?.forEach(id => {
            const checkbox = document.querySelector(`#menuEditor input[data-type="section"][data-id="${id}"]`);
            if (checkbox) checkbox.checked = false;
        });
        config.hidden.items?.forEach(id => {
            const checkbox = document.querySelector(`#menuEditor input[data-type="item"][data-id="${id}"]`);
            if (checkbox) checkbox.checked = false;
        });
    }
});

function saveFullMenuConfig() {
    const config = {
        sectionOrder: [],
        itemOrder: {},
        hidden: { sections: [], items: [] }
    };
    
    // Collect section order
    document.querySelectorAll('#menuEditor .menu-section').forEach(section => {
        const sectionId = section.dataset.section;
        if (sectionId) {
            config.sectionOrder.push(sectionId);
            
            // Collect item order for this section
            config.itemOrder[sectionId] = [];
            section.querySelectorAll('.menu-item').forEach(item => {
                if (item.dataset.item) {
                    config.itemOrder[sectionId].push(item.dataset.item);
                }
            });
        }
    });
    
    // Collect hidden sections and items
    document.querySelectorAll('#menuEditor input[data-type="section"]').forEach(checkbox => {
        if (!checkbox.checked && checkbox.dataset.id) {
            config.hidden.sections.push(checkbox.dataset.id);
        }
    });
    document.querySelectorAll('#menuEditor input[data-type="item"]').forEach(checkbox => {
        if (!checkbox.checked && checkbox.dataset.id) {
            config.hidden.items.push(checkbox.dataset.id);
        }
    });
    
    console.log('Saving menu config to server:', config);
    
    // Save to server
    fetch('<?= url('/settings/menu-config') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(config)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('تم حفظ إعدادات القائمة بنجاح!');
            location.reload();
        } else {
            alert('خطأ: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في حفظ الإعدادات');
    });
}

function resetFullMenuConfig() {
    // Reset by saving empty config
    fetch('<?= url('/settings/menu-config') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        // Also clear localStorage
        localStorage.removeItem('menuConfig');
        localStorage.removeItem('menuOrder');
        localStorage.removeItem('hiddenSections');
        localStorage.removeItem('openSections');
        alert('تم إعادة تعيين القائمة للإعدادات الافتراضية');
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في إعادة التعيين');
    });
}

// ══════════════════════════════════════════════════════════════════
// إعدادات المظهر
// ══════════════════════════════════════════════════════════════════

function switchThemeMode(mode) {
    const darkModeInput = document.getElementById('themeDarkMode');
    const lightModeColors = document.getElementById('lightModeColors');
    const darkModeColors = document.getElementById('darkModeColors');
    
    if (mode === 'dark') {
        darkModeInput.value = '1';
        lightModeColors.style.display = 'none';
        darkModeColors.style.display = 'block';
    } else {
        darkModeInput.value = '0';
        lightModeColors.style.display = 'block';
        darkModeColors.style.display = 'none';
    }
}

function getThemeValues() {
    const isDarkMode = document.getElementById('themeDarkMode').value === '1';
    
    return {
        dark_mode: isDarkMode,
        // ألوان الوضع النهاري
        primary_color: document.getElementById('themePrimaryColor').value,
        sidebar_color: document.getElementById('themeSidebarColor').value,
        sidebar_text_color: document.getElementById('themeSidebarTextColor').value,
        bg_color: document.getElementById('themeBgColor').value,
        card_color: document.getElementById('themeCardColor').value,
        text_color: document.getElementById('themeTextColor').value,
        section_header_color: document.getElementById('themeSectionHeaderColor').value,
        secondary_color: document.getElementById('themeSecondaryColor').value,
        border_color: document.getElementById('themeBorderColor').value,
        text_secondary_color: document.getElementById('themeTextSecondaryColor').value,
        // ألوان الوضع الليلي
        dark_primary_color: document.getElementById('themeDarkPrimaryColor').value,
        dark_sidebar_color: document.getElementById('themeDarkSidebarColor').value,
        dark_sidebar_text_color: document.getElementById('themeDarkSidebarTextColor').value,
        dark_bg_color: document.getElementById('themeDarkBgColor').value,
        dark_card_color: document.getElementById('themeDarkCardColor').value,
        dark_text_color: document.getElementById('themeDarkTextColor').value,
        dark_section_header_color: document.getElementById('themeDarkSectionHeaderColor').value,
        dark_secondary_color: document.getElementById('themeDarkSecondaryColor').value,
        dark_border_color: document.getElementById('themeDarkBorderColor').value,
        dark_text_secondary_color: document.getElementById('themeDarkTextSecondaryColor').value
    };
}

function applyTheme(theme) {
    const root = document.documentElement;
    
    if (theme.primary_color) {
        root.style.setProperty('--primary', theme.primary_color);
    }
    if (theme.sidebar_color) {
        root.style.setProperty('--bg-sidebar', theme.sidebar_color);
    }
    if (theme.sidebar_text_color) {
        root.style.setProperty('--text-light', theme.sidebar_text_color);
    }
    if (theme.bg_color) {
        root.style.setProperty('--bg-main', theme.bg_color);
    }
    if (theme.card_color) {
        root.style.setProperty('--bg-card', theme.card_color);
    }
    if (theme.text_color) {
        root.style.setProperty('--text-primary', theme.text_color);
    }
    
    if (theme.dark_mode) {
        document.body.classList.add('dark-mode');
    } else {
        document.body.classList.remove('dark-mode');
    }
}

function previewTheme() {
    const theme = getThemeValues();
    applyTheme(theme);
    alert('تم تطبيق المعاينة! اضغط "حفظ" لحفظ التغييرات أو أعد تحميل الصفحة للتراجع.');
}

function saveThemeConfig() {
    const theme = getThemeValues();
    
    console.log('Saving theme config:', theme);
    
    fetch('<?= url('/settings/theme-config') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(theme)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('تم حفظ إعدادات المظهر بنجاح!');
            location.reload();
        } else {
            alert('خطأ: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في حفظ المظهر');
    });
}

function resetThemeConfig() {
    if (!confirm('هل تريد إعادة المظهر للإعدادات الافتراضية؟')) {
        return;
    }
    
    fetch('<?= url('/settings/theme-config') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        alert('تم إعادة المظهر للإعدادات الافتراضية');
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في إعادة التعيين');
    });
}

function resetSystemAppearance() {
    if (!confirm('هل تريد إعادة المظهر الأساسي للنظام للإعدادات الافتراضية؟\n\nسيتم تعيين:\n- الوضع الليلي: مغلق\n- اللون الرئيسي: #1e88e5\n- لون القائمة: #1a237e')) {
        return;
    }
    
    // Create a form to submit the reset values
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= url('/settings') ?>';
    
    // Default values
    const defaults = {
        'dark_mode': '0',
        'primary_color': '#1e88e5',
        'sidebar_color': '#1a237e'
    };
    
    for (const [key, value] of Object.entries(defaults)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }
    
    document.body.appendChild(form);
    form.submit();
}
</script>


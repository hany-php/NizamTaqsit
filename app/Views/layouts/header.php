<header class="header">
    <div class="header-right">
        <button class="menu-toggle" onclick="toggleSidebar()">
            <span class="material-icons-round">menu</span>
        </button>
        <h2 class="page-title"><?= $pageTitle ?? '' ?></h2>
    </div>
    
    <div class="header-left">
        <div class="search-box">
            <span class="material-icons-round">search</span>
            <input type="text" id="globalSearch" placeholder="بحث..." autocomplete="off">
            <button type="button" class="voice-btn" onclick="startVoiceSearch()" title="بحث صوتي">
                <span class="material-icons-round">mic</span>
            </button>
        </div>
        
        <div class="header-actions">
            <button class="action-btn" onclick="toggleDarkMode()" title="الوضع الليلي">
                <span class="material-icons-round">dark_mode</span>
            </button>
            
            <div class="notifications-dropdown">
                <button class="action-btn notification-btn" onclick="toggleNotifications()">
                    <span class="material-icons-round">notifications</span>
                    <span class="badge" id="notificationBadge" style="display:none;">0</span>
                </button>
                <div class="dropdown-menu" id="notificationsMenu">
                    <div class="dropdown-header">
                        <strong>التنبيهات</strong>
                    </div>
                    <div id="notificationsList">
                        <p class="empty-message">لا توجد تنبيهات</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="current-date">
            <span class="material-icons-round">calendar_today</span>
            <span><?= date('Y/m/d') ?></span>
        </div>
    </div>
</header>

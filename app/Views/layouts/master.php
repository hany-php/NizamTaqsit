<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#1e88e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="تقسيط">
    <meta name="description" content="نظام متكامل لإدارة التقسيط والمبيعات">
    
    <title><?= $pageTitle ?? 'نظام تقسيط' ?> - <?= $settings['store_name'] ?? 'نظام تقسيط' ?></title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- Icons -->
    <link rel="icon" type="image/x-icon" href="<?= asset('icons/app_icon.ico') ?>">
    <link rel="apple-touch-icon" href="<?= asset('icons/app_icon.ico') ?>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    
    <!-- Cache Control -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <?php 
    // رقم الإصدار لتجاوز الـ Cache - غير هذا الرقم عند كل تحديث
    $version = '1.0.1.' . date('Ymd');
    ?>
    
    <!-- Stylesheets with Cache Busting -->
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>?v=<?= $version ?>">
    <link rel="stylesheet" href="<?= asset('css/responsive.css') ?>?v=<?= $version ?>"><?php 
    // تطبيق إعدادات المظهر للمستخدم (تتجاوز الإعدادات الافتراضية)
    $userTheme = $themeConfig ?? null;
    $systemDarkMode = ($settings['dark_mode'] ?? 0);
    $userDarkMode = isset($userTheme['dark_mode']) ? $userTheme['dark_mode'] : $systemDarkMode;
    ?>
    
    <style>
        :root {
            /* Dynamic theme variables */
            <?php if ($userDarkMode && !empty($userTheme)): ?>
                /* ألوان الوضع الليلي */
                <?php if (!empty($userTheme['dark_primary_color'])): ?>
                --primary: <?= $userTheme['dark_primary_color'] ?>;
                <?php endif; ?>
                <?php if (!empty($userTheme['dark_sidebar_color'])): ?>
                --bg-sidebar: <?= $userTheme['dark_sidebar_color'] ?>;
                <?php endif; ?>
                <?php if (!empty($userTheme['dark_sidebar_text_color'])): ?>
                --text-light: <?= $userTheme['dark_sidebar_text_color'] ?>;
                <?php endif; ?>
                <?php if (!empty($userTheme['dark_bg_color'])): ?>
                --bg-main: <?= $userTheme['dark_bg_color'] ?>;
                <?php endif; ?>
                <?php if (!empty($userTheme['dark_card_color'])): ?>
                --bg-card: <?= $userTheme['dark_card_color'] ?>;
                --bg-header: <?= $userTheme['dark_card_color'] ?>;
                <?php endif; ?>
                <?php if (!empty($userTheme['dark_text_color'])): ?>
                --text-primary: <?= $userTheme['dark_text_color'] ?>;
                <?php endif; ?>
                <?php if (!empty($userTheme['dark_section_header_color'])): ?>
                --primary-dark: <?= $userTheme['dark_section_header_color'] ?>;
                <?php endif; ?>
                <?php if (!empty($userTheme['dark_secondary_color'])): ?>
                --secondary: <?= $userTheme['dark_secondary_color'] ?>;
                <?php endif; ?>
                <?php if (!empty($userTheme['dark_border_color'])): ?>
                --border-color: <?= $userTheme['dark_border_color'] ?>;
                <?php endif; ?>
                <?php if (!empty($userTheme['dark_text_secondary_color'])): ?>
                --text-secondary: <?= $userTheme['dark_text_secondary_color'] ?>;
                <?php endif; ?>
            <?php else: ?>
                /* ألوان الوضع النهاري */
                <?php if (!empty($userTheme['primary_color'])): ?>
                --primary: <?= $userTheme['primary_color'] ?>;
                <?php elseif (!empty($settings['primary_color'])): ?>
                --primary: <?= $settings['primary_color'] ?>;
                <?php endif; ?>
                <?php if (!empty($userTheme['sidebar_color'])): ?>
                --bg-sidebar: <?= $userTheme['sidebar_color'] ?>;
                <?php elseif (!empty($settings['sidebar_color'])): ?>
                --bg-sidebar: <?= $settings['sidebar_color'] ?>;
                <?php endif; ?>
                <?php if (!empty($userTheme['sidebar_text_color'])): ?>
                --text-light: <?= $userTheme['sidebar_text_color'] ?>;
                <?php endif; ?>
                <?php if (!empty($userTheme['bg_color'])): ?>
                --bg-main: <?= $userTheme['bg_color'] ?>;
                <?php endif; ?>
                <?php if (!empty($userTheme['card_color'])): ?>
                --bg-card: <?= $userTheme['card_color'] ?>;
                --bg-header: <?= $userTheme['card_color'] ?>;
                <?php endif; ?>
                <?php if (!empty($userTheme['text_color'])): ?>
                --text-primary: <?= $userTheme['text_color'] ?>;
                <?php endif; ?>
                <?php if (!empty($userTheme['section_header_color'])): ?>
                --primary-dark: <?= $userTheme['section_header_color'] ?>;
                <?php endif; ?>
                <?php if (!empty($userTheme['secondary_color'])): ?>
                --secondary: <?= $userTheme['secondary_color'] ?>;
                <?php endif; ?>
                <?php if (!empty($userTheme['border_color'])): ?>
                --border-color: <?= $userTheme['border_color'] ?>;
                <?php endif; ?>
                <?php if (!empty($userTheme['text_secondary_color'])): ?>
                --text-secondary: <?= $userTheme['text_secondary_color'] ?>;
                <?php endif; ?>
            <?php endif; ?>
        }
    </style>
</head>
<body class="<?= $userDarkMode ? 'dark-mode' : '' ?>">
    <div class="app-container">
        <?php include __DIR__ . '/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include __DIR__ . '/header.php'; ?>
            
            <div class="page-content">
                <?php if ($successMsg = flash('success')): ?>
                    <div class="alert alert-success">
                        <span class="material-icons-round">check_circle</span>
                        <?= $successMsg ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($errorMsg = flash('error')): ?>
                    <div class="alert alert-danger">
                        <span class="material-icons-round">error</span>
                        <?= $errorMsg ?>
                    </div>
                <?php endif; ?>
                
                <?= $content ?>
            </div>
        </main>
    </div>
    
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <span class="material-icons-round">menu</span>
    </button>
    
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" onclick="toggleMobileMenu()"></div>
    
    <script src="<?= asset('js/app.js') ?>?v=<?= $version ?>"></script>
    
    <!-- Mobile Menu Script -->
    <script>
        function toggleMobileMenu() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            const menuBtn = document.querySelector('.mobile-menu-toggle .material-icons-round');
            
            if (!sidebar || !overlay || !menuBtn) return;
            
            const isOpen = sidebar.classList.contains('active');
            
            if (isOpen) {
                // إغلاق القائمة
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                menuBtn.textContent = 'menu';
                document.body.style.overflow = '';
            } else {
                // فتح القائمة
                sidebar.classList.add('active');
                overlay.classList.add('active');
                menuBtn.textContent = 'close';
                document.body.style.overflow = 'hidden';
            }
        }
        
        // إغلاق القائمة عند الضغط على Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const sidebar = document.querySelector('.sidebar');
                if (sidebar && sidebar.classList.contains('active')) {
                    toggleMobileMenu();
                }
            }
        });
        
        // إغلاق القائمة عند النقر على رابط
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    setTimeout(() => {
                        const sidebar = document.querySelector('.sidebar');
                        if (sidebar && sidebar.classList.contains('active')) {
                            toggleMobileMenu();
                        }
                    }, 100);
                }
            });
        });
        
        // إعادة تعيين حالة القائمة عند تغيير حجم الشاشة
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                const sidebar = document.querySelector('.sidebar');
                const overlay = document.querySelector('.sidebar-overlay');
                const menuBtn = document.querySelector('.mobile-menu-toggle .material-icons-round');
                
                if (sidebar) sidebar.classList.remove('active');
                if (overlay) overlay.classList.remove('active');
                if (menuBtn) menuBtn.textContent = 'menu';
                document.body.style.overflow = '';
            }
        });
    </script>
    
    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('SW registered:', registration.scope);
                    })
                    .catch(error => {
                        console.log('SW registration failed:', error);
                    });
            });
        }
    </script>
    
    <!-- PWA Install Prompt -->
    <script>
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('PWA: beforeinstallprompt event fired');
            // Prevent Chrome 67 and earlier from automatically showing the prompt
            e.preventDefault();
            // Stash the event so it can be triggered later
            deferredPrompt = e;
            // Show custom install button
            console.log('PWA: Showing custom install button');
            showInstallButton();
        });
        
        function showInstallButton() {
            // لا تظهر الزر إذا تم إغلاقه في هذه الجلسة
            if (sessionStorage.getItem('pwa-install-dismissed')) {
                console.log('PWA: Install button was dismissed this session');
                return;
            }
            
            // إنشاء زر التثبيت إذا لم يكن موجوداً
            if (document.getElementById('pwa-install-btn')) {
                document.getElementById('pwa-install-btn').style.display = 'flex';
                return;
            }
            
            const btnContainer = document.createElement('div');
            btnContainer.id = 'pwa-install-btn';
            btnContainer.className = 'pwa-install-btn';
            
            // زر التثبيت
            const installBtn = document.createElement('span');
            installBtn.className = 'pwa-install-text';
            installBtn.innerHTML = '<span class="material-icons-round">install_mobile</span> تثبيت التطبيق';
            installBtn.onclick = installPWA;
            
            // زر الإغلاق
            const closeBtn = document.createElement('span');
            closeBtn.className = 'pwa-close-btn';
            closeBtn.innerHTML = '<span class="material-icons-round">close</span>';
            closeBtn.onclick = dismissPWAButton;
            
            btnContainer.appendChild(installBtn);
            btnContainer.appendChild(closeBtn);
            document.body.appendChild(btnContainer);
        }
        
        function dismissPWAButton(e) {
            if (e) e.stopPropagation();
            // حفظ حالة الإغلاق في sessionStorage (تُمسح عند إغلاق المتصفح)
            sessionStorage.setItem('pwa-install-dismissed', 'true');
            const btn = document.getElementById('pwa-install-btn');
            if (btn) btn.style.display = 'none';
        }
        
        async function installPWA() {
            console.log('PWA: installPWA called, deferredPrompt:', deferredPrompt ? 'exists' : 'null');
            
            if (!deferredPrompt) {
                alert('التطبيق مثبت بالفعل أو لا يمكن تثبيته الآن');
                return;
            }
            
            // Show the install prompt
            deferredPrompt.prompt();
            
            // Wait for the user to respond to the prompt
            const { outcome } = await deferredPrompt.userChoice;
            console.log('PWA: User response to the install prompt:', outcome);
            
            // Clear the deferredPrompt
            deferredPrompt = null;
            
            // Hide the install button
            dismissPWAButton();
        }
        
        // Hide button if app is already installed
        window.addEventListener('appinstalled', () => {
            console.log('PWA: App was installed successfully');
            dismissPWAButton();
            deferredPrompt = null;
        });
        
        // Check if running in standalone mode (already installed)
        if (window.matchMedia('(display-mode: standalone)').matches) {
            console.log('PWA: Running as installed PWA (standalone mode)');
        }
    </script>
    
    <style>
        .pwa-install-btn {
            position: fixed;
            bottom: 90px;
            left: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: linear-gradient(135deg, #1e88e5, #1565c0);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Cairo', sans-serif;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(30, 136, 229, 0.4);
            z-index: 998;
            animation: pwa-pulse 2s infinite;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .pwa-install-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 6px 25px rgba(30, 136, 229, 0.5);
        }
        
        .pwa-install-btn .pwa-install-text {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        
        .pwa-install-btn .pwa-install-text .material-icons-round {
            font-size: 20px;
        }
        
        .pwa-install-btn .pwa-close-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            cursor: pointer;
            margin-right: -4px;
            transition: background 0.2s;
        }
        
        .pwa-install-btn .pwa-close-btn:hover {
            background: rgba(255, 255, 255, 0.35);
        }
        
        .pwa-install-btn .pwa-close-btn .material-icons-round {
            font-size: 16px;
        }
        
        @keyframes pwa-pulse {
            0%, 100% { box-shadow: 0 4px 20px rgba(30, 136, 229, 0.4); }
            50% { box-shadow: 0 4px 30px rgba(30, 136, 229, 0.6); }
        }
        
        @media (max-width: 480px) {
            .pwa-install-btn {
                bottom: 80px;
                left: 10px;
                right: 80px;
                padding: 10px 15px;
                font-size: 13px;
            }
        }
    </style>
</body>
</html>


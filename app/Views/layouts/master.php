<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#1e88e5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="تقسيط">
    <meta name="description" content="نظام متكامل لإدارة التقسيط والمبيعات">
    
    <title><?= $pageTitle ?? 'نظام تقسيط' ?> - <?= $settings['store_name'] ?? 'نظام تقسيط' ?></title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= asset('manifest.json') ?>">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" sizes="192x192" href="<?= asset('assets/icons/icon-192x192.png') ?>">
    <link rel="apple-touch-icon" href="<?= asset('assets/icons/icon-192x192.png') ?>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/responsive.css') ?>">
    
    <?php 
    // تطبيق إعدادات المظهر للمستخدم (تتجاوز الإعدادات الافتراضية)
    $userTheme = $themeConfig ?? null;
    $systemDarkMode = ($settings['dark_mode'] ?? 0);
    $userDarkMode = isset($userTheme['dark_mode']) ? $userTheme['dark_mode'] : $systemDarkMode;
    ?>
    
    <style>
        :root {
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
    
    <script src="<?= asset('js/app.js') ?>"></script>
    
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
            // Prevent Chrome 67 and earlier from automatically showing the prompt
            e.preventDefault();
            // Stash the event so it can be triggered later
            deferredPrompt = e;
            // Show custom install button
            showInstallButton();
        });
        
        function showInstallButton() {
            // إنشاء زر التثبيت إذا لم يكن موجوداً
            if (!document.getElementById('pwa-install-btn')) {
                const btn = document.createElement('button');
                btn.id = 'pwa-install-btn';
                btn.className = 'pwa-install-btn';
                btn.innerHTML = '<span class="material-icons-round">install_mobile</span> تثبيت التطبيق';
                btn.onclick = installPWA;
                document.body.appendChild(btn);
            }
        }
        
        async function installPWA() {
            if (!deferredPrompt) return;
            
            // Show the install prompt
            deferredPrompt.prompt();
            
            // Wait for the user to respond to the prompt
            const { outcome } = await deferredPrompt.userChoice;
            console.log('User response to the install prompt:', outcome);
            
            // Clear the deferredPrompt
            deferredPrompt = null;
            
            // Hide the install button
            const btn = document.getElementById('pwa-install-btn');
            if (btn) btn.remove();
        }
        
        // Hide button if app is already installed
        window.addEventListener('appinstalled', () => {
            console.log('PWA was installed');
            const btn = document.getElementById('pwa-install-btn');
            if (btn) btn.remove();
            deferredPrompt = null;
        });
        
        // Check if running in standalone mode (already installed)
        if (window.matchMedia('(display-mode: standalone)').matches) {
            console.log('Running as installed PWA');
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
            padding: 12px 20px;
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
            transform: scale(1.05);
            box-shadow: 0 6px 25px rgba(30, 136, 229, 0.5);
        }
        
        .pwa-install-btn .material-icons-round {
            font-size: 20px;
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


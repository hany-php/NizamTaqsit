/**
 * نظام تقسيط - JavaScript الرئيسي
 */

// تبديل الشريط الجانبي
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
}

// تبديل الوضع الليلي
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDark ? '1' : '0');
    
    // حفظ في قاعدة البيانات
    fetch(url('/settings'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'dark_mode=' + (isDark ? '1' : '0')
    });
}

// تحميل إعداد الوضع الليلي
document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('darkMode') === '1') {
        document.body.classList.add('dark-mode');
    }
});

// تبديل قائمة التنبيهات
function toggleNotifications() {
    document.getElementById('notificationsMenu').classList.toggle('show');
}

// إغلاق القوائم عند النقر خارجها
document.addEventListener('click', function(e) {
    if (!e.target.closest('.notifications-dropdown')) {
        const menu = document.getElementById('notificationsMenu');
        if (menu) menu.classList.remove('show');
    }
});

// البحث العام
const globalSearch = document.getElementById('globalSearch');
if (globalSearch) {
    let searchTimeout;
    globalSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) return;
        
        searchTimeout = setTimeout(() => {
            // يمكن إضافة بحث عام هنا
            console.log('Searching:', query);
        }, 300);
    });
}

// البحث الصوتي
function startVoiceSearch() {
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
        alert('المتصفح لا يدعم البحث الصوتي');
        return;
    }
    
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const recognition = new SpeechRecognition();
    
    recognition.lang = 'ar-SA';
    recognition.continuous = false;
    recognition.interimResults = false;
    
    recognition.onstart = function() {
        document.querySelector('.voice-btn').classList.add('listening');
    };
    
    recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript;
        document.getElementById('globalSearch').value = transcript;
        document.getElementById('globalSearch').dispatchEvent(new Event('input'));
    };
    
    recognition.onerror = function(event) {
        console.error('Speech recognition error:', event.error);
    };
    
    recognition.onend = function() {
        document.querySelector('.voice-btn').classList.remove('listening');
    };
    
    recognition.start();
}

// دالة URL المساعدة
function url(path) {
    return '/nizam-taqsit/public' + path;
}

// تنسيق المبلغ
function formatMoney(amount, currency = 'ج.م') {
    return parseFloat(amount).toLocaleString('ar-EG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }) + ' ' + currency;
}

// تأكيد الحذف
function confirmDelete(message = 'هل أنت متأكد من الحذف؟') {
    return confirm(message);
}

// عرض رسالة تحميل
function showLoading(element) {
    element.disabled = true;
    element.innerHTML = '<span class="material-icons-round spinning">sync</span> جاري التحميل...';
}

// إخفاء رسالة التحميل
function hideLoading(element, originalText) {
    element.disabled = false;
    element.innerHTML = originalText;
}

// طباعة عنصر
function printElement(elementId) {
    const content = document.getElementById(elementId).innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
            <style>
                body { font-family: 'Cairo', sans-serif; direction: rtl; }
                table { width: 100%; border-collapse: collapse; }
                th, td { padding: 10px; border: 1px solid #ddd; text-align: right; }
                th { background: #f5f5f5; }
                .text-center { text-align: center; }
                @media print { body { margin: 0; } }
            </style>
        </head>
        <body>${content}</body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// إضافة CSS للدوران
const style = document.createElement('style');
style.textContent = `
    .spinning { animation: spin 1s linear infinite; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
    .voice-btn.listening { color: #e53935; animation: pulse 1s infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    .dropdown-menu { 
        position: absolute; left: 0; top: 100%; 
        background: var(--bg-card); border-radius: 12px;
        box-shadow: var(--shadow-lg); min-width: 300px;
        display: none; z-index: 1000;
    }
    .dropdown-menu.show { display: block; }
    .dropdown-header { padding: 15px; border-bottom: 1px solid var(--border-color); }
`;
document.head.appendChild(style);

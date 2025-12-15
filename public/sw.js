// نظام تقسيط - Service Worker
// للعمل بدون إنترنت (PWA)

// تحديث رقم الإصدار عند أي تغيير في الملفات
const CACHE_VERSION = 'v2-' + new Date().toISOString().split('T')[0];
const CACHE_NAME = 'nizamtaqsit-' + CACHE_VERSION;
const OFFLINE_URL = '/offline.html';

// الملفات المطلوب تخزينها للعمل بدون إنترنت
const STATIC_ASSETS = [
  '/offline.html',
  '/manifest.json',
  '/assets/icons/icon-192x192.png',
  '/assets/icons/icon-512x512.png'
];

// الملفات التي يجب دائماً جلبها من الشبكة أولاً
const NETWORK_FIRST_PATTERNS = [
  /\.css$/,
  /\.js$/,
  /\/assets\//
];

// تثبيت Service Worker
self.addEventListener('install', (event) => {
  console.log('[SW] Installing service worker...', CACHE_NAME);
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[SW] Caching static assets');
      return cache.addAll(STATIC_ASSETS);
    })
  );
  // تفعيل فوري بدون انتظار
  self.skipWaiting();
});

// تفعيل Service Worker - حذف الكاش القديم
self.addEventListener('activate', (event) => {
  console.log('[SW] Activating service worker...', CACHE_NAME);
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName.startsWith('nizamtaqsit-') && cacheName !== CACHE_NAME) {
            console.log('[SW] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  // السيطرة على جميع الصفحات فوراً
  self.clients.claim();
});

// التحقق مما إذا كان الطلب يحتاج Network First
function isNetworkFirst(url) {
  return NETWORK_FIRST_PATTERNS.some(pattern => pattern.test(url));
}

// اعتراض الطلبات
self.addEventListener('fetch', (event) => {
  // تجاهل الطلبات غير GET
  if (event.request.method !== 'GET') return;
  
  // تجاهل طلبات API (لأنها تحتاج بيانات حية)
  if (event.request.url.includes('/api/')) return;
  
  const url = event.request.url;
  
  // للملفات الثابتة (CSS, JS) استخدم Network First
  if (isNetworkFirst(url)) {
    event.respondWith(
      fetch(event.request)
        .then((response) => {
          // خزّن نسخة جديدة
          if (response && response.status === 200) {
            const responseToCache = response.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseToCache);
            });
          }
          return response;
        })
        .catch(() => {
          // عند فشل الشبكة، استخدم الكاش
          return caches.match(event.request);
        })
    );
    return;
  }
  
  // للصفحات الأخرى، استخدم Cache First مع تحديث في الخلفية
  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      // إذا وجد في الكاش، أعده
      if (cachedResponse) {
        // تحديث الكاش في الخلفية
        fetch(event.request).then((response) => {
          if (response && response.status === 200 && response.type === 'basic') {
            caches.open(CACHE_NAME).then((cache) => {
              if (!event.request.url.includes('.php')) {
                cache.put(event.request, response);
              }
            });
          }
        });
        return cachedResponse;
      }
      
      // حاول الاتصال بالإنترنت
      return fetch(event.request)
        .then((response) => {
          // لا تخزن الاستجابات الفاشلة
          if (!response || response.status !== 200 || response.type !== 'basic') {
            return response;
          }
          
          // خزّن نسخة من الاستجابة
          const responseToCache = response.clone();
          caches.open(CACHE_NAME).then((cache) => {
            // لا تخزن صفحات PHP الديناميكية
            if (!event.request.url.includes('.php')) {
              cache.put(event.request, responseToCache);
            }
          });
          
          return response;
        })
        .catch(() => {
          // عند فشل الاتصال، اعرض صفحة أوفلاين للصفحات HTML
          if (event.request.headers.get('accept')?.includes('text/html')) {
            return caches.match(OFFLINE_URL);
          }
        });
    })
  );
});

// استقبال رسائل من التطبيق
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  // إمكانية مسح الكاش يدوياً من التطبيق
  if (event.data && event.data.type === 'CLEAR_CACHE') {
    caches.keys().then((cacheNames) => {
      return Promise.all(cacheNames.map((cacheName) => caches.delete(cacheName)));
    }).then(() => {
      console.log('[SW] All caches cleared');
      event.source.postMessage({ type: 'CACHE_CLEARED' });
    });
  }
});

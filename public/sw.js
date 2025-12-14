// نظام تقسيط - Service Worker
// للعمل بدون إنترنت (PWA)

const CACHE_NAME = 'nizamtaqsit-v1';
const OFFLINE_URL = '/offline.html';

// الملفات المطلوب تخزينها للعمل بدون إنترنت
const STATIC_ASSETS = [
  '/',
  '/offline.html',
  '/assets/css/app.css',
  '/assets/css/responsive.css',
  '/assets/js/app.js',
  '/manifest.json',
  '/assets/icons/icon-192x192.png',
  '/assets/icons/icon-512x512.png'
];

// تثبيت Service Worker
self.addEventListener('install', (event) => {
  console.log('[SW] Installing service worker...');
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[SW] Caching static assets');
      return cache.addAll(STATIC_ASSETS);
    })
  );
  self.skipWaiting();
});

// تفعيل Service Worker
self.addEventListener('activate', (event) => {
  console.log('[SW] Activating service worker...');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('[SW] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// اعتراض الطلبات
self.addEventListener('fetch', (event) => {
  // تجاهل الطلبات غير GET
  if (event.request.method !== 'GET') return;
  
  // تجاهل طلبات API (لأنها تحتاج بيانات حية)
  if (event.request.url.includes('/api/')) return;
  
  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      // إذا وجد في الكاش، أعده
      if (cachedResponse) {
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
          if (event.request.headers.get('accept').includes('text/html')) {
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
});

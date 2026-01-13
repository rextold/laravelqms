// Laravel QMS Service Worker
const CACHE_NAME = 'laravel-qms-v1';
const OFFLINE_URL = '/offline.html';

// Assets to cache immediately
const STATIC_CACHE_URLS = [
  '/',
  '/login',
  '/kiosk',
  '/monitor',
  '/offline.html',
  '/manifest.json',
  // External CDN resources
  'https://cdn.tailwindcss.com',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// API endpoints to cache with network-first strategy
const API_CACHE_URLS = [
  '/monitor/data',
  '/kiosk/counters',
  '/counter/status'
];

// Install event - cache static assets
self.addEventListener('install', event => {
  console.log('Service Worker: Installing...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Service Worker: Caching static assets');
        return cache.addAll(STATIC_CACHE_URLS);
      })
      .then(() => {
        console.log('Service Worker: Skip waiting');
        return self.skipWaiting();
      })
      .catch(error => {
        console.error('Service Worker: Cache failed', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  console.log('Service Worker: Activating...');
  
  event.waitUntil(
    caches.keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            if (cacheName !== CACHE_NAME) {
              console.log('Service Worker: Deleting old cache', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('Service Worker: Claiming clients');
        return self.clients.claim();
      })
  );
});

// Fetch event - implement caching strategies
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }
  
  // Skip Chrome extension requests
  if (url.protocol === 'chrome-extension:') {
    return;
  }
  
  // Handle different types of requests
  if (isAPIRequest(url)) {
    // API requests: Network first, cache fallback
    event.respondWith(networkFirstStrategy(request));
  } else if (isStaticAsset(url)) {
    // Static assets: Cache first, network fallback
    event.respondWith(cacheFirstStrategy(request));
  } else if (isNavigationRequest(request)) {
    // Navigation requests: Network first, offline fallback
    event.respondWith(navigationStrategy(request));
  } else {
    // Default: Network first
    event.respondWith(networkFirstStrategy(request));
  }
});

// Background sync for queue operations
self.addEventListener('sync', event => {
  console.log('Service Worker: Background sync triggered', event.tag);
  
  if (event.tag === 'queue-operations') {
    event.waitUntil(syncQueueOperations());
  }
});

// Push notifications (for future real-time updates)
self.addEventListener('push', event => {
  console.log('Service Worker: Push received', event);
  
  if (event.data) {
    const data = event.data.json();
    const options = {
      body: data.body,
      icon: '/icons/icon-192x192.png',
      badge: '/icons/icon-72x72.png',
      tag: 'qms-notification',
      requireInteraction: true,
      actions: [
        {
          action: 'view',
          title: 'View Queue'
        },
        {
          action: 'dismiss',
          title: 'Dismiss'
        }
      ]
    };
    
    event.waitUntil(
      self.registration.showNotification(data.title, options)
    );
  }
});

// Notification click handler
self.addEventListener('notificationclick', event => {
  console.log('Service Worker: Notification clicked', event);
  
  event.notification.close();
  
  if (event.action === 'view') {
    event.waitUntil(
      clients.openWindow('/monitor')
    );
  }
});

// Helper functions
function isAPIRequest(url) {
  return API_CACHE_URLS.some(pattern => url.pathname.includes(pattern)) ||
         url.pathname.startsWith('/api/');
}

function isStaticAsset(url) {
  return url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/);
}

function isNavigationRequest(request) {
  return request.mode === 'navigate' || 
         (request.method === 'GET' && request.headers.get('accept').includes('text/html'));
}

// Caching strategies
async function networkFirstStrategy(request) {
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.log('Service Worker: Network failed, trying cache', error);
    const cachedResponse = await caches.match(request);
    
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Return offline page for navigation requests
    if (isNavigationRequest(request)) {
      return caches.match(OFFLINE_URL);
    }
    
    throw error;
  }
}

async function cacheFirstStrategy(request) {
  const cachedResponse = await caches.match(request);
  
  if (cachedResponse) {
    return cachedResponse;
  }
  
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.log('Service Worker: Network and cache failed', error);
    throw error;
  }
}

async function navigationStrategy(request) {
  try {
    const networkResponse = await fetch(request);
    return networkResponse;
  } catch (error) {
    console.log('Service Worker: Navigation failed, showing offline page', error);
    return caches.match(OFFLINE_URL);
  }
}

// Background sync for queue operations
async function syncQueueOperations() {
  try {
    console.log('Service Worker: Syncing queue operations...');
    
    // Get pending operations from IndexedDB
    const pendingOps = await getPendingOperations();
    
    for (const operation of pendingOps) {
      try {
        const response = await fetch(operation.url, {
          method: operation.method,
          headers: operation.headers,
          body: operation.body
        });
        
        if (response.ok) {
          await removePendingOperation(operation.id);
          console.log('Service Worker: Operation synced successfully', operation.id);
        }
      } catch (error) {
        console.log('Service Worker: Failed to sync operation', operation.id, error);
      }
    }
  } catch (error) {
    console.error('Service Worker: Background sync failed', error);
  }
}

// IndexedDB helpers for offline operations
async function getPendingOperations() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('QMSOfflineDB', 1);
    
    request.onerror = () => reject(request.error);
    request.onsuccess = () => {
      const db = request.result;
      const transaction = db.transaction(['pendingOperations'], 'readonly');
      const store = transaction.objectStore('pendingOperations');
      const getAllRequest = store.getAll();
      
      getAllRequest.onsuccess = () => resolve(getAllRequest.result);
      getAllRequest.onerror = () => reject(getAllRequest.error);
    };
    
    request.onupgradeneeded = (event) => {
      const db = event.target.result;
      if (!db.objectStoreNames.contains('pendingOperations')) {
        db.createObjectStore('pendingOperations', { keyPath: 'id' });
      }
    };
  });
}

async function removePendingOperation(id) {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('QMSOfflineDB', 1);
    
    request.onsuccess = () => {
      const db = request.result;
      const transaction = db.transaction(['pendingOperations'], 'readwrite');
      const store = transaction.objectStore('pendingOperations');
      const deleteRequest = store.delete(id);
      
      deleteRequest.onsuccess = () => resolve();
      deleteRequest.onerror = () => reject(deleteRequest.error);
    };
  });
}
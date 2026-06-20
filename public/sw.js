const CACHE_NAME = 'progress-track-v1';

self.addEventListener('install', (e) => {
    self.skipWaiting();
});

self.addEventListener('activate', (e) => {
    e.waitUntil(clients.claim());
});

self.addEventListener('push', (e) => {
    let data = { title: 'progress.track', body: 'Time to log your daily progress!', url: '/checkins/daily' };
    try { data = { ...data, ...e.data.json() }; } catch (_) {}

    e.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/icons/icon-192.png',
            badge: '/icons/icon-72.png',
            tag: 'daily-reminder',
            renotify: true,
            vibrate: [100, 50, 100],
            data: { url: data.url },
            actions: [
                { action: 'open', title: 'Log now' },
                { action: 'dismiss', title: 'Later' },
            ],
        })
    );
});

self.addEventListener('notificationclick', (e) => {
    e.notification.close();
    if (e.action === 'dismiss') return;
    const url = e.notification.data?.url || '/';
    e.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (const client of windowClients) {
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    client.navigate(url);
                    return client.focus();
                }
            }
            if (clients.openWindow) return clients.openWindow(url);
        })
    );
});

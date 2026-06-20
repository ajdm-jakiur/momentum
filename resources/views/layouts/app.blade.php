<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d0d0d">
    <meta name="vapid-key" content="{{ config('services.vapid.public_key') }}">
    <link rel="manifest" href="/manifest.json">
    <title>{{ config('app.name', 'ProgressTrack') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700;800&family=Syne:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased text-ink-primary bg-base-bg overflow-x-hidden">

<div class="flex min-h-dvh">

    {{-- ─── Desktop Sidebar ─────────────────────────────────────────── --}}
    <aside class="hidden lg:flex flex-col fixed inset-y-0 left-0 w-64 bg-base-surface border-r border-base-border z-40">
        {{-- Logo --}}
        <div class="h-16 flex items-center px-6 border-b border-base-border flex-shrink-0">
            <a href="{{ route('dashboard') }}" wire:navigate class="font-mono font-extrabold text-lg tracking-tight">
                progress<span class="text-accent">.</span>track
            </a>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5">
            @php
                $navItems = [
                    ['route' => 'dashboard',    'label' => 'Dashboard',  'match' => 'dashboard',    'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['route' => 'sectors.index','label' => 'Sectors',    'match' => 'sectors.*',    'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                    ['route' => 'checkins.daily','label' => 'Check-in', 'match' => 'checkins.*',   'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['route' => 'tasks.index',  'label' => 'Tasks',      'match' => 'tasks.*',      'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                    ['route' => 'reports.weekly','label' => 'Reports',   'match' => 'reports.*',    'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                    ['route' => 'roadmaps.import','label' => 'Import',   'match' => 'roadmaps.import','icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12'],
                ];
            @endphp
            @foreach($navItems as $item)
                @php $isActive = request()->routeIs($item['match']); @endphp
                <a href="{{ route($item['route']) }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-mono text-sm font-medium transition-colors duration-150
                          {{ $isActive ? 'bg-accent/15 text-accent' : 'text-ink-secondary hover:bg-base-elevated hover:text-ink-primary' }}">
                    <svg class="w-4.5 h-4.5 flex-shrink-0 {{ $isActive ? 'text-accent' : '' }}" style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                    </svg>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- User + Notifications --}}
        <div class="border-t border-base-border p-3 flex-shrink-0">
            {{-- Push bell --}}
            <button id="push-bell" onclick="requestPushPermission()" title="Enable notifications"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-ink-tertiary hover:bg-base-elevated hover:text-ink-secondary transition-colors mb-1 font-mono text-xs cursor-pointer">
                <svg style="width:16px;height:16px;flex-shrink:0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span id="push-bell-label">Enable notifications</span>
            </button>
            <livewire:layout.navigation />
        </div>
    </aside>

    {{-- ─── Main content ────────────────────────────────────────────── --}}
    <div class="flex-1 min-w-0 lg:ml-64 flex flex-col min-h-dvh overflow-x-hidden">

        {{-- Mobile header --}}
        <header class="lg:hidden sticky top-0 z-30 bg-base-surface/90 backdrop-blur-md border-b border-base-border h-14 flex items-center justify-between px-4 flex-shrink-0">
            <a href="{{ route('dashboard') }}" wire:navigate class="font-mono font-extrabold text-base tracking-tight">
                progress<span class="text-accent">.</span>track
            </a>
            <div class="flex items-center gap-2">
                <button onclick="requestPushPermission()" class="p-2 rounded-lg text-ink-tertiary hover:text-ink-secondary hover:bg-base-elevated transition-colors">
                    <svg style="width:20px;height:20px" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </button>
                <livewire:layout.navigation :open-down="true" />
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 pb-24 lg:pb-8 md:px-6 min-w-0 w-full overflow-x-hidden">
            {{ $slot }}
        </main>
    </div>

    {{-- ─── Mobile bottom nav ──────────────────────────────────────── --}}
    <nav class="lg:hidden fixed bottom-0 inset-x-0 bg-base-surface/95 backdrop-blur-md border-t border-base-border z-50" style="padding-bottom: env(safe-area-inset-bottom)">
        <div class="flex items-stretch h-16">
            @php
                $mobileNav = [
                    ['route' => 'dashboard',     'label' => 'Home',    'match' => 'dashboard',  'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['route' => 'sectors.index', 'label' => 'Sectors', 'match' => 'sectors.*',  'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                    ['route' => 'checkins.daily','label' => 'Log',     'match' => 'checkins.*', 'icon' => 'M12 4v16m8-8H4'],
                    ['route' => 'tasks.index',   'label' => 'Tasks',   'match' => 'tasks.*',    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                    ['route' => 'reports.weekly','label' => 'Reports', 'match' => 'reports.*',  'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ];
            @endphp
            @foreach($mobileNav as $item)
                @php $isActive = request()->routeIs($item['match']); @endphp
                <a href="{{ route($item['route']) }}" wire:navigate
                   class="flex-1 flex flex-col items-center justify-center gap-0.5 transition-colors duration-150
                          {{ $isActive ? 'text-accent' : 'text-ink-tertiary active:text-ink-secondary' }}">
                    <svg style="width:22px;height:22px" fill="{{ $isActive ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                    </svg>
                    <span class="font-mono text-[10px] font-medium">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

</div>

@livewireScripts
<script>
// Push notification setup
async function requestPushPermission() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        alert('Push notifications not supported in this browser.');
        return;
    }
    const perm = await Notification.requestPermission();
    if (perm !== 'granted') return;
    try {
        const reg = await navigator.serviceWorker.ready;
        const vapidKey = document.querySelector('meta[name="vapid-key"]')?.content;
        if (!vapidKey) return;
        const sub = await reg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidKey),
        });
        await fetch('/push/subscribe', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify(sub.toJSON()),
        });
        const bell = document.getElementById('push-bell-label');
        if (bell) bell.textContent = 'Notifications enabled';
    } catch (e) { console.error('Push subscribe failed', e); }
}
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    return Uint8Array.from([...rawData].map(c => c.charCodeAt(0)));
}
// Check current permission state on load
if ('Notification' in window && Notification.permission === 'granted') {
    const bell = document.getElementById('push-bell-label');
    if (bell) bell.textContent = 'Notifications enabled';
}
</script>
</body>
</html>

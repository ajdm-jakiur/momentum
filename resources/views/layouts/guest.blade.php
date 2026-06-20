<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d0d0d">
    <title>{{ config('app.name', 'ProgressTrack') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700;800&family=Syne:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-base-bg text-ink-primary min-h-dvh flex items-center justify-center">
    <div class="w-full max-w-sm px-4 py-8">
        <div class="text-center mb-8">
            <a href="/" class="font-mono font-extrabold text-2xl tracking-tight">
                progress<span class="text-accent">.</span>track
            </a>
        </div>
        <div class="bg-base-surface border border-base-border rounded-2xl p-7 shadow-2xl">
            {{ $slot }}
        </div>
    </div>
    @livewireScripts
</body>
</html>

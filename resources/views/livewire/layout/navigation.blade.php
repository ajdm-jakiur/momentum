<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $openDown = false;

    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" @click.outside="open = false"
            class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-ink-secondary hover:bg-base-elevated hover:text-ink-primary transition-colors">
        <div class="w-7 h-7 rounded-full bg-accent/20 flex items-center justify-center flex-shrink-0">
            <span class="font-mono text-xs font-bold text-accent">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
        </div>
        <span class="font-mono text-xs truncate max-w-[100px] hidden lg:block">{{ auth()->user()->name }}</span>
        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1"
         class="absolute w-52 bg-base-elevated border border-base-border rounded-xl shadow-2xl overflow-hidden z-50
                {{ $openDown ? 'top-full right-0 mt-2' : 'bottom-full left-0 mb-1' }}">
        <a href="{{ route('profile') }}" wire:navigate @click="open=false"
           class="flex items-center gap-2.5 px-3.5 py-2.5 text-sm font-mono text-ink-secondary hover:bg-base-hover hover:text-ink-primary transition-colors">
            <svg style="width:15px;height:15px;flex-shrink:0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Profile
        </a>
        <a href="{{ route('roadmaps.import') }}" wire:navigate @click="open=false"
           class="flex items-center gap-2.5 px-3.5 py-2.5 text-sm font-mono text-ink-secondary hover:bg-base-hover hover:text-ink-primary transition-colors">
            <svg style="width:15px;height:15px;flex-shrink:0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Import Roadmap
        </a>
        <div class="border-t border-base-border"></div>
        <button wire:click="logout"
                class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-sm font-mono text-danger hover:bg-danger/10 transition-colors">
            <svg style="width:15px;height:15px;flex-shrink:0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Log Out
        </button>
    </div>
</div>

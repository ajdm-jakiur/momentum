<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <x-auth-session-status class="mb-4 text-ok font-mono text-sm" :status="session('status')" />

    <h1 class="font-mono font-extrabold text-xl mb-1 text-ink-primary">Welcome back</h1>
    <p class="font-mono text-xs text-ink-tertiary mb-6">Sign in to your account</p>

    <form wire:submit="login" class="space-y-4">
        <div>
            <label for="email" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-ink-tertiary mb-1.5">Email</label>
            <input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username"
                   class="w-full bg-base-elevated border border-base-border rounded-lg px-3.5 py-2.5 text-sm font-mono text-ink-primary placeholder:text-ink-tertiary focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-1.5 text-xs font-mono text-danger" />
        </div>

        <div>
            <label for="password" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-ink-tertiary mb-1.5">Password</label>
            <input wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password"
                   class="w-full bg-base-elevated border border-base-border rounded-lg px-3.5 py-2.5 text-sm font-mono text-ink-primary placeholder:text-ink-tertiary focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-1.5 text-xs font-mono text-danger" />
        </div>

        <div class="flex items-center justify-between pt-1">
            <label class="flex items-center gap-2 cursor-pointer">
                <input wire:model="form.remember" id="remember" type="checkbox"
                       class="rounded bg-base-elevated border-base-border text-accent focus:ring-accent/50" />
                <span class="font-mono text-xs text-ink-secondary">Remember me</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" wire:navigate
                   class="font-mono text-xs text-accent hover:text-accent-dark transition-colors">Forgot password?</a>
            @endif
        </div>

        <button type="submit"
                class="w-full bg-accent hover:bg-accent-dark text-white font-mono font-bold text-sm py-2.5 rounded-lg transition-colors duration-150 mt-2">
            Sign in
        </button>
    </form>
</div>

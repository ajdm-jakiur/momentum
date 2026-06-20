<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    #[Locked]
    public ?string $referralCode = null;

    #[Locked]
    public ?int $referredById = null;

    public function mount(string $code = ''): void
    {
        if (empty($code)) {
            $this->redirect(route('login'));
            return;
        }
        $referrer = User::where('referral_code', $code)->first();
        if (! $referrer) {
            session()->flash('status', 'Invalid referral link.');
            $this->redirect(route('login'));
            return;
        }
        $this->referralCode = $code;
        $this->referredById = $referrer->id;
    }

    public function register(): void
    {
        $validated = $this->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password']    = Hash::make($validated['password']);
        $validated['referred_by'] = $this->referredById;
        $validated['referral_code'] = substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(24))), 0, 16);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <h1 class="font-mono font-extrabold text-xl mb-1 text-ink-primary">Create account</h1>
    <p class="font-mono text-xs text-ink-tertiary mb-6">Invited to join progress.track</p>

    <form wire:submit="register" class="space-y-4">
        <div>
            <label for="name" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-ink-tertiary mb-1.5">Name</label>
            <input wire:model="name" id="name" type="text" name="name" required autofocus autocomplete="name"
                   class="w-full bg-base-elevated border border-base-border rounded-lg px-3.5 py-2.5 text-sm font-mono text-ink-primary focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors" />
            <x-input-error :messages="$errors->get('name')" class="mt-1.5 text-xs font-mono text-danger" />
        </div>

        <div>
            <label for="email" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-ink-tertiary mb-1.5">Email</label>
            <input wire:model="email" id="email" type="email" name="email" required autocomplete="username"
                   class="w-full bg-base-elevated border border-base-border rounded-lg px-3.5 py-2.5 text-sm font-mono text-ink-primary focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs font-mono text-danger" />
        </div>

        <div>
            <label for="password" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-ink-tertiary mb-1.5">Password</label>
            <input wire:model="password" id="password" type="password" name="password" required autocomplete="new-password"
                   class="w-full bg-base-elevated border border-base-border rounded-lg px-3.5 py-2.5 text-sm font-mono text-ink-primary focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs font-mono text-danger" />
        </div>

        <div>
            <label for="password_confirmation" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-ink-tertiary mb-1.5">Confirm password</label>
            <input wire:model="password_confirmation" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="w-full bg-base-elevated border border-base-border rounded-lg px-3.5 py-2.5 text-sm font-mono text-ink-primary focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5 text-xs font-mono text-danger" />
        </div>

        <button type="submit"
                class="w-full bg-accent hover:bg-accent-dark text-white font-mono font-bold text-sm py-2.5 rounded-lg transition-colors duration-150 mt-2">
            Create account
        </button>
    </form>

    <p class="mt-5 text-center font-mono text-xs text-ink-tertiary">
        Already have an account?
        <a href="{{ route('login') }}" wire:navigate class="text-accent hover:underline">Sign in</a>
    </p>
</div>

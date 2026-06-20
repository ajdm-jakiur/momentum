<div class="px-5 py-6 lg:px-7 space-y-6 max-w-2xl">

    <h1 class="font-mono text-2xl font-extrabold text-ink-primary">Profile</h1>

    <div class="bg-base-surface border border-base-border rounded-xl p-6">
        <livewire:profile.update-profile-information-form />
    </div>

    <div class="bg-base-surface border border-base-border rounded-xl p-6">
        <livewire:profile.update-password-form />
    </div>

    {{-- Referral / Invite section --}}
    <div class="bg-base-surface border border-base-border rounded-xl p-6">
        <h2 class="font-mono font-bold text-sm text-ink-primary mb-1">Invite link</h2>
        <p class="font-mono text-xs text-ink-tertiary mb-4">Share this link — only people with it can create an account.</p>

        @if($user->referral_code)
            @php $inviteUrl = url('/register/' . $user->referral_code); @endphp
            <div x-data="{ copied: false }" class="flex items-center gap-2">
                <code class="flex-1 font-mono text-xs bg-base-elevated border border-base-border rounded-lg px-3.5 py-2.5 text-ink-secondary truncate">{{ $inviteUrl }}</code>
                <button @click="navigator.clipboard.writeText('{{ $inviteUrl }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                        class="flex-shrink-0 bg-accent hover:bg-accent-dark text-white font-mono text-xs font-bold px-3.5 py-2.5 rounded-lg transition-colors">
                    <span x-show="!copied">Copy</span>
                    <span x-show="copied" x-cloak>Copied!</span>
                </button>
            </div>

            @if($user->referredUsers->isNotEmpty())
                <div class="mt-5 border-t border-base-border pt-4">
                    <div class="font-mono text-[10px] font-bold uppercase tracking-widest text-ink-tertiary mb-3">People you invited</div>
                    <div class="space-y-2">
                        @foreach($user->referredUsers as $invited)
                            <div class="flex items-center gap-3 py-1">
                                <div class="w-6 h-6 rounded-full bg-accent/20 flex items-center justify-center flex-shrink-0">
                                    <span class="font-mono text-[10px] font-bold text-accent">{{ strtoupper(substr($invited->name, 0, 1)) }}</span>
                                </div>
                                <span class="font-mono text-sm text-ink-secondary">{{ $invited->name }}</span>
                                <span class="font-mono text-xs text-ink-tertiary">{{ $invited->email }}</span>
                                <span class="font-mono text-[10px] text-ink-tertiary ml-auto">{{ $invited->created_at->diffForHumans() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            <p class="font-mono text-xs text-ink-tertiary">No referral code generated. Contact admin.</p>
        @endif
    </div>

    <div class="bg-base-surface border border-danger/20 rounded-xl p-6">
        <livewire:profile.delete-user-form />
    </div>

</div>

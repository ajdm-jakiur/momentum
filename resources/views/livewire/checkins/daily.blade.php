<div>
    <div class="px-5 py-6 lg:px-7">
        <h1 class="font-mono text-2xl font-extrabold text-ink-primary mb-1">Daily Check-in</h1>
        <p class="text-sm text-ink-secondary font-mono mb-6">{{ now()->format('l, F j') }} — {{ $totalMinutesToday }} min logged today</p>

        @if($streaks->isNotEmpty())
            <div class="flex flex-wrap gap-2 mb-6">
                @foreach($streaks as $streak)
                    @if($streak->current_streak > 0)
                        <span class="bg-accent/15 text-accent border border-accent/25 font-mono text-xs font-bold px-2.5 py-1 rounded-full">
                            🔥 {{ $streak->sector->name }}: {{ $streak->current_streak }}d (best {{ $streak->longest_streak }}d)
                        </span>
                    @endif
                @endforeach
            </div>
        @endif

        <div class="bg-base-surface border border-base-border rounded-xl px-5 py-5 mb-6">
            <h2 class="font-mono font-bold text-sm text-ink-primary mb-4">Log time</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                <div>
                    <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Sector</label>
                    <select wire:model="form.sector_id" class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors">
                        <option value="">None</option>
                        @foreach($sectors as $sector)
                            <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Task</label>
                    <select wire:model="form.task_id" class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors">
                        <option value="">None</option>
                        @foreach($tasks as $task)
                            <option value="{{ $task->id }}">{{ $task->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Type</label>
                    <select wire:model="form.checkin_type" class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors">
                        @foreach($checkinTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Minutes</label>
                    <input type="number" min="0" max="1440" wire:model="form.minutes_spent" class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors">
                </div>
            </div>
            <div class="mb-4">
                <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Note</label>
                <input type="text" wire:model="form.note" placeholder="What did you work on?" class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors">
            </div>
            <button wire:click="save" class="bg-accent hover:bg-accent-dark text-white font-mono font-bold text-sm px-4 py-2.5 rounded-lg transition-colors">Log it</button>
        </div>

        <h2 class="font-mono font-bold text-sm text-ink-tertiary uppercase tracking-wide mb-3">Today's log</h2>
        @forelse($todaysCheckins as $checkin)
            <div class="flex items-center gap-3 bg-base-surface border border-base-border rounded-xl px-4 py-3.5 mb-2">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-mono text-[10px] font-bold uppercase tracking-wide bg-community/20 text-community px-2 py-0.5 rounded">{{ $checkinTypes[$checkin->checkin_type] ?? $checkin->checkin_type }}</span>
                        @if($checkin->sector)<span class="font-mono text-[11px] text-ink-tertiary">{{ $checkin->sector->name }}</span>@endif
                        @if($checkin->minutes_spent)<span class="font-mono text-[11px] text-ink-secondary">{{ $checkin->minutes_spent }} min</span>@endif
                    </div>
                    @if($checkin->note)<div class="text-sm text-ink-primary mt-1">{{ $checkin->note }}</div>@endif
                </div>
                <button wire:click="delete({{ $checkin->id }})" wire:confirm="Remove this entry?" class="font-mono text-xs text-danger hover:bg-danger/10 rounded px-2 py-1 transition-colors flex-shrink-0">Remove</button>
            </div>
        @empty
            <p class="text-sm text-ink-tertiary font-mono">Nothing logged yet today.</p>
        @endforelse
    </div>
</div>

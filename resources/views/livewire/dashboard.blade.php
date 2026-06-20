<div class="px-5 py-6 lg:px-7 space-y-6">

    {{-- Hero bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="font-mono text-2xl font-extrabold text-ink-primary">
                {{ now()->hour < 12 ? 'Good morning' : (now()->hour < 18 ? 'Good afternoon' : 'Good evening') }}
            </h1>
            <p class="font-mono text-[11px] text-ink-tertiary uppercase tracking-widest mt-0.5">{{ now()->format('l, F j') }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('checkins.daily') }}" wire:navigate class="bg-accent hover:bg-accent-dark text-white font-mono font-bold rounded-lg px-4 py-2 transition-colors text-sm">Daily Check-in</a>
            <a href="{{ route('reports.weekly') }}" wire:navigate class="bg-base-elevated hover:bg-base-hover text-ink-secondary hover:text-ink-primary font-mono rounded-lg px-4 py-2 transition-colors text-sm">Weekly Report</a>
        </div>
    </div>

    {{-- Sector cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($sectorCards as $card)
            <div class="bg-base-surface border border-base-border rounded-xl p-5 relative overflow-hidden">
                {{-- Left accent bar --}}
                <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-xl" style="background-color: {{ $card['sector']->color }}"></div>

                {{-- Card header --}}
                <div class="flex items-center justify-between mb-3 pl-2">
                    <div class="flex items-center gap-2">
                        @if($card['sector']->icon)<span class="text-lg">{{ $card['sector']->icon }}</span>@endif
                        <span class="font-mono font-bold text-ink-primary">{{ $card['sector']->name }}</span>
                    </div>
                    @if($card['streak'] && $card['streak']->current_streak > 0)
                        <span class="bg-accent/15 text-accent font-mono text-xs font-bold px-2 py-1 rounded-full">🔥 {{ $card['streak']->current_streak }}d</span>
                    @endif
                </div>

                {{-- Progress bar --}}
                <div class="pl-2 mb-1">
                    <div class="h-1.5 rounded-full bg-base-elevated">
                        <div class="h-1.5 rounded-full" style="width: {{ $card['progress'] }}%; background-color: {{ $card['sector']->color }}"></div>
                    </div>
                </div>
                <div class="font-mono text-xs text-ink-secondary mb-4 pl-2">{{ $card['progress'] }}% avg across {{ $card['roadmapCount'] }} roadmap(s)</div>

                {{-- Stats row --}}
                <div class="grid grid-cols-2 gap-2 mb-4 pl-2">
                    <div class="bg-base-elevated border border-base-border rounded-lg px-2 py-1.5 text-center">
                        <div class="font-mono text-sm font-bold text-ink-primary">{{ intdiv($card['minutesThisWeek'], 60) }}h {{ $card['minutesThisWeek'] % 60 }}m</div>
                        <div class="font-mono text-[10px] font-bold uppercase tracking-widest text-ink-tertiary">This week</div>
                    </div>
                    <div class="bg-base-elevated border border-base-border rounded-lg px-2 py-1.5 text-center">
                        <div class="font-mono text-sm font-bold text-ink-primary">{{ intdiv($card['minutesThisMonth'], 60) }}h {{ $card['minutesThisMonth'] % 60 }}m</div>
                        <div class="font-mono text-[10px] font-bold uppercase tracking-widest text-ink-tertiary">This month</div>
                    </div>
                </div>

                {{-- Next block --}}
                <div class="pl-2">
                    @if($card['nextBlock'])
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-[10px] uppercase tracking-widest text-ink-tertiary">Next up:</span>
                            <span class="text-xs text-ink-secondary">{{ $card['nextBlock']->title }}</span>
                            <span class="font-mono text-[10px] bg-base-elevated text-ink-tertiary px-1.5 py-0.5 rounded">{{ $card['nextBlock']->required_done }}/{{ $card['nextBlock']->required_total }}</span>
                        </div>
                    @elseif($card['roadmapCount'] > 0)
                        <div class="text-xs font-mono text-ok font-bold">All roadmaps complete 🎉</div>
                    @else
                        <div class="text-xs text-ink-tertiary font-mono">No roadmaps yet.</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

</div>

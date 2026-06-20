@props(['report', 'rangeLabel', 'active'])

<div class="px-5 py-6 lg:px-7">
    <div class="flex items-center justify-between mb-2">
        <h1 class="font-mono text-2xl font-extrabold text-ink-primary">Reports</h1>
        <div class="flex gap-1 font-mono text-xs">
            <a href="{{ route('reports.weekly') }}" wire:navigate class="px-3 py-1.5 rounded-lg transition-colors {{ $active === 'weekly' ? 'bg-accent text-white font-bold' : 'bg-base-elevated text-ink-secondary hover:bg-base-hover hover:text-ink-primary' }}">Weekly</a>
            <a href="{{ route('reports.monthly') }}" wire:navigate class="px-3 py-1.5 rounded-lg transition-colors {{ $active === 'monthly' ? 'bg-accent text-white font-bold' : 'bg-base-elevated text-ink-secondary hover:bg-base-hover hover:text-ink-primary' }}">Monthly</a>
        </div>
    </div>
    <p class="text-sm text-ink-secondary font-mono mb-6">{{ $rangeLabel }} — {{ intdiv($report['totalMinutes'], 60) }}h {{ $report['totalMinutes'] % 60 }}m logged</p>

    <div class="mb-8">
        <div class="font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-3">Daily activity</div>
        <div class="flex gap-1 flex-wrap">
            @php $max = max(1, max($report['dailyTotals'])); @endphp
            @foreach($report['dailyTotals'] as $date => $minutes)
                @php $intensity = $minutes > 0 ? max(0.15, min(1, $minutes / $max)) : 0; @endphp
                <div class="flex flex-col items-center gap-1" title="{{ \Illuminate\Support\Carbon::parse($date)->format('M j') }}: {{ $minutes }} min">
                    <div class="w-6 h-6 rounded" style="background-color: {{ $minutes > 0 ? 'rgba(232,93,38,'.$intensity.')' : '#1c1c1c' }}"></div>
                    <span class="font-mono text-[9px] text-ink-tertiary">{{ \Illuminate\Support\Carbon::parse($date)->format('j') }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="border-t border-base-border my-5"></div>

    <div class="font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-3">By sector</div>
    @forelse($report['sectorRows'] as $row)
        <div class="flex items-center justify-between bg-base-surface border border-base-border rounded-xl px-4 py-3.5 mb-2">
            <div class="flex items-center gap-2">
                @if($row['sector']->icon)<span>{{ $row['sector']->icon }}</span>@endif
                <span class="font-mono font-semibold text-sm text-ink-primary">{{ $row['sector']->name }}</span>
                @if($row['streak'] && $row['streak']->current_streak > 0)
                    <span class="bg-accent/15 text-accent border border-accent/25 font-mono text-xs font-bold px-2.5 py-1 rounded-full">🔥 {{ $row['streak']->current_streak }}d</span>
                @endif
            </div>
            <div class="font-mono text-xs text-ink-secondary">{{ intdiv($row['minutes'], 60) }}h {{ $row['minutes'] % 60 }}m · {{ $row['count'] }} entries</div>
        </div>
    @empty
        <p class="text-sm text-ink-tertiary font-mono">No check-ins logged in this period yet.</p>
    @endforelse

    @if(($report['byType'] ?? collect())->isNotEmpty())
        <div class="border-t border-base-border my-5"></div>
        <div class="flex gap-2 flex-wrap">
            @foreach($report['byType'] as $type => $count)
                <span class="font-mono text-[10px] font-bold uppercase tracking-wide bg-community/20 text-community px-2 py-0.5 rounded">{{ $type }}: {{ $count }}</span>
            @endforeach
        </div>
    @endif
</div>

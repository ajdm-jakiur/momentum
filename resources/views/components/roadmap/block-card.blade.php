@props(['block', 'color' => '#e85d26', 'readonly' => false, 'open' => false])

@php
$missing   = method_exists($block, 'missingRequired') ? $block->missingRequired() : collect();
$kindDefs  = config('roadmap.item_kinds');
$grouped   = collect($kindDefs)
    ->mapWithKeys(fn ($cfg, $key) => [$key => $block->items->where('kind', $key)])
    ->filter(fn ($col) => $col->isNotEmpty());
@endphp

<div x-data="{ open: @js($open) }" class="bg-base-surface border border-base-border rounded-xl mb-4 overflow-hidden">
    <button type="button" @click="open = !open" class="w-full px-3 py-3 sm:px-4 sm:py-3.5 flex justify-between items-center text-left hover:bg-base-hover transition-colors">
        <div class="flex items-center gap-2 min-w-0">
            @if($block->icon ?? null)<span class="text-lg flex-shrink-0">{{ $block->icon }}</span>@endif
            <div class="min-w-0">
                <div class="font-mono font-bold text-sm truncate text-ink-primary">{{ $block->title }}</div>
                @if($block->weeks_label ?? null)
                    <div class="font-mono text-xs" style="color: {{ $color }}">{{ $block->weeks_label }}</div>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-3 flex-shrink-0 ml-3">
            <span class="font-mono text-xs font-bold px-2 py-1 rounded {{ $block->is_complete ?? false ? 'bg-ok/20 text-ok' : 'bg-base-elevated text-ink-secondary' }}">
                {{ $block->required_done ?? 0 }}/{{ $block->required_total ?? 0 }}
            </span>
            <span class="text-lg font-light text-ink-tertiary transition-transform" :class="{ 'rotate-45' : open }">+</span>
        </div>
    </button>

    <div x-show="open" x-cloak class="bg-base-elevated px-3 pb-3 pt-2.5 sm:px-4 sm:pb-4 sm:pt-3 overflow-hidden">
        @if($block->pattern_text ?? null)
            <div class="bg-base-elevated border border-base-border rounded-lg px-3 py-2.5 text-xs mb-3">
                <span class="font-mono font-bold uppercase tracking-wide text-[10px]" style="color: {{ $color }}">Pattern</span>
                <div class="mt-0.5 text-ink-secondary">{{ $block->pattern_text }}</div>
            </div>
        @endif

        @if(! $missing->isEmpty())
            <div class="bg-warn/10 border border-warn/25 rounded-lg px-3 py-2.5 text-xs mb-3">
                <span class="font-mono font-bold uppercase tracking-wide text-[10px] text-warn">Still needed</span>
                <div class="mt-1 flex flex-wrap gap-1">
                    @foreach($missing as $m)
                        <span class="font-mono text-[11px] bg-base-elevated border border-warn/30 rounded px-1.5 py-0.5 text-ink-secondary max-w-full break-words">{{ $m }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        @if($block->resources->isNotEmpty())
            <div class="mb-3">
                <div class="font-mono text-[11px] font-bold uppercase tracking-wide text-ink-tertiary mb-1.5">Books &amp; Resources</div>
                <div class="space-y-1.5">
                    @foreach($block->resources as $resource)
                        <x-roadmap.resource-row :resource="$resource" :readonly="$readonly" />
                    @endforeach
                </div>
            </div>
        @endif

        @if(($block->dailyNotes ?? collect())->isNotEmpty())
            <div class="mb-3">
                <div class="font-mono text-[11px] font-bold uppercase tracking-wide text-ink-tertiary mb-1.5">Week-by-week (info only)</div>
                <div class="space-y-1 text-xs text-ink-secondary">
                    @foreach($block->dailyNotes as $note)
                        <div class="px-3 py-1.5 bg-base-surface border border-base-border rounded-lg">{{ $note->body }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        @foreach($grouped as $kindKey => $items)
            @php
                $cfg    = $kindDefs[$kindKey] ?? ['section' => ucfirst($kindKey), 'color' => 'ink'];
                $colors = config('roadmap.kind_colors.' . ($cfg['color'] ?? 'ink'), ['bg' => '', 'text' => 'text-ink-tertiary']);
                $isProblem = $kindKey === 'problem';
            @endphp
            <div class="{{ $loop->last ? '' : 'mb-3' }}">
                <div class="font-mono text-[11px] font-bold uppercase tracking-wide {{ $colors['text'] }} mb-1.5">
                    {{ $cfg['section'] }}{{ $isProblem ? ' ('.$items->count().')' : '' }}
                </div>
                <div class="{{ $isProblem ? 'grid grid-cols-1 sm:grid-cols-2 gap-1.5' : 'space-y-1.5' }}">
                    @foreach($items as $item)
                        <x-roadmap.item-row :item="$item" :readonly="$readonly" />
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

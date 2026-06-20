@props(['item', 'readonly' => false])

<div class="flex items-start gap-3 px-3 py-2 bg-base-surface border border-base-border rounded-lg {{ $item->is_done ?? false ? 'opacity-50' : '' }}">
    @if($readonly)
        <span class="mt-0.5 w-4 h-4 rounded border border-base-border flex-shrink-0"></span>
    @else
        <input type="checkbox" wire:click="toggleItem({{ $item->id }})" @checked($item->is_done)
            class="mt-1 rounded border-base-border text-accent focus:ring-accent flex-shrink-0 accent-[#e85d26]">
    @endif
    @php
        $kindCfg   = config("roadmap.item_kinds.{$item->kind}", ['label' => ucfirst($item->kind), 'color' => 'ink']);
        $colors    = config('roadmap.kind_colors.' . ($kindCfg['color'] ?? 'ink'), ['bg' => 'bg-base-elevated', 'text' => 'text-ink-secondary']);
    @endphp
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap">
            @if($item->kind === 'problem' && !empty($item->meta['difficulty']))
                <x-roadmap.problem-badge :difficulty="$item->meta['difficulty']" />
            @else
                <span class="font-mono text-[10px] font-bold uppercase tracking-wide {{ $colors['bg'] }} {{ $colors['text'] }} px-1.5 py-0.5 rounded">
                    {{ $kindCfg['label'] }}
                </span>
            @endif

            <span class="font-medium text-sm text-ink-primary {{ ($item->is_done ?? false) ? 'line-through text-ink-tertiary' : '' }}">
                {{ $item->title ?: $item->body }}
            </span>

            @if(! ($item->is_required ?? true))
                <span class="font-mono text-[10px] text-ink-tertiary">(optional)</span>
            @endif
        </div>

        @if($item->kind === 'community' && !empty($item->meta['where']))
            <div class="text-xs text-ink-tertiary font-mono mt-0.5">→ {{ $item->meta['where'] }}</div>
        @elseif($item->kind === 'problem' && !empty($item->meta['slug']))
            <a href="https://leetcode.com/problems/{{ $item->meta['slug'] }}/" target="_blank" rel="noopener noreferrer"
               class="text-xs text-community font-mono mt-0.5 inline-block hover:text-community/80 transition-colors">
                #{{ $item->meta['lc_id'] ?? '' }} ↗ leetcode
            </a>
        @elseif($item->body && $item->kind !== 'community')
            <div class="text-xs text-ink-secondary mt-0.5">{{ $item->body }}</div>
        @endif
    </div>
</div>

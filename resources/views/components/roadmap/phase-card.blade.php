@props(['phase', 'readonly' => false, 'openFirst' => false])

@php $color = $phase->color ?? '#e85d26'; @endphp

<div class="mb-8 pl-3 sm:pl-5 border-l-[3px]" style="border-left-color: {{ $color }}">
    <div class="flex items-baseline gap-2 sm:gap-3 mb-1 flex-wrap min-w-0">
        <div class="font-mono text-base sm:text-xl font-bold text-ink-primary leading-snug min-w-0 break-words">{{ $phase->title }}</div>
        @if($phase->duration_label)
            <div class="font-mono text-xs font-semibold px-2 py-0.5 rounded" style="color: {{ $color }}; background-color: {{ $color }}22">{{ $phase->duration_label }}</div>
        @endif
        <div class="font-mono text-xs font-bold px-2 py-0.5 rounded {{ $phase->is_complete ? 'bg-ok/20 text-ok' : 'bg-base-elevated text-ink-secondary' }}">
            {{ $phase->progress_percent }}%
        </div>
    </div>
    @if($phase->description)
        <p class="text-sm text-ink-secondary mb-4">{{ $phase->description }}</p>
    @endif

    @foreach($phase->blocks as $index => $block)
        <x-roadmap.block-card :block="$block" :color="$color" :readonly="$readonly" :open="$openFirst && $index === 0" />
    @endforeach

    @if($phase->milestone)
        <x-roadmap.milestone-box :phase="$phase" :color="$color" :readonly="$readonly" />
    @endif
</div>

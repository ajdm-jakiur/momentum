@props(['difficulty'])

@php
$classes = match ($difficulty) {
    'E' => 'bg-ok/20 text-ok',
    'M' => 'bg-warn/20 text-warn',
    'H' => 'bg-danger/20 text-danger',
    default => 'bg-base-elevated text-ink-tertiary',
};
$label = match ($difficulty) {
    'E' => 'Easy', 'M' => 'Med', 'H' => 'Hard', default => $difficulty,
};
@endphp

<span class="font-mono text-[10px] font-extrabold px-1.5 py-0.5 rounded {{ $classes }}">{{ $label }}</span>

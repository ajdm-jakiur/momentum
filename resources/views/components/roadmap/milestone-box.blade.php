@props(['phase', 'color' => '#e85d26', 'readonly' => false])

<div class="mt-3 bg-accent/10 border border-accent/25 rounded-xl px-4 py-4">
    <div class="font-mono text-[11px] font-bold uppercase tracking-wide mb-1 text-accent">✅ Milestone Check</div>
    <div class="text-sm text-ink-primary mb-2">{{ $phase->milestone }}</div>
    @if(! $readonly)
        <label class="flex items-center gap-2 text-xs font-mono cursor-pointer text-ink-secondary">
            <input type="checkbox" wire:click="toggleMilestone({{ $phase->id }})" @checked($phase->milestone_confirmed)
                class="rounded border-base-border text-accent focus:ring-accent accent-[#e85d26]">
            <span>{{ $phase->milestone_confirmed ? 'Milestone confirmed' : 'Confirm milestone reached' }}</span>
        </label>
    @endif
</div>

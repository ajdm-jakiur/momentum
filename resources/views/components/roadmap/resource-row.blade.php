@props(['resource', 'readonly' => false])

<div class="flex items-start gap-2 px-2.5 py-2 sm:gap-3 sm:px-3 bg-base-surface border border-base-border rounded-lg overflow-hidden {{ $resource->is_done ?? false ? 'opacity-50' : '' }}">
    @if($readonly)
        <span class="mt-0.5 w-4 h-4 rounded border border-base-border flex-shrink-0"></span>
    @else
        <input type="checkbox" wire:click="toggleResource({{ $resource->id }})" @checked($resource->is_done)
            class="mt-1 rounded border-base-border text-accent focus:ring-accent flex-shrink-0 accent-[#e85d26]">
    @endif
    <div class="flex-1 min-w-0">
        <div class="font-semibold text-sm text-ink-primary {{ ($resource->is_done ?? false) ? 'line-through text-ink-tertiary' : '' }}">
            {{ $resource->name }}
            @if(! ($resource->is_required ?? true))
                <span class="font-mono text-[10px] text-ink-tertiary">(optional)</span>
            @endif
        </div>
        @if($resource->author_or_type)
            <div class="text-xs text-ink-secondary">{{ $resource->author_or_type }}</div>
        @endif
        @if($resource->note)
            <div class="text-xs text-ink-tertiary italic mt-0.5">{{ $resource->note }}</div>
        @endif
    </div>
    @if(!empty($resource->url))
        <a href="{{ $resource->url }}" target="_blank" rel="noopener noreferrer" class="font-mono text-xs text-community flex-shrink-0 hover:text-community/80 transition-colors">↗</a>
    @endif
</div>

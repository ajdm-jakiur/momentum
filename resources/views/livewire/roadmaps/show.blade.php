<div class="overflow-x-hidden">
    <div class="max-w-4xl mx-auto px-3 py-5 sm:px-5 sm:py-6 lg:px-7">
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('sectors.index') }}" wire:navigate class="font-mono text-xs text-ink-tertiary hover:text-accent transition-colors">&larr; Sectors</a>
            <button wire:click="trash"
                    wire:confirm="Move '{{ addslashes($roadmap->title) }}' to trash?"
                    class="font-mono text-xs text-ink-tertiary hover:text-danger transition-colors">
                Move to Trash
            </button>
        </div>

        <div class="mb-8 min-w-0">
            <div class="flex items-start gap-3 flex-wrap min-w-0">
                <h1 class="font-mono text-xl sm:text-2xl font-extrabold text-ink-primary break-words min-w-0 flex-1">{{ $roadmap->title }}</h1>
                <span class="font-mono text-xs font-bold px-2 py-1 rounded {{ $roadmap->is_complete ? 'bg-ok/20 text-ok' : 'bg-base-elevated text-ink-secondary' }}">
                    {{ $roadmap->progress_percent }}% complete
                </span>
            </div>
            @if($roadmap->description)
                <p class="text-sm text-ink-secondary font-mono mt-1">{{ $roadmap->description }}</p>
            @endif
        </div>

        @foreach($roadmap->phases as $phase)
            <x-roadmap.phase-card :phase="$phase" :openFirst="$loop->first" />
        @endforeach
    </div>
</div>

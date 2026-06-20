<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="font-mono font-extrabold text-xl text-ink-primary">Trash</h1>
            <p class="font-mono text-xs text-ink-tertiary mt-0.5">Deleted roadmaps — restore or permanently delete</p>
        </div>
        <a href="{{ route('sectors.index') }}" class="font-mono text-xs text-ink-tertiary hover:text-ink-secondary transition-colors">← Back</a>
    </div>

    @if($roadmaps->isEmpty())
        <div class="bg-base-surface border border-base-border rounded-xl px-6 py-12 text-center">
            <div class="text-3xl mb-2">🗑️</div>
            <div class="font-mono text-sm text-ink-tertiary">Trash is empty</div>
        </div>
    @else
        <div class="space-y-3">
            @foreach($roadmaps as $roadmap)
                <div class="bg-base-surface border border-base-border rounded-xl px-4 py-3.5 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="font-mono font-bold text-sm text-ink-primary truncate">{{ $roadmap->title }}</div>
                        <div class="font-mono text-xs text-ink-tertiary mt-0.5">
                            {{ $roadmap->sector->name ?? '—' }}
                            · deleted {{ $roadmap->deleted_at->diffForHumans() }}
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button wire:click="restore({{ $roadmap->id }})"
                                wire:confirm="Restore '{{ addslashes($roadmap->title) }}'?"
                                class="font-mono text-xs font-bold px-3 py-1.5 rounded-lg bg-ok/20 text-ok hover:bg-ok/30 transition-colors border border-ok/30">
                            Restore
                        </button>
                        <button wire:click="forceDelete({{ $roadmap->id }})"
                                wire:confirm="Permanently delete '{{ addslashes($roadmap->title) }}' and all its phases, blocks, and items? This cannot be undone."
                                class="font-mono text-xs font-bold px-3 py-1.5 rounded-lg bg-danger/20 text-danger hover:bg-danger/30 transition-colors border border-danger/30">
                            Delete Forever
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

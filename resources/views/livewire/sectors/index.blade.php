<div class="px-5 py-6 lg:px-7 space-y-5">

    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <h1 class="font-mono text-2xl font-extrabold text-ink-primary">Sectors</h1>
        <a href="{{ route('roadmaps.import') }}" wire:navigate class="bg-accent hover:bg-accent-dark text-white font-mono font-bold rounded-lg px-4 py-2 transition-colors text-sm">+ Import Roadmap</a>
    </div>

    {{-- Sector list --}}
    <div class="space-y-5">
        @foreach($sectors as $sector)
            <div class="bg-base-surface border border-base-border rounded-xl p-5 relative overflow-hidden">
                {{-- Left accent bar --}}
                <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-xl" style="background-color: {{ $sector->color }}"></div>

                {{-- Sector header --}}
                <div class="flex items-center gap-2 mb-4 pl-2">
                    @if($sector->icon)<span class="text-lg">{{ $sector->icon }}</span>@endif
                    <h2 class="font-mono font-bold text-lg text-ink-primary">{{ $sector->name }}</h2>
                </div>

                @if($sector->roadmaps->isEmpty())
                    <p class="text-sm text-ink-tertiary font-mono pl-2">No roadmaps yet.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pl-2">
                        @foreach($sector->roadmaps as $roadmap)
                            <a href="{{ route('roadmaps.show', $roadmap) }}" wire:navigate
                               class="block bg-base-elevated border border-base-border rounded-lg px-4 py-3 hover:border-accent/60 hover:bg-base-hover transition-colors">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-mono font-semibold text-sm text-ink-primary">{{ $roadmap->title }}</span>
                                    <span class="font-mono text-[11px] font-bold px-1.5 py-0.5 rounded {{ $roadmap->is_complete ? 'bg-ok/20 text-ok' : 'bg-base-border text-ink-secondary' }}">
                                        {{ $roadmap->progress_percent }}%
                                    </span>
                                </div>
                                <div class="h-1.5 rounded-full bg-base-elevated">
                                    <div class="h-1.5 rounded-full" style="width: {{ $roadmap->progress_percent }}%; background-color: {{ $sector->color }}"></div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

</div>

<div class="max-w-3xl mx-auto px-3 py-5 sm:px-5 sm:py-6 lg:px-7">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between gap-3 flex-wrap">
        <a href="{{ route('roadmaps.show', $roadmap) }}" wire:navigate class="font-mono text-xs text-ink-tertiary hover:text-accent transition-colors">&larr; Back to roadmap</a>
        <span class="font-mono text-xs text-ink-tertiary">Editing: <span class="text-ink-primary font-bold">{{ $roadmap->title }}</span></span>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-ok/10 border border-ok/30 text-ok font-mono text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- Tabs --}}
    <div class="flex gap-1 mb-6 bg-base-elevated p-1 rounded-xl w-fit">
        <button wire:click="$set('tab', 'metadata')"
                class="font-mono text-xs font-bold px-4 py-2 rounded-lg transition-colors {{ $tab === 'metadata' ? 'bg-base-surface text-ink-primary shadow-sm' : 'text-ink-tertiary hover:text-ink-secondary' }}">
            Metadata
        </button>
        <button wire:click="$set('tab', 'json')"
                class="font-mono text-xs font-bold px-4 py-2 rounded-lg transition-colors {{ $tab === 'json' ? 'bg-base-surface text-ink-primary shadow-sm' : 'text-ink-tertiary hover:text-ink-secondary' }}">
            JSON Editor
        </button>
    </div>

    {{-- ── Metadata Tab ─────────────────────────────────────────────── --}}
    @if($tab === 'metadata')
        <div class="bg-base-surface border border-base-border rounded-xl p-4 sm:p-6 space-y-5">
            <div>
                <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Title</label>
                <input type="text" wire:model="title"
                       class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors">
                @error('title') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Description</label>
                <textarea wire:model="description" rows="3"
                          class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Sector</label>
                    <select wire:model="sectorSlug"
                            class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors">
                        @foreach($this->sectors as $sector)
                            <option value="{{ $sector->slug }}">{{ $sector->name }}</option>
                        @endforeach
                    </select>
                    @error('sectorSlug') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Total Weeks</label>
                    <input type="number" wire:model="totalWeeks" min="1" placeholder="e.g. 12"
                           class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors">
                    @error('totalWeeks') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" wire:model="color"
                               class="h-10 w-12 rounded-lg border border-base-border bg-base-elevated cursor-pointer p-0.5">
                        <input type="text" wire:model="color" maxlength="7"
                               class="flex-1 bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3 py-2.5 text-sm font-mono focus:outline-none focus:border-accent transition-colors">
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button wire:click="saveMetadata" class="bg-accent hover:bg-accent-dark text-white font-mono font-bold text-sm px-5 py-2.5 rounded-lg transition-colors">
                    Save changes
                </button>
            </div>
        </div>
    @endif

    {{-- ── JSON Editor Tab ──────────────────────────────────────────── --}}
    @if($tab === 'json')
        <div class="space-y-4">
            <div class="bg-warn/10 border border-warn/25 rounded-lg px-4 py-3 text-xs font-mono text-ink-secondary">
                <span class="text-warn font-bold">Warning:</span> Saving JSON rebuilds the entire roadmap — all phases, blocks, and items are replaced. Completion progress is reset.
            </div>

            @if($jsonError)
                <div class="bg-danger/10 border border-danger/30 rounded-lg px-4 py-3 text-xs font-mono text-danger">{{ $jsonError }}</div>
            @endif

            @if(! $roadmap->imported_json)
                <div class="bg-base-elevated border border-base-border rounded-lg px-4 py-3 text-xs font-mono text-ink-tertiary">
                    This roadmap was created manually — no source JSON. Paste valid JSON below to convert it to a JSON-backed roadmap.
                </div>
            @endif

            <div class="bg-base-surface border border-base-border rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-4 py-2.5 border-b border-base-border bg-base-elevated">
                    <span class="font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary">roadmap.json</span>
                    <div x-data="{ copied: false }">
                        <button @click="navigator.clipboard.writeText($refs.jsonArea.value).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                                class="font-mono text-[11px] text-ink-tertiary hover:text-accent transition-colors">
                            <span x-show="!copied">Copy</span>
                            <span x-show="copied" x-cloak class="text-ok">Copied ✓</span>
                        </button>
                    </div>
                </div>
                <textarea x-ref="jsonArea"
                          wire:model="jsonRaw"
                          rows="28"
                          spellcheck="false"
                          class="w-full bg-base-elevated text-ink-primary font-mono text-xs px-4 py-3.5 focus:outline-none resize-none leading-relaxed"
                          placeholder='{ "sector": "dsa", "roadmap": { ... }, "phases": [ ... ] }'></textarea>
            </div>

            <div class="flex items-center gap-3">
                <button wire:click="saveJson"
                        wire:confirm="This will DELETE all existing phases and rebuild from this JSON. Continue?"
                        class="bg-accent hover:bg-accent-dark text-white font-mono font-bold text-sm px-5 py-2.5 rounded-lg transition-colors">
                    Save &amp; Rebuild
                </button>
                <span class="font-mono text-xs text-ink-tertiary">Validates schema before saving.</span>
            </div>
        </div>
    @endif

</div>

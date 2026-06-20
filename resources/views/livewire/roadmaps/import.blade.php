<div>
    <div class="max-w-4xl mx-auto px-5 py-6 lg:px-7">
        <div class="mb-6">
            <a href="{{ route('sectors.index') }}" wire:navigate class="font-mono text-xs text-ink-tertiary hover:text-accent transition-colors">&larr; Sectors</a>
        </div>

        <h1 class="font-mono text-2xl font-extrabold mb-2 text-ink-primary">Import Roadmap</h1>
        <p class="text-sm text-ink-secondary mb-6">
            Paste roadmap JSON matching the documented schema. Every book/project/problem/community action defaults to
            <span class="font-mono font-bold text-ink-primary">required</span> — a block only counts as complete once all of them are checked off.
        </p>

        <div x-data="{
                schema: @js($schemaSample),
                copied: false,
                copy() {
                    navigator.clipboard.writeText(this.schema);
                    this.copied = true;
                    setTimeout(() => this.copied = false, 1500);
                }
             }"
             class="mb-6 flex items-center gap-3 bg-base-surface border border-base-border rounded-xl px-4 py-3">
            <div class="flex-1 text-sm">
                <span class="font-mono font-bold text-ink-primary">Need the schema?</span>
                <span class="text-ink-secondary"> Copy it and hand it to an AI: "fill in phases/blocks/resources/items for [topic] using this exact JSON structure."</span>
            </div>
            <button type="button" @click="copy()" class="bg-base-elevated hover:bg-base-hover text-ink-secondary hover:text-ink-primary border border-base-border rounded-lg px-3 py-2 font-mono text-xs transition-colors flex-shrink-0">
                <span x-show="!copied">Copy schema</span>
                <span x-show="copied" x-cloak>Copied!</span>
            </button>
        </div>

        <p class="text-xs text-ink-tertiary font-mono mb-2">Or from a terminal: <code class="bg-base-elevated border border-base-border rounded px-1.5 py-0.5 text-ink-secondary">php artisan roadmap:schema --no-cheatsheet &gt; my-roadmap.json</code></p>

        @if(! $parsed)
            <div class="mb-4">
                <textarea wire:model="jsonInput" rows="14" placeholder="Paste roadmap JSON here..."
                    class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50"></textarea>
            </div>

            @if(! empty($errors))
                <div class="mb-4 bg-danger/10 border border-danger/30 rounded-xl px-4 py-3">
                    <div class="font-mono text-[11px] font-bold uppercase tracking-wide text-danger mb-1">Validation errors</div>
                    <ul class="text-sm text-ink-primary list-disc list-inside space-y-0.5">
                        @foreach($errors as $field => $message)
                            <li><span class="font-mono text-xs text-ink-tertiary">{{ $field }}:</span> {{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <button wire:click="preview" class="font-mono text-sm font-bold px-4 py-2 rounded-lg bg-accent text-white hover:bg-accent/90 transition-colors">
                Validate &amp; Preview
            </button>
        @else
            @php $summary = $this->summary; @endphp
            <div class="mb-4 bg-base-surface border border-base-border rounded-xl px-4 py-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-mono font-bold text-lg text-ink-primary">{{ $parsed['roadmap']['title'] }}</h2>
                    <span class="font-mono text-xs px-2 py-1 rounded-lg bg-base-elevated text-ink-secondary border border-base-border">sector: {{ $parsed['sector'] }}</span>
                </div>
                @if(!empty($parsed['roadmap']['description']))
                    <p class="text-sm text-ink-secondary mb-3">{{ $parsed['roadmap']['description'] }}</p>
                @endif

                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 mb-4">
                    <div class="bg-base-elevated border border-base-border rounded-xl px-3 py-2 text-center">
                        <div class="font-mono text-xl font-extrabold text-ink-primary">{{ count($parsed['phases']) }}</div>
                        <div class="font-mono text-[10px] uppercase text-ink-tertiary">Phases</div>
                    </div>
                    <div class="bg-base-elevated border border-base-border rounded-xl px-3 py-2 text-center">
                        <div class="font-mono text-xl font-extrabold text-ink-primary">{{ $summary['totals']['blocks'] }}</div>
                        <div class="font-mono text-[10px] uppercase text-ink-tertiary">Blocks</div>
                    </div>
                    <div class="bg-base-elevated border border-base-border rounded-xl px-3 py-2 text-center">
                        <div class="font-mono text-xl font-extrabold text-ink-primary">{{ $summary['totals']['books'] }}</div>
                        <div class="font-mono text-[10px] uppercase text-ink-tertiary">Books</div>
                    </div>
                    @foreach(config('roadmap.item_kinds') as $kindKey => $kindCfg)
                        @if(($summary['totals'][$kindKey] ?? 0) > 0)
                            @php $kColors = config('roadmap.kind_colors.' . ($kindCfg['color'] ?? 'ink'), ['text' => 'text-ink-secondary']); @endphp
                            <div class="bg-base-elevated border border-base-border rounded-xl px-3 py-2 text-center">
                                <div class="font-mono text-xl font-extrabold {{ $kColors['text'] }}">{{ $summary['totals'][$kindKey] }}</div>
                                <div class="font-mono text-[10px] uppercase {{ $kColors['text'] }} opacity-70">{{ $kindCfg['label'] }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>

                @foreach($summary['phases'] as $phase)
                    <div class="mb-3">
                        <div class="font-mono font-bold text-sm mb-1.5 text-ink-primary">{{ $phase['title'] }}</div>
                        <div class="space-y-1">
                            @foreach($phase['blocks'] as $block)
                                <div class="flex items-center justify-between bg-base-elevated border border-base-border rounded-lg px-3 py-2 text-sm">
                                    <span class="text-ink-primary">{{ $block['title'] }}</span>
                                    <span class="font-mono text-[11px] text-ink-secondary">
                                        {{ $block['books'] }} books
                                        @foreach(config('roadmap.item_kinds') as $kindKey => $kindCfg)
                                            @if(($block[$kindKey] ?? 0) > 0) · {{ $block[$kindKey] }} {{ strtolower($kindCfg['label']) }}@endif
                                        @endforeach
                                        — <span class="font-bold text-accent">{{ $block['required_total'] }} required total</span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex gap-3">
                <button wire:click="confirm" class="font-mono text-sm font-bold px-4 py-2 rounded-lg bg-ok/20 text-ok hover:bg-ok/30 transition-colors border border-ok/30">
                    Confirm &amp; Import
                </button>
                <button wire:click="cancelPreview" class="font-mono text-sm font-bold px-4 py-2 rounded-lg bg-base-elevated text-ink-secondary hover:bg-base-hover hover:text-ink-primary border border-base-border transition-colors">
                    Back to edit
                </button>
            </div>
        @endif
    </div>
</div>

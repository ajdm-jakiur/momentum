@php
    // Reader uses its own full-screen layout — suppress the app shell padding
@endphp

<div class="fixed inset-0 z-50 bg-[#1a1a1a] flex flex-col"
     x-data="pdfReader(@js($pdfUrl), @js($currentPage), $wire)"
     x-init="init()"
     @keydown.arrow-right.window="nextPage()"
     @keydown.arrow-left.window="prevPage()">

    {{-- ── Top Bar ─────────────────────────────────────────────────── --}}
    <div class="flex-shrink-0 h-12 flex items-center justify-between px-3 bg-[#111] border-b border-white/10 z-20">
        <a href="{{ route('books.index') }}"
           class="flex items-center gap-2 text-white/60 hover:text-white transition-colors">
            <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-mono text-xs hidden sm:inline">Library</span>
        </a>

        <div class="font-mono text-xs text-white/70 text-center truncate max-w-[40vw]">{{ $book->title }}</div>

        {{-- Page jump --}}
        <div class="flex items-center gap-1.5">
            <input type="number" x-model.number="jumpPage" @keydown.enter="goToPage(jumpPage)"
                   :max="totalPages || 9999" min="1"
                   class="w-14 bg-white/10 border border-white/20 text-white font-mono text-xs text-center rounded px-1.5 py-1 focus:outline-none focus:border-white/50">
            <span class="font-mono text-[11px] text-white/40" x-text="totalPages ? '/ ' + totalPages : ''"></span>
        </div>
    </div>

    {{-- ── Canvas Area ─────────────────────────────────────────────── --}}
    <div class="flex-1 overflow-hidden relative" x-ref="canvasContainer">
        {{-- Loading --}}
        <div x-show="loading" class="absolute inset-0 flex items-center justify-center z-10">
            <div class="flex flex-col items-center gap-3">
                <div class="w-8 h-8 border-2 border-white/20 border-t-white rounded-full animate-spin"></div>
                <span class="font-mono text-xs text-white/50">Loading…</span>
            </div>
        </div>

        {{-- Error --}}
        <div x-show="error" x-cloak class="absolute inset-0 flex items-center justify-center z-10 px-6">
            <div class="text-center">
                <div class="font-mono text-4xl mb-3">⚠️</div>
                <p class="font-mono text-sm text-red-400 mb-2" x-text="error"></p>
                <p class="font-mono text-xs text-white/40 mb-4" x-text="'Attempt ' + retryCount + ' of 3 failed.'"></p>
                <button @click="retry()"
                        class="bg-accent hover:bg-accent-dark text-white font-mono font-bold text-xs px-4 py-2 rounded-lg transition-colors">
                    Try Again
                </button>
            </div>
        </div>

        {{-- PDF Canvas --}}
        <canvas x-ref="canvas"
                class="mx-auto block max-w-full max-h-full object-contain"
                style="touch-action: pan-y"></canvas>

        {{-- Tap zones: left 40% = prev, right 40% = next, centre 20% = toggle controls --}}
        <div class="absolute inset-0 flex pointer-events-none">
            <div class="w-[40%] h-full pointer-events-auto cursor-pointer" @click="prevPage()"></div>
            <div class="w-[20%] h-full pointer-events-auto cursor-pointer" @click="toggleControls()"></div>
            <div class="w-[40%] h-full pointer-events-auto cursor-pointer" @click="nextPage()"></div>
        </div>
    </div>

    {{-- ── Bottom Controls ─────────────────────────────────────────── --}}
    <div x-show="showControls"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="flex-shrink-0 bg-[#111]/95 backdrop-blur-md border-t border-white/10 px-4 py-3 z-20">

        {{-- Progress bar --}}
        <div class="mb-3" x-show="totalPages > 0">
            <div class="h-1 bg-white/10 rounded-full overflow-hidden cursor-pointer" @click="seekBar($event)">
                <div class="h-full rounded-full bg-[#e85d26] transition-all"
                     :style="'width:' + (totalPages ? Math.round(currentPage/totalPages*100) : 0) + '%'"></div>
            </div>
        </div>

        <div class="flex items-center justify-between gap-4">
            <button @click="prevPage()"
                    :disabled="currentPage <= 1"
                    class="flex items-center gap-1.5 font-mono text-xs text-white/70 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
                <svg style="width:16px;height:16px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Prev
            </button>

            <div class="font-mono text-xs text-white/60 text-center">
                <span x-text="currentPage"></span>
                <span x-show="totalPages > 0" x-text="' / ' + totalPages"></span>
                &nbsp;·&nbsp;
                <span x-text="totalPages ? Math.round(currentPage/totalPages*100)+'%' : '...'"></span>
            </div>

            <button @click="nextPage()"
                    :disabled="totalPages > 0 && currentPage >= totalPages"
                    class="flex items-center gap-1.5 font-mono text-xs text-white/70 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
                Next
                <svg style="width:16px;height:16px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>

</div>

@push('scripts')
{{-- PDF.js 3.x UMD build — regular <script> runs before Alpine processes x-data --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

window.pdfReader = function(pdfUrl, savedPage) {
    // PDF.js objects kept as closure vars — never touched by Alpine's reactive proxy
    let _pdfDoc     = null;
    let _renderTask = null;
    let _started    = false;

    return {
        currentPage:  savedPage || 1,
        totalPages:   0,
        jumpPage:     savedPage || 1,
        loading:      true,
        error:        null,
        retryCount:   0,
        showControls: true,
        saveTimer:    null,

        async init() {
            if (_started) return;
            _started = true;
            setTimeout(() => { this.showControls = false; }, 3000);
            await this._load();
        },

        async _load() {
            this.error   = null;
            this.loading = true;
            const MAX    = 3;
            for (let attempt = 1; attempt <= MAX; attempt++) {
                this.retryCount = attempt;
                try {
                    const task = pdfjsLib.getDocument({
                        url:           pdfUrl,
                        disableRange:  true,  // server returns 200 not 206
                        disableStream: true,  // fetch full file before parsing — more stable
                    });
                    _pdfDoc = await task.promise;
                    this.totalPages = _pdfDoc.numPages;
                    await this.renderPage(this.currentPage);
                    this.loading = false;
                    return;
                } catch (e) {
                    if (attempt < MAX) {
                        await new Promise(r => setTimeout(r, 1500 * attempt));
                    } else {
                        this.loading = false;
                        this.error   = e.message || 'Failed to load PDF.';
                    }
                }
            }
        },

        async retry() {
            _pdfDoc   = null;
            _started  = false;
            _started  = true;
            await this._load();
        },

        async renderPage(num) {
            if (!_pdfDoc) return;
            if (_renderTask) {
                try { _renderTask.cancel(); } catch(_) {}
                _renderTask = null;
            }
            const page      = await _pdfDoc.getPage(num);
            const container = this.$refs.canvasContainer;
            const vp0       = page.getViewport({ scale: 1 });
            const scale     = Math.min(container.clientWidth / vp0.width, container.clientHeight / vp0.height, 2);
            const viewport  = page.getViewport({ scale });
            const canvas    = this.$refs.canvas;
            const ctx       = canvas.getContext('2d');
            canvas.width    = viewport.width;
            canvas.height   = viewport.height;
            _renderTask     = page.render({ canvasContext: ctx, viewport });
            await _renderTask.promise;
            _renderTask = null;
        },

        async nextPage() {
            if (this.totalPages && this.currentPage >= this.totalPages) return;
            this.currentPage++;
            this.jumpPage = this.currentPage;
            await this.renderPage(this.currentPage);
            this.scheduleSave();
        },

        async prevPage() {
            if (this.currentPage <= 1) return;
            this.currentPage--;
            this.jumpPage = this.currentPage;
            await this.renderPage(this.currentPage);
            this.scheduleSave();
        },

        async goToPage(num) {
            num = Math.max(1, Math.min(num, this.totalPages || 99999));
            this.currentPage = num;
            this.jumpPage    = num;
            await this.renderPage(num);
            this.scheduleSave();
        },

        seekBar(e) {
            if (!this.totalPages) return;
            const rect = e.currentTarget.getBoundingClientRect();
            const pct  = (e.clientX - rect.left) / rect.width;
            this.goToPage(Math.max(1, Math.round(pct * this.totalPages)));
        },

        toggleControls() { this.showControls = !this.showControls; },

        scheduleSave() {
            clearTimeout(this.saveTimer);
            this.saveTimer = setTimeout(() => {
                // this.$el is the root x-data div which also carries wire:id for Books\Read
                const wireId = this.$el?.getAttribute('wire:id');
                if (wireId && window.Livewire) {
                    Livewire.find(wireId)?.call('savePage', this.currentPage, this.totalPages);
                }
            }, 1500);
        },
    };
};
</script>
@endpush

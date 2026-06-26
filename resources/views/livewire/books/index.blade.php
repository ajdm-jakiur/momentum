<div class="px-3 py-5 sm:px-5 sm:py-6 lg:px-7 overflow-x-hidden">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="font-mono text-2xl font-extrabold text-ink-primary">Library</h1>
            <p class="text-sm text-ink-secondary mt-1">Your books — upload PDFs, read anywhere, resume where you left off.</p>
        </div>
        @if(! $showForm)
            <button wire:click="$set('showForm', true)"
                    class="bg-accent hover:bg-accent-dark text-white font-mono font-bold text-sm px-4 py-2.5 rounded-lg transition-colors flex-shrink-0 self-start sm:self-auto">
                + Add Book
            </button>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 bg-ok/10 border border-ok/30 text-ok font-mono text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- Upload Form --}}
    @if($showForm)
        <div class="bg-base-surface border border-base-border rounded-xl px-4 py-5 sm:px-6 mb-6 space-y-4"
             x-data="{
                 progress: 0, uploading: false, uploadError: '', coverPreview: null,
                 init() {
                     // Intercept XHR to capture upload response status + body
                     const orig = window.XMLHttpRequest;
                     window.XMLHttpRequest = function() {
                         const xhr = new orig();
                         const origOpen = xhr.open.bind(xhr);
                         xhr.open = function(method, url, ...rest) {
                             if (url && url.toString().includes('upload-file')) {
                                 xhr.addEventListener('loadend', () => {
                                     const info = 'HTTP ' + xhr.status + ' — ' + (xhr.responseText || '').substring(0, 300);
                                     console.error('[Upload XHR]', info);
                                     window._lastUploadDebug = info;
                                 });
                             }
                             return origOpen(method, url, ...rest);
                         };
                         return xhr;
                     };
                 }
             }"
             x-on:livewire-upload-start="uploading = true; progress = 0; uploadError = ''"
             x-on:livewire-upload-finish="uploading = false"
             x-on:livewire-upload-error="uploading = false; uploadError = 'Upload failed — ' + (window._lastUploadDebug || 'see browser console (F12)')"
             x-on:livewire-upload-progress="progress = $event.detail.progress">

            <h2 class="font-mono font-bold text-sm text-ink-primary">Add a Book</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- PDF File --}}
                <div>
                    <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">PDF File</label>
                    <input type="file" wire:model="file" accept=".pdf"
                           :disabled="uploading"
                           class="w-full bg-base-elevated border border-base-border text-ink-secondary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent transition-colors file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:font-mono file:text-xs file:bg-accent file:text-white disabled:opacity-50">
                    @error('file') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror

                    <div x-show="uploading" x-cloak class="mt-2 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[11px] text-ink-tertiary">Uploading…</span>
                            <span class="font-mono text-[11px] text-accent font-bold" x-text="progress + '%'"></span>
                        </div>
                        <div class="h-1.5 bg-base-elevated rounded-full overflow-hidden">
                            <div class="h-full bg-accent rounded-full transition-all duration-200" :style="'width:' + progress + '%'"></div>
                        </div>
                    </div>

                    <div x-show="uploadError" x-cloak class="mt-2 bg-danger/10 border border-danger/40 rounded-lg px-3.5 py-3">
                        <p class="font-mono text-xs font-bold text-danger">Upload failed</p>
                        <p class="font-mono text-xs text-danger/80 mt-0.5" x-text="uploadError"></p>
                    </div>
                </div>

                {{-- Cover Image --}}
                <div>
                    <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Cover Image <span class="text-ink-tertiary/50 normal-case">(optional · jpg/png/webp · max 5 MB)</span></label>
                    <div class="flex items-start gap-3">
                        <div class="w-16 h-20 rounded-lg overflow-hidden flex-shrink-0 bg-base-elevated border border-base-border flex items-center justify-center">
                            <template x-if="coverPreview">
                                <img :src="coverPreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!coverPreview">
                                <svg style="width:24px;height:24px" class="text-ink-tertiary/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </template>
                        </div>
                        <div class="flex-1">
                            <input type="file" wire:model="coverFile" accept="image/jpeg,image/png,image/webp"
                                   :disabled="uploading"
                                   @change="const f=$event.target.files[0]; if(f){const r=new FileReader();r.onload=e=>coverPreview=e.target.result;r.readAsDataURL(f)}else{coverPreview=null}"
                                   class="w-full bg-base-elevated border border-base-border text-ink-secondary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent transition-colors file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:font-mono file:text-xs file:bg-white/10 file:text-ink-secondary disabled:opacity-50">
                            @error('coverFile') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Title</label>
                    <input type="text" wire:model="title"
                           class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors">
                    @error('title') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Author</label>
                    <input type="text" wire:model="author"
                           class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent transition-colors">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Sector</label>
                    <select wire:model="sectorId"
                            class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent transition-colors">
                        <option value="">None</option>
                        @foreach($this->sectors as $sector)
                            <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Cover Color <span class="text-ink-tertiary/50 normal-case">(fallback)</span></label>
                    <div class="flex items-center gap-2">
                        <input type="color" wire:model="coverColor"
                               class="h-10 w-12 rounded-lg border border-base-border bg-base-elevated cursor-pointer p-0.5">
                        <input type="text" wire:model="coverColor" maxlength="7"
                               class="flex-1 bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3 py-2.5 text-sm font-mono focus:outline-none focus:border-accent transition-colors">
                    </div>
                </div>
                <div>
                    <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Description</label>
                    <input type="text" wire:model="description" placeholder="Optional"
                           class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent transition-colors">
                </div>
            </div>

            <div wire:loading wire:target="saveBook" class="bg-accent/10 border border-accent/25 rounded-lg px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="w-4 h-4 border-2 border-accent/40 border-t-accent rounded-full animate-spin flex-shrink-0"></div>
                    <span class="font-mono text-xs text-accent">Saving to cloud storage… this may take a moment for large files.</span>
                </div>
            </div>

            <div class="flex gap-3 pt-1">
                <button wire:click="saveBook"
                        :disabled="uploading"
                        wire:loading.attr="disabled" wire:target="saveBook"
                        class="bg-accent hover:bg-accent-dark text-white font-mono font-bold text-sm px-5 py-2.5 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="saveBook">Save to Library</span>
                    <span wire:loading wire:target="saveBook">Saving…</span>
                </button>
                <button wire:click="$set('showForm', false)"
                        :disabled="uploading"
                        class="bg-base-elevated hover:bg-base-hover text-ink-secondary font-mono text-sm px-4 py-2.5 rounded-lg transition-colors disabled:opacity-50">
                    Cancel
                </button>
            </div>
        </div>
    @endif

    {{-- Book Grid --}}
    @if($books->isEmpty())
        <div class="text-center py-16">
            <div class="font-mono text-4xl mb-3">📚</div>
            <p class="font-mono text-sm text-ink-tertiary">No books yet. Upload your first PDF.</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
            @foreach($books as $book)
                @php
                    $pos = $book->readingPosition;
                    $pct = ($pos && $book->page_count) ? round($pos->current_page / $book->page_count * 100) : 0;
                @endphp

                <div class="bg-base-surface border border-base-border rounded-xl overflow-hidden flex flex-col group">

                    {{-- Cover --}}
                    <div class="aspect-[3/4] relative overflow-hidden flex-shrink-0">
                        @if($book->cover_image)
                            <img src="{{ route('books.cover', $book) }}"
                                 alt="{{ $book->title }}"
                                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center gap-3"
                                 style="background: linear-gradient(135deg, {{ $book->cover_color }}33 0%, {{ $book->cover_color }}11 100%); border-bottom: 2px solid {{ $book->cover_color }}33">
                                <svg style="width:40px;height:40px;color:{{ $book->cover_color }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <span class="font-mono text-[10px] font-bold uppercase tracking-widest px-3 text-center line-clamp-2 opacity-60"
                                      style="color:{{ $book->cover_color }}">{{ $book->title }}</span>
                            </div>
                        @endif

                        {{-- Progress bar overlay at bottom of cover --}}
                        @if($pos && $book->page_count && $pct > 0)
                            <div class="absolute bottom-0 left-0 right-0 h-1 bg-black/30">
                                <div class="h-full transition-all" style="width:{{ $pct }}%;background:{{ $book->cover_color }}"></div>
                            </div>
                        @endif

                        {{-- Sector badge --}}
                        @if($book->sector)
                            <div class="absolute top-2 left-2">
                                <span class="font-mono text-[9px] font-bold uppercase tracking-wide px-1.5 py-0.5 rounded backdrop-blur-sm"
                                      style="color:{{ $book->sector->color }};background:{{ $book->sector->color }}33">{{ $book->sector->name }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="p-3 flex-1 flex flex-col gap-0.5 min-w-0">
                        <div class="font-mono font-bold text-xs text-ink-primary line-clamp-2 leading-snug">{{ $book->title }}</div>
                        @if($book->author)
                            <div class="font-mono text-[10px] text-ink-tertiary truncate">{{ $book->author }}</div>
                        @endif
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            <span class="font-mono text-[10px] text-ink-tertiary">{{ $book->fileSizeForHumans() }}</span>
                            @if($book->page_count)
                                <span class="font-mono text-[10px] text-ink-tertiary">{{ $book->page_count }}p</span>
                            @endif
                            @if($pos && $pct > 0)
                                <span class="font-mono text-[10px] font-bold" style="color:{{ $book->cover_color }}">{{ $pct }}%</span>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="px-3 pb-3 flex items-center justify-between gap-2">
                        <a href="{{ route('books.read', $book) }}"
                           class="flex-1 text-center bg-accent hover:bg-accent-dark text-white font-mono font-bold text-xs px-2 py-1.5 rounded-lg transition-colors">
                            {{ ($pos && $pos->current_page > 1) ? 'Continue' : 'Read' }}
                        </a>
                        <button wire:click="delete({{ $book->id }})"
                                wire:confirm="Delete '{{ addslashes($book->title) }}'? File will be removed from storage."
                                class="font-mono text-[10px] text-ink-tertiary hover:text-danger transition-colors px-1">
                            ✕
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($books->hasPages())
            <div class="flex items-center justify-center gap-1 flex-wrap">
                {{-- Prev --}}
                @if($books->onFirstPage())
                    <span class="font-mono text-xs px-3 py-1.5 rounded-lg text-ink-tertiary/40 cursor-not-allowed">← Prev</span>
                @else
                    <button wire:click="previousPage" class="font-mono text-xs px-3 py-1.5 rounded-lg text-ink-secondary hover:text-ink-primary hover:bg-base-elevated transition-colors">← Prev</button>
                @endif

                {{-- Page numbers --}}
                @foreach($books->getUrlRange(1, $books->lastPage()) as $page => $url)
                    @if($page == $books->currentPage())
                        <span class="font-mono text-xs px-3 py-1.5 rounded-lg bg-accent text-white font-bold">{{ $page }}</span>
                    @elseif(abs($page - $books->currentPage()) <= 2 || $page == 1 || $page == $books->lastPage())
                        <button wire:click="gotoPage({{ $page }})" class="font-mono text-xs px-3 py-1.5 rounded-lg text-ink-secondary hover:bg-base-elevated transition-colors">{{ $page }}</button>
                    @elseif(abs($page - $books->currentPage()) == 3)
                        <span class="font-mono text-xs text-ink-tertiary/40 px-1">…</span>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($books->hasMorePages())
                    <button wire:click="nextPage" class="font-mono text-xs px-3 py-1.5 rounded-lg text-ink-secondary hover:text-ink-primary hover:bg-base-elevated transition-colors">Next →</button>
                @else
                    <span class="font-mono text-xs px-3 py-1.5 rounded-lg text-ink-tertiary/40 cursor-not-allowed">Next →</span>
                @endif
            </div>
            <p class="text-center font-mono text-[11px] text-ink-tertiary/50 mt-2">
                {{ $books->firstItem() }}–{{ $books->lastItem() }} of {{ $books->total() }} books
            </p>
        @endif
    @endif

</div>

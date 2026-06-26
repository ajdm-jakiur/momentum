<div>
    <div class="px-3 py-5 sm:px-5 sm:py-6 lg:px-7 overflow-x-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <div class="min-w-0">
                <h1 class="font-mono text-2xl font-extrabold text-ink-primary">Tasks</h1>
                <p class="text-sm text-ink-secondary mt-1">Recurring work that lives outside any roadmap block — OSS contributions, Reddit/SO answers, blog posts, language practice.</p>
            </div>
            @if(! $showForm)
                <button wire:click="newTask" class="bg-accent hover:bg-accent-dark text-white font-mono font-bold text-sm px-4 py-2.5 rounded-lg transition-colors flex-shrink-0 self-start sm:self-auto">+ New Task</button>
            @endif
        </div>

        @if($showForm)
            <div class="bg-base-surface border border-base-border rounded-xl px-5 py-5 mb-6">
                <h2 class="font-mono font-bold text-sm text-ink-primary mb-4">{{ $editingId ? 'Edit Task' : 'New Task' }}</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Title</label>
                        <input type="text" wire:model="form.title" class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors">
                        @error('form.title') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Description</label>
                        <textarea wire:model="form.description" rows="2" class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors"></textarea>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Type</label>
                            <select wire:model="form.type" class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors">
                                @foreach($types as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Recurrence</label>
                            <select wire:model="form.recurrence" class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors">
                                @foreach($recurrences as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Target/period</label>
                            <input type="number" min="1" wire:model="form.target_per_period" placeholder="e.g. 3" class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors">
                        </div>
                        <div>
                            <label class="block font-mono text-[11px] font-bold uppercase tracking-widest text-ink-tertiary mb-1.5">Sector</label>
                            <select wire:model="form.sector_id" class="w-full bg-base-elevated border border-base-border text-ink-primary rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent/50 transition-colors">
                                <option value="">None</option>
                                @foreach($sectors as $sector)
                                    <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-1">
                        <button wire:click="save" class="bg-accent hover:bg-accent-dark text-white font-mono font-bold text-sm px-4 py-2.5 rounded-lg transition-colors">{{ $editingId ? 'Save changes' : 'Create task' }}</button>
                        <button wire:click="cancelForm" class="bg-base-elevated hover:bg-base-hover text-ink-secondary hover:text-ink-primary font-mono text-sm px-4 py-2.5 rounded-lg transition-colors">Cancel</button>
                    </div>
                </div>
            </div>
        @endif

        @forelse($groupedTasks as $sectorName => $tasks)
            <div class="mb-6">
                <h2 class="font-mono font-bold text-sm text-ink-tertiary uppercase tracking-wide mb-2">{{ $sectorName }}</h2>
                <div class="space-y-2">
                    @foreach($tasks as $task)
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 bg-base-surface border border-base-border rounded-xl px-3.5 py-3.5 sm:px-4 mb-2 {{ ! $task->is_active ? 'opacity-50' : '' }}">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono text-[10px] font-bold uppercase tracking-wide bg-community/20 text-community px-2 py-0.5 rounded">{{ $types[$task->type] }}</span>
                                    <span class="font-mono text-[10px] text-ink-tertiary">{{ $recurrences[$task->recurrence] }}</span>
                                    @if($task->target_per_period)
                                        <span class="font-mono text-[10px] text-ink-tertiary">target: {{ $task->target_per_period }}/period</span>
                                    @endif
                                </div>
                                <div class="font-semibold text-sm text-ink-primary mt-1 break-words">{{ $task->title }}</div>
                                @if($task->description)
                                    <div class="text-xs text-ink-secondary mt-0.5 break-words">{{ $task->description }}</div>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap flex-shrink-0">
                                @if(in_array($task->id, $loggedTodayTaskIds))
                                    <span class="bg-accent/15 text-accent border border-accent/25 font-mono text-xs font-bold px-2.5 py-1 rounded-full">Logged ✓</span>
                                @else
                                    <button wire:click="logToday({{ $task->id }})" class="bg-accent hover:bg-accent-dark text-white font-mono font-bold text-xs px-2.5 py-1 rounded-lg transition-colors">Log today</button>
                                @endif
                                <button wire:click="edit({{ $task->id }})" class="font-mono text-xs text-ink-tertiary hover:text-ink-primary px-1 transition-colors">Edit</button>
                                <button wire:click="toggleActive({{ $task->id }})" class="font-mono text-xs text-ink-tertiary hover:text-ink-primary px-1 transition-colors">{{ $task->is_active ? 'Pause' : 'Resume' }}</button>
                                <button wire:click="delete({{ $task->id }})" wire:confirm="Delete this task?" class="font-mono text-xs text-ink-tertiary hover:text-danger px-1 transition-colors">Delete</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-sm text-ink-tertiary font-mono">No tasks yet. Create one for OSS contributions, Reddit/SO answers, blog posts, or language practice.</p>
        @endforelse
    </div>
</div>

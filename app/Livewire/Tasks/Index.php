<?php

namespace App\Livewire\Tasks;

use App\Livewire\FullPageComponent;
use App\Models\Checkin;
use App\Models\Sector;
use App\Models\Task;
use Illuminate\Support\Carbon;

class Index extends FullPageComponent
{
    public array $form = [
        'title' => '',
        'description' => '',
        'type' => 'custom',
        'recurrence' => 'once',
        'target_per_period' => null,
        'sector_id' => null,
        'is_active' => true,
    ];

    public ?int $editingId = null;

    public bool $showForm = false;

    public array $types = [
        'oss_contribution' => 'OSS Contribution',
        'reddit' => 'Reddit',
        'blog' => 'Blog',
        'twitter' => 'Twitter/X',
        'so_answer' => 'Stack Overflow',
        'practice' => 'Practice (e.g. language)',
        'custom' => 'Custom',
    ];

    public array $recurrences = [
        'once' => 'Once',
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
    ];

    protected function rules(): array
    {
        return [
            'form.title' => ['required', 'string', 'max:255'],
            'form.description' => ['nullable', 'string'],
            'form.type' => ['required', 'string', 'in:'.implode(',', array_keys($this->types))],
            'form.recurrence' => ['required', 'string', 'in:'.implode(',', array_keys($this->recurrences))],
            'form.target_per_period' => ['nullable', 'integer', 'min:1'],
            'form.sector_id' => ['nullable', 'exists:sectors,id'],
        ];
    }

    public function newTask(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            Task::where('id', $this->editingId)
                ->where('user_id', auth()->id())
                ->update($this->form);
        } else {
            auth()->user()->tasks()->create($this->form);
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function edit(int $taskId): void
    {
        $task = Task::where('user_id', auth()->id())->findOrFail($taskId);

        $this->editingId = $task->id;
        $this->form = [
            'title' => $task->title,
            'description' => $task->description,
            'type' => $task->type,
            'recurrence' => $task->recurrence,
            'target_per_period' => $task->target_per_period,
            'sector_id' => $task->sector_id,
            'is_active' => $task->is_active,
        ];
        $this->showForm = true;
    }

    public function toggleActive(int $taskId): void
    {
        $task = Task::where('user_id', auth()->id())->findOrFail($taskId);
        $task->update(['is_active' => ! $task->is_active]);
    }

    public function delete(int $taskId): void
    {
        Task::where('user_id', auth()->id())->where('id', $taskId)->delete();
    }

    public function logToday(int $taskId): void
    {
        $task = Task::where('user_id', auth()->id())->findOrFail($taskId);

        $alreadyLogged = Checkin::where('task_id', $task->id)
            ->where('user_id', auth()->id())
            ->whereDate('date', Carbon::today())
            ->exists();

        if ($alreadyLogged) {
            return;
        }

        Checkin::create([
            'user_id' => auth()->id(),
            'date' => Carbon::today(),
            'sector_id' => $task->sector_id,
            'task_id' => $task->id,
            'checkin_type' => 'task',
            'note' => "Logged: {$task->title}",
        ]);
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->form = [
            'title' => '',
            'description' => '',
            'type' => 'custom',
            'recurrence' => 'once',
            'target_per_period' => null,
            'sector_id' => null,
            'is_active' => true,
        ];
    }

    public function render()
    {
        $tasks = auth()->user()->tasks()->with('sector')->orderByDesc('is_active')->orderBy('title')->get()
            ->groupBy(fn (Task $t) => $t->sector?->name ?? 'Unassigned');

        $loggedTodayTaskIds = Checkin::where('user_id', auth()->id())
            ->whereDate('date', Carbon::today())
            ->whereNotNull('task_id')
            ->pluck('task_id')
            ->all();

        return view('livewire.tasks.index', [
            'groupedTasks' => $tasks,
            'sectors' => Sector::orderBy('sort_order')->get(),
            'loggedTodayTaskIds' => $loggedTodayTaskIds,
        ]);
    }
}

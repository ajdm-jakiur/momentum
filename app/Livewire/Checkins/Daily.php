<?php

namespace App\Livewire\Checkins;

use App\Livewire\FullPageComponent;
use App\Models\Checkin;
use App\Models\Sector;
use App\Models\Streak;
use App\Models\Task;
use Illuminate\Support\Carbon;

class Daily extends FullPageComponent
{
    public array $form = [
        'sector_id' => null,
        'task_id' => null,
        'minutes_spent' => 30,
        'note' => '',
        'checkin_type' => 'study',
    ];

    public array $checkinTypes = [
        'study' => 'Study',
        'project' => 'Project',
        'community' => 'Community',
        'task' => 'Task',
    ];

    protected function rules(): array
    {
        return [
            'form.sector_id' => ['nullable', 'exists:sectors,id'],
            'form.task_id' => ['nullable', 'exists:tasks,id'],
            'form.minutes_spent' => ['required', 'integer', 'min:0', 'max:1440'],
            'form.note' => ['nullable', 'string', 'max:1000'],
            'form.checkin_type' => ['required', 'string', 'in:study,project,community,task'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        Checkin::create([
            'user_id' => auth()->id(),
            'date' => Carbon::today(),
            'sector_id' => $this->form['sector_id'],
            'task_id' => $this->form['task_id'],
            'minutes_spent' => $this->form['minutes_spent'],
            'note' => $this->form['note'],
            'checkin_type' => $this->form['checkin_type'],
        ]);

        $this->form['note'] = '';
    }

    public function delete(int $checkinId): void
    {
        Checkin::where('user_id', auth()->id())->where('id', $checkinId)->delete();
    }

    public function render()
    {
        $today = Carbon::today();

        $todaysCheckins = Checkin::where('user_id', auth()->id())
            ->whereDate('date', $today)
            ->with(['sector', 'roadmap', 'block', 'task'])
            ->orderByDesc('created_at')
            ->get();

        $streaks = Streak::where('user_id', auth()->id())->with('sector')->get()->keyBy('sector_id');

        return view('livewire.checkins.daily', [
            'todaysCheckins' => $todaysCheckins,
            'sectors' => Sector::orderBy('sort_order')->get(),
            'tasks' => Task::where('user_id', auth()->id())->where('is_active', true)->orderBy('title')->get(),
            'streaks' => $streaks,
            'totalMinutesToday' => $todaysCheckins->sum('minutes_spent'),
        ]);
    }
}

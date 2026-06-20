<?php

namespace Tests\Feature;

use App\Livewire\Tasks\Index;
use App\Models\Checkin;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_task(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(Index::class)
            ->call('newTask')
            ->set('form.title', 'Answer 3 SO questions')
            ->set('form.type', 'so_answer')
            ->set('form.recurrence', 'weekly')
            ->set('form.target_per_period', 3)
            ->call('save')
            ->assertSet('showForm', false);

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Answer 3 SO questions',
            'type' => 'so_answer',
            'recurrence' => 'weekly',
            'target_per_period' => 3,
        ]);
    }

    public function test_logging_today_creates_a_checkin_and_is_idempotent(): void
    {
        $user = User::factory()->create();
        $task = $user->tasks()->create(['title' => 'Reddit post', 'type' => 'reddit', 'recurrence' => 'weekly']);

        $component = Livewire::actingAs($user)->test(Index::class)
            ->call('logToday', $task->id);

        $this->assertSame(1, Checkin::where('task_id', $task->id)->count());

        // Calling again the same day should not create a duplicate.
        $component->call('logToday', $task->id);
        $this->assertSame(1, Checkin::where('task_id', $task->id)->count());
    }

    public function test_user_cannot_edit_another_users_task(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $task = $owner->tasks()->create(['title' => 'Private task', 'type' => 'custom', 'recurrence' => 'once']);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($intruder)->test(Index::class)
            ->call('edit', $task->id);
    }

    public function test_user_can_delete_their_own_task(): void
    {
        $user = User::factory()->create();
        $task = $user->tasks()->create(['title' => 'To delete', 'type' => 'custom', 'recurrence' => 'once']);

        Livewire::actingAs($user)->test(Index::class)
            ->call('delete', $task->id);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}

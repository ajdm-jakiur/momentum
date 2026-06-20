<?php

namespace Tests\Feature;

use App\Models\Checkin;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardAndReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_sector_progress_and_hours(): void
    {
        $user = User::factory()->create();
        $sector = Sector::create(['name' => 'DSA', 'slug' => 'dsa']);
        $roadmap = $sector->roadmaps()->create(['title' => 'DSA Roadmap']);
        $phase = $roadmap->phases()->create(['title' => 'Phase 1']);
        $block = $phase->blocks()->create(['title' => 'Block A']);
        $block->items()->create(['kind' => 'problem', 'title' => 'Two Sum']);

        Checkin::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'sector_id' => $sector->id,
            'minutes_spent' => 45,
            'checkin_type' => 'study',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('DSA');
        $response->assertSee('Block A');
    }

    public function test_weekly_report_shows_logged_minutes(): void
    {
        $user = User::factory()->create();
        $sector = Sector::create(['name' => 'DSA', 'slug' => 'dsa']);

        Checkin::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'sector_id' => $sector->id,
            'minutes_spent' => 60,
            'checkin_type' => 'study',
        ]);

        $response = $this->actingAs($user)->get(route('reports.weekly'));

        $response->assertOk();
        $response->assertSee('DSA');
        $response->assertSee('1h 0m logged');
    }

    public function test_monthly_report_renders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.monthly'));

        $response->assertOk();
        $response->assertSee('Reports');
    }
}

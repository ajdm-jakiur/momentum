<?php

namespace Tests\Feature;

use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoadmapViewerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_a_roadmap(): void
    {
        $user = User::factory()->create();
        $sector = Sector::create(['name' => 'Test Sector', 'slug' => 'test-sector']);
        $roadmap = $sector->roadmaps()->create(['title' => 'Test Roadmap']);
        $phase = $roadmap->phases()->create(['title' => 'Phase 1']);
        $block = $phase->blocks()->create(['title' => 'Block A']);
        $block->resources()->create(['name' => 'Book One']);
        $block->items()->create(['kind' => 'project', 'title' => 'Project One']);

        $response = $this->actingAs($user)->get(route('roadmaps.show', $roadmap));

        $response->assertOk();
        $response->assertSee('Test Roadmap');
        $response->assertSee('Book One');
        $response->assertSee('Project One');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $sector = Sector::create(['name' => 'Test Sector', 'slug' => 'test-sector']);
        $roadmap = $sector->roadmaps()->create(['title' => 'Test Roadmap']);

        $response = $this->get(route('roadmaps.show', $roadmap));

        $response->assertRedirect(route('login'));
    }
}

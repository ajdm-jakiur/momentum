<?php

namespace Tests\Feature;

use App\Livewire\Roadmaps\Import;
use App\Models\Roadmap;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoadmapImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_preview_and_confirm_a_valid_roadmap_import(): void
    {
        $user = User::factory()->create();
        Sector::create(['name' => 'Systems Engineering', 'slug' => 'systems-engineering']);

        $json = file_get_contents(resource_path('schemas/roadmap-sample.json'));

        Livewire::actingAs($user)->test(Import::class)
            ->set('jsonInput', $json)
            ->call('preview')
            ->assertSet('errors', [])
            ->assertSee('0.1% Engineer Roadmap v2')
            ->call('confirm')
            ->assertRedirect();

        $this->assertDatabaseHas('roadmaps', ['title' => '0.1% Engineer Roadmap v2']);

        $roadmap = Roadmap::where('title', '0.1% Engineer Roadmap v2')->first();
        $this->assertSame(2, $roadmap->phases()->first()->blocks()->first()->resources()->count());
    }

    public function test_invalid_json_shows_an_error_and_does_not_create_a_roadmap(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(Import::class)
            ->set('jsonInput', '{not valid json')
            ->call('preview')
            ->assertSet('parsed', null);

        $this->assertDatabaseCount('roadmaps', 0);
    }

    public function test_unknown_sector_slug_is_rejected(): void
    {
        $user = User::factory()->create();

        $json = json_decode(file_get_contents(resource_path('schemas/roadmap-sample.json')), true);
        $json['sector'] = 'does-not-exist';

        Livewire::actingAs($user)->test(Import::class)
            ->set('jsonInput', json_encode($json))
            ->call('preview')
            ->assertSet('parsed', null);

        $this->assertDatabaseCount('roadmaps', 0);
    }
}

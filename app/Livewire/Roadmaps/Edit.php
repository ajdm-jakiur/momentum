<?php

namespace App\Livewire\Roadmaps;

use App\Livewire\FullPageComponent;
use App\Models\Roadmap;
use App\Models\Sector;
use App\Services\RoadmapImportService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;

class Edit extends FullPageComponent
{
    public Roadmap $roadmap;

    public string $tab = 'metadata'; // 'metadata' | 'json'

    // Metadata tab fields
    public string $title = '';
    public string $description = '';
    public string $color = '#e85d26';
    public string $totalWeeks = '';
    public string $sectorSlug = '';

    // JSON tab
    public string $jsonRaw = '';
    public string $jsonError = '';

    public function mount(Roadmap $roadmap): void
    {
        $this->roadmap = $roadmap;
        $this->loadFromModel();
    }

    private function loadFromModel(): void
    {
        $this->title = $this->roadmap->title;
        $this->description = $this->roadmap->description ?? '';
        $this->color = $this->roadmap->color ?? '#e85d26';
        $this->totalWeeks = (string) ($this->roadmap->total_weeks ?? '');
        $this->sectorSlug = $this->roadmap->sector->slug ?? '';
        $this->jsonRaw = $this->roadmap->imported_json
            ? json_encode(json_decode($this->roadmap->imported_json), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            : '';
        $this->jsonError = '';
    }

    #[Computed]
    public function sectors()
    {
        return Sector::orderBy('sort_order')->get();
    }

    public function saveMetadata(): void
    {
        $data = $this->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'color'       => 'required|string|max:7',
            'totalWeeks'  => 'nullable|integer|min:1|max:520',
            'sectorSlug'  => 'required|string|exists:sectors,slug',
        ]);

        $sector = Sector::where('slug', $this->sectorSlug)->firstOrFail();

        $this->roadmap->update([
            'sector_id'   => $sector->id,
            'title'       => $this->title,
            'description' => $this->description ?: null,
            'color'       => $this->color,
            'total_weeks' => $this->totalWeeks ?: null,
        ]);

        // Sync imported_json metadata block if it exists
        if ($this->roadmap->imported_json) {
            $json = json_decode($this->roadmap->imported_json, true);
            $json['sector'] = $this->sectorSlug;
            $json['roadmap']['title'] = $this->title;
            $json['roadmap']['description'] = $this->description ?: null;
            $json['roadmap']['color'] = $this->color;
            $json['roadmap']['total_weeks'] = $this->totalWeeks ? (int) $this->totalWeeks : null;
            $this->roadmap->update(['imported_json' => json_encode($json)]);
            $this->jsonRaw = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        session()->flash('success', 'Roadmap updated.');
    }

    public function saveJson(): void
    {
        $this->jsonError = '';

        $decoded = json_decode($this->jsonRaw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->jsonError = 'Invalid JSON: '.json_last_error_msg();
            return;
        }

        try {
            $service = app(RoadmapImportService::class);
            $service->reimport($this->roadmap, $decoded);
            $this->roadmap->refresh();
            $this->loadFromModel();
            session()->flash('success', 'Roadmap rebuilt from JSON.');
        } catch (ValidationException $e) {
            $this->jsonError = collect($e->errors())->flatten()->implode(' ');
        }
    }

    public function render()
    {
        return view('livewire.roadmaps.edit');
    }
}

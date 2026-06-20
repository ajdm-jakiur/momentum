<?php

namespace App\Livewire\Roadmaps;

use App\Livewire\FullPageComponent;
use App\Models\Sector;
use App\Services\RoadmapImportService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;

class Import extends FullPageComponent
{
    public string $jsonInput = '';

    public ?array $parsed = null;

    /** @var array<string, string> */
    public array $errors = [];

    public function updatedJsonInput(): void
    {
        $this->parsed = null;
        $this->errors = [];
    }

    public function preview(RoadmapImportService $importer): void
    {
        $this->parsed = null;
        $this->errors = [];

        $data = json_decode($this->jsonInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->errors = ['json' => 'That is not valid JSON: '.json_last_error_msg()];

            return;
        }

        try {
            $importer->validate($data);
            $this->parsed = $data;
        } catch (ValidationException $e) {
            $this->errors = collect($e->errors())->map(fn ($msgs) => $msgs[0])->all();
        }
    }

    public function confirm(RoadmapImportService $importer): void
    {
        if (! $this->parsed) {
            return;
        }

        $roadmap = $importer->import($this->parsed);

        $this->redirectRoute('roadmaps.show', $roadmap);
    }

    public function cancelPreview(): void
    {
        $this->parsed = null;
        $this->errors = [];
    }

    #[Computed]
    public function summary(): ?array
    {
        if (! $this->parsed) {
            return null;
        }

        $kindDefs = config('roadmap.item_kinds');
        $phases   = [];
        $totals   = array_fill_keys(array_keys($kindDefs), 0) + ['books' => 0, 'blocks' => 0];

        foreach ($this->parsed['phases'] as $phase) {
            $blocks = [];

            foreach ($phase['blocks'] as $block) {
                $books          = count(array_filter($block['resources'] ?? [], fn ($r) => ($r['kind'] ?? 'book') === 'book'));
                $otherResources = count($block['resources'] ?? []) - $books;

                $byKind = [];
                foreach ($kindDefs as $key => $cfg) {
                    $count          = count(array_filter($block['items'] ?? [], fn ($i) => $i['kind'] === $key));
                    $byKind[$key]   = $count;
                    $totals[$key]  += $count;
                }

                $blocks[] = array_merge([
                    'title'          => $block['title'],
                    'books'          => $books,
                    'other_resources' => $otherResources,
                    'required_total' => count($block['resources'] ?? []) + count($block['items'] ?? []),
                ], $byKind);

                $totals['books']  += $books;
                $totals['blocks']++;
            }

            $phases[] = ['title' => $phase['title'], 'blocks' => $blocks];
        }

        return ['phases' => $phases, 'totals' => $totals];
    }

    public function render()
    {
        return view('livewire.roadmaps.import', [
            'sectors' => Sector::orderBy('sort_order')->get(),
            'schemaSample' => file_get_contents(resource_path('schemas/roadmap-sample.json')),
        ]);
    }
}

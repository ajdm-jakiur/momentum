<?php

namespace App\Services;

use App\Models\Roadmap;
use App\Models\Sector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * The single place that maps the documented roadmap JSON schema
 * (resources/schemas/roadmap-sample.json) into roadmaps/phases/blocks/
 * block_resources/block_items/block_daily_notes rows.
 */
class RoadmapImportService
{
    public function __construct(private CompletionService $completion) {}

    /**
     * @throws ValidationException
     */
    public function validate(array $data): void
    {
        $validator = Validator::make($data, [
            'sector' => ['required', 'string'],
            'roadmap' => ['required', 'array'],
            'roadmap.title' => ['required', 'string'],
            'roadmap.description' => ['nullable', 'string'],
            'roadmap.total_weeks' => ['nullable', 'integer'],
            'roadmap.color' => ['nullable', 'string'],
            'phases' => ['required', 'array', 'min:1'],
            'phases.*.title' => ['required', 'string'],
            'phases.*.duration_label' => ['nullable', 'string'],
            'phases.*.description' => ['nullable', 'string'],
            'phases.*.milestone' => ['nullable', 'string'],
            'phases.*.blocks' => ['required', 'array', 'min:1'],
            'phases.*.blocks.*.title' => ['required', 'string'],
            'phases.*.blocks.*.weeks_label' => ['nullable', 'string'],
            'phases.*.blocks.*.icon' => ['nullable', 'string'],
            'phases.*.blocks.*.pattern_text' => ['nullable', 'string'],
            'phases.*.blocks.*.resources' => ['nullable', 'array'],
            'phases.*.blocks.*.resources.*.name' => ['required_with:phases.*.blocks.*.resources', 'string'],
            'phases.*.blocks.*.resources.*.kind' => ['nullable', 'string'],
            'phases.*.blocks.*.daily_notes' => ['nullable', 'array'],
            'phases.*.blocks.*.daily_notes.*' => ['string'],
            'phases.*.blocks.*.items' => ['nullable', 'array'],
            'phases.*.blocks.*.items.*.kind' => ['required_with:phases.*.blocks.*.items', 'in:'.implode(',', array_keys(config('roadmap.item_kinds')))],
            'phases.*.blocks.*.items.*.title' => ['required_with:phases.*.blocks.*.items', 'string'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if (! Sector::where('slug', $data['sector'])->exists()) {
            throw ValidationException::withMessages([
                'sector' => "No sector with slug \"{$data['sector']}\" exists. Create the sector first or use one of: ".
                    Sector::pluck('slug')->implode(', '),
            ]);
        }
    }

    /**
     * Replace all content of an existing roadmap from new JSON data.
     * Keeps the roadmap record (same ID), deletes and rebuilds all phases.
     *
     * @throws ValidationException
     */
    public function reimport(Roadmap $roadmap, array $data): Roadmap
    {
        $this->validate($data);

        return DB::transaction(function () use ($roadmap, $data) {
            $sector = Sector::where('slug', $data['sector'])->firstOrFail();

            $roadmap->update([
                'sector_id' => $sector->id,
                'title' => $data['roadmap']['title'],
                'description' => $data['roadmap']['description'] ?? null,
                'color' => $data['roadmap']['color'] ?? '#e85d26',
                'total_weeks' => $data['roadmap']['total_weeks'] ?? null,
                'source' => 'json_import',
                'imported_json' => json_encode($data),
            ]);

            // Wipe and rebuild — simplest correct approach
            $roadmap->phases()->each(fn ($p) => $p->blocks()->each(fn ($b) => $b->forceDelete()));
            $roadmap->phases()->delete();

            foreach (array_values($data['phases']) as $phaseIndex => $phaseData) {
                $phase = $roadmap->phases()->create([
                    'title' => $phaseData['title'],
                    'duration_label' => $phaseData['duration_label'] ?? null,
                    'description' => $phaseData['description'] ?? null,
                    'milestone' => $phaseData['milestone'] ?? null,
                    'color' => $phaseData['color'] ?? null,
                    'sort_order' => $phaseIndex,
                ]);

                foreach (array_values($phaseData['blocks']) as $blockIndex => $blockData) {
                    $block = $phase->blocks()->create([
                        'title' => $blockData['title'],
                        'weeks_label' => $blockData['weeks_label'] ?? null,
                        'icon' => $blockData['icon'] ?? null,
                        'pattern_text' => $blockData['pattern_text'] ?? null,
                        'sort_order' => $blockIndex,
                    ]);

                    foreach (array_values($blockData['resources'] ?? []) as $resIndex => $res) {
                        $block->resources()->create([
                            'name' => $res['name'],
                            'author_or_type' => $res['author_or_type'] ?? null,
                            'note' => $res['note'] ?? null,
                            'url' => $res['url'] ?? null,
                            'kind' => $res['kind'] ?? 'book',
                            'sort_order' => $resIndex,
                            'is_required' => $res['is_required'] ?? true,
                        ]);
                    }

                    foreach (array_values($blockData['daily_notes'] ?? []) as $noteIndex => $note) {
                        $block->dailyNotes()->create(['body' => $note, 'sort_order' => $noteIndex]);
                    }

                    foreach (array_values($blockData['items'] ?? []) as $itemIndex => $item) {
                        $block->items()->create([
                            'kind' => $item['kind'],
                            'title' => $item['title'],
                            'body' => $item['body'] ?? null,
                            'meta' => $item['meta'] ?? null,
                            'sort_order' => $itemIndex,
                            'is_required' => $item['is_required'] ?? true,
                        ]);
                    }

                    $this->completion->recalculateBlock($block);
                }
            }

            return $roadmap->refresh();
        });
    }

    public function import(array $data): Roadmap
    {
        $this->validate($data);

        return DB::transaction(function () use ($data) {
            $sector = Sector::where('slug', $data['sector'])->firstOrFail();

            $roadmap = $sector->roadmaps()->create([
                'title' => $data['roadmap']['title'],
                'description' => $data['roadmap']['description'] ?? null,
                'color' => $data['roadmap']['color'] ?? '#e85d26',
                'total_weeks' => $data['roadmap']['total_weeks'] ?? null,
                'source' => 'json_import',
                'imported_json' => json_encode($data),
            ]);

            foreach (array_values($data['phases']) as $phaseIndex => $phaseData) {
                $phase = $roadmap->phases()->create([
                    'title' => $phaseData['title'],
                    'duration_label' => $phaseData['duration_label'] ?? null,
                    'description' => $phaseData['description'] ?? null,
                    'milestone' => $phaseData['milestone'] ?? null,
                    'color' => $phaseData['color'] ?? null,
                    'sort_order' => $phaseIndex,
                ]);

                foreach (array_values($phaseData['blocks']) as $blockIndex => $blockData) {
                    $block = $phase->blocks()->create([
                        'title' => $blockData['title'],
                        'weeks_label' => $blockData['weeks_label'] ?? null,
                        'icon' => $blockData['icon'] ?? null,
                        'pattern_text' => $blockData['pattern_text'] ?? null,
                        'sort_order' => $blockIndex,
                    ]);

                    foreach (array_values($blockData['resources'] ?? []) as $resIndex => $res) {
                        $block->resources()->create([
                            'name' => $res['name'],
                            'author_or_type' => $res['author_or_type'] ?? null,
                            'note' => $res['note'] ?? null,
                            'url' => $res['url'] ?? null,
                            'kind' => $res['kind'] ?? 'book',
                            'sort_order' => $resIndex,
                            'is_required' => $res['is_required'] ?? true,
                        ]);
                    }

                    foreach (array_values($blockData['daily_notes'] ?? []) as $noteIndex => $note) {
                        $block->dailyNotes()->create([
                            'body' => $note,
                            'sort_order' => $noteIndex,
                        ]);
                    }

                    foreach (array_values($blockData['items'] ?? []) as $itemIndex => $item) {
                        $block->items()->create([
                            'kind' => $item['kind'],
                            'title' => $item['title'],
                            'body' => $item['body'] ?? null,
                            'meta' => $item['meta'] ?? null,
                            'sort_order' => $itemIndex,
                            'is_required' => $item['is_required'] ?? true,
                        ]);
                    }

                    // Observers fire per resource/item save, but recalc once more
                    // here in case a block was imported with zero completable rows.
                    $this->completion->recalculateBlock($block);
                }
            }

            return $roadmap->refresh();
        });
    }
}

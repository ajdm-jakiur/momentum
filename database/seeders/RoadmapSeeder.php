<?php

namespace Database\Seeders;

use App\Services\RoadmapImportService;
use Illuminate\Database\Seeder;

class RoadmapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(RoadmapImportService $importer): void
    {
        $importer->import($this->systemsEngineeringSchema());
        $importer->import($this->dsaSchema());
    }

    private function systemsEngineeringSchema(): array
    {
        return json_decode(
            file_get_contents(__DIR__.'/data/systems-engineering-roadmap.json'),
            true,
        );
    }

    /**
     * The DSA roadmap's source data ("PHASES" from the original HTML) is already
     * valid JSON but uses a different shape (n/lc/d/s/u/t keys, problems[] instead
     * of items[]). Map it onto the documented import schema here.
     */
    private function dsaSchema(): array
    {
        $raw = json_decode(file_get_contents(__DIR__.'/data/dsa-roadmap-raw.json'), true);

        $resourceKindMap = [
            'roadmap' => 'reference',
            'sheet' => 'reference',
            'guide' => 'guide',
            'course' => 'course',
            'video' => 'video',
            'reference' => 'reference',
        ];

        $phases = array_map(function (array $phase) use ($resourceKindMap) {
            $blocks = array_map(function (array $block) use ($resourceKindMap) {
                $resources = array_map(fn (array $r) => [
                    'name' => $r['n'],
                    'author_or_type' => null,
                    'note' => null,
                    'url' => $r['u'],
                    'kind' => $resourceKindMap[$r['t']] ?? 'reference',
                ], $block['resources'] ?? []);

                $items = array_map(fn (array $p) => [
                    'kind' => 'problem',
                    'title' => $p['n'],
                    'body' => null,
                    'meta' => [
                        'lc_id' => $p['lc'],
                        'difficulty' => $p['d'],
                        'slug' => $p['s'],
                    ],
                ], $block['problems'] ?? []);

                return [
                    'title' => "Block {$block['block']}: {$block['name']}",
                    'weeks_label' => null,
                    'icon' => $block['icon'] ?? null,
                    'pattern_text' => $block['pattern'] ?? null,
                    'resources' => $resources,
                    'daily_notes' => [],
                    'items' => $items,
                ];
            }, $phase['blocks']);

            return [
                'title' => $phase['title'],
                'duration_label' => $phase['duration'] ?? null,
                'description' => $phase['description'] ?? null,
                'milestone' => $phase['milestone'] ?? null,
                'color' => $phase['color'] ?? null,
                'blocks' => $blocks,
            ];
        }, $raw);

        return [
            'sector' => 'dsa',
            'roadmap' => [
                'title' => 'DSA Interview Roadmap',
                'description' => '18 weeks - 18 topics - 150 LeetCode problems - pattern-first preparation',
                'total_weeks' => 18,
                'color' => '#e85d26',
            ],
            'phases' => $phases,
        ];
    }
}

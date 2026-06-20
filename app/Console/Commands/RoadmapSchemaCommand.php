<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('roadmap:schema {--no-cheatsheet : Print only the raw JSON, no field guide}')]
#[Description('Print the canonical roadmap JSON schema sample — copy it into an AI prompt or save it as a starting file.')]
class RoadmapSchemaCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $json = file_get_contents(resource_path('schemas/roadmap-sample.json'));

        if (! $this->option('no-cheatsheet')) {
            $this->line($this->cheatsheet());
        }

        $this->output->write($json);

        return self::SUCCESS;
    }

    private function cheatsheet(): string
    {
        $kinds = implode('|', array_keys(config('roadmap.item_kinds', ['project', 'problem', 'community'])));

        return <<<TXT
        # Roadmap JSON schema — field guide
        #
        # sector            slug of an existing sector ("systems-engineering" | "dsa" | "language-learning" | "dev-practice")
        # roadmap.title/description/total_weeks/color
        # phases[]          ordered list — title, duration_label, description, milestone (free text, manually confirmed later)
        #   blocks[]        ordered list per phase
        #     title, weeks_label, icon (single emoji), pattern_text (the core technique/idea for this block)
        #     resources[]   BOOKS/COURSES — name, author_or_type, note, url, kind ("book"|"video"|"course"|"guide"|"reference")
        #     daily_notes[] plain strings — informational only, NOT a completion gate
        #     items[]       COMPLETABLE ITEMS — kind ("$kinds"), title, body, meta
        #                   problem meta: {"lc_id": int, "difficulty": "E"|"M"|"H", "slug": "leetcode-slug"}
        #                   community meta: {"platform": "...", "where": "..."}
        #
        # IMPORTANT: every resource/item defaults to is_required = true. A block only counts as
        # complete once ALL of its required resources AND items are checked off — so if you want
        # an AI to generate a new roadmap, tell it explicitly which books/projects/community actions
        # are mandatory vs optional (is_required: false) for each block, using this exact shape.
        #
        # Save this to a file: php artisan roadmap:schema --no-cheatsheet > my-roadmap.json
        # Then paste/upload it on the Import Roadmap page.

        TXT;
    }
}

<?php

namespace App\Services;

use App\Models\Block;
use App\Models\Phase;
use App\Models\Roadmap;
use Illuminate\Support\Carbon;

/**
 * Single source of truth for "is this block/phase/roadmap done".
 *
 * A block is only complete once every required resource (book) AND every
 * required item (project/problem/community action) under it is is_done.
 * Phase completion additionally requires the milestone (if any) to be
 * manually confirmed, since milestones like "have 10+ SO answers" are
 * judgment calls, not something derivable from checkbox state.
 */
class CompletionService
{
    public function recalculateBlock(Block $block): Block
    {
        $resources = $block->resources()->get(['is_required', 'is_done']);
        $items = $block->items()->get(['is_required', 'is_done']);

        $requiredTotal = $resources->where('is_required', true)->count()
            + $items->where('is_required', true)->count();

        $requiredDone = $resources->where('is_required', true)->where('is_done', true)->count()
            + $items->where('is_required', true)->where('is_done', true)->count();

        $wasComplete = $block->is_complete;
        $isComplete = $requiredTotal > 0 && $requiredDone === $requiredTotal;

        $block->forceFill([
            'required_total' => $requiredTotal,
            'required_done' => $requiredDone,
            'is_complete' => $isComplete,
            'completed_at' => $isComplete ? ($wasComplete ? $block->completed_at : Carbon::now()) : null,
        ])->save();

        $this->recalculatePhase($block->phase);

        return $block;
    }

    public function recalculatePhase(Phase $phase): Phase
    {
        $blocks = $phase->blocks()->get(['required_total', 'required_done', 'is_complete']);

        $progress = $blocks->isEmpty() ? 0 : (int) round(
            $blocks->avg(fn (Block $b) => $b->required_total > 0
                ? ($b->required_done / $b->required_total) * 100
                : 100
            )
        );

        $allBlocksComplete = $blocks->isNotEmpty() && $blocks->every(fn (Block $b) => $b->is_complete);
        $milestoneSatisfied = blank($phase->milestone) || $phase->milestone_confirmed;
        $wasComplete = $phase->is_complete;
        $isComplete = $allBlocksComplete && $milestoneSatisfied;

        $phase->forceFill([
            'progress_percent' => $progress,
            'is_complete' => $isComplete,
            'completed_at' => $isComplete ? ($wasComplete ? $phase->completed_at : Carbon::now()) : null,
        ])->save();

        $this->recalculateRoadmap($phase->roadmap);

        return $phase;
    }

    public function recalculateRoadmap(Roadmap $roadmap): Roadmap
    {
        $phases = $roadmap->phases()->get(['progress_percent', 'is_complete']);

        $progress = $phases->isEmpty() ? 0 : (int) round($phases->avg('progress_percent'));
        $allPhasesComplete = $phases->isNotEmpty() && $phases->every(fn (Phase $p) => $p->is_complete);
        $wasComplete = $roadmap->is_complete;

        $roadmap->forceFill([
            'progress_percent' => $progress,
            'is_complete' => $allPhasesComplete,
            'completed_at' => $allPhasesComplete ? ($wasComplete ? $roadmap->completed_at : Carbon::now()) : null,
        ])->save();

        return $roadmap;
    }
}

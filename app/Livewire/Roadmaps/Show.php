<?php

namespace App\Livewire\Roadmaps;

use App\Models\BlockItem;
use App\Models\BlockResource;
use App\Models\Phase;
use App\Models\Roadmap;
use App\Services\CheckinService;
use App\Services\CompletionService;
use App\Livewire\FullPageComponent;
use Livewire\Attributes\Locked;

class Show extends FullPageComponent
{
    #[Locked]
    public Roadmap $roadmap;

    public function mount(Roadmap $roadmap): void
    {
        $this->roadmap = $roadmap;
    }

    public function toggleResource(int $resourceId): void
    {
        /** @var BlockResource $resource */
        $resource = BlockResource::with('block.phase.roadmap')->findOrFail($resourceId);
        $resource->update(['is_done' => ! $resource->is_done, 'done_at' => ! $resource->is_done ? now() : null]);

        if ($resource->is_done) {
            app(CheckinService::class)->logBlockAction(auth()->user(), $resource->block, 'study', note: "Finished: {$resource->name}");
        }
    }

    public function toggleItem(int $itemId): void
    {
        /** @var BlockItem $item */
        $item = BlockItem::with('block.phase.roadmap')->findOrFail($itemId);
        $item->update(['is_done' => ! $item->is_done, 'done_at' => ! $item->is_done ? now() : null]);

        if ($item->is_done) {
            $type = config("roadmap.item_kinds.{$item->kind}.checkin_type", 'study');
            app(CheckinService::class)->logBlockAction(auth()->user(), $item->block, $type, note: "Completed: {$item->title}");
        }
    }

    public function trash(): void
    {
        $this->roadmap->delete();
        $this->redirectRoute('roadmaps.trash');
    }

    public function toggleMilestone(int $phaseId): void
    {
        $phase = Phase::with('roadmap')->findOrFail($phaseId);
        $phase->update(['milestone_confirmed' => ! $phase->milestone_confirmed]);
        app(CompletionService::class)->recalculatePhase($phase->fresh());
    }

    public function render()
    {
        $this->roadmap->load(['phases.blocks.resources', 'phases.blocks.items', 'phases.blocks.dailyNotes']);

        return view('livewire.roadmaps.show');
    }
}

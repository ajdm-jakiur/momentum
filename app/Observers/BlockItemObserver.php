<?php

namespace App\Observers;

use App\Models\BlockItem;
use App\Services\CompletionService;

class BlockItemObserver
{
    public function __construct(private CompletionService $completion) {}

    public function saved(BlockItem $item): void
    {
        $this->completion->recalculateBlock($item->block);
    }

    public function deleted(BlockItem $item): void
    {
        $this->completion->recalculateBlock($item->block);
    }
}

<?php

namespace App\Observers;

use App\Models\BlockResource;
use App\Services\CompletionService;

class BlockResourceObserver
{
    public function __construct(private CompletionService $completion) {}

    public function saved(BlockResource $resource): void
    {
        $this->completion->recalculateBlock($resource->block);
    }

    public function deleted(BlockResource $resource): void
    {
        $this->completion->recalculateBlock($resource->block);
    }
}

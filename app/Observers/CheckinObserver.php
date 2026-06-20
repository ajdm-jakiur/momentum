<?php

namespace App\Observers;

use App\Models\Checkin;
use App\Services\CheckinService;

class CheckinObserver
{
    public function __construct(private CheckinService $checkins) {}

    public function saved(Checkin $checkin): void
    {
        if ($checkin->sector_id) {
            $this->checkins->recomputeStreak($checkin->user, $checkin->sector_id);
        }
    }

    public function deleted(Checkin $checkin): void
    {
        if ($checkin->sector_id) {
            $this->checkins->recomputeStreak($checkin->user, $checkin->sector_id);
        }
    }
}

<?php

namespace App\Services;

use App\Models\Block;
use App\Models\Checkin;
use App\Models\Streak;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Writes the checkins row that backs both the time-based weekly/monthly
 * views and per-sector streak tracking. Every completion-affecting action
 * (block checkbox toggles, task "log today", manual daily check-ins) funnels
 * through here so there is exactly one source of truth.
 */
class CheckinService
{
    /** Log completing a block resource/item. Minutes default to 0 — actual time is logged separately via Daily Check-in. */
    public function logBlockAction(User $user, Block $block, string $checkinType, int $minutes = 0, ?string $note = null): Checkin
    {
        return Checkin::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'sector_id' => $block->phase->roadmap->sector_id,
            'roadmap_id' => $block->phase->roadmap_id,
            'block_id' => $block->id,
            'minutes_spent' => $minutes,
            'note' => $note,
            'checkin_type' => $checkinType,
        ]);
    }

    /**
     * Recompute a user/sector streak from scratch off the full set of distinct
     * checkin dates. A full recompute (rather than incrementing on each save)
     * keeps backdated or deleted checkins correct without special-casing them.
     */
    public function recomputeStreak(User $user, int $sectorId): Streak
    {
        $dates = Checkin::where('user_id', $user->id)
            ->where('sector_id', $sectorId)
            ->distinct()
            ->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->startOfDay())
            ->sort()
            ->values();

        [$longest, $current, $lastDate] = $this->computeStreaks($dates);

        return Streak::updateOrCreate(
            ['user_id' => $user->id, 'sector_id' => $sectorId],
            [
                'current_streak' => $current,
                'longest_streak' => $longest,
                'last_checkin_date' => $lastDate,
            ],
        );
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Carbon>  $dates  sorted ascending, distinct
     * @return array{0: int, 1: int, 2: ?Carbon} [longest streak, current streak, last date]
     */
    private function computeStreaks($dates): array
    {
        if ($dates->isEmpty()) {
            return [0, 0, null];
        }

        $longest = 1;
        $run = 1;

        for ($i = 1; $i < $dates->count(); $i++) {
            if ((int) round(abs($dates[$i]->diffInDays($dates[$i - 1]))) === 1) {
                $run++;
            } else {
                $run = 1;
            }
            $longest = max($longest, $run);
        }

        $lastDate = $dates->last();
        $today = Carbon::today();

        // Current streak only counts if the most recent checkin was today or yesterday;
        // otherwise the streak is broken (current = 0) even though longest is preserved.
        $current = 0;
        if ($lastDate->isSameDay($today) || $lastDate->isSameDay($today->copy()->subDay())) {
            $current = 1;
            for ($i = $dates->count() - 1; $i > 0; $i--) {
                if ((int) round(abs($dates[$i]->diffInDays($dates[$i - 1]))) === 1) {
                    $current++;
                } else {
                    break;
                }
            }
        }

        return [$longest, $current, $lastDate];
    }
}

<?php

namespace App\Livewire;

use App\Models\Sector;
use App\Models\Streak;
use Illuminate\Support\Carbon;

class Dashboard extends FullPageComponent
{
    public function render()
    {
        $userId = auth()->id();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        $streaks = Streak::where('user_id', $userId)->get()->keyBy('sector_id');

        $sectors = Sector::with(['roadmaps.phases.blocks'])
            ->orderBy('sort_order')
            ->get()
            ->map(function (Sector $sector) use ($streaks, $weekStart, $monthStart, $userId) {
                $roadmaps = $sector->roadmaps;
                $avgProgress = $roadmaps->isEmpty() ? 0 : (int) round($roadmaps->avg('progress_percent'));

                $nextBlock = $roadmaps
                    ->flatMap(fn ($r) => $r->phases)
                    ->sortBy('sort_order')
                    ->flatMap(fn ($p) => $p->blocks)
                    ->sortBy('sort_order')
                    ->first(fn ($b) => ! $b->is_complete);

                $minutesThisWeek = $sector->checkins()
                    ->where('user_id', $userId)
                    ->where('date', '>=', $weekStart)
                    ->sum('minutes_spent');

                $minutesThisMonth = $sector->checkins()
                    ->where('user_id', $userId)
                    ->where('date', '>=', $monthStart)
                    ->sum('minutes_spent');

                return [
                    'sector' => $sector,
                    'progress' => $avgProgress,
                    'roadmapCount' => $roadmaps->count(),
                    'nextBlock' => $nextBlock,
                    'minutesThisWeek' => $minutesThisWeek,
                    'minutesThisMonth' => $minutesThisMonth,
                    'streak' => $streaks->get($sector->id),
                ];
            });

        return view('livewire.dashboard', ['sectorCards' => $sectors]);
    }
}

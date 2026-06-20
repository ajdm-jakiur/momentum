<?php

namespace App\Livewire\Reports\Concerns;

use App\Models\Checkin;
use App\Models\Sector;
use App\Models\Streak;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;

trait BuildsCheckinReport
{
    /** @return array{days: CarbonPeriod, sectorRows: \Illuminate\Support\Collection, dailyTotals: array<string,int>, totalMinutes: int} */
    protected function buildReport(Carbon $start, Carbon $end): array
    {
        $userId = auth()->id();

        $checkins = Checkin::where('user_id', $userId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $days = CarbonPeriod::create($start, $end);

        $dailyTotals = [];
        foreach ($days as $day) {
            $dailyTotals[$day->toDateString()] = 0;
        }
        foreach ($checkins as $checkin) {
            $key = $checkin->date->toDateString();
            $dailyTotals[$key] = ($dailyTotals[$key] ?? 0) + $checkin->minutes_spent;
        }

        $byType = $checkins->groupBy('checkin_type')->map->count();

        $streaks = Streak::where('user_id', $userId)->get()->keyBy('sector_id');

        $sectorRows = Sector::orderBy('sort_order')->get()->map(function (Sector $sector) use ($checkins, $streaks) {
            $sectorCheckins = $checkins->where('sector_id', $sector->id);

            return [
                'sector' => $sector,
                'minutes' => $sectorCheckins->sum('minutes_spent'),
                'count' => $sectorCheckins->count(),
                'streak' => $streaks->get($sector->id),
            ];
        })->filter(fn ($row) => $row['count'] > 0)->values();

        return [
            'days' => $days,
            'sectorRows' => $sectorRows,
            'dailyTotals' => $dailyTotals,
            'totalMinutes' => $checkins->sum('minutes_spent'),
            'byType' => $byType,
        ];
    }
}

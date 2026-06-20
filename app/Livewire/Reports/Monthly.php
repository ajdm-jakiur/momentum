<?php

namespace App\Livewire\Reports;

use App\Livewire\FullPageComponent;
use App\Livewire\Reports\Concerns\BuildsCheckinReport;
use Illuminate\Support\Carbon;

class Monthly extends FullPageComponent
{
    use BuildsCheckinReport;

    public function render()
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        return view('livewire.reports.monthly', [
            'report' => $this->buildReport($start, $end),
            'rangeLabel' => $start->format('F Y'),
        ]);
    }
}

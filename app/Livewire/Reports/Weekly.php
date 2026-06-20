<?php

namespace App\Livewire\Reports;

use App\Livewire\FullPageComponent;
use App\Livewire\Reports\Concerns\BuildsCheckinReport;
use Illuminate\Support\Carbon;

class Weekly extends FullPageComponent
{
    use BuildsCheckinReport;

    public function render()
    {
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        return view('livewire.reports.weekly', [
            'report' => $this->buildReport($start, $end),
            'rangeLabel' => $start->format('M j').' – '.$end->format('M j'),
        ]);
    }
}

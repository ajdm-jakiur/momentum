<?php

namespace App\Livewire\Sectors;

use App\Livewire\FullPageComponent;
use App\Models\Sector;

class Index extends FullPageComponent
{
    public function render()
    {
        $sectors = Sector::with('roadmaps')->orderBy('sort_order')->get();

        return view('livewire.sectors.index', ['sectors' => $sectors]);
    }
}

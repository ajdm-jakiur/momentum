<?php

namespace App\Livewire\Roadmaps;

use App\Livewire\FullPageComponent;
use App\Models\Roadmap;

class Trash extends FullPageComponent
{
    public function restore(int $id): void
    {
        Roadmap::onlyTrashed()->findOrFail($id)->restore();
    }

    public function forceDelete(int $id): void
    {
        Roadmap::onlyTrashed()->findOrFail($id)->forceDelete();
    }

    public function render()
    {
        return view('livewire.roadmaps.trash', [
            'roadmaps' => Roadmap::onlyTrashed()->with('sector')->latest('deleted_at')->get(),
        ]);
    }
}

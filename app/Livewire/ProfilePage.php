<?php

namespace App\Livewire;

class ProfilePage extends FullPageComponent
{
    public function render()
    {
        $user = auth()->user()->load('referredUsers');
        return view('livewire.profile-page', compact('user'));
    }
}

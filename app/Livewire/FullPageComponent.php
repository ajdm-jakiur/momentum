<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Base for every route-level Livewire component. Breeze's layout lives at
 * resources/views/layouts/app.blade.php (not the components.layouts.app
 * path Livewire defaults to), so every full-page component needs this.
 */
#[Layout('layouts.app')]
abstract class FullPageComponent extends Component {}

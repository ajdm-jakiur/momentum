<?php

namespace App\Providers;

use App\Models\BlockItem;
use App\Models\BlockResource;
use App\Models\Checkin;
use App\Observers\BlockItemObserver;
use App\Observers\BlockResourceObserver;
use App\Observers\CheckinObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        BlockResource::observe(BlockResourceObserver::class);
        BlockItem::observe(BlockItemObserver::class);
        Checkin::observe(CheckinObserver::class);
    }
}

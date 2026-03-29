<?php

namespace App\Providers;
use Illuminate\Pagination\Paginator;

use App\Models\SchoolYear;
use Illuminate\Support\ServiceProvider;
use App\Observers\SchoolYearObserver;

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
        Paginator::useBootstrapFive();  

    }
}

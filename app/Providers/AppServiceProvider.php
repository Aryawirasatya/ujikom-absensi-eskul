<?php

namespace App\Providers;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;

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
         if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) 
        && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        
        URL::forceScheme('https');
    }
        Paginator::useBootstrapFive();  


    }
}

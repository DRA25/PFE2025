<?php

namespace App\Providers;

use App\Models\Dra;
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
        Dra::creating(function ($dra) {
            $dra->created_at = $dra->created_at ?: now();
        });


    }
}

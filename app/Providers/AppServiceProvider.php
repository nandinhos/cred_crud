<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Credential;
use App\Observers\CredentialObserver;

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
        Credential::observe(CredentialObserver::class);
    }
}

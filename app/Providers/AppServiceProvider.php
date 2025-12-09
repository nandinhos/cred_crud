<?php

namespace App\Providers;

use App\Models\Credential;
use App\Observers\CredentialObserver;
use App\Observers\CredentialValidityObserver;
use Illuminate\Support\Facades\Gate;
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
        Credential::observe(CredentialObserver::class);
        Credential::observe(CredentialValidityObserver::class);

        /**
         * Super Admin Bypass
         * Garante que super admin tenha acesso TOTAL e IRRESTRITO ao sistema
         * Bypassa todas as verificações de Gate/Policy
         */
        Gate::before(function ($user, $ability) {
            if ($user->hasAnyRole(['super_admin', 'Super Admin'])) {
                return true;
            }
        });
    }
}

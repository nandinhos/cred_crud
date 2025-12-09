<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Override explícito para forceDelete User - nunca pode deletar a si mesmo
        Gate::define('forceDelete', function ($user, $model) {
            if ($model instanceof User) {
                // NUNCA pode deletar a si mesmo - verificação prioritária
                if ($user->id === $model->id) {
                    return false;
                }

                // Deve ser super admin com permissão
                return $user->hasRole('super_admin') && $user->hasPermissionTo('Excluir Usuários');
            }

            // Para outros models, usar policy padrão
            return null;
        });
    }
}

<?php

namespace App\Policies;

use App\Models\Credential;
use App\Models\User;

class CredentialPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'view_credential',
            'view_any_credential',
            'view_credentials',
            'Visualizar Credenciais',
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Credential $credential): bool
    {
        return $user->hasAnyPermission([
            'view_credential',
            'view_credentials',
            'Visualizar Credenciais',
        ]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            'create_credential',
            'create_credentials',
            'Criar Credenciais',
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Credential $credential): bool
    {
        return $user->hasAnyPermission([
            'update_credential',
            'edit_credentials',
            'Editar Credenciais',
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Credential $credential): bool
    {
        return $user->hasAnyPermission([
            'delete_credential',
            'delete_credentials',
            'Excluir Credenciais',
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Credential $credential): bool
    {
        return $user->hasAnyPermission([
            'restore_credential',
            'update_credential',
            'edit_credentials',
            'Editar Credenciais',
        ]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Credential $credential): bool
    {
        return $user->hasAnyPermission([
            'force_delete_credential',
            'delete_credential',
            'delete_credentials',
            'Excluir Credenciais',
        ]) && $user->hasAnyRole(['super_admin', 'Super Admin']);
    }
}

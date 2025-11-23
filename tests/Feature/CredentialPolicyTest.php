<?php

use App\Models\Credential;
use App\Models\Role;
use App\Models\User;
use App\Policies\CredentialPolicy;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // Criar permissões
    $permissions = [
        'Visualizar Credenciais',
        'Criar Credenciais',
        'Editar Credenciais',
        'Excluir Credenciais',
    ];
    
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }
    
    // Criar roles e atribuir permissões
    $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $superAdmin->syncPermissions(Permission::all());
    
    $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $admin->syncPermissions([
        'Visualizar Credenciais',
        'Criar Credenciais',
        'Editar Credenciais',
        'Excluir Credenciais',
    ]);
    
    $operador = Role::firstOrCreate(['name' => 'operador', 'guard_name' => 'web']);
    $operador->syncPermissions([
        'Visualizar Credenciais',
        'Criar Credenciais',
        'Editar Credenciais',
    ]);
    
    $consulta = Role::firstOrCreate(['name' => 'consulta', 'guard_name' => 'web']);
    $consulta->syncPermissions([
        'Visualizar Credenciais',
    ]);
});

it('super_admin can view any credentials', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $policy = new CredentialPolicy();

    expect($policy->viewAny($user))->toBeTrue();
});

it('super_admin can view credential', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $credential = Credential::factory()->create();
    $policy = new CredentialPolicy();

    expect($policy->view($user, $credential))->toBeTrue();
});

it('super_admin can create credentials', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $policy = new CredentialPolicy();

    expect($policy->create($user))->toBeTrue();
});

it('super_admin can update credentials', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $credential = Credential::factory()->create();
    $policy = new CredentialPolicy();

    expect($policy->update($user, $credential))->toBeTrue();
});

it('super_admin can delete credentials', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $credential = Credential::factory()->create();
    $policy = new CredentialPolicy();

    expect($policy->delete($user, $credential))->toBeTrue();
});

it('super_admin can force delete credentials', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $credential = Credential::factory()->create();
    $policy = new CredentialPolicy();

    expect($policy->forceDelete($user, $credential))->toBeTrue();
});

it('admin can view and manage credentials', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $credential = Credential::factory()->create();
    $policy = new CredentialPolicy();

    expect($policy->viewAny($user))->toBeTrue();
    expect($policy->view($user, $credential))->toBeTrue();
    expect($policy->create($user))->toBeTrue();
    expect($policy->update($user, $credential))->toBeTrue();
    expect($policy->delete($user, $credential))->toBeTrue();
});

it('admin cannot force delete credentials', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $credential = Credential::factory()->create();
    $policy = new CredentialPolicy();

    expect($policy->forceDelete($user, $credential))->toBeFalse();
});

it('operador can view and edit credentials', function () {
    $user = User::factory()->create();
    $user->assignRole('operador');

    $credential = Credential::factory()->create();
    $policy = new CredentialPolicy();

    expect($policy->viewAny($user))->toBeTrue();
    expect($policy->view($user, $credential))->toBeTrue();
    expect($policy->create($user))->toBeTrue();
    expect($policy->update($user, $credential))->toBeTrue();
});

it('operador cannot delete credentials', function () {
    $user = User::factory()->create();
    $user->assignRole('operador');

    $credential = Credential::factory()->create();
    $policy = new CredentialPolicy();

    expect($policy->delete($user, $credential))->toBeFalse();
});

it('consulta can only view credentials', function () {
    $user = User::factory()->create();
    $user->assignRole('consulta');

    $credential = Credential::factory()->create();
    $policy = new CredentialPolicy();

    expect($policy->viewAny($user))->toBeTrue();
    expect($policy->view($user, $credential))->toBeTrue();
});

it('consulta cannot create, edit or delete credentials', function () {
    $user = User::factory()->create();
    $user->assignRole('consulta');

    $credential = Credential::factory()->create();
    $policy = new CredentialPolicy();

    expect($policy->create($user))->toBeFalse();
    expect($policy->update($user, $credential))->toBeFalse();
    expect($policy->delete($user, $credential))->toBeFalse();
});

it('user without role cannot access credentials', function () {
    $user = User::factory()->create();

    $credential = Credential::factory()->create();
    $policy = new CredentialPolicy();

    expect($policy->viewAny($user))->toBeFalse();
    expect($policy->view($user, $credential))->toBeFalse();
    expect($policy->create($user))->toBeFalse();
    expect($policy->update($user, $credential))->toBeFalse();
    expect($policy->delete($user, $credential))->toBeFalse();
});

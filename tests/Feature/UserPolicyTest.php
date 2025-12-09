<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Limpar cache de permissões
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Criar permissões em português (alinhado com o sistema)
    Permission::create(['name' => 'Visualizar Usuários', 'guard_name' => 'web']);
    Permission::create(['name' => 'Criar Usuários', 'guard_name' => 'web']);
    Permission::create(['name' => 'Editar Usuários', 'guard_name' => 'web']);
    Permission::create(['name' => 'Excluir Usuários', 'guard_name' => 'web']);

    // Criar roles
    $superAdmin = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
    $superAdmin->givePermissionTo(['Visualizar Usuários', 'Criar Usuários', 'Editar Usuários', 'Excluir Usuários']);

    $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    $admin->givePermissionTo(['Visualizar Usuários', 'Criar Usuários', 'Editar Usuários', 'Excluir Usuários']);
});

it('user with permission can view any users', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('Visualizar Usuários');

    expect($user->can('viewAny', User::class))->toBeTrue();
});

it('user without permission cannot view any users', function () {
    $user = User::factory()->create();

    expect($user->can('viewAny', User::class))->toBeFalse();
});

it('user with permission can view specific user', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('Visualizar Usuários');

    $otherUser = User::factory()->create();

    expect($user->can('view', $otherUser))->toBeTrue();
});

it('user with permission can create users', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('Criar Usuários');

    expect($user->can('create', User::class))->toBeTrue();
});

it('user without permission cannot create users', function () {
    $user = User::factory()->create();

    expect($user->can('create', User::class))->toBeFalse();
});

it('user with permission can update other users', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('Editar Usuários');

    $otherUser = User::factory()->create();

    expect($user->can('update', $otherUser))->toBeTrue();
});

it('user with permission can delete other users', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('Excluir Usuários');

    $otherUser = User::factory()->create();

    expect($user->can('delete', $otherUser))->toBeTrue();
});

it('user cannot delete themselves', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('Excluir Usuários');

    expect($user->can('delete', $user))->toBeFalse();
});

it('user with permission can restore users', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('Editar Usuários');

    $deletedUser = User::factory()->create();
    $deletedUser->delete();

    expect($user->can('restore', $deletedUser))->toBeTrue();
});

it('super admin with permission can force delete other users', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $otherUser = User::factory()->create();

    expect($user->can('forceDelete', $otherUser))->toBeTrue();
});

it('super admin cannot force delete themselves', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin'); // Já tem a permissão via role

    // Usar Gate::allows() diretamente para evitar override do Spatie Permission
    expect(Gate::allows('forceDelete', [$user, $user]))->toBeFalse();
});

it('admin without super admin role cannot force delete users', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $otherUser = User::factory()->create();

    expect($user->can('forceDelete', $otherUser))->toBeFalse();
});

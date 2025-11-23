<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Limpar cache de permissões
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Criar permissões
    Permission::create(['name' => 'view_users', 'guard_name' => 'web']);
    Permission::create(['name' => 'create_users', 'guard_name' => 'web']);
    Permission::create(['name' => 'edit_users', 'guard_name' => 'web']);
    Permission::create(['name' => 'delete_users', 'guard_name' => 'web']);

    // Criar roles
    $superAdmin = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
    $superAdmin->givePermissionTo(['view_users', 'create_users', 'edit_users', 'delete_users']);

    $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    $admin->givePermissionTo(['view_users', 'create_users', 'edit_users', 'delete_users']);
});

it('user with permission can view any users', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view_users');

    expect($user->can('viewAny', User::class))->toBeTrue();
});

it('user without permission cannot view any users', function () {
    $user = User::factory()->create();

    expect($user->can('viewAny', User::class))->toBeFalse();
});

it('user with permission can view specific user', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view_users');

    $otherUser = User::factory()->create();

    expect($user->can('view', $otherUser))->toBeTrue();
});

it('user with permission can create users', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('create_users');

    expect($user->can('create', User::class))->toBeTrue();
});

it('user without permission cannot create users', function () {
    $user = User::factory()->create();

    expect($user->can('create', User::class))->toBeFalse();
});

it('user with permission can update other users', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('edit_users');

    $otherUser = User::factory()->create();

    expect($user->can('update', $otherUser))->toBeTrue();
});

it('user with permission can delete other users', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('delete_users');

    $otherUser = User::factory()->create();

    expect($user->can('delete', $otherUser))->toBeTrue();
});

it('user cannot delete themselves', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('delete_users');

    expect($user->can('delete', $user))->toBeFalse();
});

it('user with permission can restore users', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('edit_users');

    $deletedUser = User::factory()->create();
    $deletedUser->delete();

    expect($user->can('restore', $deletedUser))->toBeTrue();
});

it('super admin with permission can force delete other users', function () {
    $user = User::factory()->create();
    $user->assignRole('Super Admin');

    $otherUser = User::factory()->create();

    expect($user->can('forceDelete', $otherUser))->toBeTrue();
});

it('super admin cannot force delete themselves', function () {
    $user = User::factory()->create();
    $user->assignRole('Super Admin');

    expect($user->can('forceDelete', $user))->toBeFalse();
});

it('admin without super admin role cannot force delete users', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $otherUser = User::factory()->create();

    expect($user->can('forceDelete', $otherUser))->toBeFalse();
});

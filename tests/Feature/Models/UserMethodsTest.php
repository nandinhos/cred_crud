<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    Role::create(['name' => 'consulta', 'guard_name' => 'web']);
});

it('super admin email can access panel without role', function () {
    config(['auth.super_admin_email' => 'super@example.com']);

    $user = User::factory()->create(['email' => 'super@example.com']);

    $panel = Mockery::mock(\Filament\Panel::class);

    expect($user->canAccessPanel($panel))->toBeTrue();
});

it('user with super_admin role can access panel', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $panel = Mockery::mock(\Filament\Panel::class);

    expect($user->canAccessPanel($panel))->toBeTrue();
});

it('user with admin role can access panel', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $panel = Mockery::mock(\Filament\Panel::class);

    expect($user->canAccessPanel($panel))->toBeTrue();
});

it('user with consulta role can access panel', function () {
    $user = User::factory()->create();
    $user->assignRole('consulta');

    $panel = Mockery::mock(\Filament\Panel::class);

    expect($user->canAccessPanel($panel))->toBeTrue();
});

it('user without role cannot access panel', function () {
    $user = User::factory()->create();

    $panel = Mockery::mock(\Filament\Panel::class);

    expect($user->canAccessPanel($panel))->toBeFalse();
});

it('super_admin is admin', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($user->isAdmin())->toBeTrue();
});

it('admin is admin', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    expect($user->isAdmin())->toBeTrue();
});

it('consulta is not admin', function () {
    $user = User::factory()->create();
    $user->assignRole('consulta');

    expect($user->isAdmin())->toBeFalse();
});

it('consulta without admin role is consulta', function () {
    $user = User::factory()->create();
    $user->assignRole('consulta');

    expect($user->isConsulta())->toBeTrue();
});

it('admin with consulta role is not consulta', function () {
    $user = User::factory()->create();
    $user->assignRole(['admin', 'consulta']);

    expect($user->isConsulta())->toBeFalse();
});

it('user without consulta role is not consulta', function () {
    $user = User::factory()->create();

    expect($user->isConsulta())->toBeFalse();
});

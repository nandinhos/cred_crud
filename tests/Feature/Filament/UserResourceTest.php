<?php

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    // Limpar cache de permissões
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Criar permissões necessárias
    $permissions = [
        'Visualizar Credenciais',
        'Criar Credenciais',
        'Editar Credenciais',
        'Excluir Credenciais',
        'Visualizar Usuários',
        'Criar Usuários',
        'Editar Usuários',
        'Excluir Usuários',
        'Visualizar Logs',
        'Exportar Relatórios',
        'Gerenciar Permissões',
    ];

    foreach ($permissions as $permission) {
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    // Criar roles e atribuir permissões
    $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $superAdmin->syncPermissions($permissions);

    $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $admin->syncPermissions([
        'Visualizar Credenciais',
        'Criar Credenciais',
        'Editar Credenciais',
        'Excluir Credenciais',
        'Visualizar Usuários',
        'Criar Usuários',
        'Editar Usuários',
        'Visualizar Logs',
        'Exportar Relatórios',
    ]);

    $operador = Role::firstOrCreate(['name' => 'operador', 'guard_name' => 'web']);
    $operador->syncPermissions([
        'Visualizar Credenciais',
        'Criar Credenciais',
        'Editar Credenciais',
        'Visualizar Logs',
    ]);

    $consulta = Role::firstOrCreate(['name' => 'consulta', 'guard_name' => 'web']);
    $consulta->syncPermissions([
        'Visualizar Credenciais',
        'Visualizar Logs',
    ]);
});

it('can list users', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    
    $users = User::factory()->count(5)->create();

    $this->actingAs($admin);

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords($users);
});

it('can create user with role', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $adminRole = Role::where('name', 'admin')->first();

    $this->actingAs($admin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Novo Usuario',
            'full_name' => 'Novo Usuario Completo',
            'email' => 'novo@test.com',
            'password' => 'password123',
            'roles' => [$adminRole->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'novo@test.com')->first();
    
    expect($user)->not->toBeNull();
    expect($user->hasRole('admin'))->toBeTrue();
});

it('can create user with multiple roles', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $adminRole = Role::where('name', 'admin')->first();
    $operadorRole = Role::where('name', 'operador')->first();

    $this->actingAs($admin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'MultiRole',
            'full_name' => 'Multi Role User',
            'email' => 'multirole@test.com',
            'password' => 'password123',
            'roles' => [$adminRole->id, $operadorRole->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'multirole@test.com')->first();
    
    expect($user->hasRole('admin'))->toBeTrue();
    expect($user->hasRole('operador'))->toBeTrue();
});

it('can edit user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    
    $user = User::factory()->create(['name' => 'Original Name']);

    $this->actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->refresh()->name)->toBe('Updated Name');
});

it('can assign role to existing user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    
    $user = User::factory()->create();
    $consultaRole = Role::where('name', 'consulta')->first();

    $this->actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'roles' => [$consultaRole->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->refresh()->hasRole('consulta'))->toBeTrue();
});

it('can change user role', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    
    $user = User::factory()->create();
    $user->assignRole('consulta');
    
    $adminRole = Role::where('name', 'admin')->first();

    $this->actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'roles' => [$adminRole->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();
    
    expect($user->hasRole('admin'))->toBeTrue();
    expect($user->hasRole('consulta'))->toBeFalse();
});

it('validates unique email on create', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    
    User::factory()->create(['email' => 'existing@test.com']);

    $this->actingAs($admin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Test',
            'full_name' => 'Test User',
            'email' => 'existing@test.com',
            'password' => 'password123',
        ])
        ->call('create')
        ->assertHasFormErrors(['email']);
});

it('can search users by name', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    
    $user = User::factory()->create(['name' => 'SearchableUser']);
    $other = User::factory()->create(['name' => 'OtherUser']);

    $this->actingAs($admin);

    Livewire::test(ListUsers::class)
        ->searchTable('SearchableUser')
        ->assertCanSeeTableRecords([$user])
        ->assertCanNotSeeTableRecords([$other]);
});

it('can search users by email', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    
    $user = User::factory()->create(['email' => 'searchable@test.com']);
    $other = User::factory()->create(['email' => 'other@test.com']);

    $this->actingAs($admin);

    Livewire::test(ListUsers::class)
        ->searchTable('searchable@test.com')
        ->assertCanSeeTableRecords([$user])
        ->assertCanNotSeeTableRecords([$other]);
});

it('can filter users by role', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    
    $adminUser = User::factory()->create();
    $adminUser->assignRole('admin');
    
    $consultaUser = User::factory()->create();
    $consultaUser->assignRole('consulta');

    $this->actingAs($admin);

    $adminRole = Role::where('name', 'admin')->first();

    Livewire::test(ListUsers::class)
        ->filterTable('roles', $adminRole->id)
        ->assertCanSeeTableRecords([$adminUser])
        ->assertCanNotSeeTableRecords([$consultaUser]);
});

it('displays user roles in table', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    
    $user = User::factory()->create();
    $user->assignRole('operador');

    $this->actingAs($admin);

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords([$user]);
});

it('only super_admin and admin can create users', function () {
    $consulta = User::factory()->create();
    $consulta->assignRole('consulta');

    $this->actingAs($consulta);

    expect(\App\Filament\Resources\UserResource::canCreate())->toBeFalse();
});

it('only super_admin and admin can edit users', function () {
    $consulta = User::factory()->create();
    $consulta->assignRole('consulta');
    
    $otherUser = User::factory()->create();

    expect(\App\Filament\Resources\UserResource::canEdit($otherUser))->toBeFalse();
});

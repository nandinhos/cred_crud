<?php

use App\Models\Role;
use App\Models\User;

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

test('usuário admin pode acessar todas as funcionalidades de credenciais', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    // Verificar métodos de autorização
    expect($user->hasRole('admin'))->toBeTrue();
    expect($user->isAdmin())->toBeTrue();
    expect($user->isConsulta())->toBeFalse();
});

test('usuário super_admin pode acessar todas as funcionalidades de credenciais', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    // Verificar métodos de autorização
    expect($user->hasRole('super_admin'))->toBeTrue();
    expect($user->isAdmin())->toBeTrue();
    expect($user->isConsulta())->toBeFalse();
});

test('usuário consulta não pode criar, editar ou deletar credenciais', function () {
    $user = User::factory()->create();
    $user->assignRole('consulta');

    // Verificar métodos de autorização
    expect($user->hasRole('consulta'))->toBeTrue();
    expect($user->isConsulta())->toBeTrue();
    expect($user->isAdmin())->toBeFalse();
});

test('usuário não autenticado não pode acessar sistema', function () {
    $response = $this->get('/admin');
    $response->assertRedirect('/admin/login');
});

test('roles são criados corretamente pelos seeders', function () {
    $this->artisan('db:seed', ['--class' => 'RolesSeeder']);

    expect(Role::where('name', 'admin')->exists())->toBeTrue();
    expect(Role::where('name', 'super_admin')->exists())->toBeTrue();
    expect(Role::where('name', 'consulta')->exists())->toBeTrue();
    expect(Role::where('name', 'operador')->exists())->toBeTrue();
});

test('usuário admin principal tem role super_admin', function () {
    // Criar usuário admin principal usando factory
    $adminUser = User::factory()->create([
        'email' => 'admin@admin.com',
    ]);

    $this->artisan('db:seed', ['--class' => 'RolesSeeder']);

    $adminUser->refresh();
    expect($adminUser->hasRole('super_admin'))->toBeTrue();
});

test('usuário consulta pode acessar painel mas com permissões limitadas', function () {
    $user = User::factory()->create();
    $user->assignRole('consulta');

    $this->actingAs($user);

    // Verificar se pode acessar o painel
    $panel = app(\Filament\Panel::class);
    expect($user->canAccessPanel($panel))->toBeTrue();

    // Mas não pode editar/criar/deletar no CredentialResource
    expect(\App\Filament\Resources\Credentials\CredentialResource::canCreate())->toBeFalse();
});

test('middleware check role funciona corretamente', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole('admin');

    $consultaUser = User::factory()->create();
    $consultaUser->assignRole('consulta');

    // Testar com usuário admin (deve passar)
    $request = new \Illuminate\Http\Request;
    $request->setUserResolver(fn () => $adminUser);

    $middleware = new \App\Http\Middleware\CheckRole;

    $response = $middleware->handle($request, function ($req) {
        return response('OK');
    }, 'admin');

    expect($response->getContent())->toBe('OK');

    // Testar com usuário consulta tentando acessar área admin (deve falhar)
    $request->setUserResolver(fn () => $consultaUser);

    $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

    $middleware->handle($request, function ($req) {
        return response('OK');
    }, 'admin');
});

test('super_admin pode acessar painel administrativo', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    // Verificar se pode acessar o painel
    $panel = app(\Filament\Panel::class);
    expect($user->canAccessPanel($panel))->toBeTrue();

    // Verificar role
    expect($user->hasRole('super_admin'))->toBeTrue();
});

test('operador tem permissões limitadas para credenciais', function () {
    $user = User::factory()->create();
    $user->assignRole('operador');

    // Operador pode visualizar e editar credenciais
    expect($user->hasRole('operador'))->toBeTrue();
    expect($user->isAdmin())->toBeFalse();
    expect($user->isConsulta())->toBeFalse();
});

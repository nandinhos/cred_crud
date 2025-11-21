<?php

use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    // Criar roles para os testes
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'consulta', 'guard_name' => 'web']);
});

test('usuário admin pode acessar todas as funcionalidades de credenciais', function () {
    $user = User::create([
        'name' => 'Admin',
        'full_name' => 'Admin User Test',
        'email' => 'admin.test@example.com',
        'password' => 'password123',
    ]);
    $user->assignRole('admin');

    // Verificar métodos de autorização
    expect($user->hasRole('admin'))->toBeTrue();
    expect($user->isAdmin())->toBeTrue();
    expect($user->isConsulta())->toBeFalse();
});

test('usuário super_admin pode acessar todas as funcionalidades de credenciais', function () {
    $user = User::create([
        'name' => 'SuperAdmin',
        'full_name' => 'Super Admin User Test',
        'email' => 'superadmin.test@example.com',
        'password' => 'password123',
    ]);
    $user->assignRole('super_admin');

    // Verificar métodos de autorização
    expect($user->hasRole('super_admin'))->toBeTrue();
    expect($user->isAdmin())->toBeTrue();
    expect($user->isConsulta())->toBeFalse();
});

test('usuário consulta não pode criar, editar ou deletar credenciais', function () {
    $user = User::create([
        'name' => 'Consulta',
        'full_name' => 'Consulta User Test',
        'email' => 'consulta.test@example.com',
        'password' => 'password123',
    ]);
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
});

test('usuário admin principal tem role super_admin', function () {
    // Criar usuário admin principal
    $adminUser = User::create([
        'name' => 'Admin',
        'full_name' => 'Admin Principal Test',
        'email' => 'admin@admin.com',
        'password' => 'password123',
    ]);

    $this->artisan('db:seed', ['--class' => 'RolesSeeder']);

    $adminUser->refresh();
    expect($adminUser->hasRole('super_admin'))->toBeTrue();
});

test('usuário consulta pode acessar painel mas com permissões limitadas', function () {
    $user = User::create([
        'name' => 'Consulta',
        'full_name' => 'Consulta Panel Test',
        'email' => 'consulta.panel@example.com',
        'password' => 'password123',
    ]);
    $user->assignRole('consulta');

    // Verificar se pode acessar o painel
    $panel = app(\Filament\Panel::class);
    expect($user->canAccessPanel($panel))->toBeTrue();

    // Mas não pode editar/criar/deletar no CredentialResource
    expect(\App\Filament\Resources\Credentials\CredentialResource::canCreate())->toBeFalse();
});

test('middleware check role funciona corretamente', function () {
    $adminUser = User::create([
        'name' => 'Admin',
        'full_name' => 'Admin Test Middleware',
        'email' => 'admin.middleware@example.com',
        'password' => 'password123',
    ]);
    $adminUser->assignRole('admin');

    $consultaUser = User::create([
        'name' => 'Consulta',
        'full_name' => 'Consulta Test Middleware',
        'email' => 'consulta.middleware@example.com',
        'password' => 'password123',
    ]);
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

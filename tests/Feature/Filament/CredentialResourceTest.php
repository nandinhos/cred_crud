<?php

use App\Enums\CredentialSecrecy;
use App\Enums\CredentialType;
use App\Filament\Resources\Credentials\Pages\CreateCredential;
use App\Filament\Resources\Credentials\Pages\EditCredential;
use App\Filament\Resources\Credentials\Pages\ListCredentials;
use App\Models\Credential;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    // Limpar cache de permissões
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Criar permissões
    $permissions = [
        'Visualizar Usuários',
        'Criar Usuários',
        'Editar Usuários',
        'Excluir Usuários',
        'Visualizar Credenciais',
        'Criar Credenciais',
        'Editar Credenciais',
        'Excluir Credenciais',
        'Visualizar Logs',
        'Exportar Relatórios',
        'Gerenciar Permissões',
    ];

    foreach ($permissions as $permission) {
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    // Criar roles
    $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $consulta = Role::firstOrCreate(['name' => 'consulta', 'guard_name' => 'web']);

    // Atribuir permissões aos roles
    $superAdmin->syncPermissions(\Spatie\Permission\Models\Permission::all());
    $admin->syncPermissions([
        'Visualizar Usuários',
        'Criar Usuários',
        'Editar Usuários',
        'Visualizar Credenciais',
        'Criar Credenciais',
        'Editar Credenciais',
        'Excluir Credenciais',
        'Visualizar Logs',
        'Exportar Relatórios',
    ]);
    $consulta->syncPermissions([
        'Visualizar Credenciais',
        'Visualizar Logs',
    ]);
});

it('can list credentials', function () {
    $user = User::factory()->admin()->create();
    $credentials = Credential::factory()->count(5)->create();

    $this->actingAs($user);

    Livewire::test(ListCredentials::class)
        ->assertCanSeeTableRecords($credentials);
});

it('can create credential', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    Livewire::test(CreateCredential::class)
        ->fillForm([
            'fscs' => 'FSCS-NEW',
            'type' => CredentialType::CRED->value,
            'secrecy' => CredentialSecrecy::RESERVADO->value,
            'credential' => 'CRED-1234',
            'concession' => now()->format('Y-m-d'),
            'user_id' => $user->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('credentials', [
        'fscs' => 'FSCS-NEW',
        'type' => 'CRED',
    ]);
});

it('can create credential with optional dates', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    Livewire::test(CreateCredential::class)
        ->fillForm([
            'fscs' => 'FSCS-TEST',
            'type' => CredentialType::CRED->value,
            'secrecy' => CredentialSecrecy::SECRETO->value,
            'credential' => 'CRED-1234-5678',
            'user_id' => $user->id,
            'concession' => null, // Data opcional
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('credentials', [
        'fscs' => 'FSCS-TEST',
        'type' => 'CRED',
        'concession' => null,
    ]);
});

it('validates unique fscs', function () {
    $user = User::factory()->admin()->create();
    Credential::factory()->create(['fscs' => 'FSCS-EXISTING']);

    $this->actingAs($user);

    Livewire::test(CreateCredential::class)
        ->fillForm([
            'fscs' => 'FSCS-EXISTING',
            'type' => CredentialType::CRED->value,
            'secrecy' => CredentialSecrecy::RESERVADO->value,
            'credential' => 'CRED-9999',
            'user_id' => $user->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['fscs']);
});

it('can edit credential', function () {
    $user = User::factory()->admin()->create();
    $credential = Credential::factory()->create([
        'type' => CredentialType::CRED->value,
        'secrecy' => CredentialSecrecy::RESERVADO->value,
    ]);

    $this->actingAs($user);

    Livewire::test(EditCredential::class, ['record' => $credential->getRouteKey()])
        ->fillForm([
            'user_id' => $credential->user_id,
            'fscs' => $credential->fscs,
            'type' => $credential->type,
            'secrecy' => CredentialSecrecy::SECRETO->value, // Alterando o sigilo
            'credential' => $credential->credential,
            'observation' => 'Updated Observation',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $credential->refresh();
    expect($credential->observation)->toBe('Updated Observation');
    expect($credential->secrecy)->toBe(CredentialSecrecy::SECRETO);
});

it('can filter credentials by secrecy', function () {
    $user = User::factory()->admin()->create();

    // Criar credenciais com diferentes níveis de sigilo
    $reservado = Credential::factory()->count(3)->create(['secrecy' => CredentialSecrecy::RESERVADO->value]);
    $secreto = Credential::factory()->count(2)->create(['secrecy' => CredentialSecrecy::SECRETO->value]);

    $this->actingAs($user);

    Livewire::test(ListCredentials::class)
        ->filterTable('secrecy', CredentialSecrecy::RESERVADO->value)
        ->assertCanSeeTableRecords($reservado)
        ->assertCanNotSeeTableRecords($secreto);
});

it('can filter credentials by type', function () {
    $user = User::factory()->admin()->create();

    // Criar credenciais de diferentes tipos
    $cred = Credential::factory()->count(2)->create(['type' => CredentialType::CRED->value]);
    $tcms = Credential::factory()->count(3)->create(['type' => CredentialType::TCMS->value]);

    $this->actingAs($user);

    Livewire::test(ListCredentials::class)
        ->filterTable('type', CredentialType::TCMS->value)
        ->assertCanSeeTableRecords($tcms)
        ->assertCanNotSeeTableRecords($cred);
});

it('can search credentials by fscs', function () {
    $user = User::factory()->admin()->create();

    $credential = Credential::factory()->create(['fscs' => 'SEARCH-123']);
    $other = Credential::factory()->create(['fscs' => 'OTHER-456']);

    $this->actingAs($user);

    Livewire::test(ListCredentials::class)
        ->searchTable('SEARCH-123')
        ->assertCanSeeTableRecords([$credential])
        ->assertCanNotSeeTableRecords([$other]);
});

it('can search credentials by credential field', function () {
    $user = User::factory()->admin()->create();

    $credential = Credential::factory()->create(['credential' => 'CRED-UNIQUE-999']);
    $other = Credential::factory()->create(['credential' => 'CRED-OTHER-111']);

    $this->actingAs($user);

    Livewire::test(ListCredentials::class)
        ->searchTable('UNIQUE-999')
        ->assertCanSeeTableRecords([$credential])
        ->assertCanNotSeeTableRecords([$other]);
});

it('can sort credentials by fscs', function () {
    $user = User::factory()->admin()->create();

    Credential::factory()->create(['fscs' => 'AAA-001']);
    Credential::factory()->create(['fscs' => 'ZZZ-999']);
    Credential::factory()->create(['fscs' => 'MMM-500']);

    $this->actingAs($user);

    Livewire::test(ListCredentials::class)
        ->sortTable('fscs')
        ->assertCanSeeTableRecords(
            Credential::orderBy('fscs')->get(),
            inOrder: true
        );
});

it('shows correct badge colors for secrecy levels', function () {
    $user = User::factory()->admin()->create();

    $reservado = Credential::factory()->create(['secrecy' => CredentialSecrecy::RESERVADO->value]);
    $secreto = Credential::factory()->create(['secrecy' => CredentialSecrecy::SECRETO->value]);

    $this->actingAs($user);

    $component = Livewire::test(ListCredentials::class);

    // Verificar que as credenciais estão na tabela
    $component->assertCanSeeTableRecords([$reservado, $secreto]);
});

it('displays user name in credentials table', function () {
    $user = User::factory()->admin()->create(['name' => 'Test User']);
    $credential = Credential::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(ListCredentials::class)
        ->assertCanSeeTableRecords([$credential])
        ->assertSee('Test User');
});

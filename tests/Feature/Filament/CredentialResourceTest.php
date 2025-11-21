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
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'consulta', 'guard_name' => 'web']);
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
    $credential = Credential::factory()->create();

    $this->actingAs($user);

    Livewire::test(EditCredential::class, ['record' => $credential->getRouteKey()])
        ->fillForm([
            'observation' => 'Updated Observation',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($credential->refresh()->observation)->toBe('Updated Observation');
});

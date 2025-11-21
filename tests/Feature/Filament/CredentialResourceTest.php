<?php

use App\Filament\Resources\Credentials\CredentialResource;
use App\Filament\Resources\Credentials\Pages\CreateCredential;
use App\Filament\Resources\Credentials\Pages\EditCredential;
use App\Filament\Resources\Credentials\Pages\ListCredentials;
use App\Models\Credential;
use App\Models\User;
use App\Models\Role;
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
            'name' => 'New Credential',
            'secrecy' => 'R',
            'credential' => 'secret123',
            'concession' => now()->format('Y-m-d'),
            'validity' => now()->addYear()->format('Y-m-d'),
            'user_id' => $user->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('credentials', [
        'fscs' => 'FSCS-NEW',
        'name' => 'New Credential',
    ]);
});

it('validates validity date must be in future', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    Livewire::test(CreateCredential::class)
        ->fillForm([
            'fscs' => 'FSCS-TEST',
            'name' => 'Test Credential',
            'secrecy' => 'R',
            'validity' => now()->subDay()->format('Y-m-d'), // Past date
        ])
        ->call('create')
        ->assertHasFormErrors(['validity']);
});

it('validates unique fscs', function () {
    $user = User::factory()->admin()->create();
    Credential::factory()->create(['fscs' => 'FSCS-EXISTING']);

    $this->actingAs($user);

    Livewire::test(CreateCredential::class)
        ->fillForm([
            'fscs' => 'FSCS-EXISTING',
            'name' => 'Duplicate FSCS',
        ])
        ->call('create')
        ->assertHasFormErrors(['fscs']);
});

it('allows duplicate fscs if soft deleted', function () {
    $user = User::factory()->admin()->create();
    $credential = Credential::factory()->create(['fscs' => 'FSCS-DELETED']);
    $credential->delete();

    $this->actingAs($user);

    Livewire::test(CreateCredential::class)
        ->fillForm([
            'fscs' => 'FSCS-DELETED',
            'name' => 'New Credential',
            'secrecy' => 'S',
            'validity' => now()->addYear()->format('Y-m-d'),
            'user_id' => $user->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

it('can edit credential', function () {
    $user = User::factory()->admin()->create();
    $credential = Credential::factory()->create();

    $this->actingAs($user);

    Livewire::test(EditCredential::class, ['record' => $credential->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($credential->refresh()->name)->toBe('Updated Name');
});

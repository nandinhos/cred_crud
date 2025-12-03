<?php

use App\Filament\Pages\Dashboard;
use App\Models\Credential;
use App\Models\Office;
use App\Models\Rank;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    $permissions = ['Visualizar Credenciais'];
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $admin->syncPermissions(Permission::all());
});

it('can render dashboard page', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->assertSuccessful();
});

it('loads user with relationships on mount', function () {
    $office = Office::create(['office' => 'CINDACTA I', 'description' => 'Centro']);
    $rank = Rank::create(['abbreviation' => 'Cel', 'name' => 'Coronel', 'armed_force' => 'FAB', 'hierarchy_order' => 10]);

    $user = User::factory()->create([
        'office_id' => $office->id,
        'rank_id' => $rank->id,
    ]);
    $user->assignRole('admin');

    Credential::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $component = Livewire::test(Dashboard::class);

    expect($component->user->relationLoaded('rank'))->toBeTrue();
    expect($component->user->relationLoaded('office'))->toBeTrue();
    expect($component->user->relationLoaded('credentials'))->toBeTrue();
});

it('returns correct navigation icon', function () {
    expect(Dashboard::getNavigationIcon())->toBe('heroicon-o-home');
});

it('returns correct navigation sort', function () {
    expect(Dashboard::getNavigationSort())->toBe(-2);
});

it('returns correct navigation label', function () {
    expect(Dashboard::getNavigationLabel())->toBe('Dashboard');
});

it('page has correct title', function () {
    $dashboard = new Dashboard;

    expect($dashboard->getTitle())->toBe('Dashboard');
});

it('page has correct heading', function () {
    $dashboard = new Dashboard;

    expect($dashboard->getHeading())->toBe('Minha Credencial');
});

it('returns most recent active credential', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    // Credencial antiga
    // Credencial antiga
    $oldCredential = Credential::factory()->create([
        'user_id' => $user->id,
        'concession' => now()->subYears(2),
    ]);
    $oldCredential->delete();

    // Credencial mais recente
    $recentCredential = Credential::factory()->create([
        'user_id' => $user->id,
        'concession' => now()->subMonth(),
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Dashboard::class);

    expect($component->activeCredential->id)->toBe($recentCredential->id);
});

it('returns null when user has no credentials with concession', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    // Credencial sem concessão
    Credential::factory()->create([
        'user_id' => $user->id,
        'concession' => null,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Dashboard::class);

    expect($component->activeCredential)->toBeNull();
});

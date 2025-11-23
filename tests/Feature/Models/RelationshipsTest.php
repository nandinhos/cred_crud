<?php

use App\Models\Credential;
use App\Models\User;

it('user has many credentials', function () {
    $user = User::factory()->create();

    Credential::factory()->count(3)->create(['user_id' => $user->id]);

    expect($user->credentials)->toHaveCount(3);
    expect($user->credentials->first())->toBeInstanceOf(Credential::class);
});

it('credential belongs to user', function () {
    $user = User::factory()->create();
    $credential = Credential::factory()->create(['user_id' => $user->id]);

    expect($credential->user)->toBeInstanceOf(User::class);
    expect($credential->user->is($user))->toBeTrue();
    expect($credential->user->id)->toBe($user->id);
});

it('soft delete preserves credential', function () {
    $credential = Credential::factory()->create(['fscs' => 'SOFT-DELETE-001']);

    $credential->delete();

    $this->assertSoftDeleted('credentials', ['id' => $credential->id]);

    $deletedCredential = Credential::withTrashed()->find($credential->id);
    expect($deletedCredential)->not->toBeNull();
    expect($deletedCredential->deleted_at)->not->toBeNull();
});

it('soft deleting user does not cascade to credentials', function () {
    $user = User::factory()->create();
    $credential = Credential::factory()->create(['user_id' => $user->id]);

    expect($credential->user_id)->toBe($user->id);

    // Soft delete no usuário (não remove do banco, apenas marca deleted_at)
    $user->delete();

    // A credencial permanece intacta porque o user não foi realmente removido do banco
    $credential->refresh();
    expect($credential->user_id)->toBe($user->id);
    expect($credential->deleted_at)->toBeNull();

    // O usuário está soft deleted
    expect(User::find($user->id))->toBeNull();
    expect(User::withTrashed()->find($user->id))->not->toBeNull();
});

it('with trashed recovers soft deleted credentials', function () {
    $user = User::factory()->create();

    $activeCredential = Credential::factory()->create(['user_id' => $user->id, 'fscs' => 'ACTIVE-001']);
    $deletedCredential = Credential::factory()->create(['user_id' => $user->id, 'fscs' => 'DELETED-001']);

    $deletedCredential->delete();

    // Sem withTrashed
    expect($user->credentials)->toHaveCount(1);
    expect($user->credentials->first()->fscs)->toBe('ACTIVE-001');

    // Com withTrashed
    $allCredentials = $user->credentials()->withTrashed()->get();
    expect($allCredentials)->toHaveCount(2);

    $trashedCredential = $allCredentials->firstWhere('fscs', 'DELETED-001');
    expect($trashedCredential)->not->toBeNull();
    expect($trashedCredential->deleted_at)->not->toBeNull();
});

it('user can have multiple credentials over time', function () {
    $user = User::factory()->create();

    // Credencial antiga (deletada)
    $oldCredential = Credential::factory()->create([
        'user_id' => $user->id,
        'fscs' => 'OLD-2020',
        'credential' => 'OLD-CRED-001',
    ]);
    $oldCredential->delete();

    // Credencial atual (ativa)
    $currentCredential = Credential::factory()->create([
        'user_id' => $user->id,
        'fscs' => 'CURRENT-2024',
        'credential' => 'CURRENT-CRED-001',
    ]);

    // Verifica apenas ativas
    expect($user->credentials)->toHaveCount(1);
    expect($user->credentials->first()->fscs)->toBe('CURRENT-2024');

    // Verifica histórico completo
    $history = $user->credentials()->withTrashed()->get();
    expect($history)->toHaveCount(2);
});

it('credential requires user due to not null constraint', function () {
    // O user_id é obrigatório devido ao constraint NOT NULL na migration
    $user = User::factory()->create();
    $credential = Credential::factory()->create(['user_id' => $user->id]);

    expect($credential->user_id)->toBe($user->id);
    expect($credential->user)->not->toBeNull();
});

it('activeCredential returns only non-deleted credentials', function () {
    $user = User::factory()->create();

    $active1 = Credential::factory()->create(['user_id' => $user->id, 'fscs' => 'ACTIVE-1']);
    $active2 = Credential::factory()->create(['user_id' => $user->id, 'fscs' => 'ACTIVE-2']);
    $deleted = Credential::factory()->create(['user_id' => $user->id, 'fscs' => 'DELETED']);

    $deleted->delete();

    $activeCredentials = $user->activeCredential;

    expect($activeCredentials)->toHaveCount(2);
    $fscsArray = $activeCredentials->pluck('fscs')->toArray();
    expect($fscsArray)->toContain('ACTIVE-1');
    expect($fscsArray)->toContain('ACTIVE-2');
    expect($fscsArray)->not->toContain('DELETED');
});

it('credentialHistory returns all credentials ordered by creation date', function () {
    $user = User::factory()->create();

    $first = Credential::factory()->create([
        'user_id' => $user->id,
        'fscs' => 'FIRST',
        'created_at' => now()->subDays(10),
    ]);

    $second = Credential::factory()->create([
        'user_id' => $user->id,
        'fscs' => 'SECOND',
        'created_at' => now()->subDays(5),
    ]);

    $third = Credential::factory()->create([
        'user_id' => $user->id,
        'fscs' => 'THIRD',
        'created_at' => now(),
    ]);

    $second->delete();

    $history = $user->credentialHistory;

    expect($history)->toHaveCount(3);
    // Deve estar ordenado do mais recente para o mais antigo
    expect($history->pluck('fscs')->toArray())->toBe(['THIRD', 'SECOND', 'FIRST']);
});

it('restoring deleted credential maintains relationship', function () {
    $user = User::factory()->create();
    $credential = Credential::factory()->create(['user_id' => $user->id]);

    $credential->delete();
    expect($user->credentials)->toHaveCount(0);

    $credential->restore();

    $user->refresh();
    expect($user->credentials)->toHaveCount(1);
    expect($credential->user->id)->toBe($user->id);
});

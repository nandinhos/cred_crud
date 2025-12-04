<?php

use App\Enums\CredentialType;
use App\Models\Credential;
use App\Models\User;

it('credential with fscs 00000 has status Negada', function () {
    $credential = Credential::factory()->create(['fscs' => '00000']);

    expect($credential->status)->toBe('Negada');
});

it('credential with expired validity has status Vencida', function () {
    $credential = Credential::factory()->create([
        'fscs' => 'VALID-001',
        'type' => CredentialType::CRED,
        'concession' => now()->subYears(3),
    ]);

    // A validade será calculada automaticamente como 2 anos após concessão
    // Como a concessão foi há 3 anos, a validade estará vencida (há 1 ano)
    expect($credential->status)->toBe('Vencida');
});

it('credential TCMS with FSCS and concession has status Em Processamento', function () {
    $credential = Credential::factory()->create([
        'fscs' => 'TCMS-001',
        'type' => CredentialType::TCMS,
        'concession' => now(),
        'validity' => now()->endOfYear(),
    ]);

    expect($credential->status)->toBe('Em Processamento');
});

it('credential CRED without concession has status Pendente', function () {
    $credential = Credential::factory()->create([
        'fscs' => 'CRED-001',
        'type' => CredentialType::CRED,
        'concession' => null,
    ]);

    expect($credential->status)->toBe('Pendente');
});

it('credential CRED with concession has status Válida', function () {
    $credential = Credential::factory()->create([
        'fscs' => 'CRED-002',
        'type' => CredentialType::CRED,
        'concession' => now(),
    ]);

    expect($credential->status)->toBe('Válida');
});

it('credential with unknown state has status Pane - Verificar', function () {
    $user = User::factory()->create();
    $credential = new Credential([
        'user_id' => $user->id,
        'fscs' => '',
        'type' => CredentialType::CRED,
        'secrecy' => 'R',
        'credential' => 'TEST',
    ]);

    expect($credential->status)->toBe('Pane - Verificar');
});

it('returns correct status color for Negada', function () {
    $credential = Credential::factory()->create(['fscs' => '00000']);

    expect($credential->status_color)->toBe('secondary');
});

it('returns correct status color for Vencida', function () {
    $credential = Credential::factory()->create([
        'fscs' => 'VALID-001',
        'type' => CredentialType::CRED,
        'concession' => now()->subYears(3),
    ]);

    expect($credential->status_color)->toBe('danger');
});

it('returns correct status color for Em Processamento', function () {
    $credential = Credential::factory()->create([
        'fscs' => 'TCMS-001',
        'type' => CredentialType::TCMS,
        'concession' => now(),
        'validity' => now()->endOfYear(),
    ]);

    expect($credential->status_color)->toBe('primary');
});

it('returns correct status color for Pendente', function () {
    $credential = Credential::factory()->create([
        'fscs' => 'CRED-001',
        'type' => CredentialType::CRED,
        'concession' => null,
    ]);

    expect($credential->status_color)->toBe('warning');
});

it('returns correct status color for Válida', function () {
    $credential = Credential::factory()->create([
        'fscs' => 'CRED-002',
        'type' => CredentialType::CRED,
        'concession' => now(),
    ]);

    expect($credential->status_color)->toBe('success');
});

it('returns correct status color for Pane - Verificar', function () {
    $user = User::factory()->create();
    $credential = new Credential([
        'user_id' => $user->id,
        'fscs' => '',
        'type' => CredentialType::CRED,
        'secrecy' => 'R',
        'credential' => 'TEST',
    ]);

    expect($credential->status_color)->toBe('danger');
});

// Testes para novas regras de negócio
test('pode criar TCMS sem FSCS e credencial', function () {
    $user = User::factory()->create();

    $credential = Credential::create([
        'user_id' => $user->id,
        'type' => 'TCMS',
        'secrecy' => 'AR',
        'fscs' => null,
        'credential' => null,
        'concession' => now(),
        'validity' => now()->endOfYear(),
    ]);

    expect($credential)->not->toBeNull()
        ->and($credential->type->value)->toBe('TCMS')
        ->and($credential->secrecy->value)->toBe('AR')
        ->and($credential->fscs)->toBeNull()
        ->and($credential->credential)->toBeNull();
});

test('validade calculada automaticamente para CRED é 2 anos', function () {
    $concession = \Carbon\Carbon::parse('2025-01-15');
    $expectedValidity = $concession->copy()->addYears(2);

    expect($expectedValidity->format('Y-m-d'))->toBe('2027-01-15');
});

test('validade calculada automaticamente para TCMS é 31/12 do ano', function () {
    $concession = \Carbon\Carbon::parse('2025-06-15');
    $expectedValidity = $concession->copy()->endOfYear();

    expect($expectedValidity->format('Y-m-d'))->toBe('2025-12-31');
});

// Testes para TCMS sem FSCS - regra: só é Ativa se contiver "TCMS" no número
test('TCMS sem FSCS mas com TCMS no número de credencial tem status Válida', function () {
    $credential = Credential::factory()->create([
        'fscs' => null,
        'credential' => 'TCMS-2025-001', // Contém TCMS
        'type' => 'TCMS',
        'secrecy' => 'AR',
        'concession' => now(),
        'validity' => now()->addMonth(),
    ]);

    expect($credential->status)->toBe('Válida');
});

test('TCMS sem FSCS mas com tcms em minúscula no número também é Válida', function () {
    $credential = Credential::factory()->create([
        'fscs' => null,
        'credential' => 'tcms-2025-001', // tcms em minúscula
        'type' => 'TCMS',
        'secrecy' => 'AR',
        'concession' => now(),
        'validity' => now()->addMonth(),
    ]);

    expect($credential->status)->toBe('Válida');
});

test('TCMS sem FSCS e sem TCMS no número de credencial tem status Pane - Verificar', function () {
    $credential = Credential::factory()->create([
        'fscs' => null,
        'credential' => 'ABC-123', // Não contém TCMS
        'type' => 'TCMS',
        'secrecy' => 'AR',
        'concession' => now(),
        'validity' => now()->addMonth(),
    ]);

    expect($credential->status)->toBe('Pane - Verificar');
});

test('TCMS sem FSCS e sem número de credencial tem status Pane - Verificar', function () {
    $credential = Credential::factory()->create([
        'fscs' => null,
        'credential' => null, // Sem número
        'type' => 'TCMS',
        'secrecy' => 'AR',
        'concession' => now(),
        'validity' => now()->addMonth(),
    ]);

    expect($credential->status)->toBe('Pane - Verificar');
});

test('TCMS com FSCS e COM concessão tem status Em Processamento', function () {
    $credential = Credential::factory()->create([
        'fscs' => '12345',
        'credential' => 'TCMS-2025-001',
        'type' => 'TCMS',
        'secrecy' => 'AR',
        'concession' => now(),
        'validity' => now()->addMonth(),
    ]);

    expect($credential->status)->toBe('Em Processamento');
});

test('TCMS com FSCS mas SEM concessão tem status Pane - Verificar', function () {
    $credential = Credential::factory()->create([
        'fscs' => '12345',
        'credential' => 'TCMS-2025-001',
        'type' => 'TCMS',
        'secrecy' => 'AR',
        'concession' => null,
        'validity' => null,
    ]);

    expect($credential->status)->toBe('Pane - Verificar');
});

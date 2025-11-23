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

it('credential TCMS has status Em Processamento', function () {
    $credential = Credential::factory()->create([
        'fscs' => 'TCMS-001',
        'type' => CredentialType::TCMS,
        'concession' => null,
        'validity' => null,
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

it('credential CRED with concession has status Ativa', function () {
    $credential = Credential::factory()->create([
        'fscs' => 'CRED-002',
        'type' => CredentialType::CRED,
        'concession' => now(),
    ]);

    expect($credential->status)->toBe('Ativa');
});

it('credential with unknown state has status Desconhecido', function () {
    $user = User::factory()->create();
    $credential = new Credential([
        'user_id' => $user->id,
        'fscs' => '',
        'type' => CredentialType::CRED,
        'secrecy' => 'R',
        'credential' => 'TEST',
    ]);

    expect($credential->status)->toBe('Desconhecido');
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
        'concession' => null,
        'validity' => null,
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

it('returns correct status color for Ativa', function () {
    $credential = Credential::factory()->create([
        'fscs' => 'CRED-002',
        'type' => CredentialType::CRED,
        'concession' => now(),
    ]);

    expect($credential->status_color)->toBe('success');
});

it('returns correct status color for Desconhecido', function () {
    $user = User::factory()->create();
    $credential = new Credential([
        'user_id' => $user->id,
        'fscs' => '',
        'type' => CredentialType::CRED,
        'secrecy' => 'R',
        'credential' => 'TEST',
    ]);

    expect($credential->status_color)->toBe('gray');
});

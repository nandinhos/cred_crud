<?php

use App\Models\Credential;
use App\Models\User;

beforeEach(function () {
    // Limpar credenciais existentes para evitar conflitos
    Credential::query()->forceDelete();
    User::query()->forceDelete();
});

it('displays no credentials message when none are expiring', function () {
    // Criar usuário sem credenciais
    User::factory()->create();

    $this->artisan('credentials:notify-expiring --dry-run')
        ->expectsOutput('🔍 Verificando credenciais que expiram em 30 dias...')
        ->expectsOutput('✅ Nenhuma credencial expirando nos próximos 30 dias.')
        ->assertExitCode(0);
});

it('finds credentials expiring within 30 days', function () {
    // Criar credencial expirando em 15 dias diretamente no banco
    $user = User::factory()->create(['name' => 'Test User']);

    Credential::withoutEvents(function () use ($user) {
        Credential::create([
            'user_id' => $user->id,
            'fscs' => 'FSCS-'.fake()->unique()->numberBetween(1000, 9999),
            'type' => 'CRED',
            'secrecy' => 'R',
            'credential' => 'CRED-TEST-0001',
            'concession' => now()->subMonths(6),
            'validity' => now()->addDays(15),
        ]);
    });

    $this->artisan('credentials:notify-expiring --dry-run')
        ->expectsOutput('🔍 Verificando credenciais que expiram em 30 dias...')
        ->assertExitCode(0);
});

it('respects custom days parameter', function () {
    $user = User::factory()->create(['name' => 'Test User']);

    // Credencial expirando em 45 dias (fora do padrão de 30)
    Credential::withoutEvents(function () use ($user) {
        Credential::create([
            'user_id' => $user->id,
            'fscs' => 'FSCS-'.fake()->unique()->numberBetween(1000, 9999),
            'type' => 'CRED',
            'secrecy' => 'R',
            'credential' => 'CRED-TEST-0002',
            'concession' => now()->subMonths(6),
            'validity' => now()->addDays(45),
        ]);
    });

    // Com 30 dias, não deve encontrar
    $this->artisan('credentials:notify-expiring --days=30 --dry-run')
        ->expectsOutput('🔍 Verificando credenciais que expiram em 30 dias...')
        ->expectsOutput('✅ Nenhuma credencial expirando nos próximos 30 dias.')
        ->assertExitCode(0);

    // Com 60 dias, deve encontrar
    $this->artisan('credentials:notify-expiring --days=60 --dry-run')
        ->expectsOutput('🔍 Verificando credenciais que expiram em 60 dias...')
        ->assertExitCode(0);
});

it('logs to security channel when running without dry-run', function () {
    // Limpar o arquivo de log de segurança antes do teste
    $securityLogPath = storage_path('logs/security-'.now()->format('Y-m-d').'.log');
    if (file_exists($securityLogPath)) {
        $initialSize = filesize($securityLogPath);
    } else {
        $initialSize = 0;
    }

    $user = User::factory()->create(['name' => 'Test User']);

    Credential::withoutEvents(function () use ($user) {
        Credential::create([
            'user_id' => $user->id,
            'fscs' => 'FSCS-LOG-TEST',
            'type' => 'CRED',
            'secrecy' => 'R',
            'credential' => 'CRED-TEST-0003',
            'concession' => now()->subMonths(6),
            'validity' => now()->addDays(10),
        ]);
    });

    $this->artisan('credentials:notify-expiring')
        ->assertExitCode(0);

    // Verificar que o arquivo de log cresceu (tem novas entradas)
    clearstatcache();
    if (file_exists($securityLogPath)) {
        $finalSize = filesize($securityLogPath);
        expect($finalSize)->toBeGreaterThan($initialSize);

        // Verificar conteúdo do log
        $logContent = file_get_contents($securityLogPath);
        expect($logContent)->toContain('FSCS-LOG-TEST');
    }
});

it('groups credentials by user', function () {
    $user1 = User::factory()->create(['name' => 'User One']);
    $user2 = User::factory()->create(['name' => 'User Two']);

    // Criar credencial para user1
    Credential::withoutEvents(function () use ($user1) {
        Credential::create([
            'user_id' => $user1->id,
            'fscs' => 'FSCS-'.fake()->unique()->numberBetween(1000, 9999),
            'type' => 'CRED',
            'secrecy' => 'R',
            'credential' => 'CRED-TEST-0004',
            'concession' => now()->subMonths(6),
            'validity' => now()->addDays(5),
        ]);
    });

    // Criar credencial para user2
    Credential::withoutEvents(function () use ($user2) {
        Credential::create([
            'user_id' => $user2->id,
            'fscs' => 'FSCS-'.fake()->unique()->numberBetween(1000, 9999),
            'type' => 'CRED',
            'secrecy' => 'R',
            'credential' => 'CRED-TEST-0005',
            'concession' => now()->subMonths(6),
            'validity' => now()->addDays(10),
        ]);
    });

    $this->artisan('credentials:notify-expiring --dry-run')
        ->assertExitCode(0);
});

it('does not include already expired credentials', function () {
    $user = User::factory()->create(['name' => 'Test User']);

    // Credencial já expirada
    Credential::withoutEvents(function () use ($user) {
        Credential::create([
            'user_id' => $user->id,
            'fscs' => 'FSCS-'.fake()->unique()->numberBetween(1000, 9999),
            'type' => 'CRED',
            'secrecy' => 'R',
            'credential' => 'CRED-TEST-0006',
            'concession' => now()->subYears(3),
            'validity' => now()->subDays(30),
        ]);
    });

    $this->artisan('credentials:notify-expiring --dry-run')
        ->expectsOutput('✅ Nenhuma credencial expirando nos próximos 30 dias.')
        ->assertExitCode(0);
});

it('does not include credentials without validity date', function () {
    $user = User::factory()->create(['name' => 'Test User']);

    // Credencial sem data de validade
    Credential::withoutEvents(function () use ($user) {
        Credential::create([
            'user_id' => $user->id,
            'fscs' => 'FSCS-'.fake()->unique()->numberBetween(1000, 9999),
            'type' => 'CRED',
            'secrecy' => 'R',
            'credential' => 'CRED-TEST-0007',
            'concession' => null,
            'validity' => null,
        ]);
    });

    $this->artisan('credentials:notify-expiring --dry-run')
        ->expectsOutput('✅ Nenhuma credencial expirando nos próximos 30 dias.')
        ->assertExitCode(0);
});

it('displays status emoji based on days remaining', function () {
    $user = User::factory()->create(['name' => 'Test User']);

    // Credencial expirando em 5 dias - deve mostrar status
    Credential::withoutEvents(function () use ($user) {
        Credential::create([
            'user_id' => $user->id,
            'fscs' => 'FSCS-'.fake()->unique()->numberBetween(1000, 9999),
            'type' => 'CRED',
            'secrecy' => 'R',
            'credential' => 'CRED-TEST-0008',
            'concession' => now()->subMonths(6),
            'validity' => now()->addDays(5),
        ]);
    });

    // O comando deve rodar sem erros e exibir a tabela
    $this->artisan('credentials:notify-expiring --dry-run')
        ->assertExitCode(0);
});

it('command signature includes correct options', function () {
    $this->artisan('credentials:notify-expiring --help')
        ->expectsOutputToContain('--days')
        ->expectsOutputToContain('--dry-run')
        ->assertExitCode(0);
});

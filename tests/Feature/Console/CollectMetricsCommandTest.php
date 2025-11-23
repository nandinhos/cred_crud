<?php

use App\Models\Credential;
use App\Models\User;

it('collects metrics and creates json file', function () {
    // Criar alguns dados de teste
    User::factory()->count(5)->create();
    Credential::factory()->count(3)->create();

    // Executar o comando
    $this->artisan('metrics:collect')
        ->expectsOutput('Coletando métricas do sistema...')
        ->assertExitCode(0);

    // Verificar se o arquivo foi criado
    $filename = 'metrics_'.now()->format('Y-m-d').'.json';
    $filepath = storage_path('metrics/'.$filename);

    expect(file_exists($filepath))->toBeTrue();

    // Verificar estrutura do JSON
    $content = json_decode(file_get_contents($filepath), true);

    expect($content)->toHaveKeys(['timestamp', 'date', 'users', 'credentials', 'database']);
    expect($content['users'])->toHaveKeys(['total', 'active', 'deleted', 'with_credentials']);
    expect($content['credentials'])->toHaveKeys(['total', 'active', 'deleted', 'expired', 'expiring_soon', 'by_type', 'by_secrecy']);
    expect($content['database'])->toHaveKeys(['name', 'size_mb', 'table_count']);
});

it('includes correct user metrics', function () {
    $this->artisan('metrics:collect')->assertExitCode(0);

    $filename = 'metrics_'.now()->format('Y-m-d').'.json';
    $filepath = storage_path('metrics/'.$filename);
    $content = json_decode(file_get_contents($filepath), true);

    expect($content['users']['total'])->toBeGreaterThanOrEqual(0);
    expect($content['users']['active'])->toBeGreaterThanOrEqual(0);
});

it('includes correct credential metrics', function () {
    $this->artisan('metrics:collect')->assertExitCode(0);

    $filename = 'metrics_'.now()->format('Y-m-d').'.json';
    $filepath = storage_path('metrics/'.$filename);
    $content = json_decode(file_get_contents($filepath), true);

    expect($content['credentials']['total'])->toBeGreaterThanOrEqual(0);
    expect($content['credentials']['by_type'])->toHaveKeys(['CRED', 'TCMS']);
    expect($content['credentials']['by_secrecy'])->toHaveKeys(['R', 'S', 'AR']);
});

it('includes database metrics', function () {
    $this->artisan('metrics:collect')->assertExitCode(0);

    $filename = 'metrics_'.now()->format('Y-m-d').'.json';
    $filepath = storage_path('metrics/'.$filename);
    $content = json_decode(file_get_contents($filepath), true);

    expect($content['database']['name'])->toBe(config('database.connections.mysql.database'));
    expect($content['database']['size_mb'])->toBeGreaterThan(0);
    expect($content['database']['table_count'])->toBeGreaterThan(0);
});

it('creates metrics directory if not exists', function () {
    // Remover o diretório se existir
    $metricsDir = storage_path('metrics');
    if (is_dir($metricsDir)) {
        array_map('unlink', glob("$metricsDir/*.*"));
        rmdir($metricsDir);
    }

    expect(is_dir($metricsDir))->toBeFalse();

    $this->artisan('metrics:collect')->assertExitCode(0);

    expect(is_dir($metricsDir))->toBeTrue();
});

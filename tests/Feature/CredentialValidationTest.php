<?php

use App\Models\Credential;
use App\Models\User;

it('rejeita data de validade no passado', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->post('/credentials', [
        'fscs' => 'FSCS123',
        'name' => 'Teste Credential',
        'secrecy' => 'R',
        'concession' => '2024-01-01',
        'validity' => '2023-01-01', // Data no passado
    ]);

    $response->assertSessionHasErrors('validity');
});

it('aceita data de validade no futuro', function () {
    $user = User::factory()->create();
    
    $futureDate = now()->addDays(30)->format('Y-m-d');
    
    $response = $this->actingAs($user)->post('/credentials', [
        'fscs' => 'FSCS456',
        'name' => 'Teste Credential',
        'secrecy' => 'S',
        'concession' => '2024-01-01',
        'validity' => $futureDate,
    ]);

    $response->assertRedirect('/credentials');
    $response->assertSessionHas('success');
    
    $this->assertDatabaseHas('credentials', [
        'fscs' => 'FSCS456',
        'name' => 'Teste Credential',
    ]);
});

it('rejeita FSCS duplicado em registros ativos', function () {
    $user = User::factory()->create();
    
    // Criar primeiro registro
    Credential::create([
        'fscs' => 'FSCS789',
        'name' => 'Primeira Credential',
        'secrecy' => 'R',
        'validity' => now()->addDays(30),
    ]);

    // Tentar criar segundo registro com mesmo FSCS
    $response = $this->actingAs($user)->post('/credentials', [
        'fscs' => 'FSCS789', // FSCS duplicado
        'name' => 'Segunda Credential',
        'secrecy' => 'S',
        'validity' => now()->addDays(60)->format('Y-m-d'),
    ]);

    $response->assertSessionHasErrors('fscs');
});

it('permite FSCS duplicado quando registro anterior foi soft deleted', function () {
    $user = User::factory()->create();
    
    // Criar e deletar primeiro registro
    $credential = Credential::create([
        'fscs' => 'FSCS999',
        'name' => 'Credential Deletada',
        'secrecy' => 'R',
        'validity' => now()->addDays(30),
    ]);
    $credential->delete(); // Soft delete

    // Criar novo registro com mesmo FSCS
    $response = $this->actingAs($user)->post('/credentials', [
        'fscs' => 'FSCS999', // Mesmo FSCS do registro deletado
        'name' => 'Nova Credential',
        'secrecy' => 'S',
        'validity' => now()->addDays(90)->format('Y-m-d'),
    ]);

    $response->assertRedirect('/credentials');
    $response->assertSessionHas('success');
    
    $this->assertDatabaseHas('credentials', [
        'fscs' => 'FSCS999',
        'name' => 'Nova Credential',
        'deleted_at' => null,
    ]);
});

it('valida campos de data corretamente no update', function () {
    $user = User::factory()->create();
    
    $credential = Credential::create([
        'fscs' => 'FSCS_UPDATE',
        'name' => 'Credential para Update',
        'secrecy' => 'R',
        'validity' => now()->addDays(30),
    ]);

    // Tentar atualizar com data inválida
    $response = $this->actingAs($user)->put("/credentials/{$credential->id}", [
        'fscs' => 'FSCS_UPDATE',
        'name' => 'Credential Atualizada',
        'secrecy' => 'S',
        'concession' => '2024-01-01',
        'validity' => '2023-01-01', // Data no passado
    ]);

    $response->assertSessionHasErrors('validity');
});

it('aceita secrecy nullable', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->post('/credentials', [
        'fscs' => 'FSCS_NULL_SECRECY',
        'name' => 'Teste Secrecy Null',
        'secrecy' => null,
        'validity' => now()->addDays(30)->format('Y-m-d'),
    ]);

    $response->assertRedirect('/credentials');
    $response->assertSessionHas('success');
});

it('aceita concession nullable', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->post('/credentials', [
        'fscs' => 'FSCS_NULL_CONCESSION',
        'name' => 'Teste Concession Null',
        'secrecy' => 'R',
        'concession' => null,
        'validity' => now()->addDays(30)->format('Y-m-d'),
    ]);

    $response->assertRedirect('/credentials');
    $response->assertSessionHas('success');
});
<?php

use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has fillable attributes', function () {
    $office = Office::create([
        'office' => 'CINDACTA I',
        'description' => 'Centro Integrado de Defesa Aérea e Controle de Tráfego Aéreo I',
    ]);

    expect($office->office)->toBe('CINDACTA I');
    expect($office->description)->toBe('Centro Integrado de Defesa Aérea e Controle de Tráfego Aéreo I');
});

it('has many users', function () {
    $office = Office::create([
        'office' => 'CINDACTA I',
        'description' => 'Centro Integrado de Defesa Aérea e Controle de Tráfego Aéreo I',
    ]);

    User::factory()->count(3)->create(['office_id' => $office->id]);

    expect($office->users)->toHaveCount(3);
    expect($office->users->first())->toBeInstanceOf(User::class);
});

it('returns full name attribute', function () {
    $office = Office::create([
        'office' => 'CINDACTA I',
        'description' => 'Centro Integrado de Defesa Aérea e Controle de Tráfego Aéreo I',
    ]);

    expect($office->full_name)->toBe('CINDACTA I - Centro Integrado de Defesa Aérea e Controle de Tráfego Aéreo I');
});

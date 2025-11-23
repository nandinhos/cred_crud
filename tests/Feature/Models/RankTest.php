<?php

use App\Models\Rank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has fillable attributes', function () {
    $rank = Rank::create([
        'abbreviation' => 'Cel',
        'name' => 'Coronel',
        'armed_force' => 'FAB',
        'hierarchy_order' => 10,
    ]);

    expect($rank->abbreviation)->toBe('Cel');
    expect($rank->name)->toBe('Coronel');
    expect($rank->armed_force)->toBe('FAB');
    expect($rank->hierarchy_order)->toBe(10);
});

it('casts hierarchy_order as integer', function () {
    $rank = Rank::create([
        'abbreviation' => 'Cel',
        'name' => 'Coronel',
        'armed_force' => 'FAB',
        'hierarchy_order' => '10',
    ]);

    expect($rank->hierarchy_order)->toBeInt();
    expect($rank->hierarchy_order)->toBe(10);
});

it('has many users', function () {
    $rank = Rank::create([
        'abbreviation' => 'Cel',
        'name' => 'Coronel',
        'armed_force' => 'FAB',
        'hierarchy_order' => 10,
    ]);

    User::factory()->count(2)->create(['rank_id' => $rank->id]);

    expect($rank->users)->toHaveCount(2);
    expect($rank->users->first())->toBeInstanceOf(User::class);
});

it('can order by hierarchy descending', function () {
    $colonel = Rank::create(['abbreviation' => 'Cel', 'name' => 'Coronel', 'armed_force' => 'FAB', 'hierarchy_order' => 10]);
    $captain = Rank::create(['abbreviation' => 'Cap', 'name' => 'Capitão', 'armed_force' => 'FAB', 'hierarchy_order' => 5]);
    $major = Rank::create(['abbreviation' => 'Maj', 'name' => 'Major', 'armed_force' => 'FAB', 'hierarchy_order' => 7]);

    $ranks = Rank::orderByHierarchy('desc')->get();

    expect($ranks->first()->abbreviation)->toBe('Cel');
    expect($ranks->last()->abbreviation)->toBe('Cap');
});

it('can order by hierarchy ascending', function () {
    $colonel = Rank::create(['abbreviation' => 'Cel', 'name' => 'Coronel', 'armed_force' => 'FAB', 'hierarchy_order' => 10]);
    $captain = Rank::create(['abbreviation' => 'Cap', 'name' => 'Capitão', 'armed_force' => 'FAB', 'hierarchy_order' => 5]);
    $major = Rank::create(['abbreviation' => 'Maj', 'name' => 'Major', 'armed_force' => 'FAB', 'hierarchy_order' => 7]);

    $ranks = Rank::orderByHierarchy('asc')->get();

    expect($ranks->first()->abbreviation)->toBe('Cap');
    expect($ranks->last()->abbreviation)->toBe('Cel');
});

it('can filter by armed force', function () {
    Rank::create(['abbreviation' => 'Cel', 'name' => 'Coronel', 'armed_force' => 'FAB', 'hierarchy_order' => 10]);
    Rank::create(['abbreviation' => 'Cel', 'name' => 'Coronel', 'armed_force' => 'EB', 'hierarchy_order' => 10]);
    Rank::create(['abbreviation' => 'Cap-Cor', 'name' => 'Capitão de Corveta', 'armed_force' => 'MB', 'hierarchy_order' => 5]);

    $fabRanks = Rank::byArmedForce('FAB')->get();
    $mbRanks = Rank::byArmedForce('MB')->get();

    expect($fabRanks)->toHaveCount(1);
    expect($mbRanks)->toHaveCount(1);
    expect($fabRanks->first()->armed_force)->toBe('FAB');
});

it('returns full name attribute', function () {
    $rank = Rank::create([
        'abbreviation' => 'Cel',
        'name' => 'Coronel',
        'armed_force' => 'FAB',
        'hierarchy_order' => 10,
    ]);

    expect($rank->full_name)->toBe('Cel - Coronel (FAB)');
});

<?php

use App\Models\Credential;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

it('logs creation', function () {
    $user = User::factory()->create();
    Auth::login($user);

    $credential = Credential::factory()->create(['fscs' => 'LOG-001']);

    $this->assertDatabaseHas('activity_logs', [
        'log_name' => 'credentials',
        'description' => 'created',
        'subject_type' => Credential::class,
        'subject_id' => $credential->id,
        'causer_id' => $user->id,
    ]);
});

it('logs update', function () {
    $user = User::factory()->create();
    Auth::login($user);

    $credential = Credential::factory()->create();
    $credential->update(['name' => 'Updated Name']);

    $this->assertDatabaseHas('activity_logs', [
        'log_name' => 'credentials',
        'description' => 'updated',
        'subject_type' => Credential::class,
        'subject_id' => $credential->id,
        'causer_id' => $user->id,
    ]);

    $log = DB::table('activity_logs')->where('description', 'updated')->first();
    expect($log->properties)->toContain('Updated Name');
});

it('logs deletion', function () {
    $user = User::factory()->create();
    Auth::login($user);

    $credential = Credential::factory()->create();
    $credential->delete();

    $this->assertDatabaseHas('activity_logs', [
        'log_name' => 'credentials',
        'description' => 'deleted',
        'subject_type' => Credential::class,
        'subject_id' => $credential->id,
        'causer_id' => $user->id,
    ]);
});

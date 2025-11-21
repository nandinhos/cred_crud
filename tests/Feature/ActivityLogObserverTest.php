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
        'model_type' => Credential::class,
        'model_id' => $credential->id,
        'action' => 'created',
        'user_id' => $user->id,
    ]);
});

it('logs update', function () {
    $user = User::factory()->create();
    Auth::login($user);

    $credential = Credential::factory()->create();
    $credential->update(['name' => 'Updated Name']);

    $this->assertDatabaseHas('activity_logs', [
        'model_type' => Credential::class,
        'model_id' => $credential->id,
        'action' => 'updated',
        'user_id' => $user->id,
    ]);
    
    $log = DB::table('activity_logs')->where('action', 'updated')->first();
    expect($log->changes)->toContain('Updated Name');
});

it('logs deletion', function () {
    $user = User::factory()->create();
    Auth::login($user);

    $credential = Credential::factory()->create();
    $credential->delete();

    $this->assertDatabaseHas('activity_logs', [
        'model_type' => Credential::class,
        'model_id' => $credential->id,
        'action' => 'deleted',
        'user_id' => $user->id,
    ]);
});

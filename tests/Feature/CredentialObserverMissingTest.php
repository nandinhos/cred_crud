<?php

use App\Models\Credential;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

it('logs restored credential', function () {
    $user = User::factory()->create();
    Auth::login($user);

    $credential = Credential::factory()->create();
    $credential->delete();

    $credential->restore();

    $this->assertDatabaseHas('activity_logs', [
        'log_name' => 'credentials',
        'description' => 'restored',
        'subject_type' => Credential::class,
        'subject_id' => $credential->id,
        'causer_id' => $user->id,
    ]);
});

it('logs force deleted credential', function () {
    $user = User::factory()->create();
    Auth::login($user);

    $credential = Credential::factory()->create();
    $credentialId = $credential->id;

    $credential->forceDelete();

    $this->assertDatabaseHas('activity_logs', [
        'log_name' => 'credentials',
        'description' => 'force_deleted',
        'subject_type' => Credential::class,
        'subject_id' => $credentialId,
        'causer_id' => $user->id,
    ]);
});

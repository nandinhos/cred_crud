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
    $credential->update(['observation' => 'Updated Observation']);

    $this->assertDatabaseHas('activity_logs', [
        'log_name' => 'credentials',
        'description' => 'updated',
        'subject_type' => Credential::class,
        'subject_id' => $credential->id,
        'causer_id' => $user->id,
    ]);

    $log = DB::table('activity_logs')->where('description', 'updated')->where('subject_id', $credential->id)->first();
    $properties = json_decode($log->properties, true);
    expect($properties['attributes']['observation'])->toBe('Updated Observation');
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

it('logs include user information', function () {
    $user = User::factory()->create(['name' => 'Test Logger']);
    Auth::login($user);

    $credential = Credential::factory()->create();

    $log = DB::table('activity_logs')
        ->where('subject_type', Credential::class)
        ->where('subject_id', $credential->id)
        ->where('description', 'created')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->causer_id)->toBe($user->id);
    expect($log->causer_type)->toBe(User::class);
});

it('logs contain credential data on creation', function () {
    $user = User::factory()->create();
    Auth::login($user);

    $credential = Credential::factory()->create([
        'fscs' => 'TEST-FSCS-001',
        'credential' => 'TEST-CRED-001',
    ]);

    $log = DB::table('activity_logs')
        ->where('subject_id', $credential->id)
        ->where('description', 'created')
        ->first();

    $properties = json_decode($log->properties, true);
    expect($properties['attributes']['fscs'])->toBe('TEST-FSCS-001');
    expect($properties['attributes']['credential'])->toBe('TEST-CRED-001');
});

it('logs contain old and new values on update', function () {
    $user = User::factory()->create();
    Auth::login($user);

    $credential = Credential::factory()->create(['observation' => 'Original']);
    $credential->update(['observation' => 'Modified']);

    $log = DB::table('activity_logs')
        ->where('subject_id', $credential->id)
        ->where('description', 'updated')
        ->first();

    $properties = json_decode($log->properties, true);
    expect($properties['old']['observation'])->toBe('Original');
    expect($properties['attributes']['observation'])->toBe('Modified');
});

it('multiple updates create multiple logs', function () {
    $user = User::factory()->create();
    Auth::login($user);

    $credential = Credential::factory()->create();

    $credential->update(['observation' => 'First Update']);
    $credential->update(['observation' => 'Second Update']);
    $credential->update(['observation' => 'Third Update']);

    $count = DB::table('activity_logs')
        ->where('subject_id', $credential->id)
        ->where('description', 'updated')
        ->count();

    expect($count)->toBe(3);
});

it('logs are created without authenticated user', function () {
    // Sem login (sistema automático)
    $credential = Credential::factory()->create();

    $log = DB::table('activity_logs')
        ->where('subject_id', $credential->id)
        ->where('description', 'created')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->causer_id)->toBeNull();
});

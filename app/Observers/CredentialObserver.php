<?php

namespace App\Observers;

use App\Models\Credential;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CredentialObserver
{
    public function created(Credential $credential): void
    {
        $this->logActivity('created', $credential);
    }

    public function updated(Credential $credential): void
    {
        $this->logActivity('updated', $credential, [
            'changes' => $credential->getChanges(),
        ]);
    }

    public function deleted(Credential $credential): void
    {
        $this->logActivity('deleted', $credential);
    }

    public function restored(Credential $credential): void
    {
        $this->logActivity('restored', $credential);
    }

    public function forceDeleted(Credential $credential): void
    {
        $this->logActivity('force_deleted', $credential);
    }

    private function logActivity(string $action, Credential $credential, array $extra = []): void
    {
        DB::table('activity_logs')->insert([
            'log_name' => 'credentials',
            'description' => $action,
            'subject_type' => Credential::class,
            'subject_id' => $credential->id,
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id' => Auth::id(),
            'properties' => ! empty($extra) ? json_encode($extra) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

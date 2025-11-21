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

    private function logActivity(string $action, Credential $credential, array $extra = []): void
    {
        DB::table('activity_logs')->insert([
            'model_type' => Credential::class,
            'model_id' => $credential->id,
            'action' => $action,
            'user_id' => Auth::id(),
            'changes' => json_encode($extra),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

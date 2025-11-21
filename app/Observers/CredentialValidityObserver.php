<?php

namespace App\Observers;

use App\Enums\CredentialType;
use App\Models\Credential;
use Carbon\Carbon;

class CredentialValidityObserver
{
    /**
     * Handle the Credential "saving" event.
     * Calcula automaticamente a data de validade baseada no tipo e data de concessão.
     */
    public function saving(Credential $credential): void
    {
        // Só calcula se houver data de concessão e se ela foi alterada
        if ($credential->concession && $credential->isDirty('concession')) {
            $concessionDate = Carbon::parse($credential->concession);

            if ($credential->type === CredentialType::CRED) {
                // CRED: validade de 2 anos a partir da concessão
                $credential->validity = $concessionDate->copy()->addYears(2);
            } elseif ($credential->type === CredentialType::TCMS) {
                // TCMS: validade até 31/12 do ano de concessão
                $credential->validity = $concessionDate->copy()->endOfYear();
            }
        }

        // Se o tipo mudou e já tem concessão, recalcula
        if ($credential->isDirty('type') && $credential->concession) {
            $concessionDate = Carbon::parse($credential->concession);

            if ($credential->type === CredentialType::CRED) {
                $credential->validity = $concessionDate->copy()->addYears(2);
            } elseif ($credential->type === CredentialType::TCMS) {
                $credential->validity = $concessionDate->copy()->endOfYear();
            }
        }
    }
}

<?php

namespace App\Models;

use App\Enums\CredentialSecrecy;
use App\Enums\CredentialType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $fscs
 * @property string $type
 * @property string|null $observation
 * @property string $secrecy
 * @property string|null $credential
 * @property \Illuminate\Support\Carbon|null $concession
 * @property \Illuminate\Support\Carbon|null $validity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $user
 */
class Credential extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Boot do modelo - Adicionar validações
     */
    protected static function booted(): void
    {
        // Validar que cada usuário pode ter apenas uma credencial ativa ao CRIAR
        static::creating(function (Credential $credential) {
            if ($credential->user_id) {
                $existingCredential = static::where('user_id', $credential->user_id)
                    ->whereNull('deleted_at')
                    ->first();

                if ($existingCredential) {
                    $status = $existingCredential->status;
                    
                    // Se a credencial existente está vencida, permitir e deletar a antiga
                    if ($status === 'Vencida') {
                        // Será deletada no evento 'created'
                        return;
                    }
                    
                    // Se credencial está ATIVA, EM PROCESSAMENTO ou PANE - bloquear
                    if (in_array($status, ['Ativa', 'Em Processamento', 'Pane - Verificar', 'Pendente'])) {
                        throw new \Exception(
                            "Este usuário já possui uma credencial com status '{$status}'. " .
                            "Não é possível criar uma nova credencial enquanto houver uma ativa, em processamento ou com pane. " .
                            "Delete ou resolva a situação da credencial atual primeiro."
                        );
                    }
                }
            }
        });

        // Após criar com sucesso, deletar credencial vencida se existir
        static::created(function (Credential $credential) {
            if ($credential->user_id) {
                $vencida = static::where('user_id', $credential->user_id)
                    ->where('id', '!=', $credential->id)
                    ->whereNull('deleted_at')
                    ->get()
                    ->filter(function ($cred) {
                        return $cred->status === 'Vencida';
                    });

                // Deletar todas as credenciais vencidas (soft delete)
                foreach ($vencida as $old) {
                    $old->delete();
                }
            }
        });

        // Validar que cada usuário pode ter apenas uma credencial ativa ao RESTAURAR
        static::restoring(function (Credential $credential) {
            if ($credential->user_id) {
                $existingCredential = static::where('user_id', $credential->user_id)
                    ->where('id', '!=', $credential->id)
                    ->whereNull('deleted_at')
                    ->first();

                if ($existingCredential) {
                    $status = $existingCredential->status;
                    
                    throw new \Exception(
                        "Este usuário já possui uma credencial com status '{$status}'. " .
                        "Não é possível restaurar. Delete a credencial ativa primeiro."
                    );
                }
            }
        });
    }

    protected $fillable = [
        'user_id',
        'fscs',
        'type',
        'observation',
        'secrecy',
        'credential',
        'concession',
        'validity',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CredentialType::class,
            'secrecy' => CredentialSecrecy::class,
            'concession' => 'date',
            'validity' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Relacionamento com User
     * Uma credencial pertence a um usuário
     *
     * @return BelongsTo<User, Credential>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor para calcular o status da credencial baseado nas regras de negócio
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                // Regra 1: Negada - fscs = "00000" (considerado como não existe)
                if ($this->fscs === '00000') {
                    return 'Negada';
                }

                // Regra 2: Vencida - validity < hoje
                if ($this->validity && $this->validity < Carbon::today()) {
                    return 'Vencida';
                }

                // Regra 3: TCMS sem FSCS (documento de sigilo) - Status Válida
                // FSCS nulo + Número de Credencial contém "TCMS" = documento de sigilo válido
                if (! $this->fscs && $this->type === CredentialType::TCMS && $this->credential && stripos($this->credential, 'TCMS') !== false) {
                    return 'Válida';
                }

                // Regra 4: Em Processamento - fscs existe (não null e != "00000") + type = TCMS + COM concessão
                // TCMS sem concessão = Pane - Verificar (cai no fallback)
                if ($this->fscs && $this->fscs !== '00000' && $this->type === CredentialType::TCMS && $this->concession) {
                    return 'Em Processamento';
                }

                // Regra 5: Pendente - fscs existe (não null e != "00000") + type = CRED + sem concessão
                if ($this->fscs && $this->fscs !== '00000' && $this->type === CredentialType::CRED && ! $this->concession) {
                    return 'Pendente';
                }

                // Regra 6: Válida - fscs existe (não null e != "00000") + type = CRED + com concessão
                if ($this->fscs && $this->fscs !== '00000' && $this->type === CredentialType::CRED && $this->concession) {
                    return 'Válida';
                }

                return 'Pane - Verificar';
            }
        );
    }

    /**
     * Retorna a cor do badge baseada no status
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'Negada' => 'secondary',
            'Vencida' => 'danger',
            'Em Processamento' => 'primary',
            'Pendente' => 'warning',
            'Válida' => 'success',
            'Pane - Verificar' => 'danger', // Vermelho vivo para chamar atenção
            default => 'gray',
        };
    }
}

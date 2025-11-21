<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $fscs
 * @property string $name
 * @property string $secrecy
 * @property string $credential
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

    protected $fillable = [
        'user_id',
        'fscs',
        'name',
        'secrecy',
        'credential',
        'concession',
        'validity',
    ];

    protected $casts = [
        'concession' => 'date',
        'validity' => 'date',
    ];

    /**
     * Relacionamento com User
     * Uma credencial pertence a um usuário
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

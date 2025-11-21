<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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
}

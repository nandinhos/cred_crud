<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'office',
        'description',
    ];

    /**
     * Get the users for this office.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Retorna o nome completo formatado da unidade.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->office} - {$this->description}";
    }
}

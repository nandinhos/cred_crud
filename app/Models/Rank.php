<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rank extends Model
{
    use HasFactory;

    protected $fillable = [
        'abbreviation',
        'name',
        'armed_force',
        'hierarchy_order',
    ];

    protected $casts = [
        'hierarchy_order' => 'integer',
    ];

    /**
     * Get the users for this rank.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope para ordenar por hierarquia.
     */
    public function scopeOrderByHierarchy($query, string $direction = 'desc')
    {
        return $query->orderBy('hierarchy_order', $direction);
    }

    /**
     * Scope para filtrar por força armada.
     */
    public function scopeByArmedForce($query, string $force)
    {
        return $query->where('armed_force', $force);
    }

    /**
     * Retorna o nome completo formatado do posto/graduação.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->abbreviation} - {$this->name} ({$this->armed_force})";
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property mixed $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Credential> $credentials
 * @property-read int|null $credentials_count
 */
class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        // Permitir acesso ao admin principal sempre
        if ($this->email === 'admin@admin.com') {
            return true;
        }
        
        // Verificar se o usuário tem qualquer um dos roles autorizados
        return $this->hasRole('super_admin') || $this->hasRole('admin') || $this->hasRole('consulta');
    }


    /**
     * Verificar se o usuário é administrador
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('super_admin');
    }

    /**
     * Verificar se o usuário tem apenas permissão de consulta
     * 
     * @return bool
     */
    public function isConsulta(): bool
    {
        return $this->hasRole('consulta') && !$this->isAdmin();
    }

    /**
     * Relacionamento com Credential
     * Um usuário pode ter muitas credenciais
     */
    public function credentials(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Credential::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';
    public const ROLE_LOGISTIQUE = 'logistique';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'hr_agent_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function hrAgent(): BelongsTo
    {
        return $this->belongsTo(HrAgent::class, 'hr_agent_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isLogistique(): bool
    {
        return $this->role === self::ROLE_LOGISTIQUE;
    }

    public function canAccessLogistics(): bool
    {
        return $this->isAdmin() || $this->isLogistique();
    }
}
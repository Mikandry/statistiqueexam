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
    public const ROLE_RH = 'rh';
    public const ROLE_STATS = 'statistique';
    public const ROLE_EXAMENS = 'examens'; // Adjust string to match your DB (e.g., 'examens' or 'saisie')

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

    public function isRh(): bool
    {
        return $this->role === self::ROLE_RH;
    }

    public function canAccessRh(): bool
    {
        return $this->isAdmin() || $this->isRh();
    }

    public function isStats(): bool
    {
        return $this->role === self::ROLE_STATS;
    }

    public function canAccessStats(): bool
    {
        return $this->isAdmin() || $this->isStats();
    }

    public function isExams(): bool
    {
        return $this->role === self::ROLE_EXAMENS;
    }

    public function canAccessExams(): bool
    {
        return $this->isAdmin() || $this->isExams();
    }
}
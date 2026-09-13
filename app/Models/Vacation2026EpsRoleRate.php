<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacation2026EpsRoleRate extends Model
{
    protected $table = 'vacation_2026_eps_role_rates';
    protected $fillable = ['role_key', 'label', 'rate'];
    protected function casts(): array { return ['rate' => 'decimal:2']; }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventorySupplier extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'phone', 'email', 'supplied_products'];

    public function materials(): HasMany
    {
        return $this->hasMany(InventoryMaterial::class, 'supplier_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventorySupplyOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id',
        'order_date',
        'remaining_quantity',
        'quantity_to_order',
        'status',
        'observation',
    ];

    protected function casts(): array
    {
        return ['order_date' => 'date'];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(InventoryMaterial::class, 'material_id');
    }
}

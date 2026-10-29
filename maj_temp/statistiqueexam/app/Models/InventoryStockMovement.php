<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStockMovement extends Model
{
    use HasFactory;

    public const TYPE_IN = 'entree';
    public const TYPE_OUT = 'sortie';

    protected $fillable = [
        'material_id',
        'validated_by',
        'movement_date',
        'movement_type',
        'voucher_number',
        'requester_name',
        'requesting_service',
        'function',
        'requested_quantity',
        'granted_quantity',
        'stock_before',
        'stock_after',
        'reason',
        'signature',
        'observation',
    ];

    protected function casts(): array
    {
        return ['movement_date' => 'date'];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(InventoryMaterial::class, 'material_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}

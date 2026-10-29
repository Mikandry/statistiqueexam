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
    public const TYPE_RETURN = 'retour';

    /** Mouvements qui augmentent le stock. */
    public const IN_TYPES = [self::TYPE_IN, self::TYPE_RETURN];

    public const TYPES = [
        self::TYPE_IN => 'Entrée (réception)',
        self::TYPE_OUT => 'Sortie (remise)',
        self::TYPE_RETURN => 'Retour',
    ];

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
        'request_token',
        'supporting_document',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'code',
        'name',
        'category',
        'description',
        'unit',
        'initial_quantity',
        'available_quantity',
        'minimum_threshold',
        'unit_price',
        'total_value',
        'acquired_at',
        'condition',
        'observations',
    ];

    protected function casts(): array
    {
        return [
            'acquired_at' => 'date',
            'unit_price' => 'decimal:2',
            'total_value' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (InventoryMaterial $material): void {
            $material->total_value = round(((int) $material->available_quantity) * ((float) $material->unit_price), 2);
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(InventorySupplier::class, 'supplier_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryStockMovement::class, 'material_id');
    }

    public function supplyOrders(): HasMany
    {
        return $this->hasMany(InventorySupplyOrder::class, 'material_id');
    }

    /**
     * Rapprochement : stock théorique = quantité initiale + entrées + retours − sorties.
     * Un écart non nul signale un stock modifié hors mouvement ou un mouvement manquant.
     *
     * @return array{initial:int,in:int,out:int,expected:int,actual:int,gap:int,last_stock_after:?int}
     */
    public function reconciliation(): array
    {
        $in = (int) $this->movements()->whereIn('movement_type', InventoryStockMovement::IN_TYPES)->sum('granted_quantity');
        $out = (int) $this->movements()->where('movement_type', InventoryStockMovement::TYPE_OUT)->sum('granted_quantity');
        $expected = (int) $this->initial_quantity + $in - $out;
        $last = $this->movements()->orderByDesc('id')->value('stock_after');

        return [
            'initial' => (int) $this->initial_quantity,
            'in' => $in,
            'out' => $out,
            'expected' => $expected,
            'actual' => (int) $this->available_quantity,
            'gap' => (int) $this->available_quantity - $expected,
            'last_stock_after' => $last === null ? null : (int) $last,
        ];
    }

    public function needsSupply(): bool
    {
        return $this->available_quantity <= $this->minimum_threshold;
    }
}

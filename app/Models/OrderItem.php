<?php

namespace App\Models;

use App\Enums\ItemAvailability;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'medicine_id',
        'medicine_name',
        'quantity',
        'unit_price',
        'line_total',
        'availability',
        'pharmacy_id',
        'checked_at',
        'substitute_name',
        'requires_prescription',
    ];

    protected $casts = [
        'quantity'              => 'integer',
        'unit_price'            => 'integer',
        'line_total'            => 'integer',
        'availability'          => ItemAvailability::class,
        'checked_at'            => 'datetime',
        'requires_prescription' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    /** Pharmacie contactée pour vérifier cette ligne. */
    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    /** « 14 comprimés » — conditionnement affiché sous le nom. */
    public function getPackAttribute(): string
    {
        return $this->medicine?->pack ?: "{$this->quantity} unité(s)";
    }
}

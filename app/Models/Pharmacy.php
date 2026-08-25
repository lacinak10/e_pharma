<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pharmacy extends Model
{
    use HasFactory;

    protected $table = 'pharmacies';

    protected $fillable = [
        'name', 'area', 'phone', 'address',
        'latitude', 'longitude', 'is_24h',
        'reliability', 'avg_response_minutes', 'is_active',
    ];

    protected $casts = [
        'latitude'             => 'float',
        'longitude'            => 'float',
        'is_24h'               => 'boolean',
        'is_active'            => 'boolean',
        'reliability'          => 'integer',
        'avg_response_minutes' => 'integer',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** « Répond en 2 min en moyenne » */
    public function getResponseLabelAttribute(): string
    {
        return "Répond en {$this->avg_response_minutes} min en moyenne";
    }

    public function getAreaLabelAttribute(): string
    {
        return $this->is_24h ? "{$this->area} · 24 h/24" : $this->area;
    }

    /** Couleur du design system selon la fiabilité constatée. */
    public function getReliabilityColorAttribute(): string
    {
        return match (true) {
            $this->reliability >= 85 => '#0E5C43',
            $this->reliability >= 70 => '#B87514',
            default                  => '#A6382F',
        };
    }
}

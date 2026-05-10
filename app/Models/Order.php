<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'delivery_address',
        'delivery_phone',
        'notes',
        'payment_method',
        'total_amount',
        'delivery_fee',
        'subtotal',
        'canceled_at',
        'delivered_at',
        'prescription_path',
        'has_prescription',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'total_amount' => 'integer',
        'canceled_at' => 'datetime',
        'delivered_at' => 'datetime',
        'has_prescription' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function assignment()
    {
        return $this->hasOne(DeliveryAssignment::class, 'order_id');
    }

    public function user(){ return $this->belongsTo(User::class); }


    public function getStatusLabelAttribute(): string
    {
        /** @var OrderStatus $status */
        $status = $this->status;
        return $status->label();
    }
}

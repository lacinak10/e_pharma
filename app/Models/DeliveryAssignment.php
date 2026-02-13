<?php

namespace App\Models;

use App\Enums\AssignmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryAssignment extends Model
{
    use HasFactory;

   protected $fillable = [
        'order_id','courier_id','assigned_by','status','assigned_at','responded_at','note'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function order(){ return $this->belongsTo(Order::class); }
    public function courier(){ return $this->belongsTo(User::class, 'courier_id'); }
    public function assigner(){ return $this->belongsTo(User::class, 'assigned_by'); }
}

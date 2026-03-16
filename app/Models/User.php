<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_CLIENT  = 'client';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_COURIER = 'courier';



    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // --- Role helpers
    public function isClient(): bool
    {
        return $this->role === self::ROLE_CLIENT;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isCourier(): bool
    {
        return $this->role === self::ROLE_COURIER;
    }

    // --- Relations
    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function courierAssignments()
    {
        return $this->hasMany(DeliveryAssignment::class, 'courier_id');
    }

    public function managerAssignments()
    {
        return $this->hasMany(DeliveryAssignment::class, 'assigned_by');
    }
}

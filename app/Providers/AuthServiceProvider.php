<?php

namespace App\Providers;

use App\Models\DeliveryAssignment;
use App\Models\Medicine;
use App\Models\Order;
use App\Policies\DeliveryAssignmentPolicy;
use App\Policies\MedicinePolicy;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Medicine::class => MedicinePolicy::class,
        Order::class => OrderPolicy::class,
        DeliveryAssignment::class => DeliveryAssignmentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}

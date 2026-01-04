<?php

namespace App\Policies;

use App\Models\DeliveryAssignment;
use App\Models\User;

class DeliveryAssignmentPolicy
{
    public function view(User $user, DeliveryAssignment $assignment): bool
    {
        if ($user->isManager()) {
            return true;
        }

        if ($user->isCourier()) {
            return $assignment->courier_id === $user->id;
        }

        return false;
    }
}

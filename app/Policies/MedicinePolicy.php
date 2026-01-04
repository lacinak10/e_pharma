<?php

namespace App\Policies;

use App\Models\Medicine;
use App\Models\User;

class MedicinePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isManager();
    }

    public function view(User $user, Medicine $medicine): bool
    {
        return $user->isManager();
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Medicine $medicine): bool
    {
        return $user->isManager();
    }

    public function delete(User $user, Medicine $medicine): bool
    {
        return $user->isManager();
    }
}

<?php

namespace App\Policies;

use App\Enums\AssignmentStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return match (true) {
            $user->isManager() => true,
            $user->isClient()  => $order->user_id === $user->id,
            $user->isCourier() => $order->assignment?->courier_id === $user->id,
            default            => false,
        };
    }

    /** Le client garde la main tant que le livreur n'est pas engagé. */
    public function cancel(User $user, Order $order): bool
    {
        if ($user->isManager()) {
            return ! $order->status->isFinal() && $order->status !== OrderStatus::DELIVERED;
        }

        return $user->isClient()
            && $order->user_id === $user->id
            && $order->status->isCancelableByClient();
    }

    /** Le manager traite la commande : validation, verdict, refus. */
    public function manage(User $user, Order $order): bool
    {
        return $user->isManager() && $order->status->needsManager();
    }

    /** Attribution d'un livreur : seulement après un verdict favorable. */
    public function assign(User $user, Order $order): bool
    {
        if (! $user->isManager()) {
            return false;
        }

        if (! in_array($order->status, [OrderStatus::AVAILABLE, OrderStatus::PARTIALLY_AVAILABLE], true)) {
            return false;
        }

        // Ne pas réattribuer une course déjà acceptée par un livreur.
        return $order->assignment?->status !== AssignmentStatus::ACCEPTED;
    }

    /** Le livreur accepte ou refuse la course qui vient de lui être confiée. */
    public function courierRespond(User $user, Order $order): bool
    {
        return $user->isCourier()
            && $order->assignment?->courier_id === $user->id
            && $order->status === OrderStatus::COURIER_ASSIGNED
            && $order->assignment->status === AssignmentStatus::ASSIGNED;
    }

    /** Le livreur fait avancer sa course d'une des cinq étapes. */
    public function courierAdvance(User $user, Order $order): bool
    {
        return $user->isCourier()
            && $order->assignment?->courier_id === $user->id
            && $order->status->nextCourierStep() !== null
            && $order->assignment->status !== AssignmentStatus::REFUSED;
    }

    /** Le client note son livreur une fois la commande reçue. */
    public function review(User $user, Order $order): bool
    {
        return $user->isClient()
            && $order->user_id === $user->id
            && $order->status === OrderStatus::DELIVERED
            && $order->assignment?->courier_id !== null;
    }
}

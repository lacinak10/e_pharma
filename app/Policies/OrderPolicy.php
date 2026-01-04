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
        if ($user->isManager()) {
            return true;
        }

        if ($user->isClient()) {
            return $order->user_id === $user->id;
        }

        if ($user->isCourier()) {
            return $order->assignment && $order->assignment->courier_id === $user->id;
        }

        return false;
    }

    public function cancel(User $user, Order $order): bool
    {
        if (!$user->isClient()) {
            return false;
        }

        if ($order->user_id !== $user->id) {
            return false;
        }

        // Annulation autorisée tant que pas en livraison / livrée / déjà annulée
        return in_array($order->status, [
            OrderStatus::PENDING_ASSIGNMENT,
            OrderStatus::ASSIGNED,
            OrderStatus::REFUSED,
        ], true);
    }

    public function assign(User $user, Order $order): bool
    {
        if (!$user->isManager()) {
            return false;
        }

        // Ne pas affecter si déjà livré / annulé / en livraison
        if (in_array($order->status, [OrderStatus::DELIVERED, OrderStatus::CANCELED, OrderStatus::IN_DELIVERY], true)) {
            return false;
        }

        // Ne pas réaffecter si déjà accepté
        if ($order->assignment && $order->assignment->status === AssignmentStatus::ACCEPTED) {
            return false;
        }

        return true;
    }

    public function courierRespond(User $user, Order $order): bool
    {
        if (!$user->isCourier()) {
            return false;
        }

        if (!$order->assignment || $order->assignment->courier_id !== $user->id) {
            return false;
        }

        return $order->status === OrderStatus::ASSIGNED
            && $order->assignment->status === AssignmentStatus::ASSIGNED;
    }

    public function courierUpdateStatus(User $user, Order $order): bool
    {
        if (!$user->isCourier()) {
            return false;
        }

        if (!$order->assignment || $order->assignment->courier_id !== $user->id) {
            return false;
        }

        // Pour changer statut, l’affectation doit être acceptée
        if ($order->assignment->status !== AssignmentStatus::ACCEPTED) {
            return false;
        }

        return in_array($order->status, [
            OrderStatus::ACCEPTED,
            OrderStatus::IN_DELIVERY,
        ], true);
    }
}

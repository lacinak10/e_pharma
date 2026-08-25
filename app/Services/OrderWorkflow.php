<?php

namespace App\Services;

use App\Enums\ItemAvailability;
use App\Enums\OrderStatus;
use App\Models\CourierReview;
use App\Models\DeliveryAssignment;
use App\Models\Medicine;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\OrderItem;
use App\Models\Pharmacy;
use App\Models\User;
use App\Notifications\NewAssignmentNotification;
use App\Notifications\OrderProgressNotification;
use App\Enums\AssignmentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Pilote le cycle de vie d'une commande sur les 14 statuts du système de design.
 *
 * Toute transition passe par ce service : il applique le nouveau statut, journalise
 * un OrderEvent (qui alimente la timeline client et le flux temps réel du back-office)
 * et notifie le client. Les contrôleurs ne modifient jamais `status` directement.
 */
class OrderWorkflow
{
    // ── Étape 1 → 3 : prise en charge par le manager ─────────────────────

    /**
     * Le manager accepte la commande : le chrono de 5 minutes démarre et la
     * vérification auprès des pharmacies partenaires commence.
     */
    public function startChecking(Order $order, User $manager): Order
    {
        $this->assertStatus($order, [OrderStatus::PENDING_VALIDATION]);

        return DB::transaction(function () use ($order, $manager) {
            $order->update([
                'status'              => OrderStatus::CHECKING,
                'validated_at'        => now(),
                'checking_started_at' => now(),
                'check_deadline_at'   => now()->addSeconds(Order::CHECK_DURATION_SECONDS),
            ]);

            $this->record(
                $order,
                $manager,
                'Vérification de disponibilité lancée auprès des pharmacies partenaires.'
            );

            return $order->refresh();
        });
    }

    /** Le manager refuse la commande, avec son motif. */
    public function refuse(Order $order, User $manager, string $reason): Order
    {
        $this->assertStatus($order, [
            OrderStatus::PENDING_VALIDATION,
            OrderStatus::CHECKING,
        ]);

        return DB::transaction(function () use ($order, $manager, $reason) {
            $order->update([
                'status'         => OrderStatus::REFUSED,
                'refusal_reason' => $reason,
                'verdict_at'     => now(),
            ]);

            $this->record($order, $manager, "Commande refusée : {$reason}");

            return $order->refresh();
        });
    }

    // ── Composition du panier par le manager ─────────────────────────────

    /**
     * Ajoute une ligne à la commande — c'est ainsi que le manager traduit une
     * ordonnance téléversée en panier concret, l'image ne portant aucune donnée
     * exploitable. La ligne naît « à vérifier » comme n'importe quelle autre.
     */
    public function addItem(Order $order, Medicine $medicine, int $quantity, User $manager): OrderItem
    {
        $this->assertComposable($order);

        return DB::transaction(function () use ($order, $medicine, $quantity, $manager) {
            $existing = $order->items()->where('medicine_id', $medicine->id)->first();

            if ($existing) {
                $item = $this->setItemQuantity($existing, $existing->quantity + $quantity);
            } else {
                $item = OrderItem::create([
                    'order_id'              => $order->id,
                    'medicine_id'           => $medicine->id,
                    'medicine_name'         => $medicine->name,
                    'unit_price'            => (int) $medicine->price,
                    'quantity'              => $quantity,
                    'line_total'            => (int) $medicine->price * $quantity,
                    'availability'          => ItemAvailability::PENDING,
                    'requires_prescription' => (bool) $medicine->requires_prescription,
                ]);

                $this->syncTotals($order);
            }

            $this->record(
                $order,
                $manager,
                "Ligne ajoutée d'après l'ordonnance : {$medicine->name} × {$quantity}.",
                notify: false
            );

            return $item;
        });
    }

    /** Retire une ligne composée par erreur. */
    public function removeItem(OrderItem $item, User $manager): void
    {
        $order = $item->order;
        $this->assertComposable($order);

        DB::transaction(function () use ($item, $order, $manager) {
            $name = $item->medicine_name;
            $item->delete();

            $this->syncTotals($order);
            $this->record($order, $manager, "Ligne retirée de la commande : {$name}.", notify: false);
        });
    }

    /** Ajuste la quantité d'une ligne et réaligne le total de la ligne. */
    public function setItemQuantity(OrderItem $item, int $quantity): OrderItem
    {
        $item->update([
            'quantity'   => $quantity,
            'line_total' => $item->unit_price * $quantity,
        ]);

        $this->syncTotals($item->order);

        return $item->refresh();
    }

    /**
     * Réaligne les montants de la commande sur ses lignes.
     * Avant le verdict tout compte : le client doit voir un total cohérent avec
     * ce qu'il a demandé. Après le verdict, seules les lignes obtenables comptent.
     */
    public function syncTotals(Order $order): Order
    {
        $items = $order->items()->get();

        $billable = $order->status->number() > OrderStatus::CHECKING->number()
            ? $items->filter(fn (OrderItem $i) => $i->availability !== ItemAvailability::UNAVAILABLE)
            : $items;

        $subtotal = (int) $billable->sum('line_total');

        $order->update([
            'subtotal'     => $subtotal,
            'total_amount' => $subtotal + (int) $order->delivery_fee,
        ]);

        return $order->refresh();
    }

    /**
     * Verdict de disponibilité déduit des lignes :
     * tout obtenable → AVAILABLE · rien → UNAVAILABLE · sinon PARTIALLY_AVAILABLE.
     *
     * @param  \Illuminate\Support\Collection<int, OrderItem>  $items
     */
    private function availabilityVerdict($items): OrderStatus
    {
        $obtainable = $items->filter(
            fn (OrderItem $i) => $i->availability !== ItemAvailability::UNAVAILABLE
        );

        return match (true) {
            $obtainable->isEmpty()                   => OrderStatus::UNAVAILABLE,
            $obtainable->count() === $items->count() => OrderStatus::AVAILABLE,
            default                                  => OrderStatus::PARTIALLY_AVAILABLE,
        };
    }

    /** Le panier n'est modifiable que tant que le verdict n'est pas rendu. */
    private function assertComposable(Order $order): void
    {
        if (! in_array($order->status, [OrderStatus::PENDING_VALIDATION, OrderStatus::CHECKING], true)) {
            throw ValidationException::withMessages([
                'items' => 'Le contenu ne peut plus être modifié une fois le verdict rendu.',
            ]);
        }
    }

    // ── Étape 4 à 6 : verdict de disponibilité ───────────────────────────

    /** Enregistre le retour d'une pharmacie pour une ligne de commande. */
    public function recordItemAvailability(
        OrderItem $item,
        ItemAvailability $availability,
        ?Pharmacy $pharmacy = null,
        ?string $substituteName = null,
    ): OrderItem {
        $item->update([
            'availability'    => $availability,
            'pharmacy_id'     => $pharmacy?->id ?? $item->pharmacy_id,
            'checked_at'      => now(),
            'substitute_name' => $substituteName,
        ]);

        return $item->refresh();
    }

    /**
     * Clôt la vérification : le verdict découle de l'état des lignes.
     * Tout disponible → AVAILABLE · rien → UNAVAILABLE · sinon PARTIALLY_AVAILABLE.
     */
    public function settleVerdict(Order $order, User $manager): Order
    {
        $this->assertStatus($order, [OrderStatus::CHECKING]);

        return DB::transaction(function () use ($order, $manager) {
            $items = $order->items()->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => "Ajoutez au moins un médicament à la commande avant de rendre le verdict.",
                ]);
            }

            if ($items->contains(fn (OrderItem $i) => ! $i->availability->isSettled())) {
                throw ValidationException::withMessages([
                    'items' => 'Chaque médicament doit être marqué disponible, indisponible ou substitué.',
                ]);
            }

            $obtainable = $items->filter(
                fn (OrderItem $i) => $i->availability !== ItemAvailability::UNAVAILABLE
            );

            $status = $this->availabilityVerdict($items);

            $order->update([
                'status'       => $status,
                'verdict_at'   => now(),
                'pharmacy_id'  => $order->pharmacy_id ?? $items->firstWhere('pharmacy_id')?->pharmacy_id,
                'subtotal'     => $obtainable->sum('line_total'),
                'total_amount' => $obtainable->sum('line_total') + $order->delivery_fee,
            ]);

            $this->record($order, $manager, $this->verdictMessage($status, $obtainable->count(), $items->count()));

            return $order->refresh();
        });
    }

    // ── Étape 7 : attribution du livreur ─────────────────────────────────

    /** Le manager attribue la commande à un livreur proche. */
    public function assignCourier(Order $order, User $courier, User $manager, ?int $etaMinutes = null): Order
    {
        $this->assertStatus($order, [OrderStatus::AVAILABLE, OrderStatus::PARTIALLY_AVAILABLE]);

        if (! $courier->isCourier()) {
            throw ValidationException::withMessages(['courier_id' => "Cet utilisateur n'est pas un livreur."]);
        }

        return DB::transaction(function () use ($order, $courier, $manager, $etaMinutes) {
            DeliveryAssignment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'courier_id'  => $courier->id,
                    'assigned_by' => $manager->id,
                    'status'      => AssignmentStatus::ASSIGNED,
                    'assigned_at' => now(),
                ]
            );

            $order->update([
                'status'        => OrderStatus::COURIER_ASSIGNED,
                'assigned_at'   => now(),
                'eta_minutes'   => $etaMinutes ?? $this->estimateEta($order, $courier),
                'delivery_code' => $order->delivery_code ?? str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            ]);

            $courier->notify(new NewAssignmentNotification($order));

            $this->record(
                $order,
                $manager,
                "Course attribuée à {$courier->short_name} ({$courier->phone})."
            );

            return $order->refresh();
        });
    }

    // ── Étapes 8 à 12 : la course du livreur ─────────────────────────────

    /** Fait avancer la course d'une étape (en route → arrivé → récupéré → vers le client → livré). */
    public function advanceDelivery(Order $order, User $courier): Order
    {
        $next = $order->status->nextCourierStep();

        if ($next === null) {
            throw ValidationException::withMessages([
                'status' => "Cette commande n'attend plus d'action de votre part.",
            ]);
        }

        return DB::transaction(function () use ($order, $courier, $next) {
            $order->update(array_filter([
                'status'       => $next,
                'delivered_at' => $next === OrderStatus::DELIVERED ? now() : null,
            ], fn ($value) => $value !== null));

            $order->assignment?->update([
                'status' => match ($next) {
                    OrderStatus::TO_PHARMACY               => AssignmentStatus::ACCEPTED,
                    OrderStatus::AT_PHARMACY,
                    OrderStatus::PICKED_UP,
                    OrderStatus::TO_CLIENT                 => AssignmentStatus::DELIVERING,
                    OrderStatus::DELIVERED                 => AssignmentStatus::DELIVERED,
                    default                                => AssignmentStatus::ASSIGNED,
                },
                'responded_at' => now(),
            ]);

            $this->record($order, $courier, $next->label());

            return $order->refresh();
        });
    }

    /** Le livreur refuse la course : elle repart en attente d'attribution. */
    public function refuseAssignment(Order $order, User $courier, ?string $note = null): Order
    {
        $this->assertStatus($order, [OrderStatus::COURIER_ASSIGNED]);

        return DB::transaction(function () use ($order, $courier, $note) {
            $order->assignment?->update([
                'status'       => AssignmentStatus::REFUSED,
                'responded_at' => now(),
                'note'         => $note,
            ]);

            // On restitue le verdict de disponibilité, pas un « tout disponible »
            // de façade : une commande partielle le reste après un refus.
            $order->update([
                'status'      => $this->availabilityVerdict($order->items()->get()),
                'assigned_at' => null,
                'eta_minutes' => null,
            ]);

            $this->record($order, $courier, "Course refusée par {$courier->short_name}. À réattribuer.", notify: false);

            return $order->refresh();
        });
    }

    // ── Étape 13 : notation du livreur ───────────────────────────────────

    /**
     * @param  array<int, string>  $tags
     */
    public function rate(Order $order, User $client, int $rating, ?string $comment = null, array $tags = []): CourierReview
    {
        $this->assertStatus($order, [OrderStatus::DELIVERED]);

        $courier = $order->assignment?->courier;

        if (! $courier) {
            throw ValidationException::withMessages([
                'rating' => "Cette commande n'a pas de livreur à noter.",
            ]);
        }

        return DB::transaction(function () use ($order, $client, $courier, $rating, $comment, $tags) {
            $review = CourierReview::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'courier_id' => $courier->id,
                    'client_id'  => $client->id,
                    'rating'     => $rating,
                    'comment'    => $comment,
                    'tags'       => array_values(array_intersect($tags, CourierReview::TAGS)),
                ]
            );

            $order->update(['status' => OrderStatus::RATED]);

            $this->record(
                $order,
                $client,
                "{$client->short_name} a noté {$courier->short_name} {$rating} étoile(s) sur 5.",
                notify: false
            );

            return $review;
        });
    }

    // ── Étape 14 : annulation ────────────────────────────────────────────

    public function cancel(Order $order, User $actor, ?string $reason = null): Order
    {
        if ($order->status->isFinal() || $order->status === OrderStatus::DELIVERED) {
            throw ValidationException::withMessages([
                'status' => 'Cette commande ne peut plus être annulée.',
            ]);
        }

        return DB::transaction(function () use ($order, $actor, $reason) {
            $order->update([
                'status'        => OrderStatus::CANCELED,
                'canceled_at'   => now(),
                'canceled_by'   => $actor->id,
                'cancel_reason' => $reason,
            ]);

            $this->record(
                $order,
                $actor,
                'Commande annulée par ' . ($actor->is($order->client) ? 'le client' : 'le manager')
                    . ($reason ? " : {$reason}" : '.')
            );

            return $order->refresh();
        });
    }

    // ── Interne ──────────────────────────────────────────────────────────

    /**
     * Journalise l'événement et prévient le client.
     * Le journal est la source de la timeline client et du flux « Activité en direct ».
     */
    private function record(Order $order, ?User $actor, string $message, bool $notify = true): OrderEvent
    {
        $event = $order->events()->create([
            'user_id' => $actor?->id,
            'status'  => $order->status,
            'message' => $message,
        ]);

        if ($notify) {
            $order->client?->notify(new OrderProgressNotification($order));
        }

        return $event;
    }

    /** @param  array<int, OrderStatus>  $allowed */
    private function assertStatus(Order $order, array $allowed): void
    {
        if (! in_array($order->status, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => "Action impossible : la commande est au statut « {$order->status->badge()} ».",
            ]);
        }
    }

    private function verdictMessage(OrderStatus $status, int $obtainable, int $total): string
    {
        return match ($status) {
            OrderStatus::AVAILABLE           => "Disponibilité confirmée pour les {$total} médicaments.",
            OrderStatus::PARTIALLY_AVAILABLE => "{$obtainable} médicament(s) sur {$total} disponibles chez nos partenaires.",
            default                          => 'Aucun médicament disponible chez nos partenaires ce soir.',
        };
    }

    /** Délai estimé : trajet du livreur vers la pharmacie puis vers le client. */
    private function estimateEta(Order $order, User $courier): int
    {
        $toPharmacy = $courier->distanceToKm($order->pharmacy?->latitude, $order->pharmacy?->longitude);

        // 4 min de préparation en pharmacie + 3 min par kilomètre en deux-roues.
        return (int) max(10, round(4 + (($toPharmacy ?? 3.0) * 2 * 3)));
    }
}

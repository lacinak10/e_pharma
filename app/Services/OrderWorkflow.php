<?php

namespace App\Services;

use App\Enums\ItemAvailability;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\CourierReview;
use App\Models\DeliveryAssignment;
use App\Models\Medicine;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Pharmacy;
use App\Models\User;
use App\Notifications\NewAssignmentNotification;
use App\Notifications\OrderProgressNotification;
use App\Notifications\PaymentStatusNotification;
use App\Services\GeniusPay\GeniusPayClient;
use App\Services\GeniusPay\GeniusPayException;
use App\Enums\AssignmentStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    public function __construct(private GeniusPayClient $geniusPay)
    {
    }

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

        $order = DB::transaction(function () use ($order, $manager) {
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

        /*
         * Le lien de paiement naît ici, et pas au checkout : avant le verdict,
         * `total_amount` est indicatif — facturer un médicament qui se révèle
         * introuvable obligerait à rembourser, geste que l'API Marchand
         * GeniusPay n'expose pas.
         *
         * L'appel réseau est volontairement hors transaction : il ne doit pas
         * tenir de verrou sur la commande pendant plusieurs secondes. Son échec
         * ne remet pas le verdict en cause, le lien se régénère à la demande.
         */
        if ($order->requiresPrepayment() && $order->status->isVerdictFavorable()) {
            try {
                $this->requestPayment($order);
            } catch (GeniusPayException $e) {
                Log::channel('geniuspay')->error('Création du lien de paiement en échec', [
                    'order_id' => $order->id,
                    'code'     => $e->errorCode,
                    'message'  => $e->getMessage(),
                ]);
            }
        }

        return $order;
    }

    // ── Étape 7 : attribution du livreur ─────────────────────────────────

    /** Le manager attribue la commande à un livreur proche. */
    public function assignCourier(Order $order, User $courier, User $manager, ?int $etaMinutes = null): Order
    {
        $this->assertStatus($order, [OrderStatus::AVAILABLE, OrderStatus::PARTIALLY_AVAILABLE]);

        if (! $courier->isCourier()) {
            throw ValidationException::withMessages(['courier_id' => "Cet utilisateur n'est pas un livreur."]);
        }

        // Le livreur avance l'argent en pharmacie : il ne part pas sur une
        // commande à régler en ligne dont le paiement n'est pas encaissé.
        if ($order->awaitsPayment()) {
            throw ValidationException::withMessages([
                'courier_id' => 'Cette commande se règle en ligne et n\'est pas encore payée.',
            ]);
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

    // ── Encaissement en ligne (GeniusPay) ────────────────────────────────

    /**
     * Demande un lien de paiement pour une commande dont le verdict est rendu.
     *
     * Le montant envoyé est figé dans la ligne `payments` : c'est lui, et non
     * `orders.total_amount`, que le webhook devra retrouver. Le panier n'étant
     * plus modifiable après le verdict (cf. assertComposable), les deux ne
     * peuvent plus diverger — mais la vérification reste la bonne discipline.
     *
     * @param  bool  $force  régénère un lien même si le précédent est valide
     *
     * @throws GeniusPayException
     */
    public function requestPayment(Order $order, bool $force = false): ?Payment
    {
        if (! $order->requiresPrepayment()) {
            return null;
        }

        if ($paid = $order->payments()->completed()->first()) {
            return $paid;
        }

        $pending = $order->payments()->awaiting()->first();

        // Un lien encore valide pour le même montant est réutilisé : en créer
        // un second n'apporterait rien et brouillerait le suivi.
        if (! $force && $pending?->isUsable() && $pending->amount === (int) $order->total_amount) {
            return $pending;
        }

        $data = $this->geniusPay->createPayment($order);

        return DB::transaction(function () use ($order, $data, $pending) {
            // Le lien remplacé est clos pour que `payment()`, qui retourne le
            // plus récent, désigne bien celui que le client doit utiliser.
            $pending?->update([
                'status' => $pending->expires_at?->isPast() ? PaymentStatus::EXPIRED : PaymentStatus::CANCELLED,
            ]);

            $payment = $order->payments()->create([
                'provider'     => 'geniuspay',
                'reference'    => $data['reference'],
                'status'       => PaymentStatus::tryFrom($data['status']) ?? PaymentStatus::PENDING,
                'amount'       => $data['amount'],
                'currency'     => 'XOF',
                'method'       => $data['method'],
                'environment'  => $data['environment'],
                'checkout_url' => $data['checkout_url'],
                'expires_at'   => $data['expires_at'] ? Carbon::parse($data['expires_at']) : null,
            ]);

            $this->record(
                $order,
                null,
                'Lien de paiement en ligne transmis au client (' . number_format($payment->amount, 0, ',', ' ') . ' FCFA).',
                notify: false,
            );

            Log::channel('geniuspay')->info('Lien de paiement créé', [
                'order_id'  => $order->id,
                'reference' => $payment->reference,
                'amount'    => $payment->amount,
            ]);

            return $payment;
        });
    }

    /**
     * Applique le sort d'un paiement — point d'entrée unique du webhook, de la
     * page de retour et de la réconciliation.
     *
     * Idempotent par construction : rejouer un événement ne produit ni seconde
     * écriture ni doublon dans la timeline, et un paiement encaissé ne redescend
     * jamais d'un cran (seul un remboursement le fait bouger).
     *
     * @param  array{method?: ?string, reason?: ?string, payload?: array<string, mixed>}  $context
     */
    public function applyPaymentStatus(Payment $payment, PaymentStatus $status, array $context = []): Payment
    {
        return DB::transaction(function () use ($payment, $status, $context) {
            // Webhook et réconciliation peuvent arriver en même temps.
            $payment = Payment::whereKey($payment->getKey())->lockForUpdate()->firstOrFail();

            if ($payment->status === $status) {
                return $payment;
            }

            if ($payment->isCompleted() && $status !== PaymentStatus::REFUNDED) {
                Log::channel('geniuspay')->warning('Statut ignoré sur un paiement déjà encaissé', [
                    'reference' => $payment->reference,
                    'refused'   => $status->value,
                ]);

                return $payment;
            }

            $attributes = ['status' => $status];

            if (array_key_exists('payload', $context)) {
                $attributes['last_payload'] = $context['payload'];
            }

            if (! empty($context['method'])) {
                $attributes['method'] = $context['method'];
            }

            if ($status === PaymentStatus::COMPLETED) {
                $attributes['paid_at']        = now();
                $attributes['failure_reason'] = null;
            } elseif (! empty($context['reason'])) {
                $attributes['failure_reason'] = $context['reason'];
            }

            $payment->update($attributes);
            $payment->refresh();

            /*
             * « pending → processing » n'apprend rien à personne : on ne
             * journalise et ne notifie que les issues, pour ne pas noyer la
             * timeline client ni le flux « Activité en direct ».
             */
            if (! $status->isAwaiting()) {
                $order = $payment->order;

                $this->record($order, null, $this->paymentMessage($payment), notify: false);
                $order->client?->notify(new PaymentStatusNotification($payment));
            }

            Log::channel('geniuspay')->info('Statut de paiement appliqué', [
                'reference' => $payment->reference,
                'status'    => $status->value,
            ]);

            return $payment;
        });
    }

    /** Phrase journalisée dans la timeline pour chaque issue de paiement. */
    private function paymentMessage(Payment $payment): string
    {
        $amount = number_format($payment->amount, 0, ',', ' ');

        return match ($payment->status) {
            PaymentStatus::COMPLETED => "Paiement de {$amount} FCFA confirmé par {$payment->method_label}.",
            PaymentStatus::REFUNDED  => "Paiement de {$amount} FCFA remboursé au client.",
            PaymentStatus::FAILED    => 'Paiement refusé' . ($payment->failure_reason ? " : {$payment->failure_reason}" : '.'),
            PaymentStatus::CANCELLED => 'Paiement interrompu par le client.',
            PaymentStatus::EXPIRED   => 'Lien de paiement expiré sans règlement.',
            default                  => "Paiement en cours de traitement ({$amount} FCFA).",
        };
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

<?php

namespace App\Services;

use App\Enums\ItemAvailability;
use App\Enums\OrderStatus;
use App\Models\Medicine;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderProgressNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class OrderService
{
    private const DELIVERY_FEE = 1500;

    /**
     * Crée une commande à partir du panier session.
     *
     * @param  array<int, array{id: int, qty: int}>  $cart
     * @param  array<string, mixed>                  $data
     */
    public function createFromCart(int $userId, array $cart, array $data): Order
    {
        return DB::transaction(function () use ($userId, $cart, $data) {
            /*
             * ePharma ne détient pas de stock : rien n'est réservé ici. Le panier
             * exprime une demande, et c'est le manager qui établira la
             * disponibilité réelle en appelant les pharmacies partenaires.
             * Les montants sont donc indicatifs jusqu'au verdict.
             */
            $medicines = Medicine::query()
                ->whereIn('id', array_keys($cart))
                ->get()
                ->keyBy('id');

            $lines = collect($cart)
                ->map(fn (array $line) => [$medicines->get($line['id']), (int) $line['qty']])
                ->filter(fn (array $pair) => $pair[0]?->is_active);

            if ($lines->isEmpty()) {
                abort(400, "Aucun des médicaments demandés n'est référencé au catalogue.");
            }

            $subtotal = $lines->sum(fn (array $pair) => (int) $pair[0]->price * $pair[1]);
            $order    = $this->openOrder($userId, $data, $subtotal);

            foreach ($lines as [$medicine, $quantity]) {
                OrderItem::create([
                    'order_id'              => $order->id,
                    'medicine_id'           => $medicine->id,
                    'medicine_name'         => $medicine->name,
                    'unit_price'            => (int) $medicine->price,
                    'quantity'              => $quantity,
                    'line_total'            => (int) $medicine->price * $quantity,
                    'availability'          => ItemAvailability::PENDING,
                    'requires_prescription' => (bool) $medicine->requires_prescription,
                ]);
            }

            return $this->announce($order);
        });
    }

    /**
     * Crée une commande depuis une ordonnance téléversée.
     * Le contenu exact sera établi par le manager après lecture de l'ordonnance.
     *
     * @param  array<string, mixed>  $data  delivery_address, delivery_phone, notes,
     *                                      payment_method, prescription_scope, prescription_comment
     */
    public function createFromPrescription(int $userId, string $prescriptionPath, array $data): Order
    {
        return DB::transaction(function () use ($userId, $prescriptionPath, $data) {
            $order = $this->openOrder($userId, $data, subtotal: 0, prescription: [
                'has_prescription'     => true,
                'prescription_path'    => $prescriptionPath,
                'prescription_scope'   => $data['prescription_scope'] ?? 'all',
                'prescription_comment' => $data['prescription_comment'] ?? null,
            ]);

            return $this->announce($order);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $prescription
     */
    private function openOrder(int $userId, array $data, int $subtotal, array $prescription = []): Order
    {
        return Order::create([
            'user_id'          => $userId,
            'status'           => OrderStatus::PENDING_VALIDATION,
            'delivery_address' => $data['delivery_address'],
            'delivery_phone'   => $data['delivery_phone'] ?? null,
            'notes'            => $data['notes'] ?? null,
            'payment_method'   => $data['payment_method'],
            'subtotal'         => $subtotal,
            'delivery_fee'     => self::DELIVERY_FEE,
            'total_amount'     => $subtotal + self::DELIVERY_FEE,
            ...$prescription,
        ]);
    }

    /**
     * Ouvre le journal de la commande, confirme au client et alerte les managers.
     * C'est le message n° 1 du parcours : « prise en charge, en attente de validation ».
     */
    private function announce(Order $order): Order
    {
        $order->events()->create([
            'user_id' => $order->user_id,
            'status'  => OrderStatus::PENDING_VALIDATION,
            'message' => 'Commande reçue et en attente de validation par le manager.',
        ]);

        $order->load('client');
        $order->client?->notify(new OrderProgressNotification($order));

        Notification::send(
            User::where('role', User::ROLE_MANAGER)->get(),
            new NewOrderNotification($order)
        );

        return $order;
    }
}

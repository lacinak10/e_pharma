<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Medicine;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(private StockService $stockService)
    {
    }

    /**
     * Crée une commande à partir du panier session.
     *
     * @param  int   $userId
     * @param  array $cart    [medicine_id => ['id', 'qty', ...]]
     * @param  array $data    Champs validés (delivery_address, delivery_phone, notes, payment_method)
     * @return Order
     */
    public function createFromCart(int $userId, array $cart, array $data): Order
    {
        return DB::transaction(function () use ($userId, $cart, $data) {
            $subtotal = 0;

            // Verrouille les médicaments pour éviter les race conditions
            $medicines = Medicine::query()
                ->whereIn('id', array_keys($cart))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($cart as $line) {
                $medicine = $medicines->get($line['id']);

                if (!$medicine || !$medicine->is_active) {
                    abort(400, "Un article n'est plus disponible.");
                }

                if ($medicine->stock < (int) $line['qty']) {
                    abort(400, "Stock insuffisant pour {$medicine->name}. Disponible : {$medicine->stock}.");
                }

                $subtotal += ((int) $medicine->price) * ((int) $line['qty']);
            }

            $deliveryFee = 1500;
            $total       = $subtotal + $deliveryFee;

            $order = Order::create([
                'user_id'          => $userId,
                'status'           => OrderStatus::PENDING_ASSIGNMENT,
                'delivery_address' => $data['delivery_address'],
                'delivery_phone'   => $data['delivery_phone'] ?? null,
                'notes'            => $data['notes'] ?? null,
                'payment_method'   => $data['payment_method'],
                'subtotal'         => $subtotal,
                'delivery_fee'     => $deliveryFee,
                'total_amount'     => $total,
            ]);

            foreach ($cart as $line) {
                $medicine = $medicines->get($line['id']);

                OrderItem::create([
                    'order_id'      => $order->id,
                    'medicine_id'   => $medicine->id,
                    'medicine_name' => $medicine->name,
                    'unit_price'    => (int) $medicine->price,
                    'quantity'      => (int) $line['qty'],
                    'line_total'    => ((int) $medicine->price) * ((int) $line['qty']),
                ]);

                $medicine->decrement('stock', (int) $line['qty']);
                $this->stockService->updateStatus($medicine->fresh());
            }

            return $order;
        });
    }

    /**
     * Annule une commande et restaure le stock.
     */
    public function cancel(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->update([
                'status'      => OrderStatus::CANCELED,
                'canceled_at' => now(),
            ]);

            $order->load('items.medicine');
            $this->stockService->restoreOrderStock($order);
        });
    }
}

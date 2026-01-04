<?php

namespace App\Http\Controllers\Courier;

use App\Enums\AssignmentStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CourierDecisionRequest;
use App\Http\Requests\CourierStatusUpdateRequest;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::query()
            ->whereHas('assignment', function ($q) {
                $q->where('courier_id', auth()->id());
            })
            ->with(['client', 'assignment', 'items.medicine'])
            ->latest()
            ->paginate(20);

        return view('courier.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['client', 'assignment', 'items.medicine']);

        return view('courier.orders.show', compact('order'));
    }

    /**
     * Changement statut livraison (resource update)
     * - IN_DELIVERY (si ACCEPTED)
     * - DELIVERED (si IN_DELIVERY)
     */
    public function update(CourierStatusUpdateRequest $request, Order $order)
    {
        $this->authorize('courierUpdateStatus', $order);

        $status = OrderStatus::from($request->validated()['status']);

        DB::transaction(function () use ($order, $status) {
            $order->refresh();
            $order->load('assignment');

            if (!$order->assignment || $order->assignment->courier_id !== auth()->id()) {
                abort(403);
            }

            if ($order->assignment->status !== AssignmentStatus::ACCEPTED) {
                abort(422, 'Vous devez d’abord accepter la commande.');
            }

            if ($status === OrderStatus::IN_DELIVERY) {
                if ($order->status !== OrderStatus::ACCEPTED) {
                    abort(422, 'Transition invalide.');
                }

                $order->update(['status' => OrderStatus::IN_DELIVERY]);
            }

            if ($status === OrderStatus::DELIVERED) {
                if ($order->status !== OrderStatus::IN_DELIVERY) {
                    abort(422, 'Transition invalide.');
                }

                $order->update([
                    'status' => OrderStatus::DELIVERED,
                    'delivered_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Statut de commande mis à jour.');
    }

    public function accept(CourierDecisionRequest $request, Order $order)
    {
        $this->authorize('courierRespond', $order);

        $note = $request->validated()['note'] ?? null;

        DB::transaction(function () use ($order, $note) {
            $order->refresh();
            $order->load('assignment');

            if (!$order->assignment || $order->assignment->courier_id !== auth()->id()) {
                abort(403);
            }

            if ($order->status !== OrderStatus::ASSIGNED || $order->assignment->status !== AssignmentStatus::ASSIGNED) {
                abort(422, 'Vous ne pouvez plus accepter cette commande.');
            }

            $order->assignment->update([
                'status' => AssignmentStatus::ACCEPTED,
                'responded_at' => now(),
                'note' => $note,
            ]);

            $order->update(['status' => OrderStatus::ACCEPTED]);
        });

        return back()->with('success', 'Commande acceptée.');
    }

    public function refuse(CourierDecisionRequest $request, Order $order)
    {
        $this->authorize('courierRespond', $order);

        $note = $request->validated()['note'] ?? null;

        DB::transaction(function () use ($order, $note) {
            $order->refresh();
            $order->load('assignment');

            if (!$order->assignment || $order->assignment->courier_id !== auth()->id()) {
                abort(403);
            }

            if ($order->status !== OrderStatus::ASSIGNED || $order->assignment->status !== AssignmentStatus::ASSIGNED) {
                abort(422, 'Vous ne pouvez plus refuser cette commande.');
            }

            $order->assignment->update([
                'status' => AssignmentStatus::REFUSED,
                'responded_at' => now(),
                'note' => $note,
            ]);

            $order->update(['status' => OrderStatus::REFUSED]);
        });

        return back()->with('success', 'Commande refusée.');
    }
}

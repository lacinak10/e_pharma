<?php

namespace App\Http\Controllers\Manager;

use App\Enums\AssignmentStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignCourierRequest;
use App\Models\DeliveryAssignment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::query()
            ->with(['client', 'assignment.courier'])
            ->latest()
            ->paginate(20);

        $couriers = User::query()
            ->where('role', User::ROLE_COURIER)
            ->orderBy('name')
            ->get();

        return view('manager.orders.index', compact('orders', 'couriers'));
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['client', 'items.medicine', 'assignment.courier']);

        $couriers = User::query()
            ->where('role', User::ROLE_COURIER)
            ->orderBy('name')
            ->get();

        return view('manager.orders.show', compact('order', 'couriers'));
    }

    /**
     * Affectation livreur (resource update)
     */
    public function update(AssignCourierRequest $request, Order $order)
    {
        $this->authorize('assign', $order);

        $data = $request->validated();

        DB::transaction(function () use ($order, $data) {
            $order->refresh();
            $order->load('assignment');

            // blocages
            if (in_array($order->status, [OrderStatus::DELIVERED, OrderStatus::CANCELED, OrderStatus::IN_DELIVERY], true)) {
                abort(422, 'Impossible d’affecter cette commande (statut non compatible).');
            }

            if ($order->assignment && $order->assignment->status === AssignmentStatus::ACCEPTED) {
                abort(422, 'Commande déjà acceptée par un livreur, réaffectation impossible.');
            }

            DeliveryAssignment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'courier_id' => (int) $data['courier_id'],
                    'assigned_by' => auth()->id(),
                    'status' => AssignmentStatus::ASSIGNED,
                    'assigned_at' => now(),
                    'responded_at' => null,
                    'note' => $data['note'] ?? null,
                ]
            );

            $order->update(['status' => OrderStatus::ASSIGNED]);
        });

        return back()->with('success', 'Commande affectée au livreur.');
    }
}

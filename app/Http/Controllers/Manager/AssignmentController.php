<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAssignment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\OrderStatus;


class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $status = trim((string)$request->get('status',''));

        $assignments = DeliveryAssignment::query()
            ->with(['order.user:id,name', 'courier:id,name', 'assigner:id,name'])
            ->when($status !== '', fn($q) => $q->where('status',$status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.assignments.index', compact('assignments','status'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['required','exists:orders,id'],
            'courier_id' => ['required','exists:users,id'],
            'note' => ['nullable','string','max:255'],
        ]);

        $order = Order::findOrFail($validated['order_id']);

        DeliveryAssignment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'courier_id' => $validated['courier_id'],
                'assigned_by' => Auth::id(),
                'status' => 'assigned',
                'assigned_at' => now(),
                'note' => $validated['note'] ?? null,
            ]
        );

        // Met à jour statut commande
        $order->update(['status' => OrderStatus::ASSIGNED]);

        return back()->with('success','Livreur affecté.');
    }
}

<?php

namespace App\Http\Controllers\Manager;

use App\Enums\AssignmentStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\DeliveryAssignment;
use App\Models\Order;
use App\Models\User;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $status = trim((string) $request->get('status', ''));

        $assignments = DeliveryAssignment::query()
            ->with(['order.user:id,name', 'courier:id,name', 'assigner:id,name'])
            ->when($status !== '', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.assignments.index', compact('assignments', 'status'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id'   => ['required', 'exists:orders,id'],
            'courier_id' => ['required', Rule::exists('users', 'id')->where('role', 'courier')],
            'note'       => ['nullable', 'string', 'max:255'],
        ]);

        $order = Order::findOrFail($validated['order_id']);

        DeliveryAssignment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'courier_id'  => $validated['courier_id'],
                'assigned_by' => Auth::id(),
                'status'      => AssignmentStatus::ASSIGNED,
                'assigned_at' => now(),
                'note'        => $validated['note'] ?? null,
            ]
        );

        $order->update(['status' => OrderStatus::ASSIGNED]);

        // Notifier le livreur
        $courier = User::find($validated['courier_id']);
        $courier?->notify(new NewAssignmentNotification($order));

        return back()->with('success', 'Livreur affecté.');
    }
}

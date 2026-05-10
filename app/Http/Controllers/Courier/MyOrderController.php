<?php

namespace App\Http\Controllers\Courier;

use App\Enums\AssignmentStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class MyOrderController extends Controller
{
    public function index(Request $request)
    {
        $status = trim((string) $request->get('status', ''));

        $orders = Order::query()
            ->with(['user:id,name', 'assignment'])
            ->whereHas('assignment', fn($q) => $q->where('courier_id', Auth::id()))
            ->when($status !== '', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.my_orders.index', compact('orders', 'status'));
    }

    public function show(Order $order)
    {
        $order->load(['user:id,name', 'items.medicine:id,name,price', 'assignment']);

        abort_unless(optional($order->assignment)->courier_id === Auth::id(), 403);

        return view('admin.my_orders.show', compact('order'));
    }

    public function accept(Order $order)
    {
        $assignment = $order->assignment;
        abort_unless($assignment && $assignment->courier_id === Auth::id(), 403);
        abort_unless($assignment->status === AssignmentStatus::ASSIGNED, 403);

        $assignment->update([
            'status'       => AssignmentStatus::ACCEPTED,
            'responded_at' => now(),
        ]);

        $order->update(['status' => OrderStatus::ACCEPTED]);

        $managers = User::where('role', User::ROLE_MANAGER)->get();
        Notification::send($managers, new OrderStatusChangedNotification($order, 'accepted'));

        return back()->with('success', 'Commande acceptée.');
    }

    public function refuse(Request $request, Order $order)
    {
        $assignment = $order->assignment;
        abort_unless($assignment && $assignment->courier_id === Auth::id(), 403);
        abort_unless($assignment->status === AssignmentStatus::ASSIGNED, 403);

        $assignment->update([
            'status'       => AssignmentStatus::REFUSED,
            'responded_at' => now(),
        ]);

        $order->update(['status' => OrderStatus::PENDING_ASSIGNMENT]);

        $managers = User::where('role', User::ROLE_MANAGER)->get();
        Notification::send($managers, new OrderStatusChangedNotification($order, 'refused'));

        return back()->with('success', 'Commande refusée.');
    }

    public function startDelivery(Order $order)
    {
        $assignment = $order->assignment;
        abort_unless($assignment && $assignment->courier_id === Auth::id(), 403);
        abort_unless($assignment->status === AssignmentStatus::ACCEPTED, 403);

        $assignment->update(['status' => AssignmentStatus::DELIVERING]);
        $order->update(['status' => OrderStatus::IN_DELIVERY]);

        $managers = User::where('role', User::ROLE_MANAGER)->get();
        Notification::send($managers, new OrderStatusChangedNotification($order, 'in_delivery'));

        return back()->with('success', 'Livraison démarrée.');
    }

    public function markDelivered(Order $order)
    {
        $assignment = $order->assignment;
        abort_unless($assignment && $assignment->courier_id === Auth::id(), 403);
        abort_unless($assignment->status === AssignmentStatus::DELIVERING, 403);

        $assignment->update(['status' => AssignmentStatus::DELIVERED]);
        $order->update([
            'status'       => OrderStatus::DELIVERED,
            'delivered_at' => now(),
        ]);

        $managers = User::where('role', User::ROLE_MANAGER)->get();
        Notification::send($managers, new OrderStatusChangedNotification($order, 'delivered'));

        return back()->with('success', 'Livraison confirmée.');
    }

    public function downloadPrescription(Order $order)
    {
        $assignment = $order->assignment;
        abort_unless($assignment && $assignment->courier_id === Auth::id(), 403);
        abort_unless($order->has_prescription && $order->prescription_path, 404);

        return Storage::disk('local')->download($order->prescription_path);
    }
}

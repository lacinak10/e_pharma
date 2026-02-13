<?php

namespace App\Http\Controllers\Courier;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\DeliveryAssignment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class MyOrderController extends Controller
{
    public function index(Request $request)
    {
        $status = trim((string)$request->get('status',''));

        $orders = Order::query()
            ->with(['user:id,name', 'assignment'])
            ->whereHas('assignment', fn($q) => $q->where('courier_id', Auth::id()))
            ->when($status !== '', fn($q) => $q->where('status',$status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.my_orders.index', compact('orders','status'));
    }

    public function show(Order $order)
    {
        $order->load(['user:id,name', 'items.medicine:id,name,price', 'assignment']);

        abort_unless(optional($order->assignment)->courier_id === Auth::id(), 403);

        return view('admin.my_orders.show', compact('order'));
    }

    public function accept(Order $order)
    {
        $a = $order->assignment;
        abort_unless($a && $a->courier_id === Auth::id(), 403);

        $a->update(['status'=>'accepted','responded_at'=>now()]);
        return back()->with('success','Commande acceptée.');
    }

    public function refuse(Request $request, Order $order)
    {
        $a = $order->assignment;
        abort_unless($a && $a->courier_id === Auth::id(), 403);

        $a->update(['status'=>'refused','responded_at'=>now()]);
        $order->update(['status'=>'pending_courier']);

        return back()->with('success','Commande refusée.');
    }

    public function startDelivery(Order $order)
    {
        $a = $order->assignment;
        abort_unless($a && $a->courier_id === Auth::id(), 403);

        $a->update(['status'=>'delivering']);
        $order->update(['status'=> OrderStatus::IN_DELIVERY]);

        return back()->with('success','Livraison démarrée.');
    }

    public function markDelivered(Order $order)
    {
        $a = $order->assignment;
        abort_unless($a && $a->courier_id === Auth::id(), 403);

        $a->update(['status'=>'delivered']);
        $order->update(['status'=> OrderStatus::DELIVERED,'delivered_at'=>now()]);

        return back()->with('success','Livraison confirmée.');
    }
}

<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $status = trim((string) $request->get('status', \App\Enums\OrderStatus::IN_DELIVERY->value));

        $orders = Order::query()
            ->with(['user:id,name', 'assignment.courier:id,name'])
            ->when($status !== '', fn($q) => $q->where('status',$status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.deliveries.index', compact('orders','status'));
    }
}

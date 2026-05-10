<?php

namespace App\Http\Controllers\Manager;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Enum;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q',''));
        $status = trim((string)$request->get('status',''));
        $withPrescription = $request->boolean('with_prescription');

        $orders = Order::query()
            ->with(['user:id,name', 'assignment.courier:id,name'])
            ->when($q !== '', fn($qq) => $qq->whereHas('user', fn($u) => $u->where('name','like',"%{$q}%")))
            ->when($status !== '', fn($qq) => $qq->where('status',$status))
            ->when($withPrescription, fn($qq) => $qq->where('has_prescription', true))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.orders.index', compact('orders','q','status','withPrescription'));
    }

    public function show(Order $order)
    {
        $order->load(['user:id,name', 'items.medicine:id,name,price', 'assignment.courier:id,name']);
        $couriers = User::where('role','courier')->orderBy('name')->get(['id','name']);

        return view('admin.orders.show', compact('order','couriers'));
    }


public function update(Request $request, Order $order)
{
    $validated = $request->validate([
        'status' => ['required', new Enum(OrderStatus::class)],
    ]);

    $order->update([
        'status' => OrderStatus::from($validated['status'])
    ]);

    return back()->with('success','Commande mise à jour.');
}

public function downloadPrescription(Order $order)
{
    abort_unless($order->has_prescription && $order->prescription_path, 404);

    return Storage::disk('local')->download($order->prescription_path);
}

}

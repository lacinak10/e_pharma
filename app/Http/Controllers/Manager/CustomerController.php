<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q',''));

        $customers = User::query()
            ->where('role','client')
            ->when($q !== '', fn($qq) => $qq->where('name','like',"%{$q}%"))
            ->withCount('orders')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.customers.index', compact('customers','q'));
    }

    public function show(User $user)
    {
        abort_unless($user->role === 'client', 404);

        $orders = Order::where('user_id',$user->id)->latest()->paginate(10);

        return view('admin.customers.show', compact('user','orders'));
    }
}

<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->where('has_prescription', true)
            ->with(['client:id,name,phone', 'items'])
            ->when($request->string('scope')->toString(), fn ($q, $scope) => $q->where('prescription_scope', $scope))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.prescriptions.index', compact('orders'));
    }
}

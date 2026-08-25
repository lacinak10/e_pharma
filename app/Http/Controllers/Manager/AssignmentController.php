<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Pharmacy;
use App\Models\User;
use App\Services\OrderWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Attribution du livreur — étape 7 du parcours.
 */
class AssignmentController extends Controller
{
    public function __construct(private OrderWorkflow $workflow)
    {
    }

    public function index(): View
    {
        $orders = Order::awaitingCourier()
            ->with(['client:id,name,phone', 'items', 'pharmacy:id,name,area,latitude,longitude'])
            ->oldest()
            ->get();

        $pharmacy = $orders->first()?->pharmacy ?? Pharmacy::active()->first();

        $couriers = User::couriers()
            ->withAvg('reviews as reviews_avg_rating', 'rating')
            ->withCount('reviews')
            ->get()
            ->map(function (User $courier) use ($pharmacy) {
                $courier->setAttribute('distance_km', $courier->distanceToKm($pharmacy?->latitude, $pharmacy?->longitude));
                $courier->setAttribute('state', $courier->courierState());
                $courier->setAttribute('load', $courier->activeDeliveriesCount());

                return $courier;
            })
            ->sortBy(fn (User $c) => [$c->state !== 'Disponible', $c->distance_km ?? 99])
            ->values();

        return view('admin.assignments.index', compact('orders', 'couriers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id'    => ['required', 'exists:orders,id'],
            'courier_id'  => ['required', Rule::exists('users', 'id')->where('role', User::ROLE_COURIER)],
            'eta_minutes' => ['nullable', 'integer', 'between:5,180'],
        ]);

        $order   = Order::findOrFail($validated['order_id']);
        $courier = User::findOrFail($validated['courier_id']);

        $this->authorize('assign', $order);

        $order = $this->workflow->assignCourier($order, $courier, Auth::user(), $validated['eta_minutes'] ?? null);

        return back()->with(
            'success',
            "{$courier->short_name} prend en charge {$order->reference}. Le client a reçu son nom, son numéro et le délai estimé."
        );
    }
}

<?php

namespace App\Http\Controllers\Courier;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Espace livreur — étapes 8 à 12 du parcours.
 */
class MyOrderController extends Controller
{
    public function __construct(private OrderWorkflow $workflow)
    {
    }

    public function index(Request $request): View
    {
        $filter = $request->string('filter')->toString() ?: 'all';

        $base = fn () => Order::query()
            ->whereHas('assignment', fn ($q) => $q->where('courier_id', Auth::id()))
            ->with(['client:id,name,phone', 'items', 'pharmacy:id,name,area,phone', 'assignment']);

        $orders = $base()
            ->when($filter === 'new', fn ($q) => $q->withStatus([OrderStatus::COURIER_ASSIGNED]))
            ->when($filter === 'active', fn ($q) => $q->inDelivery()->withStatus([
                OrderStatus::TO_PHARMACY, OrderStatus::AT_PHARMACY,
                OrderStatus::PICKED_UP, OrderStatus::TO_CLIENT,
            ]))
            ->when($filter === 'done', fn ($q) => $q->withStatus([OrderStatus::DELIVERED, OrderStatus::RATED]))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.my_orders.index', [
            'orders' => $orders,
            'filter' => $filter,
            'counts' => [
                'new'    => $base()->withStatus([OrderStatus::COURIER_ASSIGNED])->count(),
                'active' => $base()->inDelivery()->withStatus([
                    OrderStatus::TO_PHARMACY, OrderStatus::AT_PHARMACY,
                    OrderStatus::PICKED_UP, OrderStatus::TO_CLIENT,
                ])->count(),
                'done'   => $base()->withStatus([OrderStatus::DELIVERED, OrderStatus::RATED])->count(),
            ],
        ]);
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load([
            'client:id,name,phone',
            'items.medicine:id,name,pack,dosage',
            'pharmacy',
            'assignment.courier:id,name,phone',
            'events',
        ]);

        return view('admin.my_orders.show', compact('order'));
    }

    /** Le livreur accepte : la course démarre vers la pharmacie. */
    public function accept(Order $order): RedirectResponse
    {
        $this->authorize('courierRespond', $order);

        $this->workflow->advanceDelivery($order, Auth::user());

        return back()->with('success', 'Course acceptée. Direction la pharmacie.');
    }

    public function refuse(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('courierRespond', $order);

        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $this->workflow->refuseAssignment($order, Auth::user(), $validated['note'] ?? null);

        return redirect()
            ->route('courier.my_orders.index')
            ->with('success', 'Course refusée. Le manager va la réattribuer.');
    }

    /** Passe à l'étape suivante des cinq du suivi client. */
    public function advance(Order $order): RedirectResponse
    {
        $this->authorize('courierAdvance', $order);

        $order = $this->workflow->advanceDelivery($order, Auth::user());

        return back()->with('success', "Étape enregistrée : {$order->status->badge()}. Le client est prévenu.");
    }

    public function downloadPrescription(Order $order): mixed
    {
        $this->authorize('view', $order);
        abort_unless($order->has_prescription && $order->prescription_path, 404);

        return Storage::disk('local')->download($order->prescription_path);
    }
}

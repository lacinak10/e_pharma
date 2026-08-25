<?php

namespace App\Http\Controllers\Manager;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private OrderWorkflow $workflow)
    {
    }

    public function index(Request $request): View
    {
        $q                = $request->string('q')->toString();
        $status           = $request->string('status')->toString();
        $withPrescription = $request->boolean('with_prescription');

        $orders = Order::query()
            ->with(['client:id,name', 'items', 'assignment.courier:id,name'])
            ->when($q !== '', fn ($query) => $query->whereHas('client', fn ($u) => $u->where('name', 'like', "%{$q}%")))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($withPrescription, fn ($query) => $query->where('has_prescription', true))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders'           => $orders,
            'q'                => $q,
            'status'           => $status,
            'withPrescription' => $withPrescription,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load([
            'client:id,name,phone,email',
            'items.medicine:id,name,pack,dosage',
            'items.pharmacy:id,name,phone',
            'pharmacy',
            'assignment.courier:id,name,phone',
            'events.author:id,name',
            'review',
        ]);

        return view('admin.orders.show', [
            'order'    => $order,
            'couriers' => User::couriers()->orderBy('name')->get(['id', 'name', 'phone']),
        ]);
    }

    /**
     * Correction manuelle du statut — cas exceptionnels uniquement.
     * Les transitions qui ont des effets métier gardent leur chemin dédié
     * (vérification, attribution, espace livreur) afin de rester journalisées
     * et notifiées correctement.
     */
    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $target = OrderStatus::from($validated['status']);
        $reason = $validated['reason'] ?? null;

        return match ($target) {
            OrderStatus::CHECKING => tap(
                back()->with('success', 'Vérification lancée, le client est prévenu.'),
                fn () => $this->workflow->startChecking($order, Auth::user())
            ),
            OrderStatus::REFUSED => tap(
                back()->with('success', 'Commande refusée, le motif a été transmis au client.'),
                fn () => $this->workflow->refuse($order, Auth::user(), $reason ?: 'Commande refusée par le manager.')
            ),
            OrderStatus::CANCELED => tap(
                back()->with('success', 'Commande annulée, le client est prévenu.'),
                fn () => $this->workflow->cancel($order, Auth::user(), $reason)
            ),
            default => $this->forceStatus($order, $target),
        };
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $this->workflow->cancel($order, Auth::user(), $validated['reason'] ?? null);

        return back()->with('success', "Commande {$order->reference} annulée. Le client est prévenu.");
    }

    public function downloadPrescription(Order $order)
    {
        abort_unless($order->has_prescription && $order->prescription_path, 404);

        return Storage::disk('local')->download($order->prescription_path);
    }

    /** Réalignement manuel : journalisé, mais sans effet de bord métier. */
    private function forceStatus(Order $order, OrderStatus $target): RedirectResponse
    {
        $order->update(['status' => $target]);

        $order->events()->create([
            'user_id' => Auth::id(),
            'status'  => $target,
            'message' => "Statut corrigé manuellement par le manager : {$target->badge()}.",
        ]);

        return back()->with('success', "Statut passé à « {$target->badge()} ».");
    }
}

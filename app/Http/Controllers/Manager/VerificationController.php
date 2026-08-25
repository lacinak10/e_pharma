<?php

namespace App\Http\Controllers\Manager;

use App\Enums\ItemAvailability;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pharmacy;
use App\Services\OrderWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Vérification de disponibilité auprès des pharmacies partenaires —
 * le poste de travail central du manager (étapes 1 à 6 du parcours).
 */
class VerificationController extends Controller
{
    public function __construct(private OrderWorkflow $workflow)
    {
    }

    public function index(): View
    {
        $awaiting = Order::awaitingManager()
            ->with(['client:id,name,phone', 'items'])
            ->oldest()
            ->get();

        $checking = Order::beingChecked()
            ->with(['client:id,name,phone', 'items.medicine:id,name,pack,dosage', 'items.pharmacy:id,name,phone'])
            ->orderBy('check_deadline_at')
            ->get();

        return view('admin.verifications.index', [
            'awaiting'   => $awaiting,
            'checking'   => $checking,
            'pharmacies' => Pharmacy::active()->orderBy('name')->get(),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load([
            'client:id,name,phone,email',
            'items.medicine:id,name,pack,dosage,requires_prescription',
            'items.pharmacy:id,name,phone',
            'events.author:id,name',
        ]);

        return view('admin.verifications.show', [
            'order'      => $order,
            'pharmacies' => Pharmacy::active()->orderBy('name')->get(),
            'medicines'  => Medicine::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'pack', 'price', 'requires_prescription']),
        ]);
    }

    /** Étape 1 → 3 : le manager accepte, le chrono de 5 minutes démarre. */
    public function start(Order $order): RedirectResponse
    {
        $this->workflow->startChecking($order, Auth::user());

        return back()->with('success', "Vérification lancée pour {$order->reference}. Le client est prévenu : résultat sous 5 minutes.");
    }

    /** Étape 2 : refus motivé. */
    public function refuse(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ], [], ['reason' => 'motif']);

        $this->workflow->refuse($order, Auth::user(), $validated['reason']);

        return redirect()
            ->route('manager.verifications.index')
            ->with('success', "Commande {$order->reference} refusée. Le client a reçu le motif.");
    }

    /**
     * Compose le panier d'une commande sur ordonnance.
     *
     * L'image téléversée n'est pas exploitable automatiquement : le manager la
     * lit et saisit ici les médicaments correspondants, avant de les vérifier
     * ligne par ligne auprès des pharmacies partenaires.
     */
    public function storeItem(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'medicine_id' => ['required', Rule::exists('medicines', 'id')->where('is_active', true)],
            'quantity'    => ['required', 'integer', 'min:1', 'max:99'],
        ], [], [
            'medicine_id' => 'médicament',
            'quantity'    => 'quantité',
        ]);

        $medicine = Medicine::findOrFail($validated['medicine_id']);

        $this->workflow->addItem($order, $medicine, (int) $validated['quantity'], Auth::user());

        return back()->with('success', "{$medicine->name} ajouté à la commande {$order->reference}.");
    }

    /** Retire une ligne composée par erreur. */
    public function destroyItem(Order $order, OrderItem $item): RedirectResponse
    {
        abort_unless($item->order_id === $order->id, 404);

        $name = $item->medicine_name;

        $this->workflow->removeItem($item, Auth::user());

        return back()->with('success', "{$name} retiré de la commande.");
    }

    /** Retour d'une pharmacie sur une ligne de la commande. */
    public function updateItem(Request $request, Order $order, OrderItem $item): RedirectResponse
    {
        abort_unless($item->order_id === $order->id, 404);

        $validated = $request->validate([
            'availability'    => ['required', Rule::enum(ItemAvailability::class)],
            'pharmacy_id'     => ['nullable', 'exists:pharmacies,id'],
            'substitute_name' => ['nullable', 'string', 'max:120', 'required_if:availability,substituted'],
            'quantity'        => ['nullable', 'integer', 'min:1', 'max:99'],
        ], [], [
            'availability'    => 'verdict',
            'substitute_name' => 'substitut',
            'quantity'        => 'quantité',
        ]);

        if (isset($validated['quantity']) && (int) $validated['quantity'] !== $item->quantity) {
            $this->workflow->setItemQuantity($item, (int) $validated['quantity']);
        }

        $this->workflow->recordItemAvailability(
            $item,
            ItemAvailability::from($validated['availability']),
            isset($validated['pharmacy_id']) ? Pharmacy::find($validated['pharmacy_id']) : null,
            $validated['substitute_name'] ?? null,
        );

        return back()->with('success', "{$item->medicine_name} : {$item->fresh()->availability->label()}.");
    }

    /** Étapes 4 à 6 : le verdict est rendu et le client notifié. */
    public function settle(Order $order): RedirectResponse
    {
        $order = $this->workflow->settleVerdict($order, Auth::user());

        $message = match ($order->status) {
            OrderStatus::AVAILABLE           => "Commande {$order->reference} validée. Attribuez un livreur.",
            OrderStatus::PARTIALLY_AVAILABLE => "Disponibilité partielle transmise au client pour {$order->reference}.",
            default                          => "Indisponibilité transmise au client pour {$order->reference}.",
        };

        return redirect()
            ->route($order->status === OrderStatus::UNAVAILABLE ? 'manager.verifications.index' : 'manager.assignments.index')
            ->with('success', $message);
    }
}

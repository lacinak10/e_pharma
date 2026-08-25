<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PrescriptionController extends Controller
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function create()
    {
        return view('store.prescriptions.create');
    }

    public function store(Request $request)
    {
        $request->merge([
            'delivery_phone' => preg_replace('/\s+/', '', (string) $request->delivery_phone),
        ]);

        $data = $request->validate([
            'prescription'         => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'delivery_address'     => ['required', 'string', 'min:5', 'max:255'],
            'delivery_phone'       => ['required', 'string', 'regex:/^(\+225)?[0-9]{10}$/'],
            // Le client précise s'il veut toute l'ordonnance ou seulement une partie.
            'prescription_scope'   => ['required', 'in:all,partial'],
            'prescription_comment' => ['nullable', 'string', 'max:1000', 'required_if:prescription_scope,partial'],
            'notes'                => ['nullable', 'string', 'max:500'],
            'payment_method'       => ['required', 'in:cash,momo,card'],
        ], [
            'prescription_comment.required_if' => 'Précisez quels médicaments de l\'ordonnance vous souhaitez.',
        ], [
            'prescription'         => 'ordonnance',
            'delivery_address'     => 'adresse de livraison',
            'delivery_phone'       => 'téléphone',
            'prescription_scope'   => 'périmètre de la commande',
            'prescription_comment' => 'précision',
        ]);

        $file = $request->file('prescription');
        $ext  = $file->getClientOriginalExtension();
        $path = $file->storeAs('prescriptions', Str::uuid() . '.' . $ext, 'local');

        $order = $this->orderService->createFromPrescription(Auth::id(), $path, $data);

        return redirect()
            ->route('store.orders.show', $order)
            ->with('success', 'Ordonnance envoyée avec succès ! Votre commande a été créée.');
    }

    public function download(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        abort_unless($order->has_prescription && $order->prescription_path, 404);

        return Storage::disk('local')->download($order->prescription_path);
    }
}

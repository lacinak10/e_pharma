<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\CourierReview;
use App\Models\Order;
use App\Services\OrderWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Notation du livreur par le client — étape 13 du parcours.
 */
class ReviewController extends Controller
{
    public function __construct(private OrderWorkflow $workflow)
    {
    }

    public function store(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('review', $order);

        $validated = $request->validate([
            'rating'  => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'tags'    => ['nullable', 'array'],
            'tags.*'  => [Rule::in(CourierReview::TAGS)],
        ], [], [
            'rating'  => 'note',
            'comment' => 'commentaire',
        ]);

        $this->workflow->rate(
            $order,
            $request->user(),
            $validated['rating'],
            $validated['comment'] ?? null,
            $validated['tags'] ?? [],
        );

        return back()->with('success', 'Merci, votre note a bien été enregistrée.');
    }
}

<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\CourierReview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = CourierReview::query()
            ->with(['courier:id,name', 'client:id,name', 'order:id,delivery_address'])
            ->when($request->integer('courier'), fn ($q, $id) => $q->where('courier_id', $id))
            ->when($request->integer('rating'), fn ($q, $r) => $q->where('rating', $r))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $distribution = CourierReview::selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $count = (int) $distribution->sum();

        return view('admin.reviews.index', [
            'reviews'      => $reviews,
            'average'      => round((float) CourierReview::avg('rating'), 1),
            'count'        => $count,
            'distribution' => collect(range(5, 1))->mapWithKeys(fn (int $star) => [
                $star => $count > 0 ? (int) round(($distribution[$star] ?? 0) / $count * 100) : 0,
            ]),
            'couriers'     => User::couriers()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}

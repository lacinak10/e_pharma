<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\CourierReview;
use App\Models\Medicine;
use App\Models\Pharmacy;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('store.home', [
            'featured'  => Medicine::active()->withAvailabilitySignal()->with('category')
                ->orderByDesc('available_partners')->orderBy('name')->limit(10)->get(),
            'partners'  => Pharmacy::active()->orderByDesc('reliability')->limit(4)->get(),
            'testimonies' => CourierReview::with(['client:id,name', 'courier:id,name'])
                ->whereNotNull('comment')
                ->latest()
                ->limit(3)
                ->get(),
        ]);
    }

    public function partners(): View
    {
        return view('store.partners', [
            'pharmacies' => Pharmacy::active()->orderBy('area')->orderBy('name')->get(),
            'areas'      => Pharmacy::active()->distinct()->orderBy('area')->pluck('area'),
        ]);
    }

    public function reviews(): View
    {
        $distribution = CourierReview::selectRaw('rating, COUNT(*) as total')->groupBy('rating')->pluck('total', 'rating');
        $count        = (int) $distribution->sum();

        return view('store.reviews', [
            'reviews'      => CourierReview::with(['client:id,name', 'courier:id,name', 'order:id,delivery_address'])
                ->whereNotNull('comment')
                ->latest()
                ->paginate(12),
            'average'      => round((float) CourierReview::avg('rating'), 1),
            'count'        => $count,
            'distribution' => collect(range(5, 1))->mapWithKeys(fn (int $star) => [
                $star => $count > 0 ? (int) round(($distribution[$star] ?? 0) / $count * 100) : 0,
            ]),
        ]);
    }
}

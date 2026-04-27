<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Medicine;

class HomeController extends Controller
{
    public function index()
    {
        $featured = Medicine::query()
            ->where('is_active', true)
            ->with('category')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('store.home', [
            'featured' => $featured,
        ]);
    }
}

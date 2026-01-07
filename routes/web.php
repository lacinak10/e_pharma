<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\Client\OrderController as ClientOrderController;
use App\Http\Controllers\Courier\OrderController as CourierOrderController;
use App\Http\Controllers\Manager\MedicineController;
use App\Http\Controllers\Manager\OrderController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return  view('home');
});

// Catalogue public
Route::resource('catalog', CatalogController::class)
    ->only(['index', 'show']);

// Auth (Breeze)
require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {

    // // Panier (client) - on bind {cart} => Medicine via parameters()
    // Route::resource('cart', CartController::class)
    //     ->only(['index', 'store', 'update', 'destroy'])
    //     ->parameters(['cart' => 'medicine']);

    // // Espace Client
    // Route::prefix('client')
    //     ->name('client.')
    //     ->middleware(EnsureRole::class . ':client')
    //     ->group(function () {
    //         Route::resource('orders', ClientOrderController::class)
    //             ->only(['index', 'show', 'store', 'destroy']);
    //     });

    // Espace Gestionnaire
    Route::prefix('admin')
        ->name('manager.')
        ->middleware(EnsureRole::class . ':manager')
        ->group(function () {
            Route::resource('medicines', MedicineController::class);

            Route::resource('orders', OrderController::class)
                ->only(['index', 'show', 'update']); // update = affectation livreur
        });

    Route::get('/admin/dashboard', function () {
    return  view('admin.dashboard');
})->name("dashboard");



    // // Espace Livreur
    // Route::prefix('courier')
    //     ->name('courier.')
    //     ->middleware(EnsureRole::class . ':courier')
    //     ->group(function () {
    //         Route::resource('orders', CourierOrderController::class)
    //             ->only(['index', 'show', 'update']); // update = IN_DELIVERY/DELIVERED

    //         Route::patch('orders/{order}/accept', [CourierOrderController::class, 'accept'])
    //             ->name('orders.accept');

    //         Route::patch('orders/{order}/refuse', [CourierOrderController::class, 'refuse'])
    //             ->name('orders.refuse');
    //     });
});

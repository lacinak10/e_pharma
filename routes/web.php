<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\Client\OrderController as ClientOrderController;
use App\Http\Controllers\Courier\OrderController as CourierOrderController;
use App\Http\Controllers\Manager\CategoryController;
use App\Http\Controllers\Manager\MedicineController as ManagerMedicineController;
use App\Http\Controllers\Manager\OrderController as ManagerOrderController;
use App\Http\Middleware\EnsureRole;
use App\Models\Medicine;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\MedicineController;
use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\CheckoutController;
use App\Http\Controllers\Store\OrderController;
use App\Http\Controllers\Store\PrescriptionController;

//Page web

Route::name('store.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('/medicaments', [MedicineController::class, 'index'])->name('medicines.index');
    Route::get('/medicaments/{medicine}', [MedicineController::class, 'show'])->name('medicines.show');

    // Cart (session)
    Route::get('/panier', [CartController::class, 'index'])->name('cart.index');
    Route::post('/panier/ajouter/{medicine}', [CartController::class, 'store'])->name('cart.add');
    Route::patch('/panier/maj/{medicine}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/panier/supprimer/{medicine}', [CartController::class, 'destroy'])->name('cart.remove');
    Route::delete('/panier/vider', [CartController::class, 'clear'])->name('cart.clear');

    // Prescription (simple)
    Route::get('/scan-ordonnance', [PrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::post('/scan-ordonnance', [PrescriptionController::class, 'store'])->name('prescriptions.store');

    // Checkout + Orders (auth)
    Route::middleware(['auth'])->group(function () {
        Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

        Route::get('/mes-commandes', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/mes-commandes/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/mes-commandes/{order}/annuler', [OrderController::class, 'cancel'])->name('orders.cancel');
    });
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

        Route::prefix('admin')
    ->name('manager.')
    // ->middleware(['auth'])  // si besoin
    ->group(function () {
        Route::resource('categories', CategoryController::class);
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

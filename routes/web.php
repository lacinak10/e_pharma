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

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Manager\StockController;
use App\Http\Controllers\Manager\AssignmentController;
use App\Http\Controllers\Manager\DeliveryController;
use App\Http\Controllers\Manager\CustomerController;
use App\Http\Controllers\Manager\CourierController;
use App\Http\Controllers\Manager\UserController;

use App\Http\Controllers\Courier\MyOrderController;
use App\Http\Controllers\ProfileController as UserProfileController;

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

    // Prescription + Checkout + Orders (auth + email vérifié)
    Route::middleware(['auth'])->group(function () {
        Route::get('/scan-ordonnance', [PrescriptionController::class, 'create'])->name('prescriptions.create');
        Route::post('/scan-ordonnance', [PrescriptionController::class, 'store'])->name('prescriptions.store');

        Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

        Route::get('/mes-commandes', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/mes-commandes/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/mes-commandes/{order}/annuler', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::get('/mes-commandes/{order}/ordonnance', [PrescriptionController::class, 'download'])->name('prescriptions.download');
    });
});


// Profil utilisateur (client)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [UserProfileController::class, 'destroy'])->name('profile.destroy');
});

// Catalogue public
Route::resource('catalog', CatalogController::class)
    ->only(['index', 'show']);

// Auth (Breeze)
require __DIR__.'/auth.php';
Route::middleware(['auth', EnsureRole::class . ':manager,courier'])->prefix('admin')->group(function () {

    // Notifications (manager + courier)
    Route::get('notifications', [NotificationController::class, 'index'])->name('admin.notifications.index');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('admin.notifications.markAllAsRead');
    Route::post('notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('admin.notifications.markAsRead');

    // Profil + Paramètres (manager + courier)
    Route::get('profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::get('settings', [SettingsController::class, 'edit'])->name('admin.settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('admin.settings.update');

    // ====== MANAGER UNIQUEMENT ======
    Route::middleware(EnsureRole::class . ':manager')->group(function () {

        // Dashboard (manager seulement — les livreurs ne voient pas cette page)
        Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::name('manager.')->group(function () {

            // Catalogue
            Route::resource('medicines', ManagerMedicineController::class);
            Route::patch('medicines/{medicine}/toggle', [ManagerMedicineController::class, 'toggle'])
                ->name('medicines.toggle');

            Route::resource('categories', CategoryController::class);

            // Stock (pages dédiées)
            Route::get('stock', [StockController::class, 'index'])->name('stock.index');
            Route::get('stock/{medicine}/edit', [StockController::class, 'edit'])->name('stock.edit');
            Route::put('stock/{medicine}', [StockController::class, 'update'])->name('stock.update');

            // Commandes & livraisons
            Route::resource('orders', ManagerOrderController::class)->only(['index', 'show', 'update']);
            Route::get('orders/{order}/ordonnance', [ManagerOrderController::class, 'downloadPrescription'])->name('orders.prescription');

            Route::get('assignments', [AssignmentController::class, 'index'])->name('assignments.index');
            Route::post('assignments', [AssignmentController::class, 'store'])->name('assignments.store');

            Route::get('deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');

            // Utilisateurs
            Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
            Route::get('customers/{user}', [CustomerController::class, 'show'])->name('customers.show');
            Route::resource('couriers', CourierController::class);

            // Création d'utilisateurs (tous rôles)
            Route::get('users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
        });
    });

    // ====== LIVREUR UNIQUEMENT ======
    Route::middleware(EnsureRole::class . ':courier')->name('courier.')->group(function () {

        Route::get('my-orders', [MyOrderController::class, 'index'])->name('my_orders.index');
        Route::get('my-orders/{order}', [MyOrderController::class, 'show'])->name('my_orders.show');

        Route::patch('my-orders/{order}/accept', [MyOrderController::class, 'accept'])->name('my_orders.accept');
        Route::patch('my-orders/{order}/refuse', [MyOrderController::class, 'refuse'])->name('my_orders.refuse');

        Route::patch('my-orders/{order}/start', [MyOrderController::class, 'startDelivery'])->name('my_orders.start');
        Route::patch('my-orders/{order}/delivered', [MyOrderController::class, 'markDelivered'])->name('my_orders.delivered');
        Route::get('my-orders/{order}/ordonnance', [MyOrderController::class, 'downloadPrescription'])->name('my_orders.prescription');
    });
});

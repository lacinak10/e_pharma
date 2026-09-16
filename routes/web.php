<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Courier\MyOrderController;
use App\Http\Controllers\Manager\AssignmentController;
use App\Http\Controllers\Manager\CategoryController;
use App\Http\Controllers\Manager\CourierController;
use App\Http\Controllers\Manager\CustomerController;
use App\Http\Controllers\Manager\DeliveryController;
use App\Http\Controllers\Manager\MedicineController as ManagerMedicineController;
use App\Http\Controllers\Manager\OrderController as ManagerOrderController;
use App\Http\Controllers\Manager\PharmacyController;
use App\Http\Controllers\Manager\PrescriptionController as ManagerPrescriptionController;
use App\Http\Controllers\Manager\ReviewController as ManagerReviewController;
use App\Http\Controllers\Manager\StatsController;
use App\Http\Controllers\Manager\UserController;
use App\Http\Controllers\Manager\VerificationController;
use App\Http\Controllers\ProfileController as UserProfileController;
use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\CheckoutController;
use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\MedicineController;
use App\Http\Controllers\Store\PaymentController;
use App\Http\Controllers\Store\OrderController;
use App\Http\Controllers\Store\PrescriptionController;
use App\Http\Controllers\Store\ReviewController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Boutique — parcours client (web et mobile, mêmes routes)
|--------------------------------------------------------------------------
*/

Route::name('store.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('/medicaments', [MedicineController::class, 'index'])->name('medicines.index');
    Route::get('/medicaments/{medicine}', [MedicineController::class, 'show'])->name('medicines.show');

    Route::view('/comment-ca-marche', 'store.pages.how')->name('how');
    Route::get('/pharmacies-partenaires', [HomeController::class, 'partners'])->name('partners');
    Route::get('/avis-clients', [HomeController::class, 'reviews'])->name('reviews');

    // Panier (session)
    Route::get('/panier', [CartController::class, 'index'])->name('cart.index');
    Route::post('/panier/ajouter/{medicine}', [CartController::class, 'store'])->name('cart.add');
    Route::patch('/panier/maj/{medicine}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/panier/supprimer/{medicine}', [CartController::class, 'destroy'])->name('cart.remove');
    Route::delete('/panier/vider', [CartController::class, 'clear'])->name('cart.clear');

    Route::middleware('auth')->group(function () {
        // Ordonnance
        Route::get('/scan-ordonnance', [PrescriptionController::class, 'create'])->name('prescriptions.create');
        Route::post('/scan-ordonnance', [PrescriptionController::class, 'store'])->name('prescriptions.store');
        Route::get('/mes-commandes/{order}/ordonnance', [PrescriptionController::class, 'download'])->name('prescriptions.download');

        // Commande
        Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

        // Règlement en ligne — après le verdict, avant l'attribution du livreur
        Route::post('/mes-commandes/{order}/payer', [PaymentController::class, 'pay'])->name('payments.pay');
        Route::get('/mes-commandes/{order}/paiement', [PaymentController::class, 'return'])->name('payments.return');

        // Suivi
        Route::get('/mes-commandes', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/mes-commandes/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/mes-commandes/{order}/annuler', [OrderController::class, 'cancel'])->name('orders.cancel');

        // Notation du livreur (étape 13)
        Route::post('/mes-commandes/{order}/avis', [ReviewController::class, 'store'])->name('orders.review');
    });
});

// Profil client
Route::middleware('auth')->group(function () {
    Route::get('/profile', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [UserProfileController::class, 'destroy'])->name('profile.destroy');
});

// Ancien catalogue, remplacé par /medicaments : on conserve les liens existants.
Route::redirect('/catalog', '/medicaments', 301);
Route::get('/catalog/{medicine}', fn ($medicine) => redirect()->route('store.medicines.show', $medicine, 301));

require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Back-office — manager et livreur
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', EnsureRole::class . ':manager,courier'])->prefix('admin')->group(function () {

    Route::get('notifications', [NotificationController::class, 'index'])->name('admin.notifications.index');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('admin.notifications.markAllAsRead');
    Route::post('notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('admin.notifications.markAsRead');

    Route::get('profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::get('settings', [SettingsController::class, 'edit'])->name('admin.settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('admin.settings.update');

    // ====== MANAGER ======
    Route::middleware(EnsureRole::class . ':manager')->group(function () {

        Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::name('manager.')->group(function () {

            // Vérification de disponibilité — étapes 1 à 6
            Route::get('verifications', [VerificationController::class, 'index'])->name('verifications.index');
            Route::get('verifications/{order}', [VerificationController::class, 'show'])->name('verifications.show');
            Route::post('verifications/{order}/lancer', [VerificationController::class, 'start'])->name('verifications.start');
            Route::post('verifications/{order}/refuser', [VerificationController::class, 'refuse'])->name('verifications.refuse');
            // Composition du panier : indispensable aux commandes sur ordonnance,
            // qui arrivent sans aucune ligne.
            Route::post('verifications/{order}/lignes', [VerificationController::class, 'storeItem'])->name('verifications.items.store');
            Route::delete('verifications/{order}/lignes/{item}', [VerificationController::class, 'destroyItem'])->name('verifications.items.destroy');
            Route::patch('verifications/{order}/lignes/{item}', [VerificationController::class, 'updateItem'])->name('verifications.item');
            Route::post('verifications/{order}/verdict', [VerificationController::class, 'settle'])->name('verifications.settle');

            // Attribution du livreur — étape 7
            Route::get('assignments', [AssignmentController::class, 'index'])->name('assignments.index');
            Route::post('assignments', [AssignmentController::class, 'store'])->name('assignments.store');

            // Commandes & suivi
            Route::resource('orders', ManagerOrderController::class)->only(['index', 'show', 'update'])->whereNumber('order');
            Route::post('orders/{order}/annuler', [ManagerOrderController::class, 'cancel'])->name('orders.cancel');
            Route::get('orders/{order}/ordonnance', [ManagerOrderController::class, 'downloadPrescription'])->name('orders.prescription');
            Route::get('deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');

            // Ordonnances reçues
            Route::get('prescriptions', [ManagerPrescriptionController::class, 'index'])->name('prescriptions.index');

            // Réseau partenaire
            Route::resource('pharmacies', PharmacyController::class)->except(['show'])->whereNumber('pharmacy');

            // Catalogue
            Route::resource('medicines', ManagerMedicineController::class)->whereNumber('medicine');
            Route::patch('medicines/{medicine}/toggle', [ManagerMedicineController::class, 'toggle'])->name('medicines.toggle');
            // La création se fait dans le panneau latéral de l'index : ni « create » ni « show ».
            Route::resource('categories', CategoryController::class)->except(['create', 'show'])->whereNumber('category');

            // Qualité
            Route::get('reviews', [ManagerReviewController::class, 'index'])->name('reviews.index');
            Route::get('stats', [StatsController::class, 'index'])->name('stats.index');

            // Utilisateurs
            Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
            Route::get('customers/{user}', [CustomerController::class, 'show'])->name('customers.show');
            Route::resource('couriers', CourierController::class)->whereNumber('courier');
            Route::get('users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
        });
    });

    // ====== LIVREUR — étapes 8 à 12 ======
    Route::middleware(EnsureRole::class . ':courier')->name('courier.')->group(function () {
        Route::get('my-orders', [MyOrderController::class, 'index'])->name('my_orders.index');
        Route::get('my-orders/{order}', [MyOrderController::class, 'show'])->name('my_orders.show');

        Route::patch('my-orders/{order}/accepter', [MyOrderController::class, 'accept'])->name('my_orders.accept');
        Route::patch('my-orders/{order}/refuser', [MyOrderController::class, 'refuse'])->name('my_orders.refuse');
        Route::patch('my-orders/{order}/avancer', [MyOrderController::class, 'advance'])->name('my_orders.advance');
        Route::get('my-orders/{order}/ordonnance', [MyOrderController::class, 'downloadPrescription'])->name('my_orders.prescription');
    });
});

<?php

namespace App\Providers;

use App\View\Composers\StoreComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        /*
         * Les vues de pagination livrées par Laravel sont écrites pour Tailwind,
         * que ce projet n'utilise pas : sans ses classes, les chevrons SVG
         * s'étiraient sur toute la largeur. On sert donc les nôtres.
         */
        Paginator::defaultView('vendor.pagination.epharma');
        Paginator::defaultSimpleView('vendor.pagination.epharma-simple');

        /*
         * Panier, commande en cours et preuve sociale sur tout le parcours client.
         * Les vues enfants sont rendues avant le layout : il faut les viser
         * explicitement, sinon leurs sections n'ont pas accès à ces données.
         */
        View::composer(
            ['layouts.store', 'store.*', 'components.store.*', 'components.ep.*'],
            StoreComposer::class
        );
    }
}

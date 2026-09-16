<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Numéro de commande « CMD-20260904-0001 ».
     *
     * La référence était jusqu'ici dérivée de l'`id` (« CMD-00042 ») : elle
     * exposait le volume total de la plateforme et ne disait rien de la date.
     * Elle devient une colonne, seule façon d'avoir une séquence quotidienne
     * stable — un accesseur devrait recompter les commandes du jour à chaque
     * affichage.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('reference', 24)->nullable()->unique();
        });

        $this->backfill();
    }

    /** Renumérote l'existant par jour, dans l'ordre de création. */
    private function backfill(): void
    {
        $counters = [];

        DB::table('orders')
            ->orderBy('created_at')
            ->orderBy('id')
            ->select('id', 'created_at')
            ->chunk(500, function ($orders) use (&$counters) {
                foreach ($orders as $order) {
                    $day = Carbon::parse($order->created_at)->format('Ymd');

                    $counters[$day] = ($counters[$day] ?? 0) + 1;

                    DB::table('orders')
                        ->where('id', $order->id)
                        ->update(['reference' => sprintf('CMD-%s-%04d', $day, $counters[$day])]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['reference']);
            $table->dropColumn('reference');
        });
    }
};

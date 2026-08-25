<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ePharma ne détient aucun inventaire : le catalogue est un référentiel de ce
 * que l'on sait commander, pas un stock. La disponibilité réelle est établie
 * commande par commande auprès des pharmacies partenaires, et l'historique de
 * ces vérifications (order_items.availability) fournit le signal affiché au
 * client. Ces trois colonnes ne pouvaient donc que mentir.
 */
return new class extends Migration
{
    public function up(): void
    {
        // « status » portait un index : SQLite refuse de supprimer une colonne
        // encore indexée, il faut donc défaire l'index d'abord.
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropIndex('medicines_status_index');
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn(['stock', 'alert_threshold', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->integer('stock')->default(0);
            $table->integer('alert_threshold')->default(0);
            $table->string('status')->default('active')->index();
        });
    }
};

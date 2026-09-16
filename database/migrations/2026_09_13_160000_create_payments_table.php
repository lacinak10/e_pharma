<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->string('provider')->default('geniuspay');

            // Référence GeniusPay « MTX-A1B2C3D4E5 » : notre seule clé de
            // confiance pour rapprocher un webhook d'une commande.
            $table->string('reference')->unique();

            $table->string('status')->index();

            // Montant figé à la création du lien. On ne compare jamais un
            // webhook à orders.total_amount, qui pourrait avoir bougé.
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('XOF');

            $table->string('method')->nullable();        // wave, orange_money, card…
            $table->string('environment')->nullable();   // sandbox | live
            $table->text('checkout_url')->nullable();

            $table->timestamp('expires_at')->nullable(); // 24 h côté GeniusPay
            $table->timestamp('paid_at')->nullable();
            $table->string('failure_reason')->nullable();

            $table->json('last_payload')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
        });

        /*
         * GeniusPay redélivre un webhook tant qu'il n'a pas reçu de 2xx : sans
         * déduplication, un « payment.success » rejoué recréditerait la commande
         * et polluerait la timeline d'un doublon.
         */
        Schema::create('payment_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_id')->unique();
            $table->string('event');
            $table->string('reference')->nullable()->index();
            $table->timestamp('received_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhook_events');
        Schema::dropIfExists('payments');
    }
};

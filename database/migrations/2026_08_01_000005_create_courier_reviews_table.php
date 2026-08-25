<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->foreignId('courier_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');     // 1..5
            $table->text('comment')->nullable();
            $table->json('tags')->nullable();          // Ponctuel, Bien emballé, Poli…
            $table->timestamps();

            $table->index(['courier_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_reviews');
    }
};

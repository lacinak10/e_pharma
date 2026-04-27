<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();

            // Relation catégorie
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->index();

            $table->string('name')->index();
            $table->text('description')->nullable();

            // Prix en plus petite unité (ex: FCFA). Évite les float.
            $table->unsignedBigInteger('price');

            $table->unsignedInteger('stock')->default(0);

            // Exemple: active/inactive/out_of_stock...
            $table->string('status')->default('active')->index();

            // Référence interne
            $table->string('reference')->unique()->index();

            $table->unsignedInteger('alert_threshold')->default(0);

            $table->string('image_url')->nullable();

            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();

            // Index utiles
            $table->index(['category_id', 'is_active']);
            $table->index(['name', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};

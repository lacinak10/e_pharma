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

            $table->string('name')->index();
            $table->text('description')->nullable();

            // Prix en plus petite unité (ex: FCFA). Évite les float.
            $table->unsignedBigInteger('price');

            $table->unsignedInteger('stock')->default(0);

            $table->string('category');

            $table->string('status');

            $table->string('Reference');

            $table->unsignedInteger('alert_threshold')->default(0);

            $table->string('image_url')->nullable();

            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};

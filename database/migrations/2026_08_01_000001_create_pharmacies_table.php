<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('area');                       // Quartier : Plateau, Cocody Angré…
            $table->string('phone');
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_24h')->default(false);
            $table->unsignedTinyInteger('reliability')->default(100); // % de réponses positives
            $table->unsignedSmallInteger('avg_response_minutes')->default(3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'area']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacies');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('zone')->nullable()->after('phone');          // Quartier de rattachement
            $table->decimal('latitude', 10, 7)->nullable()->after('zone');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('avatar_path')->nullable()->after('longitude');
            $table->boolean('is_available')->default(true)->after('avatar_path'); // Livreur en service
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['zone', 'latitude', 'longitude', 'avatar_path', 'is_available']);
        });
    }
};

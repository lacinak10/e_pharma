<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method')->default('cash')->after('notes');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('medicine_name')->after('medicine_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('medicine_name');
        });
    }
};

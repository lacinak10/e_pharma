<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // pending | available | unavailable | substituted
            $table->string('availability')->default('pending')->after('line_total');
            $table->foreignId('pharmacy_id')->nullable()->after('availability')
                ->constrained('pharmacies')->nullOnDelete();
            $table->timestamp('checked_at')->nullable()->after('pharmacy_id');
            $table->string('substitute_name')->nullable()->after('checked_at');
            $table->boolean('requires_prescription')->default(false)->after('substitute_name');

            $table->index(['order_id', 'availability']);
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pharmacy_id');
            $table->dropIndex(['order_id', 'availability']);
            $table->dropColumn(['availability', 'checked_at', 'substitute_name', 'requires_prescription']);
        });
    }
};

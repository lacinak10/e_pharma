<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->string('indication')->nullable()->after('description'); // « Douleur et fièvre — dès 50 kg »
            $table->string('pack')->nullable()->after('indication');        // « 8 comprimés »
            $table->string('dosage')->nullable()->after('pack');            // « 1000 mg »
            $table->boolean('requires_prescription')->default(false)->after('dosage');

            $table->index('requires_prescription');
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropIndex(['requires_prescription']);
            $table->dropColumn(['indication', 'pack', 'dosage', 'requires_prescription']);
        });
    }
};

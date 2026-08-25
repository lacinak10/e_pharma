<?php

use App\Enums\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Anciens statuts → les 14 statuts du système de design. */
    private const STATUS_MAP = [
        'PENDING_ASSIGNMENT' => OrderStatus::AVAILABLE->value,
        'ASSIGNED'           => OrderStatus::COURIER_ASSIGNED->value,
        'ACCEPTED'           => OrderStatus::TO_PHARMACY->value,
        'IN_DELIVERY'        => OrderStatus::TO_CLIENT->value,
    ];

    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('pharmacy_id')->nullable()->after('user_id')
                ->constrained('pharmacies')->nullOnDelete();

            // Jalons du parcours
            $table->timestamp('validated_at')->nullable()->after('status');
            $table->timestamp('checking_started_at')->nullable()->after('validated_at');
            $table->timestamp('check_deadline_at')->nullable()->after('checking_started_at');
            $table->timestamp('verdict_at')->nullable()->after('check_deadline_at');
            $table->timestamp('assigned_at')->nullable()->after('verdict_at');

            // Motifs
            $table->string('refusal_reason')->nullable()->after('notes');
            $table->string('cancel_reason')->nullable()->after('refusal_reason');
            $table->foreignId('canceled_by')->nullable()->after('cancel_reason')
                ->constrained('users')->nullOnDelete();

            // Ordonnance : périmètre demandé par le client
            $table->string('prescription_scope')->nullable()->after('has_prescription'); // all | partial
            $table->text('prescription_comment')->nullable()->after('prescription_scope');

            // Livraison
            $table->unsignedSmallInteger('eta_minutes')->nullable()->after('delivery_fee');
            $table->string('delivery_code', 6)->nullable()->after('eta_minutes');

            $table->index('checking_started_at');
        });

        foreach (self::STATUS_MAP as $old => $new) {
            DB::table('orders')->where('status', $old)->update(['status' => $new]);
        }
    }

    public function down(): void
    {
        foreach (array_flip(self::STATUS_MAP) as $new => $old) {
            DB::table('orders')->where('status', $new)->update(['status' => $old]);
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pharmacy_id');
            $table->dropConstrainedForeignId('canceled_by');
            $table->dropIndex(['checking_started_at']);
            $table->dropColumn([
                'validated_at', 'checking_started_at', 'check_deadline_at', 'verdict_at',
                'assigned_at', 'refusal_reason', 'cancel_reason',
                'prescription_scope', 'prescription_comment', 'eta_minutes', 'delivery_code',
            ]);
        });
    }
};

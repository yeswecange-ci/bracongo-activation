<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lck_orders', function (Blueprint $table) {
            // cash_on_delivery | online
            $table->string('payment_method')->default('cash_on_delivery')->after('total');
            // pending | paid | failed | refunded
            $table->string('payment_status')->default('pending')->after('payment_method');
            // Référence externe retournée par l'API de paiement
            $table->string('payment_reference')->nullable()->after('payment_status');
            // Montant confirmé payé (peut différer du total si remise)
            $table->decimal('amount_paid', 10, 2)->nullable()->after('payment_reference');
            // Timestamp de la confirmation de paiement
            $table->timestamp('paid_at')->nullable()->after('amount_paid');
        });
    }

    public function down(): void
    {
        Schema::table('lck_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status', 'payment_reference', 'amount_paid', 'paid_at']);
        });
    }
};

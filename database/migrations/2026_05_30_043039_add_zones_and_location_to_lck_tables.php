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
        // Zones couvertes par chaque commercant (tableau JSON)
        Schema::table('commercants', function (Blueprint $table) {
            $table->json('zones')->nullable()->after('phone');
        });

        // Zone/quartier du client au moment de la commande
        Schema::table('lck_orders', function (Blueprint $table) {
            $table->string('customer_location')->nullable()->after('customer_name');
        });
    }

    public function down(): void
    {
        Schema::table('commercants', function (Blueprint $table) {
            $table->dropColumn('zones');
        });
        Schema::table('lck_orders', function (Blueprint $table) {
            $table->dropColumn('customer_location');
        });
    }
};

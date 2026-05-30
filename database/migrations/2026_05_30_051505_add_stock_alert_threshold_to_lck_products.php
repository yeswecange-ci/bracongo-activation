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
        Schema::table('lck_products', function (Blueprint $table) {
            $table->integer('stock_alert_threshold')->default(3)->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('lck_products', function (Blueprint $table) {
            $table->dropColumn('stock_alert_threshold');
        });
    }
};

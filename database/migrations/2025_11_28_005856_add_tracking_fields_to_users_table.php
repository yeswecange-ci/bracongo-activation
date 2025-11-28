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
        Schema::table('users', function (Blueprint $table) {
            $table->string('source_type')->nullable()->after('village_id'); // AFFICHE, PDV_PARTENAIRE, DIGITAL, FLYER, DIRECT
            $table->string('source_detail')->nullable()->after('source_type'); // GOMBE, BRACONGO, FB, etc.
            $table->timestamp('scan_timestamp')->nullable()->after('source_detail');
            $table->string('registration_status')->default('PENDING')->after('scan_timestamp'); // SCAN, OPT_IN, INSCRIT, REFUS, etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['source_type', 'source_detail', 'scan_timestamp', 'registration_status']);
        });
    }
};

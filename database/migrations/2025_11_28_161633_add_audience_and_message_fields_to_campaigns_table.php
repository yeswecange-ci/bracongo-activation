<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            // Ajouter les nouvelles colonnes
            $table->string('audience_type')->default('all')->after('name');
            $table->string('audience_status')->nullable()->after('audience_type');
            $table->text('message')->nullable()->after('status');
        });

        // Migrer les données existantes de target_segment vers audience_type
        DB::statement("UPDATE campaigns SET audience_type = CASE
            WHEN target_segment = 'village' THEN 'village'
            WHEN target_segment = 'custom' THEN 'status'
            ELSE 'all'
        END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['audience_type', 'audience_status', 'message']);
        });
    }
};

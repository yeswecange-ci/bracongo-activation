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
        Schema::create('lck_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Valeurs par défaut
        \Illuminate\Support\Facades\DB::table('lck_settings')->insert([
            ['key' => 'pickup_name',      'value' => 'La Clé des Châteaux',                    'created_at' => now(), 'updated_at' => now()],
            ['key' => 'pickup_address',   'value' => "Boulevard du 30 Juin\nCommune de Gombe", 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'pickup_city',      'value' => 'Kinshasa, RDC',                           'created_at' => now(), 'updated_at' => now()],
            ['key' => 'pickup_phone',     'value' => '',                                         'created_at' => now(), 'updated_at' => now()],
            ['key' => 'pickup_hours',     'value' => 'Lun–Sam  9h–19h',                         'created_at' => now(), 'updated_at' => now()],
            ['key' => 'pickup_deadline',  'value' => '5',                                        'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lck_settings');
        // Note: down() drops the table + data, re-running up() will re-seed defaults
    }
};

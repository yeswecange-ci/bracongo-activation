<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Code unique du QR
            $table->string('source'); // Source (ex: affiche_gombe, flyer_bandalungwa)
            $table->string('qr_image_path')->nullable(); // Chemin de l'image QR générée
            $table->integer('scan_count')->default(0); // Nombre de scans
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};

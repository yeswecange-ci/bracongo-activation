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
        Schema::create('commercants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();            // numéro WhatsApp pour les notifications
            $table->enum('role', ['commercial', 'caviste'])->default('commercial');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        // Ajout de la FK commercant_id sur lck_orders (table déjà créée)
        Schema::table('lck_orders', function (Blueprint $table) {
            $table->foreign('commercant_id')->references('id')->on('commercants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lck_orders', function (Blueprint $table) {
            $table->dropForeign(['commercant_id']);
        });

        Schema::dropIfExists('commercants');
    }

};

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
        Schema::create('lck_cart_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('token', 32)->unique();          // token unique envoyé dans WhatsApp
            $table->string('customer_phone')->nullable();   // rempli si connu à la création
            $table->string('customer_name')->nullable();    // rempli après identification dans le bot
            $table->json('items');                          // [{product_id, name, price, qty}, ...]
            $table->decimal('total', 10, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'expired'])->default('pending');
            $table->string('source')->default('website');   // website | bot
            $table->timestamp('expires_at');                // expiration après 24h
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lck_cart_sessions');
    }
};

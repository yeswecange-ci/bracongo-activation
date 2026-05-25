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
        Schema::create('lck_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_ref')->unique();           // LCK-20260001
            $table->foreignId('cart_session_id')->nullable()->constrained('lck_cart_sessions')->nullOnDelete();
            $table->unsignedBigInteger('commercant_id')->nullable(); // FK ajoutée après la migration commercants
            $table->string('customer_phone');
            $table->string('customer_name')->nullable();
            $table->decimal('total', 10, 2)->default(0);
            $table->enum('status', [
                'received',     // reçue depuis WhatsApp
                'confirmed',    // confirmée par la commercante
                'preparing',    // en préparation
                'ready',        // prête à être récupérée
                'delivered',    // livrée / récupérée
                'cancelled',    // annulée
            ])->default('received');
            $table->text('notes')->nullable();              // notes internes
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index('customer_phone');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lck_orders');
    }
};

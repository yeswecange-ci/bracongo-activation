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
        Schema::create('lck_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('lck_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('lck_products')->nullOnDelete();
            $table->string('product_name');                 // snapshot au moment de la commande
            $table->string('product_category')->nullable(); // snapshot catégorie
            $table->decimal('unit_price', 10, 2);           // snapshot prix au moment de la commande
            $table->integer('quantity');
            $table->decimal('subtotal', 10, 2);             // unit_price × quantity
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lck_order_items');
    }
};

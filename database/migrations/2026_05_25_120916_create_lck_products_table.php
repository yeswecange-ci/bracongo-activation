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
        Schema::create('lck_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('lck_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('origin')->nullable();          // pays/région du vin
            $table->string('vintage')->nullable();         // millésime (ex: 2022)
            $table->decimal('price', 10, 2);
            $table->string('image')->nullable();
            $table->string('whatsapp_label');              // label court pour le bot: "Château du Bousquet 2022"
            $table->integer('stock')->nullable();          // null = illimité
            $table->boolean('is_available')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lck_products');
    }
};

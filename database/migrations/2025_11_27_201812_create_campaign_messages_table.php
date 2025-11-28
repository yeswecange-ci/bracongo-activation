<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('message_templates')->nullOnDelete();
            $table->text('content'); // Contenu du message (peut être personnalisé)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_messages');
    }
};

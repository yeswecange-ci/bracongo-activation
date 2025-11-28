<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom de la campagne
            $table->enum('type', ['welcome', 'village_choice', 'match_reminder', 'pronostic', 'results', 'general']); // Type de campagne
            $table->enum('target_segment', ['all', 'village', 'non_respondents', 'custom'])->default('all'); // Segment cible
            $table->foreignId('village_id')->nullable()->constrained()->nullOnDelete(); // Si segment = village
            $table->dateTime('scheduled_at')->nullable(); // Date d'envoi programmé
            $table->enum('status', ['draft', 'scheduled', 'processing', 'sent', 'failed'])->default('draft');
            $table->integer('total_recipients')->default(0); // Nombre de destinataires
            $table->integer('sent_count')->default(0); // Nombre d'envois réussis
            $table->integer('failed_count')->default(0); // Nombre d'échecs
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};

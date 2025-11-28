<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->string('team_a'); // Équipe A (ex: RDC)
            $table->string('team_b'); // Équipe B (ex: Maroc)
            $table->dateTime('match_date'); // Date et heure du match
            $table->integer('score_a')->nullable(); // Score équipe A
            $table->integer('score_b')->nullable(); // Score équipe B
            $table->enum('status', ['scheduled', 'live', 'finished'])->default('scheduled');
            $table->boolean('pronostic_enabled')->default(true); // Activer les pronostics
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};

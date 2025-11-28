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
        Schema::table('message_templates', function (Blueprint $table) {
            // Catégorie du template (match_notification, prize_alert, reminder, etc.)
            $table->string('category')->after('type')->nullable();

            // Header (texte ou media)
            $table->string('header_type')->after('category')->nullable(); // text, image, video, document
            $table->string('header_text', 60)->after('header_type')->nullable();
            $table->string('header_media_path')->after('header_text')->nullable();

            // Renommer 'content' en 'body' pour plus de clarté
            $table->renameColumn('content', 'body');

            // Footer (texte optionnel)
            $table->string('footer', 60)->after('body')->nullable();

            // Boutons (JSON array)
            $table->json('buttons')->after('footer')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'header_type',
                'header_text',
                'header_media_path',
                'footer',
                'buttons',
            ]);

            $table->renameColumn('body', 'content');
        });
    }
};

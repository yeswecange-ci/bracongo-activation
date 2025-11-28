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
        Schema::table('campaign_messages', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('campaign_id')->constrained()->nullOnDelete();
            $table->text('message')->nullable()->after('content');
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed'])->default('pending')->after('message');
            $table->timestamp('sent_at')->nullable()->after('status');
            $table->text('error_message')->nullable()->after('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_messages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'message', 'status', 'sent_at', 'error_message']);
        });
    }
};

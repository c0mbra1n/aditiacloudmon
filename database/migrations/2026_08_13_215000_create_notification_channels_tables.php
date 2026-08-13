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
        Schema::create('notification_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['TELEGRAM', 'DISCORD', 'EMAIL'])->default('TELEGRAM');
            $table->json('config'); // telegram: bot_token, chat_id | discord: webhook_url
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_id')->constrained('alerts')->onDelete('cascade');
            $table->foreignId('notification_channel_id')->constrained('notification_channels')->onDelete('cascade');
            $table->enum('status', ['SUCCESS', 'FAILED'])->default('SUCCESS');
            $table->text('response_message')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_channels');
    }
};

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
        Schema::create('agent_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('server_id')->constrained('servers')->cascadeOnDelete();
            $table->enum('command', ['reboot', 'shutdown']);
            $table->enum('status', ['pending', 'executed', 'cancelled'])->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_commands');
    }
};

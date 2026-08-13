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
        Schema::create('servers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('hostname');
            $table->string('ip_address')->nullable();
            $table->string('os_name')->nullable();
            $table->string('os_version')->nullable();
            $table->string('agent_version')->nullable();
            $table->string('cpu_model')->nullable();
            $table->integer('cpu_cores')->default(1);
            $table->bigInteger('ram_total_bytes')->default(0);
            $table->enum('status', ['ONLINE', 'WARNING', 'CRITICAL', 'OFFLINE', 'MAINTENANCE', 'UNKNOWN'])->default('UNKNOWN');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('agents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('server_id')->constrained('servers')->onDelete('cascade');
            $table->string('agent_version')->default('1.0.0');
            $table->enum('status', ['ACTIVE', 'INACTIVE', 'REVOKED'])->default('ACTIVE');
            $table->integer('heartbeat_interval_seconds')->default(30);
            $table->timestamps();
        });

        Schema::create('agent_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('agent_id')->constrained('agents')->onDelete('cascade');
            $table->string('token_hash');
            $table->string('name')->default('Default Token');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_tokens');
        Schema::dropIfExists('agents');
        Schema::dropIfExists('servers');
    }
};

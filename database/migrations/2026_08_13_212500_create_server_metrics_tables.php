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
        Schema::create('server_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('server_id')->constrained('servers')->onDelete('cascade');
            $table->double('cpu_usage_percent', 5, 2)->default(0.0);
            $table->bigInteger('ram_total_bytes')->default(0);
            $table->bigInteger('ram_used_bytes')->default(0);
            $table->double('ram_usage_percent', 5, 2)->default(0.0);
            $table->bigInteger('uptime_seconds')->default(0);
            $table->timestamps();

            // Index composite for time-series range queries
            $table->index(['server_id', 'created_at']);
        });

        Schema::create('server_disks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_metric_id')->constrained('server_metrics')->onDelete('cascade');
            $table->foreignUuid('server_id')->constrained('servers')->onDelete('cascade');
            $table->string('drive_letter', 10);
            $table->string('label')->nullable();
            $table->string('filesystem')->nullable();
            $table->bigInteger('total_bytes')->default(0);
            $table->bigInteger('free_bytes')->default(0);
            $table->bigInteger('used_bytes')->default(0);
            $table->double('usage_percent', 5, 2)->default(0.0);
            $table->timestamps();
        });

        Schema::create('server_networks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_metric_id')->constrained('server_metrics')->onDelete('cascade');
            $table->foreignUuid('server_id')->constrained('servers')->onDelete('cascade');
            $table->string('interface_name');
            $table->string('ip_address')->nullable();
            $table->bigInteger('bytes_sent_per_sec')->default(0);
            $table->bigInteger('bytes_recv_per_sec')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_networks');
        Schema::dropIfExists('server_disks');
        Schema::dropIfExists('server_metrics');
    }
};

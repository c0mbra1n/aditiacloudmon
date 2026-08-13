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
        Schema::create('alert_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('server_id')->nullable()->constrained('servers')->onDelete('cascade');
            $table->string('name');
            $table->enum('metric_type', ['CPU', 'RAM', 'DISK', 'OFFLINE', 'SERVICE', 'PORT']);
            $table->string('target_name')->nullable(); // service_name or port number
            $table->string('operator', 5)->default('>'); // >, >=, <, =, !=
            $table->double('threshold_value', 10, 2)->default(0.0);
            $table->enum('severity', ['WARNING', 'CRITICAL'])->default('WARNING');
            $table->integer('duration_minutes')->default(1);
            $table->integer('cooldown_minutes')->default(15);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('server_id')->constrained('servers')->onDelete('cascade');
            $table->foreignId('alert_rule_id')->nullable()->constrained('alert_rules')->onDelete('set null');
            $table->string('title');
            $table->text('message');
            $table->enum('severity', ['WARNING', 'CRITICAL'])->default('WARNING');
            $table->enum('status', ['OPEN', 'ACKNOWLEDGED', 'RESOLVED'])->default('OPEN');
            $table->timestamp('triggered_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('alert_rules');
    }
};

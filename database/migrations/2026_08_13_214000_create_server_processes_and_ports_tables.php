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
        Schema::create('server_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('server_id')->constrained('servers')->onDelete('cascade');
            $table->string('process_name');
            $table->integer('pid')->nullable();
            $table->double('cpu_percent', 5, 2)->default(0.0);
            $table->bigInteger('memory_bytes')->default(0);
            $table->enum('status', ['Running', 'Stopped', 'Unknown'])->default('Running');
            $table->timestamps();

            $table->unique(['server_id', 'process_name']);
        });

        Schema::create('server_ports', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('server_id')->constrained('servers')->onDelete('cascade');
            $table->integer('port');
            $table->string('protocol', 10)->default('TCP');
            $table->enum('status', ['Open', 'Closed'])->default('Closed');
            $table->timestamps();

            $table->unique(['server_id', 'port', 'protocol']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_ports');
        Schema::dropIfExists('server_processes');
    }
};

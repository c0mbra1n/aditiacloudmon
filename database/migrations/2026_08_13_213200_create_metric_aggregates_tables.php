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
        Schema::create('metric_aggregates_1m', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('server_id')->constrained('servers')->onDelete('cascade');
            $table->double('avg_cpu', 5, 2)->default(0.0);
            $table->double('max_cpu', 5, 2)->default(0.0);
            $table->double('avg_ram', 5, 2)->default(0.0);
            $table->double('max_ram', 5, 2)->default(0.0);
            $table->double('avg_disk', 5, 2)->default(0.0);
            $table->double('max_disk', 5, 2)->default(0.0);
            $table->integer('sample_count')->default(1);
            $table->timestamp('bucket_at');
            $table->timestamps();

            $table->index(['server_id', 'bucket_at']);
        });

        Schema::create('metric_aggregates_5m', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('server_id')->constrained('servers')->onDelete('cascade');
            $table->double('avg_cpu', 5, 2)->default(0.0);
            $table->double('max_cpu', 5, 2)->default(0.0);
            $table->double('avg_ram', 5, 2)->default(0.0);
            $table->double('max_ram', 5, 2)->default(0.0);
            $table->double('avg_disk', 5, 2)->default(0.0);
            $table->double('max_disk', 5, 2)->default(0.0);
            $table->integer('sample_count')->default(1);
            $table->timestamp('bucket_at');
            $table->timestamps();

            $table->index(['server_id', 'bucket_at']);
        });

        Schema::create('metric_aggregates_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('server_id')->constrained('servers')->onDelete('cascade');
            $table->double('avg_cpu', 5, 2)->default(0.0);
            $table->double('max_cpu', 5, 2)->default(0.0);
            $table->double('avg_ram', 5, 2)->default(0.0);
            $table->double('max_ram', 5, 2)->default(0.0);
            $table->double('avg_disk', 5, 2)->default(0.0);
            $table->double('max_disk', 5, 2)->default(0.0);
            $table->integer('sample_count')->default(1);
            $table->date('bucket_date');
            $table->timestamps();

            $table->index(['server_id', 'bucket_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metric_aggregates_daily');
        Schema::dropIfExists('metric_aggregates_5m');
        Schema::dropIfExists('metric_aggregates_1m');
    }
};

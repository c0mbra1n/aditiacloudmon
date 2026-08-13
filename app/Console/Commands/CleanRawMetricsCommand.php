<?php

namespace App\Console\Commands;

use App\Models\MetricAggregate1m;
use App\Models\MetricAggregate5m;
use App\Models\MetricAggregateDaily;
use App\Models\ServerMetric;
use Illuminate\Console\Command;

class CleanRawMetricsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:clean-raw-metrics {--hours=24 : Retention limit jam untuk raw metrics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghapus data raw metrics yang melewati batas rentang retention policy (default 24 jam).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int)$this->option('hours');
        $cutoffRaw = now()->subHours($hours);

        // 1. Delete Raw Metrics > 24 hours
        $deletedRaw = ServerMetric::where('created_at', '<', $cutoffRaw)->delete();

        // 2. Retention 1m > 30 days
        $deleted1m = MetricAggregate1m::where('bucket_at', '<', now()->subDays(30))->delete();

        // 3. Retention 5m > 90 days
        $deleted5m = MetricAggregate5m::where('bucket_at', '<', now()->subDays(90))->delete();

        // 4. Retention Daily > 365 days
        $deletedDaily = MetricAggregateDaily::where('bucket_date', '<', now()->subDays(365))->delete();

        $this->info("Retention Policy cleanup selesai:");
        $this->info("- Raw Metrics terhapus: {$deletedRaw}");
        $this->info("- 1m Aggregates (>30d) terhapus: {$deleted1m}");
        $this->info("- 5m Aggregates (>90d) terhapus: {$deleted5m}");
        $this->info("- Daily Aggregates (>365d) terhapus: {$deletedDaily}");

        return Command::SUCCESS;
    }
}

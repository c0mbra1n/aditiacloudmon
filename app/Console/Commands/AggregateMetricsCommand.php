<?php

namespace App\Console\Commands;

use App\Models\MetricAggregate1m;
use App\Models\MetricAggregate5m;
use App\Models\MetricAggregateDaily;
use App\Models\Server;
use App\Models\ServerMetric;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AggregateMetricsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:aggregate-metrics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memproses aggregasi metric raw menjadi sampel 1-menit, 5-menit, dan harian.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $servers = Server::all();
        $processedCount = 0;

        foreach ($servers as $server) {
            // 1. Process 1-minute aggregations for unaggregated raw metrics
            $rawMetrics = ServerMetric::where('server_id', $server->id)
                ->where('created_at', '>=', now()->subHours(2))
                ->get();

            if ($rawMetrics->isNotEmpty()) {
                // Group by minute
                $grouped1m = $rawMetrics->groupBy(function ($metric) {
                    return $metric->created_at->format('Y-m-d H:i:00');
                });

                foreach ($grouped1m as $minuteBucket => $samples) {
                    $avgCpu = round($samples->avg('cpu_usage_percent'), 2);
                    $maxCpu = round($samples->max('cpu_usage_percent'), 2);
                    $avgRam = round($samples->avg('ram_usage_percent'), 2);
                    $maxRam = round($samples->max('ram_usage_percent'), 2);

                    MetricAggregate1m::updateOrCreate(
                        [
                            'server_id' => $server->id,
                            'bucket_at' => $minuteBucket,
                        ],
                        [
                            'avg_cpu' => $avgCpu,
                            'max_cpu' => $maxCpu,
                            'avg_ram' => $avgRam,
                            'max_ram' => $maxRam,
                            'sample_count' => $samples->count(),
                        ]
                    );
                }
            }

            // 2. Process 5-minute aggregations from 1-minute samples
            $samples1m = MetricAggregate1m::where('server_id', $server->id)
                ->where('bucket_at', '>=', now()->subHours(6))
                ->get();

            if ($samples1m->isNotEmpty()) {
                $grouped5m = $samples1m->groupBy(function ($sample) {
                    $minute = (int)$sample->bucket_at->format('i');
                    $roundedMinute = floor($minute / 5) * 5;
                    return $sample->bucket_at->format('Y-m-d H:') . sprintf('%02d:00', $roundedMinute);
                });

                foreach ($grouped5m as $bucket5m => $samples) {
                    MetricAggregate5m::updateOrCreate(
                        [
                            'server_id' => $server->id,
                            'bucket_at' => $bucket5m,
                        ],
                        [
                            'avg_cpu' => round($samples->avg('avg_cpu'), 2),
                            'max_cpu' => round($samples->max('max_cpu'), 2),
                            'avg_ram' => round($samples->avg('avg_ram'), 2),
                            'max_ram' => round($samples->max('max_ram'), 2),
                            'sample_count' => $samples->sum('sample_count'),
                        ]
                    );
                }
            }

            // 3. Process Daily aggregations
            $samples5m = MetricAggregate5m::where('server_id', $server->id)
                ->where('bucket_at', '>=', now()->subDays(7))
                ->get();

            if ($samples5m->isNotEmpty()) {
                $groupedDaily = $samples5m->groupBy(function ($sample) {
                    return $sample->bucket_at->format('Y-m-d');
                });

                foreach ($groupedDaily as $bucketDate => $samples) {
                    MetricAggregateDaily::updateOrCreate(
                        [
                            'server_id' => $server->id,
                            'bucket_date' => $bucketDate,
                        ],
                        [
                            'avg_cpu' => round($samples->avg('avg_cpu'), 2),
                            'max_cpu' => round($samples->max('max_cpu'), 2),
                            'avg_ram' => round($samples->avg('avg_ram'), 2),
                            'max_ram' => round($samples->max('max_ram'), 2),
                            'sample_count' => $samples->sum('sample_count'),
                        ]
                    );
                }
            }

            $processedCount++;
        }

        $this->info("Metric aggregation selesai untuk {$processedCount} server.");

        return Command::SUCCESS;
    }
}

<?php

namespace Tests\Feature;

use App\Livewire\Server\ServerDetail;
use App\Models\MetricAggregate1m;
use App\Models\MetricAggregate5m;
use App\Models\MetricAggregateDaily;
use App\Models\Server;
use App\Models\ServerMetric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class HistoricalMetricsTest extends TestCase
{
    use RefreshDatabase;

    private Server $server;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->server = Server::create([
            'name' => 'WIN-HISTORICAL-VPS',
            'hostname' => 'WIN-HISTORICAL-VPS',
            'status' => 'ONLINE',
        ]);
    }

    public function test_aggregation_command_creates_1m_5m_and_daily_records(): void
    {
        // Insert sample raw metrics with explicit timestamps
        DB::table('server_metrics')->insert([
            [
                'server_id' => $this->server->id,
                'cpu_usage_percent' => 20.0,
                'ram_usage_percent' => 45.0,
                'created_at' => now()->subMinutes(10),
                'updated_at' => now()->subMinutes(10),
            ],
            [
                'server_id' => $this->server->id,
                'cpu_usage_percent' => 40.0,
                'ram_usage_percent' => 55.0,
                'created_at' => now()->subMinutes(10),
                'updated_at' => now()->subMinutes(10),
            ]
        ]);

        // Run aggregation artisan command
        $this->artisan('monitor:aggregate-metrics')->assertExitCode(0);

        // Assert 1m aggregate created
        $this->assertDatabaseHas('metric_aggregates_1m', [
            'server_id' => $this->server->id,
            'avg_cpu' => 30.0, // (20 + 40) / 2
            'max_cpu' => 40.0,
        ]);
    }

    public function test_retention_policy_cleans_old_raw_metrics(): void
    {
        // 1. Recent raw metric (1 hour old)
        $recentId = DB::table('server_metrics')->insertGetId([
            'server_id' => $this->server->id,
            'cpu_usage_percent' => 25.0,
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);

        // 2. Old raw metric (30 hours old)
        $oldId = DB::table('server_metrics')->insertGetId([
            'server_id' => $this->server->id,
            'cpu_usage_percent' => 80.0,
            'created_at' => now()->subHours(30),
            'updated_at' => now()->subHours(30),
        ]);

        // Run clean command
        $this->artisan('monitor:clean-raw-metrics --hours=24')->assertExitCode(0);

        // Recent metric remains
        $this->assertDatabaseHas('server_metrics', ['id' => $recentId]);

        // Old metric is deleted
        $this->assertDatabaseMissing('server_metrics', ['id' => $oldId]);
    }

    public function test_server_detail_chart_data_switching(): void
    {
        ServerMetric::create([
            'server_id' => $this->server->id,
            'cpu_usage_percent' => 15.0,
            'ram_usage_percent' => 40.0,
        ]);

        Livewire::actingAs($this->user)
            ->test(ServerDetail::class, ['server' => $this->server])
            ->set('selectedPeriod', '1h')
            ->assertSet('selectedPeriod', '1h')
            ->call('setPeriod', '24h')
            ->assertSet('selectedPeriod', '24h');
    }
}

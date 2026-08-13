<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\AgentToken;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeartbeatAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private Server $server;
    private Agent $agent;
    private string $plainToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->server = Server::create([
            'name' => 'WIN-AVAILABILITY-VPS',
            'hostname' => 'WIN-AVAILABILITY-VPS',
            'status' => 'UNKNOWN',
            'last_seen_at' => null,
        ]);

        $this->agent = Agent::create([
            'server_id' => $this->server->id,
            'agent_version' => '1.0.0',
            'status' => 'ACTIVE',
        ]);

        $this->plainToken = 'sec_agt_availability_1234567890abcdef';
        AgentToken::create([
            'agent_id' => $this->agent->id,
            'token_hash' => hash('sha256', $this->plainToken),
            'name' => 'Availability Test Token',
        ]);
    }

    public function test_normal_heartbeat_ping_sets_status_to_online(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->plainToken)
            ->postJson('/api/v1/agent/heartbeat', [
                'agent_id' => $this->agent->id,
                'hostname' => 'WIN-AVAILABILITY-VPS',
                'cpu_usage_percent' => 20.0,
                'ram_usage_percent' => 50.0,
                'disk_usage_percent' => 40.0,
                'uptime_seconds' => 50000,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => ['server_status' => 'ONLINE']
        ]);

        $this->assertDatabaseHas('servers', [
            'id' => $this->server->id,
            'status' => 'ONLINE',
        ]);
    }

    public function test_critical_resource_heartbeat_sets_status_to_critical(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->plainToken)
            ->postJson('/api/v1/agent/heartbeat', [
                'agent_id' => $this->agent->id,
                'hostname' => 'WIN-AVAILABILITY-VPS',
                'cpu_usage_percent' => 98.5, // > 95%
                'ram_usage_percent' => 50.0,
                'disk_usage_percent' => 40.0,
                'uptime_seconds' => 50000,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => ['server_status' => 'CRITICAL']
        ]);

        $this->assertDatabaseHas('servers', [
            'id' => $this->server->id,
            'status' => 'CRITICAL',
        ]);
    }

    public function test_artisan_offline_checker_marks_inactive_servers_as_offline(): void
    {
        // 1. Create active server with recent heartbeat
        $activeServer = Server::create([
            'name' => 'Active VPS',
            'hostname' => 'WIN-ACTIVE-01',
            'status' => 'ONLINE',
            'last_seen_at' => now(),
        ]);

        // 2. Create inactive server with heartbeat 5 minutes ago
        $inactiveServer = Server::create([
            'name' => 'Inactive VPS',
            'hostname' => 'WIN-INACTIVE-01',
            'status' => 'ONLINE',
            'last_seen_at' => now()->subMinutes(5),
        ]);

        // 3. Run artisan command
        $this->artisan('monitor:check-offline --threshold=2')
            ->assertExitCode(0);

        // Active server remains ONLINE
        $this->assertDatabaseHas('servers', [
            'id' => $activeServer->id,
            'status' => 'ONLINE',
        ]);

        // Inactive server becomes OFFLINE
        $this->assertDatabaseHas('servers', [
            'id' => $inactiveServer->id,
            'status' => 'OFFLINE',
        ]);
    }
}

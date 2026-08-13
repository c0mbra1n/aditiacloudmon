<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\AgentToken;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentApiTest extends TestCase
{
    use RefreshDatabase;

    private Server $server;
    private Agent $agent;
    private string $plainToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->server = Server::create([
            'name' => 'WIN-TEST-VPS',
            'hostname' => 'WIN-TEST-VPS',
            'status' => 'UNKNOWN',
        ]);

        $this->agent = Agent::create([
            'server_id' => $this->server->id,
            'agent_version' => '1.0.0',
            'status' => 'ACTIVE',
        ]);

        $this->plainToken = 'sec_agt_test1234567890abcdef1234567890';
        AgentToken::create([
            'agent_id' => $this->agent->id,
            'token_hash' => hash('sha256', $this->plainToken),
            'name' => 'Test Token',
        ]);
    }

    public function test_unauthorized_request_without_token_returns_401(): void
    {
        $response = $this->postJson('/api/v1/agent/heartbeat', [
            'agent_id' => $this->agent->id,
            'hostname' => 'WIN-TEST-VPS',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Unauthorized agent token',
        ]);
    }

    public function test_heartbeat_with_valid_token_updates_server_status(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->plainToken)
            ->postJson('/api/v1/agent/heartbeat', [
                'agent_id' => $this->agent->id,
                'hostname' => 'WIN-TEST-VPS',
                'agent_version' => '1.0.0',
                'timestamp' => now()->toIso8601String(),
                'uptime_seconds' => 12345,
                'cpu_usage_percent' => 25.5,
                'ram_usage_percent' => 60.0,
                'disk_usage_percent' => 70.0,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Heartbeat acknowledged',
            'data' => [
                'server_status' => 'ONLINE',
            ]
        ]);

        $this->assertDatabaseHas('servers', [
            'id' => $this->server->id,
            'status' => 'ONLINE',
        ]);
    }

    public function test_metrics_ingestion_stores_payload_in_database(): void
    {
        $payload = [
            'agent_id' => $this->agent->id,
            'timestamp' => now()->toIso8601String(),
            'cpu' => [
                'usage_percent' => 30.5,
                'load_average' => 1.2,
            ],
            'memory' => [
                'total_bytes' => 17179869184,
                'used_bytes' => 10000000000,
                'free_bytes' => 7179869184,
                'usage_percent' => 58.2,
            ],
            'disks' => [
                [
                    'drive_letter' => 'C:',
                    'label' => 'System',
                    'filesystem' => 'NTFS',
                    'total_bytes' => 100000000000,
                    'free_bytes' => 30000000000,
                    'used_bytes' => 70000000000,
                    'usage_percent' => 70.0,
                ]
            ],
            'networks' => [
                [
                    'interface_name' => 'Ethernet 1',
                    'ip_address' => '103.140.20.15',
                    'bytes_sent_per_sec' => 50000,
                    'bytes_recv_per_sec' => 120000,
                ]
            ]
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->plainToken)
            ->postJson('/api/v1/agent/metrics', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Metrics stored successfully',
        ]);

        $this->assertDatabaseHas('server_metrics', [
            'server_id' => $this->server->id,
            'cpu_usage_percent' => 30.5,
        ]);

        $this->assertDatabaseHas('server_disks', [
            'server_id' => $this->server->id,
            'drive_letter' => 'C:',
        ]);

        $this->assertDatabaseHas('server_networks', [
            'server_id' => $this->server->id,
            'interface_name' => 'Ethernet 1',
            'ip_address' => '103.140.20.15',
        ]);
    }
}

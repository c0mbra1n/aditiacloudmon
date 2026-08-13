<?php

namespace Tests\Feature;

use App\Livewire\Server\ServerDetail;
use App\Models\Agent;
use App\Models\AgentToken;
use App\Models\Server;
use App\Models\ServerPort;
use App\Models\ServerProcess;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProcessPortMonitoringTest extends TestCase
{
    use RefreshDatabase;

    private Server $server;
    private Agent $agent;
    private string $plainToken;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->server = Server::create([
            'name' => 'WIN-PROCESS-PORT-VPS',
            'hostname' => 'WIN-PROCESS-PORT-VPS',
            'status' => 'ONLINE',
        ]);

        $this->agent = Agent::create([
            'server_id' => $this->server->id,
            'agent_version' => '1.0.0',
            'status' => 'ACTIVE',
        ]);

        $this->plainToken = 'sec_agt_procport_1234567890abcdef';
        AgentToken::create([
            'agent_id' => $this->agent->id,
            'token_hash' => hash('sha256', $this->plainToken),
            'name' => 'ProcPort Test Token',
        ]);
    }

    public function test_unauthorized_processes_api_request_returns_401(): void
    {
        $response = $this->postJson('/api/v1/agent/processes', [
            'agent_id' => $this->agent->id,
            'processes' => [],
            'ports' => [],
        ]);

        $response->assertStatus(401);
    }

    public function test_processes_and_ports_ingestion_api_stores_data(): void
    {
        $payload = [
            'agent_id' => $this->agent->id,
            'timestamp' => now()->toIso8601String(),
            'processes' => [
                ['process_name' => 'mysqld.exe', 'pid' => 4812, 'cpu_percent' => 2.5, 'memory_bytes' => 450000000, 'status' => 'Running'],
                ['process_name' => 'nginx.exe', 'pid' => 1204, 'cpu_percent' => 0.8, 'memory_bytes' => 85000000, 'status' => 'Running'],
            ],
            'ports' => [
                ['port' => 80, 'protocol' => 'TCP', 'status' => 'Open'],
                ['port' => 443, 'protocol' => 'TCP', 'status' => 'Open'],
                ['port' => 3306, 'protocol' => 'TCP', 'status' => 'Open'],
                ['port' => 3389, 'protocol' => 'TCP', 'status' => 'Closed'],
            ]
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->plainToken)
            ->postJson('/api/v1/agent/processes', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Processes and ports status updated',
        ]);

        $this->assertDatabaseHas('server_processes', [
            'server_id' => $this->server->id,
            'process_name' => 'mysqld.exe',
            'pid' => 4812,
        ]);

        $this->assertDatabaseHas('server_ports', [
            'server_id' => $this->server->id,
            'port' => 80,
            'status' => 'Open',
        ]);
    }

    public function test_server_detail_view_renders_processes_and_ports_tabs(): void
    {
        ServerProcess::create([
            'server_id' => $this->server->id,
            'process_name' => 'httpd.exe',
            'pid' => 1010,
            'cpu_percent' => 1.2,
            'memory_bytes' => 100000000,
            'status' => 'Running',
        ]);

        ServerPort::create([
            'server_id' => $this->server->id,
            'port' => 443,
            'protocol' => 'TCP',
            'status' => 'Open',
        ]);

        Livewire::actingAs($this->user)
            ->test(ServerDetail::class, ['server' => $this->server])
            ->call('setTab', 'processes')
            ->assertSet('activeTab', 'processes')
            ->assertSee('httpd.exe')
            ->call('setTab', 'ports')
            ->assertSet('activeTab', 'ports')
            ->assertSee('443')
            ->assertSee('Open');
    }
}

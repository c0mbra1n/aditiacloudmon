<?php

namespace Tests\Feature;

use App\Livewire\Server\ServerDetail;
use App\Models\Agent;
use App\Models\AgentToken;
use App\Models\Server;
use App\Models\ServerService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WindowsServicesTest extends TestCase
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
            'name' => 'WIN-SERVICES-VPS',
            'hostname' => 'WIN-SERVICES-VPS',
            'status' => 'ONLINE',
        ]);

        $this->agent = Agent::create([
            'server_id' => $this->server->id,
            'agent_version' => '1.0.0',
            'status' => 'ACTIVE',
        ]);

        $this->plainToken = 'sec_agt_services_1234567890abcdef';
        AgentToken::create([
            'agent_id' => $this->agent->id,
            'token_hash' => hash('sha256', $this->plainToken),
            'name' => 'Services Test Token',
        ]);
    }

    public function test_unauthorized_services_api_request_returns_401(): void
    {
        $response = $this->postJson('/api/v1/agent/services', [
            'agent_id' => $this->agent->id,
            'services' => [],
        ]);

        $response->assertStatus(401);
    }

    public function test_services_ingestion_api_stores_services_in_database(): void
    {
        $payload = [
            'agent_id' => $this->agent->id,
            'timestamp' => now()->toIso8601String(),
            'services' => [
                ['service_name' => 'W3SVC', 'display_name' => 'World Wide Web Publishing Service', 'status' => 'Running'],
                ['service_name' => 'MySQL80', 'display_name' => 'MySQL Server 8.0', 'status' => 'Running'],
                ['service_name' => 'Redis', 'display_name' => 'Redis Service', 'status' => 'Stopped'],
            ]
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->plainToken)
            ->postJson('/api/v1/agent/services', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Windows Services status updated',
        ]);

        $this->assertDatabaseHas('server_services', [
            'server_id' => $this->server->id,
            'service_name' => 'W3SVC',
            'status' => 'Running',
        ]);

        $this->assertDatabaseHas('server_services', [
            'server_id' => $this->server->id,
            'service_name' => 'Redis',
            'status' => 'Stopped',
        ]);
    }

    public function test_server_detail_view_renders_services_tab(): void
    {
        ServerService::create([
            'server_id' => $this->server->id,
            'service_name' => 'W3SVC',
            'display_name' => 'IIS Web Server',
            'status' => 'Running',
        ]);

        Livewire::actingAs($this->user)
            ->test(ServerDetail::class, ['server' => $this->server])
            ->call('setTab', 'services')
            ->assertSet('activeTab', 'services')
            ->assertSee('W3SVC')
            ->assertSee('Running');
    }
}

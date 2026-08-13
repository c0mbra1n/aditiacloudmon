<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\AgentCommand;
use App\Models\AgentToken;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServerPowerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_queue_reboot_and_shutdown_commands(): void
    {
        $user = User::factory()->create();
        $server = Server::create([
            'name' => 'Test Windows VPS',
            'hostname' => 'WIN-TEST01',
            'status' => 'ONLINE',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Server\ServerDetail::class, ['server' => $server])
            ->call('rebootVps')
            ->assertDispatched('swal:toast');

        $this->assertDatabaseHas('agent_commands', [
            'server_id' => $server->id,
            'command' => 'reboot',
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Server\ServerDetail::class, ['server' => $server])
            ->call('shutdownVps')
            ->assertDispatched('swal:toast');

        $this->assertDatabaseHas('agent_commands', [
            'server_id' => $server->id,
            'command' => 'shutdown',
            'status' => 'pending',
            'created_by' => $user->id,
        ]);
    }

    public function test_heartbeat_api_returns_pending_command_and_marks_executed(): void
    {
        $server = Server::create([
            'name' => 'Production Windows VPS',
            'hostname' => 'WIN-PROD01',
            'status' => 'ONLINE',
        ]);

        $agent = Agent::create([
            'server_id' => $server->id,
            'status' => 'ACTIVE',
        ]);

        $plainToken = 'sec_agt_power_test_token_12345';
        AgentToken::create([
            'agent_id' => $agent->id,
            'token_hash' => hash('sha256', $plainToken),
            'name' => 'Test Token',
        ]);

        AgentCommand::create([
            'server_id' => $server->id,
            'command' => 'reboot',
            'status' => 'pending',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $plainToken)
            ->postJson('/api/v1/agent/heartbeat', [
                'agent_id' => $agent->id,
                'hostname' => 'WIN-PROD01',
                'cpu_usage_percent' => 25.5,
                'ram_usage_percent' => 45.0,
                'disk_usage_percent' => 60.0,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'pending_command' => 'reboot',
            ]);

        $this->assertDatabaseHas('agent_commands', [
            'server_id' => $server->id,
            'command' => 'reboot',
            'status' => 'executed',
        ]);
    }
}

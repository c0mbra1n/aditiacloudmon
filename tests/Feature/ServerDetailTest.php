<?php

namespace Tests\Feature;

use App\Livewire\Server\ServerDetail;
use App\Models\Agent;
use App\Models\AgentToken;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServerDetailTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Server $server;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->server = Server::create([
            'name' => 'WIN-DETAIL-VPS',
            'hostname' => 'WIN-DETAIL-VPS',
            'ip_address' => '103.140.20.99',
            'os_name' => 'Windows Server 2022 Datacenter',
            'status' => 'ONLINE',
            'cpu_cores' => 4,
            'ram_total_bytes' => 17179869184,
            'last_seen_at' => now(),
        ]);

        $agent = Agent::create([
            'server_id' => $this->server->id,
            'agent_version' => '1.0.0',
            'status' => 'ACTIVE',
        ]);

        AgentToken::create([
            'agent_id' => $agent->id,
            'token_hash' => hash('sha256', 'sec_agt_initial123'),
            'name' => 'Initial Token',
        ]);
    }

    public function test_guest_is_redirected_from_server_detail(): void
    {
        $response = $this->get(route('servers.show', $this->server->id));
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_server_detail_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('servers.show', $this->server->id));

        $response->assertStatus(200);
        $response->assertSee('WIN-DETAIL-VPS');
        $response->assertSee('103.140.20.99');
        $response->assertSee('ONLINE');
    }

    public function test_livewire_server_detail_tab_switching(): void
    {
        Livewire::actingAs($this->user)
            ->test(ServerDetail::class, ['server' => $this->server])
            ->assertSet('activeTab', 'overview')
            ->call('setTab', 'disk')
            ->assertSet('activeTab', 'disk')
            ->call('setTab', 'tokens')
            ->assertSet('activeTab', 'tokens');
    }

    public function test_secret_token_rotation_generates_new_token(): void
    {
        Livewire::actingAs($this->user)
            ->test(ServerDetail::class, ['server' => $this->server])
            ->call('regenerateToken')
            ->assertSet('newRegeneratedToken', fn ($val) => !empty($val) && str_starts_with($val, 'sec_agt_'));

        $this->assertDatabaseCount('agent_tokens', 2);
    }
}

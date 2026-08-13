<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Models\User;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Masuk ke Windows VPS Monitoring Dashboard');
    }

    public function test_admin_user_can_login_successfully(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@aditiacloudmon.com',
            'password' => bcrypt('password123'),
        ]);

        Livewire::test(Login::class)
            ->set('email', 'admin@aditiacloudmon.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_can_view_server_list(): void
    {
        $user = User::factory()->create();

        Server::create([
            'name' => 'WIN-TEST-01',
            'hostname' => 'WIN-TEST-01',
            'status' => 'ONLINE',
        ]);

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $response->assertSee('WIN-TEST-01');
    }
}

<?php

namespace Tests\Feature;

use App\Livewire\Settings\NotificationChannels;
use App\Models\Alert;
use App\Models\NotificationChannel;
use App\Models\NotificationLog;
use App\Models\Server;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationChannelsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Server $server;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->server = Server::create([
            'name' => 'WIN-NOTIF-VPS',
            'hostname' => 'WIN-NOTIF-VPS',
            'status' => 'ONLINE',
        ]);
    }

    public function test_notification_service_dispatches_telegram_and_discord(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
            'discord.com/*' => Http::response([], 204),
        ]);

        $telegram = NotificationChannel::create([
            'name' => 'Admin Telegram',
            'type' => 'TELEGRAM',
            'config' => [
                'bot_token' => '123456789:ABCDEF',
                'chat_id' => '-100987654321',
            ],
            'is_enabled' => true,
        ]);

        $discord = NotificationChannel::create([
            'name' => 'DevOps Discord',
            'type' => 'DISCORD',
            'config' => [
                'webhook_url' => 'https://discord.com/api/webhooks/123/abc',
            ],
            'is_enabled' => true,
        ]);

        $alert = Alert::create([
            'server_id' => $this->server->id,
            'title' => 'Critical CPU Breach',
            'message' => 'CPU reached 98%',
            'severity' => 'CRITICAL',
            'status' => 'OPEN',
            'triggered_at' => now(),
        ]);

        $service = new NotificationService();
        $service->sendAlertNotification($alert);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'telegram.org') || str_contains($request->url(), 'discord.com');
        });

        $this->assertDatabaseHas('notification_logs', [
            'alert_id' => $alert->id,
            'notification_channel_id' => $telegram->id,
            'status' => 'SUCCESS',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'alert_id' => $alert->id,
            'notification_channel_id' => $discord->id,
            'status' => 'SUCCESS',
        ]);
    }

    public function test_notification_channels_livewire_component(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        $channel = NotificationChannel::create([
            'name' => 'Telegram Test',
            'type' => 'TELEGRAM',
            'config' => [
                'bot_token' => '123:abc',
                'chat_id' => '12345',
            ],
            'is_enabled' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(NotificationChannels::class)
            ->assertSee('Telegram Test')
            ->call('testChannel', $channel->id)
            ->call('toggleEnabled', $channel->id);

        $this->assertDatabaseHas('notification_channels', [
            'id' => $channel->id,
            'is_enabled' => false,
        ]);
    }
}

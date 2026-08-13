<?php

namespace Tests\Feature;

use App\Livewire\Alerts\AlertIndex;
use App\Livewire\Alerts\AlertRules;
use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\Server;
use App\Models\ServerMetric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AlertEngineTest extends TestCase
{
    use RefreshDatabase;

    private Server $server;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->server = Server::create([
            'name' => 'WIN-ALERT-VPS',
            'hostname' => 'WIN-ALERT-VPS',
            'status' => 'ONLINE',
        ]);
    }

    public function test_evaluate_alerts_command_triggers_and_auto_resolves_alerts(): void
    {
        // 1. Create a CPU High Usage Rule (> 80%)
        $rule = AlertRule::create([
            'server_id' => $this->server->id,
            'name' => 'High CPU Alert (> 80%)',
            'metric_type' => 'CPU',
            'operator' => '>',
            'threshold_value' => 80.0,
            'severity' => 'CRITICAL',
            'cooldown_minutes' => 15,
            'is_enabled' => true,
        ]);

        // 2. High CPU Metric Breach (88%)
        ServerMetric::create([
            'server_id' => $this->server->id,
            'cpu_usage_percent' => 88.0,
            'ram_usage_percent' => 40.0,
        ]);

        // Run evaluation command
        $this->artisan('monitor:evaluate-alerts')->assertExitCode(0);

        // Assert OPEN Alert created
        $this->assertDatabaseHas('alerts', [
            'server_id' => $this->server->id,
            'alert_rule_id' => $rule->id,
            'severity' => 'CRITICAL',
            'status' => 'OPEN',
        ]);

        // 3. Telemetry returns to normal (45%)
        ServerMetric::create([
            'server_id' => $this->server->id,
            'cpu_usage_percent' => 45.0,
            'ram_usage_percent' => 40.0,
        ]);

        // Run evaluation command again
        $this->artisan('monitor:evaluate-alerts')->assertExitCode(0);

        // Assert Alert auto-resolved
        $this->assertDatabaseHas('alerts', [
            'server_id' => $this->server->id,
            'alert_rule_id' => $rule->id,
            'status' => 'RESOLVED',
        ]);
    }

    public function test_alert_index_and_rules_livewire_components(): void
    {
        $rule = AlertRule::create([
            'name' => 'Test Global Rule',
            'metric_type' => 'RAM',
            'operator' => '>',
            'threshold_value' => 90.0,
            'severity' => 'WARNING',
            'is_enabled' => true,
        ]);

        $alert = Alert::create([
            'server_id' => $this->server->id,
            'alert_rule_id' => $rule->id,
            'title' => 'RAM High Alert',
            'message' => 'RAM High Alert',
            'severity' => 'WARNING',
            'status' => 'OPEN',
            'triggered_at' => now(),
        ]);

        // Test AlertIndex rendering and Acknowledge action
        Livewire::actingAs($this->user)
            ->test(AlertIndex::class)
            ->assertSee('RAM High Alert')
            ->call('acknowledgeAlert', $alert->id);

        $this->assertDatabaseHas('alerts', [
            'id' => $alert->id,
            'status' => 'ACKNOWLEDGED',
        ]);

        // Test AlertRules rendering and Toggle Enabled action
        Livewire::actingAs($this->user)
            ->test(AlertRules::class)
            ->assertSee('Test Global Rule')
            ->call('toggleEnabled', $rule->id);

        $this->assertDatabaseHas('alert_rules', [
            'id' => $rule->id,
            'is_enabled' => false,
        ]);
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\Server;
use App\Models\ServerMetric;
use App\Models\ServerPort;
use App\Models\ServerService;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class EvaluateAlertsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:evaluate-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluates active alert rules against latest server telemetry and manages alert lifecycles (Trigger/Cooldown/Auto-Resolve)';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $rules = AlertRule::where('is_enabled', true)->get();

        foreach ($rules as $rule) {
            $servers = $rule->server_id
                ? Server::where('id', $rule->server_id)->get()
                : Server::all();

            foreach ($servers as $server) {
                $isBreached = $this->evaluateRuleCondition($rule, $server);

                $activeAlert = Alert::where('server_id', $server->id)
                    ->where('alert_rule_id', $rule->id)
                    ->whereIn('status', ['OPEN', 'ACKNOWLEDGED'])
                    ->first();

                if ($isBreached) {
                    if (!$activeAlert) {
                        // Check cooldown window
                        $lastAlert = Alert::where('server_id', $server->id)
                            ->where('alert_rule_id', $rule->id)
                            ->latest('triggered_at')
                            ->first();

                        $inCooldown = $lastAlert && $lastAlert->triggered_at->gt(now()->subMinutes($rule->cooldown_minutes));

                        if (!$inCooldown) {
                            $newAlert = Alert::create([
                                'server_id' => $server->id,
                                'alert_rule_id' => $rule->id,
                                'title' => "Alert: {$rule->name} on {$server->name}",
                                'message' => "Rule [{$rule->name}] triggered for {$server->name}. Metric: {$rule->metric_type} breached threshold.",
                                'severity' => $rule->severity,
                                'status' => 'OPEN',
                                'triggered_at' => now(),
                            ]);

                            $notificationService->sendAlertNotification($newAlert);

                            $this->info("Triggered OPEN alert for rule [{$rule->name}] on server [{$server->name}]");
                        }
                    }
                } else {
                    // Auto-resolve active alert if condition returned to normal
                    if ($activeAlert) {
                        $activeAlert->update([
                            'status' => 'RESOLVED',
                            'resolved_at' => now(),
                        ]);

                        $notificationService->sendAlertNotification($activeAlert);

                        $this->info("Auto-resolved alert [#{$activeAlert->id}] for rule [{$rule->name}] on server [{$server->name}]");
                    }
                }
            }
        }

        return 0;
    }

    private function evaluateRuleCondition(AlertRule $rule, Server $server): bool
    {
        $latestMetric = ServerMetric::where('server_id', $server->id)->latest()->first();

        switch ($rule->metric_type) {
            case 'CPU':
                $val = $latestMetric ? $latestMetric->cpu_usage_percent : 0;
                return $this->compareValue($val, $rule->operator, $rule->threshold_value);

            case 'RAM':
                $val = $latestMetric ? $latestMetric->ram_usage_percent : 0;
                return $this->compareValue($val, $rule->operator, $rule->threshold_value);

            case 'DISK':
                $mainDisk = $latestMetric && $latestMetric->disks->count() > 0 ? $latestMetric->disks->first() : null;
                $val = $mainDisk ? $mainDisk->usage_percent : 0;
                return $this->compareValue($val, $rule->operator, $rule->threshold_value);

            case 'OFFLINE':
                return $server->status === 'OFFLINE';

            case 'SERVICE':
                $svc = ServerService::where('server_id', $server->id)
                    ->where('service_name', $rule->target_name)
                    ->first();
                return $svc ? ($svc->status === 'Stopped') : true;

            case 'PORT':
                $port = ServerPort::where('server_id', $server->id)
                    ->where('port', (int)$rule->target_name)
                    ->first();
                return $port ? ($port->status === 'Closed') : true;

            default:
                return false;
        }
    }

    private function compareValue(float $value, string $operator, float $threshold): bool
    {
        return match ($operator) {
            '>' => $value > $threshold,
            '>=' => $value >= $threshold,
            '<' => $value < $threshold,
            '<=' => $value <= $threshold,
            '=' => $value == $threshold,
            '!=' => $value != $threshold,
            default => $value > $threshold,
        };
    }
}

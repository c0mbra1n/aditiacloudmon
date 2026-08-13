<?php

namespace App\Livewire\Server;

use App\Models\AgentToken;
use App\Models\MetricAggregate1m;
use App\Models\MetricAggregate5m;
use App\Models\MetricAggregateDaily;
use App\Models\Server;
use App\Models\ServerMetric;
use App\Models\ServerPort;
use App\Models\ServerProcess;
use App\Models\ServerService;
use Illuminate\Support\Str;
use Livewire\Component;

class ServerDetail extends Component
{
    public Server $server;
    public string $activeTab = 'overview';
    public string $selectedPeriod = '1h';
    public string $newRegeneratedToken = '';
    public string $serviceSearch = '';
    public string $processSearch = '';

    protected $listeners = [
        'tokenRevoked' => 'revokeToken',
        'serverDeleted' => 'deleteServer',
    ];

    public function mount(Server $server): void
    {
        $this->server = $server->load(['agent.tokens', 'services', 'processes', 'ports']);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function setPeriod(string $period): void
    {
        $this->selectedPeriod = $period;
    }

    public function regenerateToken(): void
    {
        if (!$this->server->agent) {
            $this->dispatch('swal:toast', [
                'icon' => 'error',
                'title' => 'Agent belum terdaftar di server ini.'
            ]);
            return;
        }

        $plainToken = 'sec_agt_' . Str::random(32);

        AgentToken::create([
            'agent_id' => $this->server->agent->id,
            'token_hash' => hash('sha256', $plainToken),
            'name' => 'Rotated Token (' . now()->format('d/m/Y H:i') . ')',
        ]);

        $this->newRegeneratedToken = $plainToken;
        $this->server->load('agent.tokens');

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Token Secret Agent Baru Berhasil Dibuat!'
        ]);
    }

    public function confirmRevokeToken(string $tokenId): void
    {
        $this->dispatch('swal:confirm', [
            'title' => 'Cabut Secret Token Agent?',
            'text' => 'Token ini tidak dapat digunakan lagi oleh Agent untuk mengirim heartbeat.',
            'icon' => 'warning',
            'confirmButtonText' => 'Ya, Cabut Token',
            'cancelButtonText' => 'Batal',
            'method' => 'tokenRevoked',
            'params' => [$tokenId],
        ]);
    }

    public function revokeToken(string $tokenId): void
    {
        $token = AgentToken::find($tokenId);
        if ($token) {
            $token->update(['revoked_at' => now()]);
            $this->server->load('agent.tokens');

            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Secret token berhasil dicabut.'
            ]);
        }
    }

    public function getFormattedUptimeProperty(): string
    {
        $latestMetric = ServerMetric::where('server_id', $this->server->id)
            ->latest()
            ->first();

        $seconds = $latestMetric ? $latestMetric->uptime_seconds : 0;
        if ($seconds <= 0) return 'Belum ada data';

        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $parts = [];
        if ($days > 0) $parts[] = "{$days} Hari";
        if ($hours > 0) $parts[] = "{$hours} Jam";
        $parts[] = "{$minutes} Menit";

        return implode(' ', $parts);
    }

    public function getChartDataProperty(): array
    {
        $categories = [];
        $cpuData = [];
        $ramData = [];

        switch ($this->selectedPeriod) {
            case '6h':
                $samples = MetricAggregate1m::where('server_id', $this->server->id)
                    ->where('bucket_at', '>=', now()->subHours(6))
                    ->orderBy('bucket_at', 'asc')
                    ->get();
                foreach ($samples as $sample) {
                    $categories[] = $sample->bucket_at->format('H:i');
                    $cpuData[] = round($sample->avg_cpu, 1);
                    $ramData[] = round($sample->avg_ram, 1);
                }
                break;

            case '24h':
                $samples = MetricAggregate5m::where('server_id', $this->server->id)
                    ->where('bucket_at', '>=', now()->subHours(24))
                    ->orderBy('bucket_at', 'asc')
                    ->get();
                foreach ($samples as $sample) {
                    $categories[] = $sample->bucket_at->format('H:i');
                    $cpuData[] = round($sample->avg_cpu, 1);
                    $ramData[] = round($sample->avg_ram, 1);
                }
                break;

            case '7d':
                $samples = MetricAggregate5m::where('server_id', $this->server->id)
                    ->where('bucket_at', '>=', now()->subDays(7))
                    ->orderBy('bucket_at', 'asc')
                    ->get();
                foreach ($samples as $sample) {
                    $categories[] = $sample->bucket_at->format('d/m H:i');
                    $cpuData[] = round($sample->avg_cpu, 1);
                    $ramData[] = round($sample->avg_ram, 1);
                }
                break;

            case '30d':
                $samples = MetricAggregateDaily::where('server_id', $this->server->id)
                    ->where('bucket_date', '>=', now()->subDays(30))
                    ->orderBy('bucket_date', 'asc')
                    ->get();
                foreach ($samples as $sample) {
                    $categories[] = $sample->bucket_date->format('d/m');
                    $cpuData[] = round($sample->avg_cpu, 1);
                    $ramData[] = round($sample->avg_ram, 1);
                }
                break;

            case '1h':
            default:
                $samples = ServerMetric::where('server_id', $this->server->id)
                    ->where('created_at', '>=', now()->subHour())
                    ->orderBy('created_at', 'asc')
                    ->get();
                foreach ($samples as $sample) {
                    $categories[] = $sample->created_at->format('H:i:s');
                    $cpuData[] = round($sample->cpu_usage_percent, 1);
                    $ramData[] = round($sample->ram_usage_percent, 1);
                }
                break;
        }

        return [
            'categories' => $categories,
            'cpu' => $cpuData,
            'ram' => $ramData,
        ];
    }

    public function render()
    {
        $this->server->refresh();

        $latestMetric = ServerMetric::with(['disks', 'networks'])
            ->where('server_id', $this->server->id)
            ->latest()
            ->first();

        // Services Query
        $servicesQuery = ServerService::where('server_id', $this->server->id);
        if (!empty($this->serviceSearch)) {
            $servicesQuery->where(function ($q) {
                $q->where('service_name', 'like', '%' . $this->serviceSearch . '%')
                  ->orWhere('display_name', 'like', '%' . $this->serviceSearch . '%');
            });
        }
        $services = $servicesQuery->orderBy('service_name', 'asc')->get();

        // Processes Query
        $processesQuery = ServerProcess::where('server_id', $this->server->id);
        if (!empty($this->processSearch)) {
            $processesQuery->where('process_name', 'like', '%' . $this->processSearch . '%');
        }
        $processes = $processesQuery->orderBy('process_name', 'asc')->get();

        // Ports Query
        $ports = ServerPort::where('server_id', $this->server->id)
            ->orderBy('port', 'asc')
            ->get();

        return view('livewire.server.server-detail', [
            'latestMetric' => $latestMetric,
            'services' => $services,
            'processes' => $processes,
            'ports' => $ports,
            'formattedUptime' => $this->getFormattedUptimeProperty(),
            'chartData' => $this->getChartDataProperty(),
        ])->layout('components.layouts.app', ['title' => 'Detail Server - ' . $this->server->name]);
    }
}

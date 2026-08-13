<?php

namespace App\Livewire\Server;

use App\Models\Server;
use App\Models\Agent;
use App\Models\AgentToken;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class ServerList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'ALL';
    public bool $showRegisterModal = false;

    // Form inputs for new server registration
    public string $newServerName = '';
    public string $newHostname = '';
    public string $generatedToken = '';
    public string $generatedServerId = '';

    protected $listeners = [
        'serverDeleted' => 'deleteServer',
    ];

    protected array $rules = [
        'newServerName' => 'required|min:3|max:255',
        'newHostname' => 'required|min:3|max:255|unique:servers,hostname',
    ];

    protected array $messages = [
        'newServerName.required' => 'Nama server wajib diisi.',
        'newHostname.required' => 'Hostname Windows VPS wajib diisi.',
        'newHostname.unique' => 'Hostname tersebut sudah terdaftar di sistem.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openRegisterModal(): void
    {
        $this->resetForm();
        $this->showRegisterModal = true;
    }

    public function closeRegisterModal(): void
    {
        $this->showRegisterModal = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->newServerName = '';
        $this->newHostname = '';
        $this->generatedToken = '';
        $this->generatedServerId = '';
        $this->resetValidation();
    }

    public function createServer()
    {
        $this->validate();

        // 1. Create Server Record
        $server = Server::create([
            'name' => $this->newServerName,
            'hostname' => strtoupper($this->newHostname),
            'status' => 'UNKNOWN',
            'last_seen_at' => null,
        ]);

        // 2. Create Agent Record
        $agent = Agent::create([
            'server_id' => $server->id,
            'agent_version' => '1.0.0',
            'status' => 'ACTIVE',
            'heartbeat_interval_seconds' => 30,
        ]);

        // 3. Generate Secret Token
        $plainToken = 'sec_agt_' . Str::random(32);
        AgentToken::create([
            'agent_id' => $agent->id,
            'token_hash' => hash('sha256', $plainToken),
            'name' => 'Initial Secret Token',
        ]);

        $this->generatedToken = $plainToken;
        $this->generatedServerId = $server->id;

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Server & Agent Credentials Berhasil Dibuat!'
        ]);
    }

    public function confirmDeleteServer(string $serverId): void
    {
        $server = Server::find($serverId);
        if (!$server) return;

        $this->dispatch('swal:confirm', [
            'title' => 'Hapus Server Monitoring?',
            'text' => "Apakah Anda yakin ingin menghapus server {$server->name} ({$server->hostname})?",
            'icon' => 'warning',
            'confirmButtonText' => 'Ya, Hapus Server',
            'cancelButtonText' => 'Batal',
            'method' => 'serverDeleted',
            'params' => [$serverId],
        ]);
    }

    public function deleteServer(string $serverId): void
    {
        $server = Server::find($serverId);
        if ($server) {
            $server->delete();
            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Server berhasil dihapus.'
            ]);
        }
    }

    public function render()
    {
        // Query Servers with search & status filter
        $query = Server::query()->with('agent');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('hostname', 'like', '%' . $this->search . '%')
                  ->orWhere('ip_address', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== 'ALL') {
            $query->where('status', $this->statusFilter);
        }

        $servers = $query->orderBy('updated_at', 'desc')->paginate(10);

        // Status Counters Summary
        $totalServers = Server::count();
        $onlineCount = Server::where('status', 'ONLINE')->count();
        $warningCount = Server::where('status', 'WARNING')->count();
        $criticalCount = Server::where('status', 'CRITICAL')->count();
        $offlineCount = Server::where('status', 'OFFLINE')->count();
        $unknownCount = Server::whereIn('status', ['UNKNOWN', 'MAINTENANCE'])->count();

        return view('livewire.server.server-list', [
            'servers' => $servers,
            'totalServers' => $totalServers,
            'onlineCount' => $onlineCount,
            'warningCount' => $warningCount,
            'criticalCount' => $criticalCount,
            'offlineCount' => $offlineCount,
            'unknownCount' => $unknownCount,
        ])->layout('components.layouts.app', ['title' => 'Daftar Windows VPS']);
    }
}

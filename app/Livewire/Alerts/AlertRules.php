<?php

namespace App\Livewire\Alerts;

use App\Models\AlertRule;
use App\Models\Server;
use Livewire\Component;

class AlertRules extends Component
{
    public bool $showModal = false;
    public ?int $editingRuleId = null;

    public ?string $server_id = null;
    public string $name = '';
    public string $metric_type = 'CPU';
    public string $target_name = '';
    public string $operator = '>';
    public float $threshold_value = 85.0;
    public string $severity = 'WARNING';
    public int $duration_minutes = 1;
    public int $cooldown_minutes = 15;
    public bool $is_enabled = true;

    protected $listeners = [
        'ruleDeleted' => 'deleteRule',
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'metric_type' => 'required|in:CPU,RAM,DISK,OFFLINE,SERVICE,PORT',
        'operator' => 'required|in:>,>=,<,<=,=,!=',
        'threshold_value' => 'required|numeric',
        'severity' => 'required|in:WARNING,CRITICAL',
        'duration_minutes' => 'required|integer|min:1',
        'cooldown_minutes' => 'required|integer|min:1',
    ];

    public function createRule(): void
    {
        $this->resetInput();
        $this->showModal = true;
    }

    public function editRule(int $ruleId): void
    {
        $rule = AlertRule::find($ruleId);
        if ($rule) {
            $this->editingRuleId = $rule->id;
            $this->server_id = $rule->server_id;
            $this->name = $rule->name;
            $this->metric_type = $rule->metric_type;
            $this->target_name = $rule->target_name ?? '';
            $this->operator = $rule->operator;
            $this->threshold_value = $rule->threshold_value;
            $this->severity = $rule->severity;
            $this->duration_minutes = $rule->duration_minutes;
            $this->cooldown_minutes = $rule->cooldown_minutes;
            $this->is_enabled = $rule->is_enabled;
            $this->showModal = true;
        }
    }

    public function saveRule(): void
    {
        $this->validate();

        AlertRule::updateOrCreate(
            ['id' => $this->editingRuleId],
            [
                'server_id' => $this->server_id ?: null,
                'name' => $this->name,
                'metric_type' => $this->metric_type,
                'target_name' => in_array($this->metric_type, ['SERVICE', 'PORT']) ? $this->target_name : null,
                'operator' => $this->operator,
                'threshold_value' => $this->threshold_value,
                'severity' => $this->severity,
                'duration_minutes' => $this->duration_minutes,
                'cooldown_minutes' => $this->cooldown_minutes,
                'is_enabled' => $this->is_enabled,
            ]
        );

        $this->showModal = false;
        $this->resetInput();

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Aturan alert berhasil disimpan!'
        ]);
    }

    public function toggleEnabled(int $ruleId): void
    {
        $rule = AlertRule::find($ruleId);
        if ($rule) {
            $rule->update(['is_enabled' => !$rule->is_enabled]);
            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Status aktif aturan alert diperbarui.'
            ]);
        }
    }

    public function confirmDeleteRule(int $ruleId): void
    {
        $this->dispatch('swal:confirm', [
            'title' => 'Hapus Aturan Alert?',
            'text' => 'Aturan alert yang dihapus tidak dapat dikembalikan.',
            'icon' => 'warning',
            'confirmButtonText' => 'Ya, Hapus',
            'cancelButtonText' => 'Batal',
            'method' => 'ruleDeleted',
            'params' => [$ruleId],
        ]);
    }

    public function deleteRule(int $ruleId): void
    {
        $rule = AlertRule::find($ruleId);
        if ($rule) {
            $rule->delete();
            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Aturan alert berhasil dihapus.'
            ]);
        }
    }

    private function resetInput(): void
    {
        $this->editingRuleId = null;
        $this->server_id = null;
        $this->name = '';
        $this->metric_type = 'CPU';
        $this->target_name = '';
        $this->operator = '>';
        $this->threshold_value = 85.0;
        $this->severity = 'WARNING';
        $this->duration_minutes = 1;
        $this->cooldown_minutes = 15;
        $this->is_enabled = true;
    }

    public function render()
    {
        $rules = AlertRule::with('server')->orderBy('id', 'desc')->get();
        $servers = Server::all();

        return view('livewire.alerts.alert-rules', [
            'rules' => $rules,
            'servers' => $servers,
        ])->layout('components.layouts.app', ['title' => 'Manajemen Aturan Alert Rules']);
    }
}

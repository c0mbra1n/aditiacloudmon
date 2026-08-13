<?php

namespace App\Livewire\Alerts;

use App\Models\Alert;
use Livewire\Component;
use Livewire\WithPagination;

class AlertIndex extends Component
{
    use WithPagination;

    public string $statusFilter = 'ALL';
    public string $severityFilter = 'ALL';

    protected $listeners = [
        'alertAcknowledged' => 'acknowledgeAlert',
        'alertResolved' => 'resolveAlert',
    ];

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function setSeverityFilter(string $severity): void
    {
        $this->severityFilter = $severity;
        $this->resetPage();
    }

    public function confirmAcknowledge(int $alertId): void
    {
        $this->dispatch('swal:confirm', [
            'title' => 'Konfirmasi Acknowledge Alert?',
            'text' => 'Tandai bahwa alert ini sedang ditangani.',
            'icon' => 'question',
            'confirmButtonText' => 'Ya, Acknowledge',
            'cancelButtonText' => 'Batal',
            'method' => 'alertAcknowledged',
            'params' => [$alertId],
        ]);
    }

    public function acknowledgeAlert(int $alertId): void
    {
        $alert = Alert::find($alertId);
        if ($alert) {
            $alert->update([
                'status' => 'ACKNOWLEDGED',
                'acknowledged_at' => now(),
                'acknowledged_by' => auth()->id(),
            ]);

            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Alert telah di-acknowledge.'
            ]);
        }
    }

    public function confirmResolve(int $alertId): void
    {
        $this->dispatch('swal:confirm', [
            'title' => 'Tandai Alert Selesai (Resolve)?',
            'text' => 'Alert akan diubah statusnya menjadi RESOLVED.',
            'icon' => 'info',
            'confirmButtonText' => 'Ya, Resolve',
            'cancelButtonText' => 'Batal',
            'method' => 'alertResolved',
            'params' => [$alertId],
        ]);
    }

    public function resolveAlert(int $alertId): void
    {
        $alert = Alert::find($alertId);
        if ($alert) {
            $alert->update([
                'status' => 'RESOLVED',
                'resolved_at' => now(),
            ]);

            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Alert berhasil di-resolve.'
            ]);
        }
    }

    public function render()
    {
        $query = Alert::with(['server', 'acknowledgedBy'])->latest();

        if ($this->statusFilter !== 'ALL') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->severityFilter !== 'ALL') {
            $query->where('severity', $this->severityFilter);
        }

        $alerts = $query->paginate(15);

        return view('livewire.alerts.alert-index', [
            'alerts' => $alerts,
        ])->layout('components.layouts.app', ['title' => 'Daftar Notification Alerts']);
    }
}

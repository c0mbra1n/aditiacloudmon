<?php

namespace App\Livewire\Settings;

use App\Models\NotificationChannel;
use App\Services\NotificationService;
use Livewire\Component;

class NotificationChannels extends Component
{
    public bool $showModal = false;
    public ?int $editingChannelId = null;

    public string $name = '';
    public string $type = 'TELEGRAM';
    public string $telegram_bot_token = '';
    public string $telegram_chat_id = '';
    public string $discord_webhook_url = '';
    public bool $is_enabled = true;

    protected $listeners = [
        'channelDeleted' => 'deleteChannel',
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|in:TELEGRAM,DISCORD',
    ];

    public function createChannel(): void
    {
        $this->resetInput();
        $this->showModal = true;
    }

    public function editChannel(int $channelId): void
    {
        $channel = NotificationChannel::find($channelId);
        if ($channel) {
            $this->editingChannelId = $channel->id;
            $this->name = $channel->name;
            $this->type = $channel->type;
            $this->telegram_bot_token = $channel->config['bot_token'] ?? '';
            $this->telegram_chat_id = $channel->config['chat_id'] ?? '';
            $this->discord_webhook_url = $channel->config['webhook_url'] ?? '';
            $this->is_enabled = $channel->is_enabled;
            $this->showModal = true;
        }
    }

    public function saveChannel(): void
    {
        $this->validate();

        $config = [];
        if ($this->type === 'TELEGRAM') {
            $config = [
                'bot_token' => $this->telegram_bot_token,
                'chat_id' => $this->telegram_chat_id,
            ];
        } elseif ($this->type === 'DISCORD') {
            $config = [
                'webhook_url' => $this->discord_webhook_url,
            ];
        }

        NotificationChannel::updateOrCreate(
            ['id' => $this->editingChannelId],
            [
                'name' => $this->name,
                'type' => $this->type,
                'config' => $config,
                'is_enabled' => $this->is_enabled,
            ]
        );

        $this->showModal = false;
        $this->resetInput();

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Kanal notifikasi berhasil disimpan!'
        ]);
    }

    public function toggleEnabled(int $channelId): void
    {
        $channel = NotificationChannel::find($channelId);
        if ($channel) {
            $channel->update(['is_enabled' => !$channel->is_enabled]);
            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Status aktif kanal notifikasi diperbarui.'
            ]);
        }
    }

    public function testChannel(int $channelId, NotificationService $notificationService): void
    {
        $channel = NotificationChannel::find($channelId);
        if ($channel) {
            $sent = $notificationService->sendTestNotification($channel);

            if ($sent) {
                $this->dispatch('swal:toast', [
                    'icon' => 'success',
                    'title' => 'Pesan uji coba berhasil terkirim ke ' . $channel->name
                ]);
            } else {
                $this->dispatch('swal:toast', [
                    'icon' => 'error',
                    'title' => 'Gagal mengirim pesan ke ' . $channel->name . '. Periksa token/URL!'
                ]);
            }
        }
    }

    public function confirmDeleteChannel(int $channelId): void
    {
        $this->dispatch('swal:confirm', [
            'title' => 'Hapus Kanal Notifikasi?',
            'text' => 'Kanal yang dihapus tidak akan menerima alert notifikasi lagi.',
            'icon' => 'warning',
            'confirmButtonText' => 'Ya, Hapus',
            'cancelButtonText' => 'Batal',
            'method' => 'channelDeleted',
            'params' => [$channelId],
        ]);
    }

    public function deleteChannel(int $channelId): void
    {
        $channel = NotificationChannel::find($channelId);
        if ($channel) {
            $channel->delete();
            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Kanal notifikasi berhasil dihapus.'
            ]);
        }
    }

    private function resetInput(): void
    {
        $this->editingChannelId = null;
        $this->name = '';
        $this->type = 'TELEGRAM';
        $this->telegram_bot_token = '';
        $this->telegram_chat_id = '';
        $this->discord_webhook_url = '';
        $this->is_enabled = true;
    }

    public function render()
    {
        $channels = NotificationChannel::orderBy('id', 'desc')->get();

        return view('livewire.settings.notification-channels', [
            'channels' => $channels,
        ])->layout('components.layouts.app', ['title' => 'Pengaturan Notification Channels']);
    }
}

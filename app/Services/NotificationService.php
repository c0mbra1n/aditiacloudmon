<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\NotificationChannel;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Broadcast alert notification to all active channels
     */
    public function sendAlertNotification(Alert $alert): void
    {
        $channels = NotificationChannel::where('is_enabled', true)->get();

        foreach ($channels as $channel) {
            $this->dispatchToChannel($channel, $alert);
        }
    }

    /**
     * Send test notification to a specific channel
     */
    public function sendTestNotification(NotificationChannel $channel): bool
    {
        $dummyAlert = new Alert([
            'title' => 'Test Notification Channel Connection',
            'message' => 'Test message from AditiaCloudMon Dashboard. Channel configuration verified successfully!',
            'severity' => 'WARNING',
            'status' => 'OPEN',
            'triggered_at' => now(),
        ]);
        $dummyAlert->setRelation('server', new \App\Models\Server(['name' => 'Test Server VPS']));

        return $this->dispatchToChannel($channel, $dummyAlert);
    }

    private function dispatchToChannel(NotificationChannel $channel, Alert $alert): bool
    {
        try {
            $success = false;
            $responseMessage = '';

            if ($channel->type === 'TELEGRAM') {
                $botToken = $channel->config['bot_token'] ?? '';
                $chatId = $channel->config['chat_id'] ?? '';

                if (!empty($botToken) && !empty($chatId)) {
                    $icon = $alert->status === 'RESOLVED' ? '✅' : ($alert->severity === 'CRITICAL' ? '🔴' : '⚠️');
                    $serverName = $alert->server->name ?? 'Global Server';

                    $text = "{$icon} <b>AditiaCloudMon Alert: {$alert->status}</b>\n\n";
                    $text .= "<b>Server:</b> {$serverName}\n";
                    $text .= "<b>Severity:</b> {$alert->severity}\n";
                    $text .= "<b>Judul:</b> {$alert->title}\n";
                    $text .= "<b>Pesan:</b> {$alert->message}\n";
                    $text .= "<b>Waktu:</b> " . ($alert->triggered_at ? $alert->triggered_at->format('H:i:s d/m/Y') : now()->format('H:i:s d/m/Y'));

                    $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id' => $chatId,
                        'text' => $text,
                        'parse_mode' => 'HTML',
                    ]);

                    $success = $response->successful();
                    $responseMessage = $response->body();
                }
            } elseif ($channel->type === 'DISCORD') {
                $webhookUrl = $channel->config['webhook_url'] ?? '';

                if (!empty($webhookUrl)) {
                    $color = $alert->status === 'RESOLVED' ? 65280 : ($alert->severity === 'CRITICAL' ? 15158332 : 16753920);
                    $serverName = $alert->server->name ?? 'Global Server';

                    $response = Http::post($webhookUrl, [
                        'embeds' => [
                            [
                                'title' => "AditiaCloudMon Alert: {$alert->status}",
                                'description' => "**Server:** {$serverName}\n**Severity:** {$alert->severity}\n**Judul:** {$alert->title}\n**Pesan:** {$alert->message}",
                                'color' => $color,
                                'timestamp' => now()->toIso8601String(),
                            ]
                        ]
                    ]);

                    $success = $response->successful();
                    $responseMessage = $response->body();
                }
            }

            if ($alert->exists && $channel->exists) {
                NotificationLog::create([
                    'alert_id' => $alert->id,
                    'notification_channel_id' => $channel->id,
                    'status' => $success ? 'SUCCESS' : 'FAILED',
                    'response_message' => substr($responseMessage, 0, 1000),
                    'sent_at' => now(),
                ]);
            }

            return $success;
        } catch (\Exception $ex) {
            Log::error("Failed to send notification via channel [{$channel->name}]: " . $ex->getMessage());
            return false;
        }
    }
}

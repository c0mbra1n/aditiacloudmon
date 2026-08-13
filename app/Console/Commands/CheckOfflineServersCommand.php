<?php

namespace App\Console\Commands;

use App\Models\Server;
use Illuminate\Console\Command;

class CheckOfflineServersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:check-offline {--threshold=2 : Ambang batas menit tanpa heartbeat untuk mengubah status menjadi OFFLINE}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memeriksa dan memperbarui status server menjadi OFFLINE jika heartbeat tidak diterima melewati threshold.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $thresholdMinutes = (int) $this->option('threshold');
        $cutoffTime = now()->subMinutes($thresholdMinutes);

        // Find servers that missed heartbeat and are not in MAINTENANCE mode
        $offlineServers = Server::whereNotIn('status', ['MAINTENANCE', 'OFFLINE'])
            ->where(function ($query) use ($cutoffTime) {
                $query->whereNull('last_seen_at')
                      ->orWhere('last_seen_at', '<', $cutoffTime);
            })
            ->get();

        $updatedCount = 0;

        foreach ($offlineServers as $server) {
            $server->update([
                'status' => 'OFFLINE',
            ]);
            $updatedCount++;
            $this->info("Server [{$server->name}] ({$server->hostname}) diubah menjadi status OFFLINE (Last Seen: " . ($server->last_seen_at ? $server->last_seen_at->diffForHumans() : 'Belum pernah ping') . ").");
        }

        $this->info("Pemeriksaan selesai. Total {$updatedCount} server diubah menjadi OFFLINE.");

        return Command::SUCCESS;
    }
}

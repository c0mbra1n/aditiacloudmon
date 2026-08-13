<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Server;
use App\Models\Agent;
use App\Models\AgentToken;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        $user = User::updateOrCreate(
            ['email' => 'admin@aditiacloudmon.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Dummy Server 1 (For testing Phase 1 UI)
        $server1 = Server::updateOrCreate(
            ['hostname' => 'WIN-JAKARTA-01'],
            [
                'name' => 'Production Web Server (Jakarta)',
                'ip_address' => '103.140.20.15',
                'os_name' => 'Windows Server 2022 Datacenter',
                'os_version' => '10.0.20348',
                'agent_version' => '1.0.0',
                'cpu_model' => 'Intel(R) Xeon(R) Gold 6248R CPU @ 3.00GHz',
                'cpu_cores' => 4,
                'ram_total_bytes' => 17179869184, // 16 GB
                'status' => 'ONLINE',
                'last_seen_at' => now(),
            ]
        );

        $agent1 = Agent::updateOrCreate(
            ['server_id' => $server1->id],
            [
                'agent_version' => '1.0.0',
                'status' => 'ACTIVE',
                'heartbeat_interval_seconds' => 30,
            ]
        );

        AgentToken::updateOrCreate(
            ['agent_id' => $agent1->id, 'name' => 'Primary Secret Token'],
            [
                'token_hash' => hash('sha256', 'sec_agt_9876543210abcdef9876543210abcdef'),
                'last_used_at' => now(),
            ]
        );

        // Dummy Server 2
        $server2 = Server::updateOrCreate(
            ['hostname' => 'WIN-SURABAYA-02'],
            [
                'name' => 'Database Backup VPS (Surabaya)',
                'ip_address' => '103.140.22.88',
                'os_name' => 'Windows Server 2019 Standard',
                'os_version' => '10.0.17763',
                'agent_version' => '1.0.0',
                'cpu_model' => 'AMD EPYC 7702P 64-Core Processor',
                'cpu_cores' => 8,
                'ram_total_bytes' => 34359738368, // 32 GB
                'status' => 'WARNING',
                'last_seen_at' => now()->subMinutes(1),
            ]
        );

        $agent2 = Agent::updateOrCreate(
            ['server_id' => $server2->id],
            [
                'agent_version' => '1.0.0',
                'status' => 'ACTIVE',
                'heartbeat_interval_seconds' => 30,
            ]
        );

        AgentToken::updateOrCreate(
            ['agent_id' => $agent2->id, 'name' => 'Secondary Token'],
            [
                'token_hash' => hash('sha256', 'sec_agt_1234567890abcdef1234567890abcdef'),
                'last_used_at' => now()->subMinutes(1),
            ]
        );
    }
}

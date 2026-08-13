<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\AgentToken;
use App\Models\Server;
use App\Models\ServerDisk;
use App\Models\ServerMetric;
use App\Models\ServerNetwork;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AgentApiController extends Controller
{
    /**
     * Validate Bearer token and return AgentToken model.
     */
    private function authenticateAgent(Request $request): ?AgentToken
    {
        $header = $request->header('Authorization');
        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return null;
        }

        $plainToken = substr($header, 7);
        $tokenHash = hash('sha256', $plainToken);

        $agentToken = AgentToken::with('agent.server')
            ->where('token_hash', $tokenHash)
            ->whereNull('revoked_at')
            ->first();

        if ($agentToken && $agentToken->agent && $agentToken->agent->server) {
            $agentToken->update(['last_used_at' => now()]);
            return $agentToken;
        }

        return null;
    }

    /**
     * POST /api/v1/agent/heartbeat
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $token = $this->authenticateAgent($request);
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized agent token',
                'errors' => ['token' => ['Invalid or revoked Bearer token.']]
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|string',
            'hostname' => 'required|string',
            'cpu_usage_percent' => 'numeric',
            'ram_usage_percent' => 'numeric',
            'disk_usage_percent' => 'numeric',
            'uptime_seconds' => 'numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $server = $token->agent->server;

        // Calculate Status based on thresholds
        $cpu = (float)$request->input('cpu_usage_percent', 0.0);
        $ram = (float)$request->input('ram_usage_percent', 0.0);
        $disk = (float)$request->input('disk_usage_percent', 0.0);

        $status = 'ONLINE';
        if ($cpu > 95.0 || $ram > 95.0 || $disk > 95.0) {
            $status = 'CRITICAL';
        } elseif ($cpu > 85.0 || $ram > 85.0 || $disk > 85.0) {
            $status = 'WARNING';
        }

        $server->update([
            'status' => $status,
            'hostname' => strtoupper($request->input('hostname')),
            'agent_version' => $request->input('agent_version', '1.0.0'),
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Heartbeat acknowledged',
            'data' => [
                'server_status' => $status,
                'last_seen_at' => $server->last_seen_at->toIso8601String(),
            ]
        ], 200);
    }

    /**
     * POST /api/v1/agent/metrics
     */
    public function metrics(Request $request): JsonResponse
    {
        $token = $this->authenticateAgent($request);
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized agent token',
                'errors' => ['token' => ['Invalid or revoked Bearer token.']]
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|string',
            'cpu' => 'required|array',
            'memory' => 'required|array',
            'disks' => 'present|array',
            'networks' => 'present|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $server = $token->agent->server;

        // 1. Create ServerMetric record
        $metric = ServerMetric::create([
            'server_id' => $server->id,
            'cpu_usage_percent' => (float)($request->input('cpu.usage_percent') ?? 0),
            'ram_total_bytes' => (int)($request->input('memory.total_bytes') ?? 0),
            'ram_used_bytes' => (int)($request->input('memory.used_bytes') ?? 0),
            'ram_usage_percent' => (float)($request->input('memory.usage_percent') ?? 0),
            'uptime_seconds' => (int)($request->input('uptime_seconds') ?? 0),
        ]);

        // 2. Save Disks
        foreach ($request->input('disks', []) as $disk) {
            ServerDisk::create([
                'server_metric_id' => $metric->id,
                'server_id' => $server->id,
                'drive_letter' => $disk['drive_letter'] ?? 'C:',
                'label' => $disk['label'] ?? 'Local Disk',
                'filesystem' => $disk['filesystem'] ?? 'NTFS',
                'total_bytes' => (int)($disk['total_bytes'] ?? 0),
                'free_bytes' => (int)($disk['free_bytes'] ?? 0),
                'used_bytes' => (int)($disk['used_bytes'] ?? 0),
                'usage_percent' => (float)($disk['usage_percent'] ?? 0),
            ]);
        }

        // 3. Save Networks
        $primaryIp = null;
        foreach ($request->input('networks', []) as $network) {
            ServerNetwork::create([
                'server_metric_id' => $metric->id,
                'server_id' => $server->id,
                'interface_name' => $network['interface_name'] ?? 'Ethernet 1',
                'ip_address' => $network['ip_address'] ?? null,
                'bytes_sent_per_sec' => (int)($network['bytes_sent_per_sec'] ?? 0),
                'bytes_recv_per_sec' => (int)($network['bytes_recv_per_sec'] ?? 0),
            ]);

            if (empty($primaryIp) && !empty($network['ip_address']) && $network['ip_address'] !== '127.0.0.1') {
                $primaryIp = $network['ip_address'];
            }
        }

        // Update Server info
        $server->update([
            'ram_total_bytes' => (int)($request->input('memory.total_bytes') ?? $server->ram_total_bytes),
            'ip_address' => $primaryIp ?? $server->ip_address,
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Metrics stored successfully',
            'data' => [
                'metric_id' => $metric->id,
            ]
        ], 200);
    }

    /**
     * POST /api/v1/agent/services
     */
    public function services(Request $request): JsonResponse
    {
        $token = $this->authenticateAgent($request);
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized agent token',
                'errors' => ['token' => ['Invalid or revoked Bearer token.']]
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|string',
            'services' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $server = $token->agent->server;

        foreach ($request->input('services', []) as $svc) {
            \App\Models\ServerService::updateOrCreate(
                [
                    'server_id' => $server->id,
                    'service_name' => $svc['service_name'] ?? 'Unknown',
                ],
                [
                    'display_name' => $svc['display_name'] ?? $svc['service_name'] ?? 'Unknown',
                    'status' => in_array($svc['status'] ?? '', ['Running', 'Stopped', 'Paused', 'Unknown']) ? $svc['status'] : 'Unknown',
                ]
            );
        }

        $server->update(['last_seen_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Windows Services status updated',
            'data' => []
        ], 200);
    }

    /**
     * POST /api/v1/agent/processes
     */
    public function processes(Request $request): JsonResponse
    {
        $token = $this->authenticateAgent($request);
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized agent token',
                'errors' => ['token' => ['Invalid or revoked Bearer token.']]
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|string',
            'processes' => 'present|array',
            'ports' => 'present|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $server = $token->agent->server;

        // 1. Process items
        foreach ($request->input('processes', []) as $proc) {
            \App\Models\ServerProcess::updateOrCreate(
                [
                    'server_id' => $server->id,
                    'process_name' => $proc['process_name'] ?? 'unknown.exe',
                ],
                [
                    'pid' => (int)($proc['pid'] ?? null),
                    'cpu_percent' => (float)($proc['cpu_percent'] ?? 0),
                    'memory_bytes' => (int)($proc['memory_bytes'] ?? 0),
                    'status' => in_array($proc['status'] ?? '', ['Running', 'Stopped', 'Unknown']) ? $proc['status'] : 'Running',
                ]
            );
        }

        // 2. Port items
        foreach ($request->input('ports', []) as $port) {
            \App\Models\ServerPort::updateOrCreate(
                [
                    'server_id' => $server->id,
                    'port' => (int)($port['port'] ?? 80),
                    'protocol' => strtoupper($port['protocol'] ?? 'TCP'),
                ],
                [
                    'status' => in_array($port['status'] ?? '', ['Open', 'Closed']) ? $port['status'] : 'Closed',
                ]
            );
        }

        $server->update(['last_seen_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Processes and ports status updated',
            'data' => []
        ], 200);
    }
}

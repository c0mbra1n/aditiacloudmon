<div class="space-y-6" wire:poll.5s>
    <!-- Top Header & Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-slate-800 border border-slate-700/80 rounded-2xl p-6 shadow-sm">
        <div class="space-y-2">
            <div class="flex items-center space-x-3">
                <a href="{{ route('servers.index') }}" class="p-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-xl transition-colors" title="Kembali ke Daftar Server">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <div class="flex items-center space-x-3">
                        <h1 class="text-2xl font-bold text-white tracking-tight">{{ $server->name }}</h1>
                        <!-- Status Badge -->
                        @if($server->status === 'ONLINE')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                ONLINE
                            </span>
                        @elseif($server->status === 'WARNING')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                <span class="w-2 h-2 rounded-full bg-amber-500 mr-1.5"></span>
                                WARNING
                            </span>
                        @elseif($server->status === 'CRITICAL')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                <span class="w-2 h-2 rounded-full bg-rose-500 mr-1.5"></span>
                                CRITICAL
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-700 text-slate-400 border border-slate-600">
                                <span class="w-2 h-2 rounded-full bg-slate-400 mr-1.5"></span>
                                {{ $server->status }}
                            </span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400 mt-1 font-mono">
                        <span>Hostname: <strong class="text-slate-200">{{ $server->hostname }}</strong></span>
                        <span>&bull;</span>
                        <span>IP: <strong class="text-indigo-400">{{ $server->ip_address ?? 'N/A' }}</strong></span>
                        <span>&bull;</span>
                        <span>OS: <strong class="text-slate-200">{{ $server->os_name ?? 'Windows VPS' }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header Action Buttons -->
        <div class="flex items-center space-x-3">
            <button wire:click="regenerateToken" type="button" class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-semibold rounded-xl transition-all active:scale-95 flex items-center space-x-2">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                <span>Rotasi Secret Token</span>
            </button>
        </div>
    </div>

    <!-- Regenerated Token Notification Alert -->
    @if($newRegeneratedToken)
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl text-xs space-y-2">
            <div class="flex items-center justify-between text-emerald-400 font-bold">
                <span>✓ Secret Token Baru Berhasil Dihasilkan!</span>
                <button wire:click="$set('newRegeneratedToken', '')" class="text-slate-400 hover:text-white">&times;</button>
            </div>
            <p class="text-slate-300">Harap update token di file <code class="font-mono bg-slate-900 px-1.5 py-0.5 rounded text-indigo-300">config.json</code> Agent pada Windows VPS:</p>
            <div class="p-3 bg-slate-900 border border-slate-700 rounded-xl font-mono text-emerald-400 break-all select-all font-semibold">
                {{ $newRegeneratedToken }}
            </div>
        </div>
    @endif

    <!-- Real-time Performance Metrics Cards (4 Grid) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- CPU Usage Card -->
        @php
            $cpuPercent = $latestMetric ? $latestMetric->cpu_usage_percent : 0;
            $cpuColorClass = $cpuPercent > 95 ? 'bg-rose-500' : ($cpuPercent > 85 ? 'bg-amber-500' : 'bg-emerald-500');
        @endphp
        <div class="bg-slate-800 border border-slate-700/80 rounded-2xl p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">CPU Utilization</span>
                <span class="text-xs font-bold text-slate-200">{{ $server->cpu_cores }} Cores</span>
            </div>
            <div class="flex items-baseline justify-between">
                <span class="text-3xl font-bold text-white tracking-tight">{{ number_format($cpuPercent, 1) }}%</span>
                <span class="text-xs font-mono text-slate-400">{{ $server->cpu_model ?? 'Intel Xeon / AMD EPYC' }}</span>
            </div>
            <div class="w-full h-2.5 bg-slate-900 rounded-full overflow-hidden">
                <div class="h-full {{ $cpuColorClass }} transition-all duration-500" style="width: {{ min(100, max(0, $cpuPercent)) }}%"></div>
            </div>
        </div>

        <!-- RAM Usage Card -->
        @php
            $ramPercent = $latestMetric ? $latestMetric->ram_usage_percent : 0;
            $ramUsedGB = $latestMetric ? round($latestMetric->ram_used_bytes / (1024*1024*1024), 2) : 0;
            $ramTotalGB = $server->ram_total_bytes > 0 ? round($server->ram_total_bytes / (1024*1024*1024), 2) : 16;
            $ramColorClass = $ramPercent > 95 ? 'bg-rose-500' : ($ramPercent > 85 ? 'bg-amber-500' : 'bg-indigo-600');
        @endphp
        <div class="bg-slate-800 border border-slate-700/80 rounded-2xl p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">RAM Utilization</span>
                <span class="text-xs font-bold text-slate-200">{{ $ramUsedGB }} GB / {{ $ramTotalGB }} GB</span>
            </div>
            <div class="flex items-baseline justify-between">
                <span class="text-3xl font-bold text-white tracking-tight">{{ number_format($ramPercent, 1) }}%</span>
                <span class="text-xs font-mono text-slate-400">Physical Memory</span>
            </div>
            <div class="w-full h-2.5 bg-slate-900 rounded-full overflow-hidden">
                <div class="h-full {{ $ramColorClass }} transition-all duration-500" style="width: {{ min(100, max(0, $ramPercent)) }}%"></div>
            </div>
        </div>

        <!-- Main Disk C: Usage Card -->
        @php
            $mainDisk = $latestMetric && $latestMetric->disks->count() > 0 ? $latestMetric->disks->first() : null;
            $diskPercent = $mainDisk ? $mainDisk->usage_percent : 0;
            $diskColorClass = $diskPercent > 95 ? 'bg-rose-500' : ($diskPercent > 85 ? 'bg-amber-500' : 'bg-indigo-600');
        @endphp
        <div class="bg-slate-800 border border-slate-700/80 rounded-2xl p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">System Storage (C:)</span>
                <span class="text-xs font-bold text-slate-200">{{ $mainDisk ? round($mainDisk->used_bytes / (1024*1024*1024), 1) : 0 }} GB Used</span>
            </div>
            <div class="flex items-baseline justify-between">
                <span class="text-3xl font-bold text-white tracking-tight">{{ number_format($diskPercent, 1) }}%</span>
                <span class="text-xs font-mono text-slate-400">{{ $mainDisk ? $mainDisk->filesystem : 'NTFS' }}</span>
            </div>
            <div class="w-full h-2.5 bg-slate-900 rounded-full overflow-hidden">
                <div class="h-full {{ $diskColorClass }} transition-all duration-500" style="width: {{ min(100, max(0, $diskPercent)) }}%"></div>
            </div>
        </div>

        <!-- Uptime & System Health -->
        <div class="bg-slate-800 border border-slate-700/80 rounded-2xl p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">System Uptime</span>
                <span class="text-xs font-bold text-emerald-400">Heartbeat Active</span>
            </div>
            <div>
                <p class="text-lg font-bold text-white tracking-tight">{{ $formattedUptime }}</p>
                <p class="text-xs text-slate-400 mt-1">Terakhir ping: {{ $server->last_seen_at ? $server->last_seen_at->diffForHumans() : 'Belum pernah' }}</p>
            </div>
            <div class="text-[11px] font-mono text-slate-500 border-t border-slate-700/60 pt-2">
                Agent v{{ $server->agent_version ?? '1.0.0' }} &bull; HTTPS Outbound
            </div>
        </div>
    </div>

    <!-- Navigation Tabs Bar -->
    <div class="bg-slate-800 border border-slate-700/80 rounded-2xl p-2 shadow-sm">
        <nav class="flex space-x-2 overflow-x-auto">
            <button wire:click="setTab('overview')" class="px-4 py-2 text-xs font-semibold rounded-xl transition-all whitespace-nowrap @if($activeTab === 'overview') bg-indigo-600 text-white shadow-sm @else text-slate-400 hover:text-white hover:bg-slate-700/50 @endif">
                Ringkasan (Overview)
            </button>
            <button wire:click="setTab('history')" class="px-4 py-2 text-xs font-semibold rounded-xl transition-all whitespace-nowrap @if($activeTab === 'history') bg-indigo-600 text-white shadow-sm @else text-slate-400 hover:text-white hover:bg-slate-700/50 @endif">
                Grafik Histori Performa
            </button>
            <button wire:click="setTab('services')" class="px-4 py-2 text-xs font-semibold rounded-xl transition-all whitespace-nowrap @if($activeTab === 'services') bg-indigo-600 text-white shadow-sm @else text-slate-400 hover:text-white hover:bg-slate-700/50 @endif">
                Windows Services ({{ count($services) }})
            </button>
            <button wire:click="setTab('processes')" class="px-4 py-2 text-xs font-semibold rounded-xl transition-all whitespace-nowrap @if($activeTab === 'processes') bg-indigo-600 text-white shadow-sm @else text-slate-400 hover:text-white hover:bg-slate-700/50 @endif">
                Proses Target ({{ count($processes) }})
            </button>
            <button wire:click="setTab('ports')" class="px-4 py-2 text-xs font-semibold rounded-xl transition-all whitespace-nowrap @if($activeTab === 'ports') bg-indigo-600 text-white shadow-sm @else text-slate-400 hover:text-white hover:bg-slate-700/50 @endif">
                Port Listener ({{ count($ports) }})
            </button>
            <button wire:click="setTab('disk')" class="px-4 py-2 text-xs font-semibold rounded-xl transition-all whitespace-nowrap @if($activeTab === 'disk') bg-indigo-600 text-white shadow-sm @else text-slate-400 hover:text-white hover:bg-slate-700/50 @endif">
                Penyimpanan Disk ({{ $latestMetric ? $latestMetric->disks->count() : 0 }})
            </button>
            <button wire:click="setTab('network')" class="px-4 py-2 text-xs font-semibold rounded-xl transition-all whitespace-nowrap @if($activeTab === 'network') bg-indigo-600 text-white shadow-sm @else text-slate-400 hover:text-white hover:bg-slate-700/50 @endif">
                Jaringan Network ({{ $latestMetric ? $latestMetric->networks->count() : 0 }})
            </button>
            <button wire:click="setTab('tokens')" class="px-4 py-2 text-xs font-semibold rounded-xl transition-all whitespace-nowrap @if($activeTab === 'tokens') bg-indigo-600 text-white shadow-sm @else text-slate-400 hover:text-white hover:bg-slate-700/50 @endif">
                Secret Tokens ({{ $server->agent ? $server->agent->tokens->count() : 0 }})
            </button>
        </nav>
    </div>

    <!-- Tab Contents -->
    @if($activeTab === 'overview')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Hardware Specifications Card -->
            <div class="bg-slate-800 border border-slate-700/80 rounded-2xl p-6 space-y-4">
                <h3 class="text-base font-bold text-white border-b border-slate-700 pb-3 flex items-center space-x-2">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                    <span>Informasi Hardware & OS</span>
                </h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <dt class="text-slate-400">Nama Tampilan Server</dt>
                        <dd class="font-semibold text-slate-100 mt-1">{{ $server->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Hostname</dt>
                        <dd class="font-mono text-slate-100 mt-1">{{ $server->hostname }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Sistem Operasi</dt>
                        <dd class="text-slate-100 mt-1">{{ $server->os_name ?? 'Windows Server 2022' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Model Prosessor</dt>
                        <dd class="text-slate-100 mt-1">{{ $server->cpu_model ?? 'Intel Xeon Gold 6248R' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Jumlah CPU Cores</dt>
                        <dd class="text-slate-100 mt-1">{{ $server->cpu_cores }} Cores</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Kapasitas Total Memory</dt>
                        <dd class="text-slate-100 mt-1">{{ round(($server->ram_total_bytes ?? 0) / (1024*1024*1024), 2) }} GB</dd>
                    </div>
                </dl>
            </div>

            <!-- Agent & Security Status Card -->
            <div class="bg-slate-800 border border-slate-700/80 rounded-2xl p-6 space-y-4">
                <h3 class="text-base font-bold text-white border-b border-slate-700 pb-3 flex items-center space-x-2">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span>Status Agent & Keamanan</span>
                </h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <dt class="text-slate-400">Service Name</dt>
                        <dd class="font-mono text-indigo-300 mt-1">AditiaMonitorAgent</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Versi Agent</dt>
                        <dd class="text-slate-100 mt-1">v{{ $server->agent_version ?? '1.0.0' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Komunikasi Inbound</dt>
                        <dd class="text-emerald-400 font-semibold mt-1">Blocked (Tidak Ada Inbound Port)</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Interval Heartbeat</dt>
                        <dd class="text-slate-100 mt-1">{{ $server->agent ? $server->agent->heartbeat_interval_seconds : 30 }} Detik</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Status Autentikasi Token</dt>
                        <dd class="text-emerald-400 font-semibold mt-1">Valid & Hashed (SHA-256)</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Terakhir Dilihat (Last Seen)</dt>
                        <dd class="text-slate-100 mt-1">{{ $server->last_seen_at ? $server->last_seen_at->format('H:i:s d/m/Y') : 'Belum Ada' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    @elseif($activeTab === 'history')
        <!-- Historical Performance Chart Section -->
        <div class="bg-slate-800 border border-slate-700/80 rounded-2xl p-6 shadow-xl space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-700 pb-4">
                <div>
                    <h3 class="text-lg font-bold text-white tracking-tight">Grafik Histori Performa CPU & Memory</h3>
                    <p class="text-xs text-slate-400 mt-1">Visualisasi data metric ter-agregasi berdasarkan periode waktu.</p>
                </div>
                <div class="flex items-center space-x-1.5 bg-slate-900 p-1.5 rounded-xl border border-slate-700 text-xs">
                    <button wire:click="setPeriod('1h')" type="button" class="px-3 py-1.5 rounded-lg font-semibold transition-all @if($selectedPeriod === '1h') bg-indigo-600 text-white shadow-sm @else text-slate-400 hover:text-white @endif">1 Jam</button>
                    <button wire:click="setPeriod('6h')" type="button" class="px-3 py-1.5 rounded-lg font-semibold transition-all @if($selectedPeriod === '6h') bg-indigo-600 text-white shadow-sm @else text-slate-400 hover:text-white @endif">6 Jam</button>
                    <button wire:click="setPeriod('24h')" type="button" class="px-3 py-1.5 rounded-lg font-semibold transition-all @if($selectedPeriod === '24h') bg-indigo-600 text-white shadow-sm @else text-slate-400 hover:text-white @endif">24 Jam</button>
                    <button wire:click="setPeriod('7d')" type="button" class="px-3 py-1.5 rounded-lg font-semibold transition-all @if($selectedPeriod === '7d') bg-indigo-600 text-white shadow-sm @else text-slate-400 hover:text-white @endif">7 Hari</button>
                    <button wire:click="setPeriod('30d')" type="button" class="px-3 py-1.5 rounded-lg font-semibold transition-all @if($selectedPeriod === '30d') bg-indigo-600 text-white shadow-sm @else text-slate-400 hover:text-white @endif">30 Hari</button>
                </div>
            </div>

            <div x-data="{
                initChart() {
                    const categories = {{ json_encode($chartData['categories']) }};
                    const cpuData = {{ json_encode($chartData['cpu']) }};
                    const ramData = {{ json_encode($chartData['ram']) }};

                    const options = {
                        chart: {
                            type: 'line',
                            height: 350,
                            toolbar: { show: false },
                            background: 'transparent',
                            foreColor: '#94a3b8'
                        },
                        colors: ['#4f46e5', '#10b981'],
                        stroke: { curve: 'smooth', width: 3 },
                        series: [
                            { name: 'CPU Usage (%)', data: cpuData },
                            { name: 'RAM Usage (%)', data: ramData }
                        ],
                        xaxis: {
                            categories: categories,
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: {
                            min: 0,
                            max: 100,
                            labels: { formatter: (val) => val + '%' }
                        },
                        grid: { borderColor: '#334155' },
                        tooltip: { theme: 'dark' }
                    };

                    if (window.performanceChart) {
                        window.performanceChart.destroy();
                    }
                    window.performanceChart = new ApexCharts(this.$refs.chart, options);
                    window.performanceChart.render();
                }
            }" x-init="initChart()" x-effect="initChart()" class="w-full">
                <div x-ref="chart" class="w-full"></div>
            </div>
        </div>
    @elseif($activeTab === 'services')
        <!-- Windows Services Tab -->
        <div class="bg-slate-800 border border-slate-700/80 rounded-2xl overflow-hidden shadow-xl p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-white">Status Windows Services Target</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Laporan status layanan IIS, MySQL, MariaDB, Redis, Apache, MSSQL, & OpenSSH.</p>
                </div>
                <div class="relative w-full sm:w-64">
                    <input wire:model.live.debounce.300ms="serviceSearch" type="text" class="w-full px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Cari nama service...">
                </div>
            </div>

            @if(count($services) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-900/60 text-slate-400 uppercase font-semibold border-b border-slate-700">
                                <th class="py-3.5 px-4">Service Name</th>
                                <th class="py-3.5 px-4">Display Name</th>
                                <th class="py-3.5 px-4">Status Layanan</th>
                                <th class="py-3.5 px-4 text-right">Terakhir Update</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/60">
                            @foreach($services as $svc)
                                <tr class="hover:bg-slate-700/30 transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-semibold text-indigo-300">{{ $svc->service_name }}</td>
                                    <td class="py-3.5 px-4 text-slate-200 font-medium">{{ $svc->display_name }}</td>
                                    <td class="py-3.5 px-4">
                                        @if($svc->status === 'Running')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                                Running
                                            </span>
                                        @elseif($svc->status === 'Stopped')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                                Stopped
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-700 text-slate-400">
                                                {{ $svc->status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right text-slate-400">{{ $svc->updated_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-12 text-center text-slate-400">
                    <p class="text-sm font-semibold text-slate-300">Belum ada data Windows Services dilaporkan.</p>
                </div>
            @endif
        </div>
    @elseif($activeTab === 'processes')
        <!-- Target Processes Tab -->
        <div class="bg-slate-800 border border-slate-700/80 rounded-2xl overflow-hidden shadow-xl p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-white">Pemantauan Proses Target Executable</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Status konsumsi CPU & Memory dari mysqld.exe, httpd.exe, nginx.exe, php-cgi.exe, & redis-server.exe.</p>
                </div>
                <div class="relative w-full sm:w-64">
                    <input wire:model.live.debounce.300ms="processSearch" type="text" class="w-full px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Cari nama proses...">
                </div>
            </div>

            @if(count($processes) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-900/60 text-slate-400 uppercase font-semibold border-b border-slate-700">
                                <th class="py-3.5 px-4">Nama Proses Executable</th>
                                <th class="py-3.5 px-4">Process ID (PID)</th>
                                <th class="py-3.5 px-4">Penggunaan CPU</th>
                                <th class="py-3.5 px-4">Konsumsi RAM (Working Set)</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 text-right">Terakhir Update</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/60">
                            @foreach($processes as $proc)
                                <tr class="hover:bg-slate-700/30 transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-semibold text-emerald-300">{{ $proc->process_name }}</td>
                                    <td class="py-3.5 px-4 font-mono text-slate-300">{{ $proc->pid ?? '-' }}</td>
                                    <td class="py-3.5 px-4 font-bold text-white">{{ number_format($proc->cpu_percent, 1) }}%</td>
                                    <td class="py-3.5 px-4 font-mono text-slate-300">{{ round($proc->memory_bytes / (1024*1024), 1) }} MB</td>
                                    <td class="py-3.5 px-4">
                                        @if($proc->status === 'Running')
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Running</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">Stopped</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right text-slate-400">{{ $proc->updated_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-12 text-center text-slate-400">
                    <p class="text-sm font-semibold text-slate-300">Belum ada data proses target dilaporkan.</p>
                </div>
            @endif
        </div>
    @elseif($activeTab === 'ports')
        <!-- Port Listener Tab -->
        <div class="bg-slate-800 border border-slate-700/80 rounded-2xl overflow-hidden shadow-xl p-6 space-y-4">
            <div>
                <h3 class="text-base font-bold text-white">Status Port Listener Lokal (TCP)</h3>
                <p class="text-xs text-slate-400 mt-0.5">Pemeriksaan ketersediaan listener port 80 (HTTP), 443 (HTTPS), 3306 (MySQL), 3389 (RDP), & 22 (SSH).</p>
            </div>

            @if(count($ports) > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                    @foreach($ports as $port)
                        <div class="bg-slate-900 border border-slate-700/80 rounded-xl p-4 space-y-2 text-center">
                            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Port {{ $port->port }}</div>
                            <div class="text-2xl font-bold font-mono text-white">{{ $port->port }} / {{ $port->protocol }}</div>
                            <div>
                                @if($port->status === 'Open')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                        Open
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                        Closed
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-12 text-center text-slate-400">
                    <p class="text-sm font-semibold text-slate-300">Belum ada data port listener dilaporkan.</p>
                </div>
            @endif
        </div>
    @elseif($activeTab === 'disk')
        <!-- Disk Drives List -->
        <div class="bg-slate-800 border border-slate-700/80 rounded-2xl overflow-hidden shadow-xl p-6 space-y-4">
            <h3 class="text-base font-bold text-white mb-4">Penggunaan Storage Disk Per Drive</h3>
            @if($latestMetric && $latestMetric->disks->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($latestMetric->disks as $disk)
                        <div class="bg-slate-900 border border-slate-700/80 rounded-xl p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <span class="w-8 h-8 rounded-lg bg-indigo-600/20 text-indigo-400 font-bold font-mono text-sm flex items-center justify-center border border-indigo-500/30">
                                        {{ $disk->drive_letter }}
                                    </span>
                                    <div>
                                        <div class="font-semibold text-slate-100 text-sm">{{ $disk->label }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $disk->filesystem }} Format</div>
                                    </div>
                                </div>
                                <div class="text-right text-xs">
                                    <div class="font-bold text-white text-base">{{ number_format($disk->usage_percent, 1) }}%</div>
                                    <div class="text-slate-400 text-[11px]">{{ round($disk->used_bytes / (1024*1024*1024), 1) }} GB / {{ round($disk->total_bytes / (1024*1024*1024), 1) }} GB</div>
                                </div>
                            </div>
                            <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full @if($disk->usage_percent > 90) bg-rose-500 @elseif($disk->usage_percent > 80) bg-amber-500 @else bg-indigo-600 @endif" style="width: {{ min(100, max(0, $disk->usage_percent)) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-400 py-6 text-center">Belum ada sampel metric disk yang diterima dari agent.</p>
            @endif
        </div>
    @elseif($activeTab === 'network')
        <!-- Network Interfaces List -->
        <div class="bg-slate-800 border border-slate-700/80 rounded-2xl overflow-hidden shadow-xl p-6 space-y-4">
            <h3 class="text-base font-bold text-white mb-4">Interface Jaringan Network Adapter</h3>
            @if($latestMetric && $latestMetric->networks->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($latestMetric->networks as $net)
                        <div class="bg-slate-900 border border-slate-700/80 rounded-xl p-4 space-y-2 text-xs">
                            <div class="flex items-center justify-between border-b border-slate-700/60 pb-2">
                                <span class="font-semibold text-slate-200 text-sm">{{ $net->interface_name }}</span>
                                <span class="font-mono text-indigo-400 font-medium">{{ $net->ip_address ?? 'N/A' }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 pt-1 text-slate-400">
                                <div>
                                    <span>Sent (Upload):</span>
                                    <p class="font-mono font-bold text-slate-200 text-sm mt-0.5">{{ number_format($net->bytes_sent_per_sec / 1024, 1) }} KB/s</p>
                                </div>
                                <div>
                                    <span>Received (Download):</span>
                                    <p class="font-mono font-bold text-slate-200 text-sm mt-0.5">{{ number_format($net->bytes_recv_per_sec / 1024, 1) }} KB/s</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-400 py-6 text-center">Belum ada sampel metric jaringan yang diterima.</p>
            @endif
        </div>
    @elseif($activeTab === 'tokens')
        <!-- Agent Secret Tokens Table -->
        <div class="bg-slate-800 border border-slate-700/80 rounded-2xl overflow-hidden shadow-xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Daftar Secret Token Autentikasi Agent</h3>
                <button wire:click="regenerateToken" type="button" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl shadow-sm">
                    + Buat Secret Token Baru
                </button>
            </div>
            @if($server->agent && $server->agent->tokens->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-900/60 text-slate-400 uppercase font-semibold border-b border-slate-700">
                                <th class="py-3 px-4">Nama Token</th>
                                <th class="py-3 px-4">Hashed Token</th>
                                <th class="py-3 px-4">Terakhir Digunakan</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/60">
                            @foreach($server->agent->tokens as $token)
                                <tr>
                                    <td class="py-3 px-4 font-semibold text-slate-200">{{ $token->name }}</td>
                                    <td class="py-3 px-4 font-mono text-[11px] text-slate-400 truncate max-w-[200px]">{{ substr($token->token_hash, 0, 16) }}...</td>
                                    <td class="py-3 px-4 text-slate-300">{{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Belum Pernah' }}</td>
                                    <td class="py-3 px-4">
                                        @if($token->revoked_at)
                                            <span class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 font-semibold text-[10px]">REVOKED</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-semibold text-[10px]">ACTIVE</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        @if(!$token->revoked_at)
                                            <button wire:click="confirmRevokeToken('{{ $token->id }}')" class="text-rose-400 hover:text-rose-300 font-semibold">Cabut Token</button>
                                        @else
                                            <span class="text-slate-600">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-xs text-slate-400 py-6 text-center">Belum ada token rahasia terdaftar untuk agent ini.</p>
            @endif
        </div>
    @endif
</div>

<div wire:poll.3s class="space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-bold text-white tracking-tight">Monitoring Windows VPS</h1>
                <span class="inline-flex items-center text-xs font-medium text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-full border border-emerald-500/20">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse mr-1.5"></span>
                    Live Refresh (3s)
                </span>
            </div>
            <p class="text-sm text-slate-400 mt-1">Kelola dan pantau status ketersediaan VPS Windows secara terpusat.</p>
        </div>
        <button wire:click="openRegisterModal" type="button" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-md transition-all duration-200 active:scale-95 space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Tambah Windows VPS</span>
        </button>
    </div>

    <!-- Status Overview Cards Grid (Neo-Flat Solid Design) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <!-- Total Servers -->
        <div wire:click="$set('statusFilter', 'ALL')" class="cursor-pointer bg-slate-800 border rounded-2xl p-5 shadow-sm transition-all duration-200 hover:scale-[1.02] @if($statusFilter === 'ALL') border-indigo-500 ring-2 ring-indigo-500/20 @else border-slate-700/80 @endif">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Total Server</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"></path></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-white">{{ $totalServers }}</p>
            <p class="text-xs text-slate-400 mt-1">Windows VPS Terdaftar</p>
        </div>

        <!-- Online Servers -->
        <div wire:click="$set('statusFilter', 'ONLINE')" class="cursor-pointer bg-slate-800 border rounded-2xl p-5 shadow-sm transition-all duration-200 hover:scale-[1.02] @if($statusFilter === 'ONLINE') border-emerald-500 ring-2 ring-emerald-500/20 @else border-slate-700/80 @endif">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Online</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                </div>
            </div>
            <p class="text-2xl font-bold text-emerald-400">{{ $onlineCount }}</p>
            <p class="text-xs text-slate-400 mt-1">Normal & Active Ping</p>
        </div>

        <!-- Warning Servers -->
        <div wire:click="$set('statusFilter', 'WARNING')" class="cursor-pointer bg-slate-800 border rounded-2xl p-5 shadow-sm transition-all duration-200 hover:scale-[1.02] @if($statusFilter === 'WARNING') border-amber-500 ring-2 ring-amber-500/20 @else border-slate-700/80 @endif">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-400">Warning</span>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-amber-400">{{ $warningCount }}</p>
            <p class="text-xs text-slate-400 mt-1">High CPU / RAM Usage</p>
        </div>

        <!-- Critical Servers -->
        <div wire:click="$set('statusFilter', 'CRITICAL')" class="cursor-pointer bg-slate-800 border rounded-2xl p-5 shadow-sm transition-all duration-200 hover:scale-[1.02] @if($statusFilter === 'CRITICAL') border-rose-500 ring-2 ring-rose-500/20 @else border-slate-700/80 @endif">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-rose-400">Critical</span>
                <div class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-rose-400">{{ $criticalCount }}</p>
            <p class="text-xs text-slate-400 mt-1">Threshold Exceeded</p>
        </div>

        <!-- Offline Servers -->
        <div wire:click="$set('statusFilter', 'OFFLINE')" class="cursor-pointer bg-slate-800 border rounded-2xl p-5 shadow-sm transition-all duration-200 hover:scale-[1.02] @if($statusFilter === 'OFFLINE') border-slate-500 ring-2 ring-slate-500/20 @else border-slate-700/80 @endif">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Offline</span>
                <div class="w-8 h-8 rounded-lg bg-slate-700 text-slate-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.04 10.04 0 013.682-.763c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m-5.411-5.411L2 2l20 20"></path></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-400">{{ $offlineCount }}</p>
            <p class="text-xs text-slate-400 mt-1">No Heartbeat > 2m</p>
        </div>
    </div>

    <!-- Filter & Search Controls Bar -->
    <div class="bg-slate-800 border border-slate-700/80 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <!-- Search Input -->
        <div class="relative w-full sm:w-96">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" class="w-full pl-10 pr-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Cari nama server, hostname, atau IP...">
        </div>

        <!-- Filter Buttons / Dropdown -->
        <div class="flex items-center space-x-2 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
            <span class="text-xs text-slate-400 font-medium whitespace-nowrap">Filter Status:</span>
            <select wire:model.live="statusFilter" class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="ALL">Semua Status ({{ $totalServers }})</option>
                <option value="ONLINE">ONLINE ({{ $onlineCount }})</option>
                <option value="WARNING">WARNING ({{ $warningCount }})</option>
                <option value="CRITICAL">CRITICAL ({{ $criticalCount }})</option>
                <option value="OFFLINE">OFFLINE ({{ $offlineCount }})</option>
                <option value="UNKNOWN">UNKNOWN / BARU ({{ $unknownCount }})</option>
            </select>
        </div>
    </div>

    <!-- Server List Table -->
    <div class="bg-slate-800 border border-slate-700/80 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/60 border-b border-slate-700 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6">Server / Hostname</th>
                        <th class="py-4 px-6">IP Address</th>
                        <th class="py-4 px-6">Sistem Operasi</th>
                        <th class="py-4 px-6">Hardware Spec</th>
                        <th class="py-4 px-6">Terakhir Terlihat</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/60 text-sm">
                    @forelse($servers as $server)
                        <tr class="hover:bg-slate-700/30 transition-colors">
                            <!-- Status Badge -->
                            <td class="py-4 px-6 whitespace-nowrap">
                                @if($server->status === 'ONLINE')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                        ONLINE
                                    </span>
                                @elseif($server->status === 'WARNING')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                                        WARNING
                                    </span>
                                @elseif($server->status === 'CRITICAL')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                        CRITICAL
                                    </span>
                                @elseif($server->status === 'OFFLINE')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-700 text-slate-400 border border-slate-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-1.5"></span>
                                        OFFLINE
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 mr-1.5"></span>
                                        {{ $server->status }}
                                    </span>
                                @endif
                            </td>

                            <!-- Server Name & Hostname -->
                            <td class="py-4 px-6 whitespace-nowrap">
                                <a href="{{ route('servers.show', $server->id) }}" class="font-semibold text-slate-100 hover:text-indigo-400 transition-colors">
                                    {{ $server->name }}
                                </a>
                                <div class="text-xs text-slate-400 font-mono mt-0.5">{{ $server->hostname }}</div>
                            </td>

                            <!-- IP Address -->
                            <td class="py-4 px-6 whitespace-nowrap font-mono text-xs text-slate-300">
                                {{ $server->ip_address ?? 'Belum Terhubung' }}
                            </td>

                            <!-- OS Name -->
                            <td class="py-4 px-6 whitespace-nowrap">
                                <div class="text-xs text-slate-300">{{ $server->os_name ?? 'Windows VPS' }}</div>
                                <div class="text-[11px] text-slate-400">Agent v{{ $server->agent_version ?? '1.0.0' }}</div>
                            </td>

                            <!-- Hardware Spec -->
                            <td class="py-4 px-6 whitespace-nowrap text-xs text-slate-300">
                                <div>{{ $server->cpu_cores }} Cores CPU</div>
                                <div class="text-slate-400">{{ round(($server->ram_total_bytes ?? 0) / (1024*1024*1024), 1) }} GB RAM</div>
                            </td>

                            <!-- Last Seen -->
                            <td class="py-4 px-6 whitespace-nowrap text-xs text-slate-300">
                                @if($server->last_seen_at)
                                    <div>{{ $server->last_seen_at->diffForHumans() }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $server->last_seen_at->format('H:i:s d/m/Y') }}</div>
                                @else
                                    <span class="text-slate-500">Belum ada heartbeat</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-6 whitespace-nowrap text-right text-xs font-medium space-x-2">
                                <a href="{{ route('servers.show', $server->id) }}" class="inline-flex items-center px-2.5 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg transition-colors" title="Lihat Detail Server">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    <span>Detail</span>
                                </a>
                                <button wire:click="confirmDeleteServer('{{ $server->id }}')" type="button" class="p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg transition-colors" title="Hapus Server">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-700 flex items-center justify-center mx-auto mb-3 text-slate-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <p class="text-base font-semibold text-slate-300">Tidak ada Windows VPS ditemukan.</p>
                                <p class="text-xs text-slate-500 mt-1">Klik tombol "Tambah Windows VPS" untuk me-registrasikan server baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($servers->hasPages())
            <div class="px-6 py-4 border-t border-slate-700/60 bg-slate-900/40">
                {{ $servers->links() }}
            </div>
        @endif
    </div>

    <!-- Registration / Token Generator Modal (Alpine.js Enabled) -->
    @if($showRegisterModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-6 animate-fadeIn">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-700 pb-4">
                <h3 class="text-lg font-bold text-white">Registrasi Server Windows VPS Baru</h3>
                <button wire:click="closeRegisterModal" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            @if(!$generatedToken)
                <!-- Input Form -->
                <form wire:submit.prevent="createServer" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Nama Tampilan Server</label>
                        <input wire:model.defer="newServerName" type="text" required class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="contoh: Web Server Jakarta 01">
                        @error('newServerName') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Hostname Windows VPS</label>
                        <input wire:model.defer="newHostname" type="text" required class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-slate-100 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="contoh: WIN-SERVER-01">
                        @error('newHostname') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4 flex justify-end space-x-3">
                        <button type="button" wire:click="closeRegisterModal" class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm font-semibold rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-md transition-all active:scale-95">Generate Credentials</button>
                    </div>
                </form>
            @else
                <!-- Credentials Result Display -->
                <div class="space-y-4">
                    <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-xs text-emerald-400">
                        ✓ Server berhasil terdaftar! Harap salin token rahasia ini ke file <code class="font-mono bg-slate-900 px-1 py-0.5 rounded">config.json</code> Agent pada Windows VPS.
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1">Server ID (UUID)</label>
                        <div class="bg-slate-900 border border-slate-700 p-2.5 rounded-xl text-xs font-mono text-indigo-300 break-all select-all">
                            {{ $generatedServerId }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1">Agent Secret Token (Plain Text)</label>
                        <div class="bg-slate-900 border border-slate-700 p-3 rounded-xl text-xs font-mono text-emerald-400 break-all select-all">
                            {{ $generatedToken }}
                        </div>
                        <p class="text-[11px] text-amber-400 mt-1.5">⚠️ Simpan token ini baik-baik. Token hashed disimpan di database dan tidak dapat ditampilkan ulang.</p>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="button" wire:click="closeRegisterModal" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl">Selesai & Tutup</button>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>

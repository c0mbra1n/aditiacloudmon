<div class="space-y-6" wire:poll.5s>
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-slate-800 border border-slate-700/80 rounded-2xl p-6 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Daftar Alert & Notifikasi System</h1>
            <p class="text-xs text-slate-400 mt-1">Evaluasi otomatis kondisi kritis server VPS, Windows Services, dan Port Listener.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('alert-rules.index') }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl shadow-sm transition-all active:scale-95 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                <span>Kelola Aturan Alert Rules</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-800 border border-slate-700/80 rounded-2xl p-4 shadow-sm text-xs">
        <!-- Status Filter Buttons -->
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-slate-400 font-semibold mr-2">Status:</span>
            <button wire:click="setStatusFilter('ALL')" class="px-3 py-1.5 rounded-xl font-semibold transition-all @if($statusFilter === 'ALL') bg-indigo-600 text-white @else bg-slate-900 text-slate-400 hover:text-white @endif">Semua</button>
            <button wire:click="setStatusFilter('OPEN')" class="px-3 py-1.5 rounded-xl font-semibold transition-all @if($statusFilter === 'OPEN') bg-rose-600 text-white @else bg-slate-900 text-rose-400 hover:text-white @endif">OPEN</button>
            <button wire:click="setStatusFilter('ACKNOWLEDGED')" class="px-3 py-1.5 rounded-xl font-semibold transition-all @if($statusFilter === 'ACKNOWLEDGED') bg-amber-600 text-white @else bg-slate-900 text-amber-400 hover:text-white @endif">ACKNOWLEDGED</button>
            <button wire:click="setStatusFilter('RESOLVED')" class="px-3 py-1.5 rounded-xl font-semibold transition-all @if($statusFilter === 'RESOLVED') bg-emerald-600 text-white @else bg-slate-900 text-emerald-400 hover:text-white @endif">RESOLVED</button>
        </div>

        <!-- Severity Filter Buttons -->
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-slate-400 font-semibold mr-2">Severity:</span>
            <button wire:click="setSeverityFilter('ALL')" class="px-3 py-1.5 rounded-xl font-semibold transition-all @if($severityFilter === 'ALL') bg-indigo-600 text-white @else bg-slate-900 text-slate-400 hover:text-white @endif">Semua</button>
            <button wire:click="setSeverityFilter('CRITICAL')" class="px-3 py-1.5 rounded-xl font-semibold transition-all @if($severityFilter === 'CRITICAL') bg-rose-600 text-white @else bg-slate-900 text-rose-400 hover:text-white @endif">CRITICAL</button>
            <button wire:click="setSeverityFilter('WARNING')" class="px-3 py-1.5 rounded-xl font-semibold transition-all @if($severityFilter === 'WARNING') bg-amber-600 text-white @else bg-slate-900 text-amber-400 hover:text-white @endif">WARNING</button>
        </div>
    </div>

    <!-- Alerts Table -->
    <div class="bg-slate-800 border border-slate-700/80 rounded-2xl overflow-hidden shadow-xl">
        @if($alerts->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-900/60 text-slate-400 uppercase font-semibold border-b border-slate-700">
                            <th class="py-3.5 px-4">Severity</th>
                            <th class="py-3.5 px-4">Judul Alert</th>
                            <th class="py-3.5 px-4">Target VPS Server</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">Waktu Trigger</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        @foreach($alerts as $alert)
                            <tr class="hover:bg-slate-700/30 transition-colors">
                                <td class="py-3.5 px-4">
                                    @if($alert->severity === 'CRITICAL')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5 animate-pulse"></span>
                                            CRITICAL
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                                            WARNING
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-white text-sm">{{ $alert->title }}</div>
                                    <div class="text-slate-400 text-xs mt-0.5">{{ $alert->message }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-indigo-300">
                                    {{ $alert->server->name ?? 'Global' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($alert->status === 'OPEN')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">OPEN</span>
                                    @elseif($alert->status === 'ACKNOWLEDGED')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">ACKNOWLEDGED</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">RESOLVED</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-300">
                                    {{ $alert->triggered_at->format('H:i:s d/m/Y') }}
                                    <div class="text-[11px] text-slate-400">{{ $alert->triggered_at->diffForHumans() }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-2">
                                    @if($alert->status === 'OPEN')
                                        <button wire:click="confirmAcknowledge({{ $alert->id }})" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-500 text-white rounded-lg font-semibold shadow-sm transition-all">
                                            Acknowledge
                                        </button>
                                        <button wire:click="confirmResolve({{ $alert->id }})" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg font-semibold shadow-sm transition-all">
                                            Resolve
                                        </button>
                                    @elseif($alert->status === 'ACKNOWLEDGED')
                                        <button wire:click="confirmResolve({{ $alert->id }})" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg font-semibold shadow-sm transition-all">
                                            Resolve
                                        </button>
                                    @else
                                        <span class="text-slate-500 font-semibold">✓ Resolved</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-700">
                {{ $alerts->links() }}
            </div>
        @else
            <div class="py-16 text-center text-slate-400">
                <svg class="w-12 h-12 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-sm font-semibold text-slate-300">Tidak ada alert ditemukan.</p>
                <p class="text-xs text-slate-500 mt-1">Sistem dalam keadaan normal dan tidak ada pelanggaran aturan threshold saat ini.</p>
            </div>
        @endif
    </div>
</div>

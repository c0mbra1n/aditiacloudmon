<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-slate-800 border border-slate-700/80 rounded-2xl p-6 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Manajemen Aturan Alert (Alert Rules)</h1>
            <p class="text-xs text-slate-400 mt-1">Konfigurasi batas ambang (threshold), durasi evaluasi, dan cooldown window untuk pemicu notifikasi alert.</p>
        </div>
        <div class="flex items-center space-x-2">
            <button wire:click="createRule" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl shadow-sm transition-all active:scale-95 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Buat Aturan Alert Baru</span>
            </button>
        </div>
    </div>

    <!-- Alert Rules Table -->
    <div class="bg-slate-800 border border-slate-700/80 rounded-2xl overflow-hidden shadow-xl">
        @if($rules->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-900/60 text-slate-400 uppercase font-semibold border-b border-slate-700">
                            <th class="py-3.5 px-4">Nama Aturan</th>
                            <th class="py-3.5 px-4">Target Server</th>
                            <th class="py-3.5 px-4">Metric / Target</th>
                            <th class="py-3.5 px-4">Kondisi Threshold</th>
                            <th class="py-3.5 px-4">Severity</th>
                            <th class="py-3.5 px-4">Cooldown Window</th>
                            <th class="py-3.5 px-4">Status Active</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        @foreach($rules as $rule)
                            <tr class="hover:bg-slate-700/30 transition-colors">
                                <td class="py-3.5 px-4 font-bold text-white text-sm">{{ $rule->name }}</td>
                                <td class="py-3.5 px-4 font-semibold text-indigo-300">
                                    {{ $rule->server->name ?? 'Semua Server (Global)' }}
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-200">
                                    {{ $rule->metric_type }}
                                    @if($rule->target_name)
                                        <span class="text-indigo-400">({{ $rule->target_name }})</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-mono text-amber-400 font-semibold">
                                    {{ $rule->operator }} {{ $rule->threshold_value }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($rule->severity === 'CRITICAL')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">CRITICAL</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">WARNING</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-300 font-mono">
                                    {{ $rule->cooldown_minutes }} Menit
                                </td>
                                <td class="py-3.5 px-4">
                                    <button wire:click="toggleEnabled({{ $rule->id }})" class="px-2.5 py-1 rounded-full text-xs font-semibold transition-all @if($rule->is_enabled) bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 @else bg-slate-700 text-slate-400 @endif">
                                        {{ $rule->is_enabled ? 'ENABLED' : 'DISABLED' }}
                                    </button>
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-2">
                                    <button wire:click="editRule({{ $rule->id }})" class="text-indigo-400 hover:text-indigo-300 font-semibold">Edit</button>
                                    <button wire:click="confirmDeleteRule({{ $rule->id }})" class="text-rose-400 hover:text-rose-300 font-semibold">Hapus</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-16 text-center text-slate-400">
                <p class="text-sm font-semibold text-slate-300">Belum ada aturan alert terkonfigurasi.</p>
                <p class="text-xs text-slate-500 mt-1">Klik "+ Buat Aturan Alert Baru" untuk memulai.</p>
            </div>
        @endif
    </div>

    <!-- Create / Edit Rule Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="bg-slate-800 border border-slate-700/80 rounded-2xl p-6 w-full max-w-lg shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-700 pb-3">
                    <h3 class="text-lg font-bold text-white">{{ $editingRuleId ? 'Edit Aturan Alert' : 'Buat Aturan Alert Baru' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white">&times;</button>
                </div>

                <form wire:submit.prevent="saveRule" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Aturan Alert</label>
                        <input wire:model="name" type="text" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Misal: CPU High Usage > 90%">
                        @error('name') <span class="text-rose-400 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Target VPS Server</label>
                            <select wire:model="server_id" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Semua Server (Global Rule)</option>
                                @foreach($servers as $srv)
                                    <option value="{{ $srv->id }}">{{ $srv->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Tipe Metric / Subject</label>
                            <select wire:model="metric_type" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="CPU">CPU Usage (%)</option>
                                <option value="RAM">RAM Usage (%)</option>
                                <option value="DISK">Disk Storage (%)</option>
                                <option value="OFFLINE">Server Offline</option>
                                <option value="SERVICE">Windows Service Stopped</option>
                                <option value="PORT">Port Closed</option>
                            </select>
                        </div>
                    </div>

                    @if(in_array($metric_type, ['SERVICE', 'PORT']))
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Target Name / Port Number</label>
                            <input wire:model="target_name" type="text" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Misal: W3SVC atau 3306">
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Operator Pembanding</label>
                            <select wire:model="operator" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value=">">Lebih Besar Dari (&gt;)</option>
                                <option value=">=">Lebih Besar Sama Dengan (&gt;=)</option>
                                <option value="<">Lebih Kecil Dari (&lt;)</option>
                                <option value="=">Sama Dengan (=)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Nilai Ambang (Threshold)</label>
                            <input wire:model="threshold_value" type="number" step="0.1" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Tingkat Severity Alert</label>
                            <select wire:model="severity" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="WARNING">WARNING (Kuning)</option>
                                <option value="CRITICAL">CRITICAL (Merah)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Cooldown Window (Menit)</label>
                            <input wire:model="cooldown_minutes" type="number" min="1" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 border-t border-slate-700 pt-3">
                        <input wire:model="is_enabled" type="checkbox" id="is_enabled_chk" class="rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                        <label for="is_enabled_chk" class="text-slate-300 font-semibold">Aktifkan aturan ini langsung (Enabled)</label>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-2">
                        <button wire:click="$set('showModal', false)" type="button" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-xl font-semibold">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-semibold shadow-sm">Simpan Aturan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

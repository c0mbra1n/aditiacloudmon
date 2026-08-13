<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-slate-800 border border-slate-700/80 rounded-2xl p-6 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Kanal Notifikasi (Telegram & Discord Integration)</h1>
            <p class="text-xs text-slate-400 mt-1">Konfigurasi integrasi Telegram Bot API dan Discord Webhook untuk menerima pemberitahuan alert real-time.</p>
        </div>
        <div class="flex items-center space-x-2">
            <button wire:click="createChannel" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl shadow-sm transition-all active:scale-95 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Tambah Kanal Notifikasi</span>
            </button>
        </div>
    </div>

    <!-- Channels Table -->
    <div class="bg-slate-800 border border-slate-700/80 rounded-2xl overflow-hidden shadow-xl">
        @if($channels->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-900/60 text-slate-400 uppercase font-semibold border-b border-slate-700">
                            <th class="py-3.5 px-4">Nama Kanal</th>
                            <th class="py-3.5 px-4">Tipe Kanal</th>
                            <th class="py-3.5 px-4">Detail Konfigurasi</th>
                            <th class="py-3.5 px-4">Status Active</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        @foreach($channels as $ch)
                            <tr class="hover:bg-slate-700/30 transition-colors">
                                <td class="py-3.5 px-4 font-bold text-white text-sm">{{ $ch->name }}</td>
                                <td class="py-3.5 px-4">
                                    @if($ch->type === 'TELEGRAM')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-500/10 text-sky-400 border border-sky-500/20">
                                            Telegram Bot
                                        </span>
                                    @elseif($ch->type === 'DISCORD')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                            Discord Webhook
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-700 text-slate-300">
                                            {{ $ch->type }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-300">
                                    @if($ch->type === 'TELEGRAM')
                                        Chat ID: <span class="text-indigo-400 font-bold">{{ $ch->config['chat_id'] ?? 'N/A' }}</span>
                                    @elseif($ch->type === 'DISCORD')
                                        Webhook: <span class="text-indigo-400 font-bold truncate max-w-[200px] inline-block align-bottom">{{ substr($ch->config['webhook_url'] ?? '', 0, 30) }}...</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <button wire:click="toggleEnabled({{ $ch->id }})" class="px-2.5 py-1 rounded-full text-xs font-semibold transition-all @if($ch->is_enabled) bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 @else bg-slate-700 text-slate-400 @endif">
                                        {{ $ch->is_enabled ? 'ENABLED' : 'DISABLED' }}
                                    </button>
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-2">
                                    <button wire:click="testChannel({{ $ch->id }})" class="px-2.5 py-1 bg-slate-700 hover:bg-slate-600 text-emerald-400 rounded-lg font-semibold transition-all">Uji Koneksi</button>
                                    <button wire:click="editChannel({{ $ch->id }})" class="text-indigo-400 hover:text-indigo-300 font-semibold">Edit</button>
                                    <button wire:click="confirmDeleteChannel({{ $ch->id }})" class="text-rose-400 hover:text-rose-300 font-semibold">Hapus</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-16 text-center text-slate-400">
                <p class="text-sm font-semibold text-slate-300">Belum ada kanal notifikasi terdaftar.</p>
                <p class="text-xs text-slate-500 mt-1">Klik "+ Tambah Kanal Notifikasi" untuk mendaftarkan bot Telegram atau Discord Webhook.</p>
            </div>
        @endif
    </div>

    <!-- Create / Edit Channel Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="bg-slate-800 border border-slate-700/80 rounded-2xl p-6 w-full max-w-lg shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-700 pb-3">
                    <h3 class="text-lg font-bold text-white">{{ $editingChannelId ? 'Edit Kanal Notifikasi' : 'Tambah Kanal Notifikasi' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-white">&times;</button>
                </div>

                <form wire:submit.prevent="saveChannel" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Kanal Notifikasi</label>
                        <input wire:model="name" type="text" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Misal: Telegram Bot Admin Infrastructure">
                        @error('name') <span class="text-rose-400 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Tipe Integrasi Platform</label>
                        <select wire:model.live="type" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="TELEGRAM">Telegram Bot API</option>
                            <option value="DISCORD">Discord Webhook API</option>
                        </select>
                    </div>

                    @if($type === 'TELEGRAM')
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Telegram Bot Token (dari @BotFather)</label>
                            <input wire:model="telegram_bot_token" type="text" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyZ">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Telegram Chat ID / Channel ID</label>
                            <input wire:model="telegram_chat_id" type="text" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="-100123456789 atau 987654321">
                        </div>
                    @elseif($type === 'DISCORD')
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Discord Webhook URL</label>
                            <input wire:model="discord_webhook_url" type="text" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="https://discord.com/api/webhooks/12345/abcde...">
                        </div>
                    @endif

                    <div class="flex items-center space-x-2 border-t border-slate-700 pt-3">
                        <input wire:model="is_enabled" type="checkbox" id="is_enabled_channel_chk" class="rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                        <label for="is_enabled_channel_chk" class="text-slate-300 font-semibold">Aktifkan kanal notifikasi ini (Enabled)</label>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-2">
                        <button wire:click="$set('showModal', false)" type="button" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-xl font-semibold">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-semibold shadow-sm">Simpan Kanal</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

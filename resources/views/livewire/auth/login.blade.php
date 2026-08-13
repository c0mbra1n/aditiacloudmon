<div class="w-full max-w-md space-y-8">
    <!-- Brand Header -->
    <div class="text-center">
        <div
            class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600 text-white font-bold text-2xl shadow-md mb-4 transform transition-transform duration-200 hover:scale-105">
            AC
        </div>
        <h2 class="text-3xl font-bold tracking-tight text-white">AditiaCloud<span class="text-indigo-400">Mon</span>
        </h2>
        <p class="mt-2 text-sm text-slate-400">Masuk ke Windows VPS Monitoring Dashboard</p>
    </div>

    <!-- Login Card (Neo-Flat Solid Design) -->
    <div class="bg-slate-800 border border-slate-700/80 rounded-2xl p-8 shadow-xl">
        <form wire:submit.prevent="login" class="space-y-6">
            <!-- Email Input -->
            <div>
                <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Alamat Email Admin</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207">
                            </path>
                        </svg>
                    </div>
                    <input wire:model.defer="email" id="email" type="email" autocomplete="email" required
                        class="w-full pl-11 pr-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm @error('email') border-rose-500 @enderror"
                        placeholder="">
                </div>
                @error('email')
                    <p class="mt-2 text-xs text-rose-400 flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <!-- Password Input with Eye Icon Toggle -->
            <div>
                <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Kata Sandi
                    (Password)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                            </path>
                        </svg>
                    </div>
                    <input wire:model.defer="password" id="password" :type="$wire.showPassword ? 'text' : 'password'"
                        autocomplete="current-password" required
                        class="w-full pl-11 pr-11 py-3 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm @error('password') border-rose-500 @enderror"
                        placeholder="••••••••">

                    <!-- Eye Icon Toggle Button -->
                    <button type="button" wire:click="togglePasswordVisibility"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200 focus:outline-none transition-colors"
                        title="Lihat/Sembunyikan Password">
                        @if($showPassword)
                            <!-- Eye Slash Icon (Hide) -->
                            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.04 10.04 0 013.682-.763c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m-5.411-5.411L2 2l20 20">
                                </path>
                            </svg>
                        @else
                            <!-- Eye Open Icon (Show) -->
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                </path>
                            </svg>
                        @endif
                    </button>
                </div>
                @error('password')
                    <p class="mt-2 text-xs text-rose-400 flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input wire:model="remember" id="remember" type="checkbox"
                        class="h-4 w-4 bg-slate-900 border-slate-700 rounded text-indigo-600 focus:ring-indigo-500">
                    <label for="remember" class="ml-2 block text-sm text-slate-400">Ingat Saya</label>
                </div>
            </div>

            <!-- Submit Button (Solid Indigo, Micro-interactions hover scale) -->
            <button type="submit"
                class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-800 transition-all duration-200 active:scale-95 flex items-center justify-center space-x-2">
                <span wire:loading.remove>Masuk ke Dashboard</span>
                <span wire:loading class="inline-flex items-center space-x-2">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span>Memproses...</span>
                </span>
            </button>
        </form>
    </div>

    <!-- System Info Footer -->
    <div class="text-center text-xs text-slate-500 space-y-1">
        <p>Akses Terenkripsi HTTPS &bull; Password Eye Toggle Enabled</p>
        <p>Default Login: <code
                class="text-indigo-400 bg-slate-800 px-1.5 py-0.5 rounded">admin@aditiacloudmon.com</code> / <code
                class="text-indigo-400 bg-slate-800 px-1.5 py-0.5 rounded">password123</code></p>
    </div>
</div>
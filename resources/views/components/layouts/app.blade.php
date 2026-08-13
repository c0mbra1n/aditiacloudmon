<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name', 'AditiaCloudMon') }}</title>

    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased bg-slate-900 text-slate-100 selection:bg-indigo-600 selection:text-white" x-data="{ sidebarOpen: false }">

    @auth
    <div class="min-h-screen flex flex-col">
        <!-- Top Navigation Bar -->
        <header class="bg-slate-800 border-b border-slate-700/60 sticky top-0 z-30 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Brand & Mobile Menu Toggle -->
                    <div class="flex items-center space-x-6">
                        <button @click="sidebarOpen = !sidebarOpen" type="button" class="md:hidden text-slate-400 hover:text-white focus:outline-none">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <a href="{{ route('servers.index') }}" class="flex items-center space-x-3 group">
                            <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center font-bold text-white shadow-sm transition-transform duration-200 group-hover:scale-105">
                                AC
                            </div>
                            <span class="font-bold text-lg text-white tracking-tight">AditiaCloud<span class="text-indigo-400">Mon</span></span>
                        </a>

                        <nav class="hidden md:flex items-center space-x-4 pl-4 border-l border-slate-700">
                            <a href="{{ route('servers.index') }}" class="text-xs font-semibold text-slate-300 hover:text-white transition-colors">Daftar Server</a>
                            <a href="{{ route('alerts.index') }}" class="text-xs font-semibold text-slate-300 hover:text-white transition-colors">Alerts</a>
                            <a href="{{ route('alert-rules.index') }}" class="text-xs font-semibold text-slate-300 hover:text-white transition-colors">Aturan Alert</a>
                            <a href="{{ route('notification-channels.index') }}" class="text-xs font-semibold text-slate-300 hover:text-white transition-colors">Kanal Notifikasi</a>
                        </nav>
                    </div>

                    <!-- Right User Dropdown & Status -->
                    <div class="flex items-center space-x-4" x-data="{ open: false }">
                        <div class="hidden sm:flex items-center space-x-2 bg-slate-900/80 px-3 py-1.5 rounded-lg border border-slate-700/50">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-xs font-medium text-slate-300">API Gateway v1 Active</span>
                        </div>

                        <!-- User Profile Dropdown -->
                        <div class="relative">
                            <button @click="open = !open" class="flex items-center space-x-3 focus:outline-none group">
                                <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center text-sm font-semibold text-slate-200 border border-slate-600 group-hover:border-indigo-500 transition-colors">
                                    {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                                </div>
                                <span class="hidden md:inline-block text-sm font-medium text-slate-200 group-hover:text-white">{{ auth()->user()->name ?? 'Admin' }}</span>
                                <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-200 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" class="absolute right-0 mt-2 w-48 bg-slate-800 rounded-xl shadow-lg border border-slate-700 py-1 z-50">
                                <div class="px-4 py-2 border-b border-slate-700">
                                    <p class="text-xs text-slate-400">Login sebagai</p>
                                    <p class="text-sm font-semibold text-slate-200 truncate">{{ auth()->user()->email ?? '' }}</p>
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-rose-400 hover:bg-slate-700/60 flex items-center space-x-2 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                        <span>Keluar (Logout)</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-slate-800 border-t border-slate-700/60 py-4 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center text-xs text-slate-400">
                <p>&copy; {{ date('Y') }} AditiaCloudMon — Windows VPS Monitoring Platform.</p>
                <p class="mt-2 sm:mt-0">PHP 8.3 &bull; Laravel 12 &bull; Outbound Agent Only</p>
            </div>
        </footer>
    </div>
    @else
    <!-- Guest Layout for Login -->
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-900">
        {{ $slot }}
    </div>
    @endauth

    @livewireScripts
</body>
</html>

<?php

use App\Livewire\Alerts\AlertIndex;
use App\Livewire\Alerts\AlertRules;
use App\Livewire\Auth\Login;
use App\Livewire\Server\ServerDetail;
use App\Livewire\Server\ServerList;
use App\Livewire\Settings\NotificationChannels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', ServerList::class)->name('dashboard')->middleware('auth');
Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::middleware('auth')->group(function () {
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');

    Route::get('/servers', ServerList::class)->name('servers.index');
    Route::get('/servers/{server}', ServerDetail::class)->name('servers.show');

    Route::get('/alerts', AlertIndex::class)->name('alerts.index');
    Route::get('/alert-rules', AlertRules::class)->name('alert-rules.index');
    Route::get('/notification-channels', NotificationChannels::class)->name('notification-channels.index');
});

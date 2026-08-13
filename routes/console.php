<?php

use Illuminate\Support\Facades\Schedule;

// 1. Periksa server offline setiap 1 menit (threshold 2 menit tanpa heartbeat)
Schedule::command('monitor:check-offline --threshold=2')->everyMinute();

// 2. Agregasi data metrics (1m, 5m, daily) setiap 1 menit
Schedule::command('monitor:aggregate-metrics')->everyMinute();

// 3. Evaluasi Aturan Alert setiap 1 menit
Schedule::command('monitor:evaluate-alerts')->everyMinute();

// 4. Bersihkan data raw metric yang lebih tua dari 24 jam setiap jam
Schedule::command('monitor:clean-raw-metrics --hours=24')->hourly();

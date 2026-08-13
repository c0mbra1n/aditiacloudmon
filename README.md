# Windows VPS Monitoring Platform — AditiaCloudMon

Platform monitoring modern khusus untuk infrastruktur **Windows VPS** yang dibangun sebagai proyek **Laravel 12 Murni di Root** yang dilengkapi dengan C# .NET System Tray Agent.

---

## 🚀 Fitur Utama Sistem

1. **Dashboard Monitoring Real-time**:
   - Tampilan Neo-Flat & Clean dengan warna solid tanpa gradasi (Mengikuti panduan UI/UX).
   - Indikator status server (ONLINE, WARNING, CRITICAL, OFFLINE).
   - Visualisasi pemanfaatan CPU, RAM, Disk C:, Uptime, dan IP Address.
   - Rotasi Secret Token autentikasi Agent & notifikasi SweetAlert2 + NProgress.
2. **Grafik Histori Performa (ApexCharts)**:
   - Pemilihan rentang waktu interaktif: **1 Jam**, **6 Jam**, **24 Jam**, **7 Hari**, dan **30 Hari**.
   - Agregasi data time-series otomatis (1m, 5m, daily) dan retention policy otomatis (Pembersihan data raw > 24 jam).
3. **Windows Services Monitoring**:
   - Pemantauan status *service* server utama: **IIS (W3SVC)**, **MySQL**, **MariaDB**, **Redis**, **Apache**, **SQL Server (MSSQLSERVER)**, dan **OpenSSH (sshd)**.
   - Indikator status badge: Running (Emerald), Stopped (Rose), Paused (Amber), Unknown (Slate).
4. **Target Process & Port Listener Monitoring**:
   - Monitoring konsumsi CPU, RAM (Working Set), PID dari executable `mysqld.exe`, `httpd.exe`, `nginx.exe`, `php-cgi.exe`, dan `redis-server.exe`.
   - Pemantauan ketersediaan listener port TCP **80 (HTTP)**, **443 (HTTPS)**, **3306 (MySQL)**, **3389 (RDP)**, dan **22 (SSH)**.
5. **Alert Engine & Cooldown Rules**:
   - Evaluasi otomatis batas threshold metric/service/port.
   - Durasi cooldown window debounce untuk mencegah duplikasi pemicuan notifikasi.
   - Status penanganan alert: **OPEN**, **ACKNOWLEDGED**, **RESOLVED** (dengan fitur *auto-resolve* saat kondisi kembali normal).
6. **Multi-Channel Notifications (Telegram & Discord)**:
   - Integrasi penyiaran alert ke **Telegram Bot API** (Format HTML dengan emoji) dan **Discord Webhook** (Format Rich Embeds).
   - Fitur "Uji Koneksi" langsung dari antarmuka web.

---

## 🛠️ Teknologi & Stack

* **Dashboard (Web Application)**:
  * Framework: Laravel 12 (PHP 8.3)
  * Database: MySQL 8 / MariaDB (`aditia_cloud_mon`)
  * Frontend: Livewire, Alpine.js, Tailwind CSS (Solid colors), ApexCharts, SweetAlert2, NProgress
* **Agent (Worker & System Tray Desktop App)**:
  * Framework: C# .NET 8 System Tray Application (`AditiaMonitorAgent` / `AditiaMonitor.Agent.exe`)
  * Komunikasi: **Outbound HTTPS POST Only** ke `/api/v1/agent/*` (Tidak ada inbound listening port).
  * Autentikasi: Bearer Secret Token (SHA-256 Hashed).

---

## 📂 Struktur Repository (Laravel Root Project)

```text
aditiacloudmon/                       # Project Root (Langsung Laravel)
├── app/                              # Models, Controllers, Livewire, Services
│   ├── Console/Commands/             # CheckOffline, AggregateMetrics, CleanRawMetrics, EvaluateAlerts
│   ├── Http/Controllers/             # AgentApiController (/api/v1/agent/*)
│   ├── Livewire/                     # ServerList, ServerDetail, AlertIndex, AlertRules, NotificationChannels
│   ├── Models/                       # Server, Agent, AgentToken, ServerMetric, ServerService, ServerProcess, ServerPort, AlertRule, Alert, NotificationChannel, NotificationLog
│   └── Services/                     # NotificationService
├── bootstrap/                        # Laravel Bootstrap
├── config/                           # Laravel Config
├── database/migrations/              # Complete database schema migrations
├── public/                           # Web Server Document Root (Point ke sini di HestiaCP / Nginx)
├── resources/                        # Blade Views & CSS/JS Tailwind
├── routes/                           # web.php, api.php, console.php
├── agent/                            # C# .NET 8 Worker & System Tray Agent
│   ├── Forms/                        # SettingsForm.cs
│   ├── Models/                       # Payload DTOs
│   ├── Services/                     # WindowsMetricCollector, ApiClient
│   └── TrayApplicationContext.cs     # Windows System Tray Application
├── docs/                             # Technical documentation
├── scripts/                          # PowerShell & GUI Setup scripts (install-wizard.ps1, install-agent.ps1)
├── .env.example
├── composer.json
├── package.json
└── README.md
```

---

## 🧪 Pengujian Otomatis (Automated Test Suite)

Jalankan perintah pengujian langsung di root project:

```bash
php artisan test
```

**Hasil Pengujian Otomatis**: **29 PASSED (81 Assertions)** 🟢

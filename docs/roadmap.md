# ROADMAP & PHASE BREAKDOWN — WINDOWS VPS MONITORING PLATFORM

Dokumen ini mendeskripsikan tahapan implementasi bertahap (*Phase Breakdown*) untuk membangun Windows VPS Monitoring Platform dari landasan arsitektur hingga *production-ready*.

---

## 🟢 PHASE 0 — Architecture & Foundation Setup (SELESAI)

* **Fokus**: Perancangan struktur monorepo, penetapan standar pengkodean, serta pembuatan dokumentasi arsitektur dan spesifikasi API.
* **Deliverables**:
  * `AGENTS.md` (Instruksi operasional & aturan pengkodean AI agent/developer).
  * `README.md` (Dokumentasi utama repositori).
  * `docs/architecture.md` (Spesifikasi arsitektur & aliran data).
  * `docs/api-contract.md` (Spesifikasi REST API Contract v1).
  * `docs/database.md` (Desain skema database & retention policy).
  * `docs/metrics.md` (Klasifikasi & spesifikasi metric).
  * `docs/security.md` (Standar & aturan keamanan).
  * `docs/deployment.md` (Panduan deployment produksi).
  * `docs/roadmap.md` (Rencana tahapan eksekusi).
  * `.gitignore` (Konfigurasi pengabaian berkas monorepo).
* **Acceptance Criteria**: Struktur repositori siap dan seluruh dokumen arsitektur diverifikasi.

---

## 🟢 PHASE 1 — Dashboard Foundation (SELESAI)

* **Fokus**: Inisialisasi proyek Laravel 12 pada folder `dashboard/`, sistem autentikasi pengguna, antarmuka dasar berbasis Tailwind CSS, Livewire, Alpine.js, NProgress, dan SweetAlert2.
* **Deliverables**:
  * Aplikasi Laravel 12 dengan PHP 8.3 & MySQL (`aditia_cloud_mon`).
  * Autentikasi Pengguna Admin (Login & Logout dengan toggle password eye icon).
  * Migration & Model tabel: `users`, `servers`, `agents`, `agent_tokens`.
  * Layout Admin Dashboard (Neo-Flat solid colors, NProgress, SweetAlert2 confirm & toast notifications).
* **Acceptance Criteria**: User Admin dapat login dan melihat halaman daftar server dasar (Terverifikasi 6 Automated Tests Pass).

---

## 🟢 PHASE 2 — Agent MVP (SELESAI)

* **Fokus**: Inisialisasi C# .NET Worker Service pada folder `agent/` untuk mengumpulkan metric dasar dan Dashboard Ingestion API.
* **Deliverables**:
  * Project `.NET Worker Service` (`AditiaMonitorAgent` / `AditiaMonitor.Agent.csproj`).
  * System Metric Collectors: Hostname, OS Version, CPU usage, RAM usage, Disk usage, Network Interfaces, Uptime.
  * HttpClient API Client dengan Outbound HTTPS POST, Bearer Token, dan Retry Exponential Backoff.
  * Dashboard API Receiver Endpoints: `POST /api/v1/agent/heartbeat` & `POST /api/v1/agent/metrics`.
* **Acceptance Criteria**: Agent C# dan Dashboard API terintegrasi penuh (Terverifikasi 9 Automated Tests Pass).

---

## 🟢 PHASE 3 — Heartbeat & Server Availability (SELESAI)

* **Fokus**: Implementasi heartbeat ping periodik, kalkulasi status ketersediaan server (ONLINE, WARNING, CRITICAL, OFFLINE), dan otomatisasi deteksi server offline.
* **Deliverables**:
  * API Endpoint `POST /api/v1/agent/heartbeat` dengan kalkulasi status threshold dinamis.
  * Tracking `last_seen_at` dan kalkulasi selisih durasi heartbeat.
  * Artisan Command `monitor:check-offline` (Threshold default 2 menit) & terdaftar di Laravel Scheduler (`routes/console.php`).
* **Acceptance Criteria**: Dashboard dan Scheduler memproses heartbeat serta mengubah status server menjadi OFFLINE secara otomatis saat heartbeat terhenti (Terverifikasi 12 Automated Tests Pass).

---

## 🟢 PHASE 4 — Real-time Dashboard Monitoring UI (SELESAI)

* **Fokus**: Pembuatan antarmuka visual monitoring server di Dashboard (Server Cards Summary & Halaman Detail Server Real-time).
* **Deliverables**:
  * Dashboard Summary Status Cards Grid (Total, Online, Warning, Critical, Offline).
  * Halaman Detail Server (`/servers/{server}`) dengan Livewire Real-time Polling (`wire:poll.5s`).
  * Card Utilization Performa (CPU %, RAM GB & %, Disk C: Storage, Uptime, IP Address, Specs).
  * Multi-Tab Interface (Overview, Penyimpanan Disk, Jaringan Network, & Secret Tokens).
  * Fitur Rotasi Secret Token Agent & Pencabutan Token dengan SweetAlert2 confirm.
* **Acceptance Criteria**: Pengguna dapat memantau kondisi terkini seluruh Windows VPS secara real-time / near real-time dan mengelola token agent (Terverifikasi 16 Automated Tests Pass).

---

## 🟢 PHASE 5 — Historical Metrics & Aggregation (SELESAI)

* **Fokus**: Penyimpanan data metric beruntun, mesin agregasi data (1m, 5m, daily), visualisasi grafik histori ApexCharts, dan eksekusi Retention Policy.
* **Deliverables**:
  * Skema database agregasi: `metric_aggregates_1m`, `metric_aggregates_5m`, `metric_aggregates_daily`.
  * Artisan Command `monitor:aggregate-metrics` (Agregasi otomatis 1m/5m/daily) & terdaftar di Scheduler.
  * Artisan Command `monitor:clean-raw-metrics` (Pembersihan otomatis data raw > 24 jam) & terdaftar di Scheduler.
  * Visualisasi Grafik Histori ApexCharts di Halaman Detail Server dengan pemilih periode: 1 Jam, 6 Jam, 24 Jam, 7 Hari, & 30 Hari.
* **Acceptance Criteria**: Grafik fluktuasi performa CPU & Memory tampil secara interaktif dan retention policy otomatis membersihkan data raw tanpa membebani database (Terverifikasi 19 Automated Tests Pass).

---

## 🟢 PHASE 6 — Windows Services Monitoring (SELESAI)

* **Fokus**: Pemantauan status Windows Services target (IIS, MySQL, MariaDB, Redis, Apache, MSSQL, & OpenSSH) pada Windows VPS.
* **Deliverables**:
  * C# Agent Windows Services Query Collector (ServiceController API) & DTO `ServiceStatusPayload`.
  * Dashboard REST API Endpoint `POST /api/v1/agent/services`.
  * Skema database `server_services` (`id`, `server_id`, `service_name`, `display_name`, `status`, `updated_at`).
  * Tab UI "Windows Services" pada Halaman Detail Server dengan indikator badge status (Running, Stopped, Paused, Unknown) dan pencarian instan.
* **Acceptance Criteria**: Dashboard dan Agent mengumpulkan status layanan Windows VPS dan menampikannya di UI (Terverifikasi 22 Automated Tests Pass).

---

## 🟢 PHASE 7 — Process & Port Monitoring (SELESAI)

* **Fokus**: Pemantauan proses target executable (`mysqld.exe`, `httpd.exe`, `nginx.exe`, `php-cgi.exe`, `redis-server.exe`) dan status ketersediaan port listener lokal (80, 443, 3306, 3389, 22).
* **Deliverables**:
  * C# Agent Process & Port Query Collector (`Process.GetProcessesByName()` & `IPGlobalProperties` TCP listeners).
  * Dashboard REST API Endpoint `POST /api/v1/agent/processes`.
  * Skema database `server_processes` dan `server_ports`.
  * Tab UI "Proses Target" (PID, CPU %, RAM MB, Status) & Tab UI "Port Listener" (Open/Closed badges) pada Halaman Detail Server.
* **Acceptance Criteria**: Dashboard dan Agent mengumpulkan serta menampilkan status proses target dan port listener (Terverifikasi 25 Automated Tests Pass).

---

## 🟢 PHASE 8 — Alert Engine & Cooldown Rules (SELESAI)

* **Fokus**: Pembuatan mesin evaluasi alert otomatis pada Dashboard Laravel, pemicuan notifikasi berdasarkan threshold metric/service/port, cooldown window debounce, serta manajemen status (OPEN, ACKNOWLEDGED, RESOLVED).
* **Deliverables**:
  * Skema database `alert_rules` dan `alerts`.
  * Artisan Command `monitor:evaluate-alerts` (Evaluasi otomatis, cooldown debounce, & auto-resolve) terdaftar di Scheduler.
  * Halaman Manajemen Alert (`/alerts`) dengan filter status (OPEN, ACKNOWLEDGED, RESOLVED) dan aksi konfirmasi SweetAlert2.
  * Halaman Manajemen Aturan Alert (`/alert-rules`) dengan modal formulir pembuatan/pengeditan aturan dan toggle status active.
* **Acceptance Criteria**: Mesin evaluasi alert mendeteksi pelanggaran threshold, mencegah duplikasi pemicuan di jendela cooldown, dan meng-auto-resolve alert jika kondisi kembali normal (Terverifikasi 27 Automated Tests Pass).

---

## 🟢 PHASE 9 — Notification Channels (Telegram & Discord Integration) (SELESAI)

* **Fokus**: Integrasi penyiaran notifikasi alert real-time ke Telegram Bot API dan Discord Webhooks beserta pencatatan log eksekusi.
* **Deliverables**:
  * Skema database `notification_channels` & `notification_logs`.
  * Service Layer `App\Services\NotificationService` (Format pesan HTML/Embeds untuk Telegram & Discord, Uji Koneksi Channel, & Logging).
  * Terintegrasi dengan mesin evaluasi alert (`EvaluateAlertsCommand`).
  * Halaman Manajemen Kanal Notifikasi (`/notification-channels`) dengan pengujian koneksi langsung, pembuatan/pengeditan token & webhook URL, serta toggle status active.
* **Acceptance Criteria**: Notifikasi alert dikirimkan ke Telegram dan Discord secara real-time saat pemicuan dan auto-resolve alert terjadi (Terverifikasi 29 Automated Tests Pass).

---

## 🟢 PHASE 10 — Security Hardening, Compilation & Production Packaging (SELESAI)

* **Fokus**: Kompilasi executable Agent C#, otomatisasi skrip instalasi PowerShell, verifikasi keamanan API, dan rilis produksi.
* **Deliverables**:
  * Kompilasi C# Agent standalone single-file (`PublishSingleFile=true`) `AditiaMonitor.Agent.exe`.
  * PowerShell Automation Script: `scripts/install-agent.ps1` & `scripts/uninstall-agent.ps1`.
  * Panduan Deployment Production & Audit Keamanan (`docs/deployment.md`).
  * Seluruh 29 Automated Feature Tests Passed 100%.
* **Acceptance Criteria**: Seluruh 10 fase pengembangan platform monitoring Windows VPS berhasil diselesaikan, diuji, dan siap dideploy ke lingkungan produksi.

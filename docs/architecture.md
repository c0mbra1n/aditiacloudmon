# ARSITEKTUR SISTEM — WINDOWS VPS MONITORING PLATFORM

Dokumen ini menjelaskan arsitektur teknis, aliran data, komponen utama, serta strategi ketahanan (*resilience*) dan skalabilitas dari Windows VPS Monitoring Platform.

---

## 1. HIGHLIGHT ARSITEKTUR

Platform ini dirancang dengan prinsip **Decoupled Outbound Monitoring Architecture**. Agent yang terpasang di Windows VPS hanya melakukan komunikasi satu arah (*outbound*) ke Dashboard API melalui protokol HTTPS aman.

### Visualisasi Arsitektur:

```text
                                INTERNET
                                   │
                                   │ HTTPS (Standard Port 443)
                                   ▼
                ┌─────────────────────────────────────┐
                │        Monitoring Server            │
                │                                     │
                │  Laravel 12 REST API (/api/v1/)     │
                │  Redis (Cache & Queue Worker)       │
                │  MySQL 8 Database                   │
                │  Livewire & Alpine.js Dashboard     │
                └──────────────────┬──────────────────┘
                                   ▲
                                   │
                         HTTPS Outbound Request
                                   │
          ┌────────────────────────┼────────────────────────┐
          │                        │                        │
          ▼                        ▼                        ▼
    Windows VPS 01           Windows VPS 02           Windows VPS 03
┌───────────────────────┐┌───────────────────────┐┌───────────────────────┐
│ Windows Service       ││ Windows Service       ││ Windows Service       │
│ AditiaMonitorAgent    ││ AditiaMonitorAgent    ││ AditiaMonitorAgent    │
│                       ││                       ││                       │
│ Collect Metrics       ││ Collect Metrics       ││ Collect Metrics       │
│ Validate & Send       ││ Validate & Send       ││ Validate & Send       │
└───────────────────────┘└───────────────────────┘└───────────────────────┘
```

---

## 2. KOMPONEN UTAMA SISTEM

### A. Windows Monitoring Agent (`/agent`)
* **Teknologi**: C# .NET modern (Worker Service / Windows Service).
* **Service Name**: `AditiaMonitorAgent`
* **Executable**: `AditiaMonitor.Agent.exe`
* **Tugas**:
  1. Pengumpulan metric sistem (CPU, Memory, Disk, Network, System Info, Windows Services, Process, Port).
  2. Pembungkusan data ke dalam payload JSON standar.
  3. Mengirimkan payload ke Dashboard API via `HttpClient` (HTTPS POST).
  4. Penanganan retry berbasis **Exponential Backoff** apabila Dashboard API mengalami kendala/downtime.
* **Batasan**: Tidak mendengarkan (*listen*) pada port inbound mana pun dan tidak mengeksekusi *remote shell script* dari server.

### B. Web Monitoring Dashboard (`/dashboard`)
* **Teknologi**: Laravel 12 (PHP 8.3), MySQL 8, Redis, Livewire, Tailwind CSS, Alpine.js, ApexCharts, SweetAlert2, NProgress.
* **Tugas**:
  1. **API Endpoints (`/api/v1/agent/*`)**: Memvalidasi Bearer Token agent, menerima heartbeat dan payload metric.
  2. **Storage & Queue**: Menyimpan metric rawa pada database MySQL dan memproses aggregasi via Redis Laravel Queue.
  3. **Alert Engine**: Memeriksa threshold metric secara asinkron, mengelola state alert (ACTIVE, ACKNOWLEDGED, RESOLVED), dan mencegah alert spam dengan cooldown/debounce.
  4. **Notifikasi**: Mengirim notifikasi otomatis melalui channel Telegram Bot dan Email.
  5. **Web UI**: Menampilkan status server secara real-time / near real-time, statistik, grafik histori, manajemen Windows Services, dan audit log.

---

## 3. ALIRAN DATA & INTERAKSI (DATA FLOW)

```text
[Agent: Collect Metric] ──> [Validate Payload] ──> [HTTPS POST /api/v1/agent/metrics]
                                                               │
                                                               ▼
                                                  [Dashboard: Autentikasi Token]
                                                               │
                                                               ▼
                                                  [Simpan Raw Metric ke MySQL]
                                                               │
                                                               ▼
                                                  [Dispatch Laravel Queue Job]
                                                               │
                           ┌───────────────────────────────────┴───────────────────────────────────┐
                           ▼                                                                       ▼
               [Evaluasi Alert Rules]                                                 [Aggregasi Data Histori]
                           │                                                                       │
             (Jika Threshold Terlewati)                                            (Simpan Aggregat 1m/5m/1d)
                           ▼
             [Kirim Telegram / Email Alert]
```

---

## 4. STRATEGI KETAHANAN & SKALABILITAS (RESILIENCE & SCALABILITY)

### Agent Resilience
* **Network Interruption**: Jika request HTTPS gagal, Agent tidak boleh crash. Agent mengimplementasikan *Retry Mechanism* dengan *Exponential Backoff* (misal: retry setelah 5s, 10s, 30s, max 5 menit) dan Cancellation Token.
* **Resource Footprint**: Agent membatasi penggunaan CPU (< 1%) dan RAM (< 30 MB) pada Windows VPS agar tidak mengganggu performa server target.

### Dashboard & Database Scalability
* **Asynchronous Processing**: Ingesting metric API merespon secepat mungkin (< 50ms) dan menyerahkan evaluasi alert & aggregasi ke Redis Queue Worker.
* **Metric Retention & Partitioning**: Data metric raw hanya disimpan selama 24 jam. Aggregasi 1-menit disimpan 30 hari, 5-menit selama 90 hari, dan harian hingga 1 tahun untuk mencegah penggelembungan database.

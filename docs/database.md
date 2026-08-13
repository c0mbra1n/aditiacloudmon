# SKEMA DATABASE & METRIC RETENTION POLICY — WINDOWS VPS MONITORING PLATFORM

Dokumen ini menjelaskan struktur database relasional (MySQL 8 / MariaDB), relasi antar entitas, strategi indexing, serta kebijakan pengarsipan (*retention policy*) data metric.

---

## 1. STRUKTUR ENTITAS ENTITY-RELATIONSHIP (ERD)

Database dirancang terpusat dengan minimal 15 entitas utama:

```text
               ┌──────────────┐
               │    users     │
               └──────┬───────┘
                      │ 1:N
                      ▼
               ┌──────────────┐          1:1          ┌──────────────┐
               │   servers    ├──────────────────────►│    agents    │
               └──────┬───────┘                       └──────┬───────┘
                      │                                      │ 1:N
     ┌────────────────┼────────────────┐                     ▼
     │ 1:N            │ 1:N            │ 1:N          ┌──────────────┐
     ▼                ▼                ▼              │ agent_tokens │
┌──────────────┐┌──────────────┐┌──────────────┐      └──────────────┘
│server_metrics││server_services││server_process│
└──────────────┘└──────────────┘└──────────────┘
     │ 1:N            │                │
     ├──────────────┐ │                │
     ▼              ▼ ▼                ▼
┌──────────────┐┌──────────────┐┌──────────────┐
│ server_disks ││ alert_rules  ││    alerts    │
└──────────────┘└──────┬───────┘└──────┬───────┘
                       │ 1:N           │ 1:N
                       ▼               ▼
               ┌──────────────┐┌──────────────┐
               │notif_channels││notifications │
               └──────────────┘└──────────────┘
```

---

## 2. DETAIL DEFINISI TABEL UTAMA

### A. Tabel Core & Server
1. **`users`**: Data pengembang / admin platform monitoring.
2. **`servers`**: Menyimpan master data Windows VPS yang dipantau.
   * `id` (UUID PK), `name`, `hostname`, `ip_address`, `os_name`, `os_version`, `status` (ONLINE, WARNING, CRITICAL, OFFLINE, MAINTENANCE, UNKNOWN), `last_seen_at`, `created_at`, `updated_at`.
3. **`agents`**: Menyimpan detail aplikasi agent pada server.
   * `id` (UUID PK), `server_id` (FK servers), `agent_version`, `status` (ACTIVE, INACTIVE, REVOKED), `heartbeat_interval_seconds`.
4. **`agent_tokens`**: Menyimpan token autentikasi agent yang di-hash.
   * `id` (UUID PK), `agent_id` (FK agents), `token_hash` (VARCHAR 255), `name`, `last_used_at`, `expires_at`, `revoked_at`.

### B. Tabel Metrics & Data Telemetri
5. **`server_metrics`**: Data raw per-sample dari agent.
   * `id` (BIGINT UNSIGNED PK AUTO_INCREMENT), `server_id` (FK), `cpu_usage_percent`, `ram_total_bytes`, `ram_used_bytes`, `ram_usage_percent`, `uptime_seconds`, `created_at` (INDEX).
6. **`server_disks`**: Data penggunaan drive/disk per server.
   * `id` (BIGINT UNSIGNED PK), `server_metric_id` (FK), `server_id` (FK), `drive_letter`, `label`, `filesystem`, `total_bytes`, `free_bytes`, `used_bytes`, `usage_percent`.
7. **`server_networks`**: Data interface jaringan & bandwidth.
   * `id` (BIGINT UNSIGNED PK), `server_metric_id` (FK), `server_id` (FK), `interface_name`, `ip_address`, `bytes_sent_per_sec`, `bytes_recv_per_sec`.
8. **`server_services`**: Status Windows Services target.
   * `id` (BIGINT UNSIGNED PK), `server_id` (FK), `service_name`, `display_name`, `status` (Running, Stopped, Paused, Unknown), `updated_at`.
9. **`server_processes`**: Status proses target & port.
   * `id` (BIGINT UNSIGNED PK), `server_id` (FK), `process_name`, `pid`, `cpu_percent`, `memory_bytes`, `status`.

### C. Tabel Aggregasi Metric (Performance Storage)
10. **`metric_aggregates_1m`**: Agregat per 1 menit (min, max, avg CPU/RAM/Disk).
11. **`metric_aggregates_5m`**: Agregat per 5 menit.
12. **`metric_aggregates_daily`**: Agregat harian.

### D. Tabel Alerts & Notifications
13. **`alert_rules`**: Aturan kondisi alert (misal: CPU > 90% selama 5m).
   * `id` (UUID PK), `server_id` (FK nullable/all), `metric_type`, `operator`, `threshold`, `duration_seconds`, `cooldown_seconds`, `severity` (INFO, WARNING, CRITICAL).
14. **`alerts`**: Log alert yang terpicu.
   * `id` (UUID PK), `server_id` (FK), `rule_id` (FK), `severity`, `status` (ACTIVE, ACKNOWLEDGED, RESOLVED), `message`, `triggered_at`, `resolved_at`, `acknowledged_at`.
15. **`notification_channels`**: Channel pengiriman notifikasi (Telegram, Email, Webhook).
16. **`notifications`**: Log riwayat notifikasi yang terkirim.
17. **`maintenance_windows`**: Jadwal pemeliharaan server (supaya alert di-pause).
18. **`audit_logs`**: Log aktivitas pengubahan konfigurasi di dashboard.

---

## 3. STRATEGI INDEXING & PERFORMANCE

* **Index Utama Metric**: Index komposit `(server_id, created_at)` pada tabel `server_metrics` untuk mempercepat query grafik histori berdasarkan kurun waktu.
* **Avoid Full Table Scans**: Query dashboard HANYA membaca tabel aggregat (`metric_aggregates_1m` / `5m` / `daily`) untuk rentang waktu di atas 24 jam.

---

## 4. METRIC RETENTION POLICY (KEBIJAKAN PENGARSIPAN DATA)

Untuk mencegah pembengkakan ukuran database MySQL:

| Tipe Data | Periode Retention | Eksekusi Aggregasi |
| :--- | :--- | :--- |
| **Raw Metrics** | **24 Jam** | Laravel Scheduler (Setiap jam menghapus data > 24 jam) |
| **Agregat 1-Menit** | **30 Hari** | Laravel Queue Worker (Setiap menit memproses data raw) |
| **Agregat 5-Menit** | **90 Hari** | Laravel Queue Worker (Setiap 5 menit memproses data 1m) |
| **Agregat Harian** | **1 Tahun (365 Hari)** | Laravel Scheduler (Setiap tengah malam memproses 5m) |

---

## 5. MIGRATION GUIDELINES

* Semua migration Laravel wajib menyertakan Foreign Key constraint lengkap dengan `onDelete('cascade')` atau `onDelete('set null')` sesuai domain logic.
* DILARANG mengubah atau menghapus file migration yang sudah berjalan di environment staging/production. Selalu buat file migration baru untuk perubahan skema.

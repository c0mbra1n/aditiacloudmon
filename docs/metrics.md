# METRIC SPECIFICATION — WINDOWS VPS MONITORING PLATFORM

Dokumen ini mencakup rincian seluruh metric sistem yang dikumpulkan oleh **Windows Monitoring Agent** dari Windows VPS serta bagaimana metric tersebut diklasifikasikan dan ditampilkan di **Dashboard Monitoring**.

---

## 1. CATEGORY METRICS

### A. System & Hardware Information (Static / Infrequent)
Metric informasi dasar server yang diambil saat agent startup atau registrasi:

| Metric Name | Type | Description | Contoh Value |
| :--- | :--- | :--- | :--- |
| `hostname` | String | Hostname Windows VPS | `WIN-JAKARTA-01` |
| `os_name` | String | Nama Sistem Operasi | `Windows Server 2022 Datacenter` |
| `os_version` | String | Versi/Build OS Windows | `10.0.20348` |
| `cpu_model` | String | Model Prosessor / CPU | `Intel Xeon Gold 6248R` |
| `cpu_cores` | Integer | Jumlah Core CPU | `4` |
| `ram_total_bytes` | BigInt | Total Kapasitas RAM (Bytes) | `17179869184` (16 GB) |
| `ip_address` | String | IP Address Utama Server | `103.140.20.15` |
| `agent_version` | String | Versi Aplikasi Agent | `1.0.0` |

---

### B. Dynamic Resource Utilization Metrics (High Frequency)
Metric performa komputasi yang diambil secara periodik (default per 30-60 detik):

#### 1. CPU Metrics
* **`cpu_usage_percent`**: Persentase total penggunaan CPU saat ini (0.0% - 100.0%).
* **`load_average`**: Beban prosessor jika tersedia pada sampel kurun waktu.

#### 2. RAM / Memory Metrics
* **`ram_used_bytes`**: Jumlah memori terpakai dalam Bytes.
* **`ram_free_bytes`**: Jumlah memori bebas dalam Bytes.
* **`ram_usage_percent`**: Persentase penggunaan RAM terhitung `(used / total) * 100`.

#### 3. Disk Storage Metrics (Per Logical Drive)
* **`drive_letter`**: Huruf drive (misal `C:`, `D:`).
* **`label`**: Label drive volume (misal `System`, `Data`).
* **`filesystem`**: Format sistem berkas (misal `NTFS`, `ReFS`).
* **`total_bytes`**: Total ukuran disk dalam Bytes.
* **`used_bytes`**: Ukuran disk terpakai dalam Bytes.
* **`free_bytes`**: Ukuran sisa disk bebas dalam Bytes.
* **`usage_percent`**: Persentase penggunaan disk.

#### 4. Network Utilization Metrics (Per Network Interface)
* **`interface_name`**: Nama NIC / Ethernet Adapter.
* **`bytes_sent_per_sec`**: Kecepatan data terkirim (Upload bandwidth B/s).
* **`bytes_recv_per_sec`**: Kecepatan data diterima (Download bandwidth B/s).

---

### C. Server Availability & Health Metrics
Metric ketersediaan server yang dievaluasi di Dashboard:

* **`uptime_seconds`**: Durasi waktu (detik) sejak server Windows VPS menyala (*boot time*).
* **`last_seen_at`**: Timestamp terakhir kali Dashboard menerima heartbeat dari Agent.
* **`server_status`**: Status kesehatan terhitung berdasarkan kombinasi metric:
  * `ONLINE`: Heartbeat diterima tepat waktu dan seluruh resource di bawah threshold warning.
  * `WARNING`: Heartbeat aktif tetapi terdapat resource (CPU/RAM/Disk) melewati threshold Warning.
  * `CRITICAL`: Heartbeat aktif tetapi resource melewati threshold Critical (misal CPU > 95% atau Disk > 95%).
  * `OFFLINE`: Heartbeat tidak diterima lebih dari threshold `offline_threshold` (default 2 menit).
  * `MAINTENANCE`: Server dalam periode pemeliharaan terjadwal.
  * `UNKNOWN`: Status awal registrasi sebelum heartbeat pertama diterima.

---

### D. Windows Services Monitoring Metrics
Agent memeriksa status Windows Services spesifik yang terkonfigurasi (contoh: IIS, MySQL, MariaDB, Apache, Redis, MSSQL, OpenSSH):

| State Status | Keterangan |
| :--- | :--- |
| `Running` | Service sedang berjalan normal. |
| `Stopped` | Service dalam keadaan terhenti (*stopped*). |
| `Paused` | Service sedang dalam kondisi paused. |
| `Unknown` | Status service tidak dapat diidentifikasi oleh Agent. |

---

### E. Target Process Monitoring Metrics
Agent memantau proses spesifik yang terdaftar di konfigurasi (misal `mysqld.exe`, `httpd.exe`, `nginx.exe`, `php-cgi.exe`, `redis-server.exe`):

* **`pid`**: Process ID sistem Windows.
* **`cpu_percent`**: Penggunaan CPU spesifik oleh proses tersebut.
* **`memory_bytes`**: Konsumsi RAM (Working Set) oleh proses tersebut dalam Bytes.
* **`status`**: Status eksekusi proses (`Running` / `Not Found`).

---

### F. Port Availability Monitoring Metrics
Agent memeriksa ketersediaan listener port pada Windows VPS lokal:

* **Port Target**: `80` (HTTP), `443` (HTTPS), `3306` (MySQL), `3389` (RDP), `22` (SSH).
* **Status**: `Open` / `Closed` / `Filtered`.

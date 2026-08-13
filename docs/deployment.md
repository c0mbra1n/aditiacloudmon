# Production Deployment & Compilation Guide — AditiaCloudMon

Dokumen ini berisi panduan kompilasi C# Agent, instalasi Windows Service, setup Dashboard Laravel 12 di environment production (termasuk HestiaCP), serta langkah-langkah penguatan keamanan (*security hardening*).

---

## 1. Kompilasi C# Agent (`AditiaMonitor.Agent.exe`)

Agent C# dikompilasi menjadi sebuah file *standalone single-file executable* (`.exe`) yang menyertakan .NET 8 runtime di dalamnya sehingga dapat langsung dijalankan pada Windows VPS target tanpa perlu menginstal .NET SDK secara terpisah.

### Perintah Kompilasi (Windows / macOS / Linux Build Pipeline):

```bash
cd agent
dotnet publish -c Release -r win-x64 --self-contained true -p:PublishSingleFile=true -o ./publish
```

**Output File**: `agent/publish/AditiaMonitor.Agent.exe`

---

## 2. Instalasi Agent pada Target Windows VPS

1. Salin file `AditiaMonitor.Agent.exe` dan `scripts/install-wizard.bat` ke server Windows VPS target.
2. Klik 2x pada file **`scripts/install-wizard.bat`** (atau `install-wizard.ps1`).
3. Masukkan **Server URL** dan **Secret Token**, lalu klik **Install & Start Service**.

---

## 3. Deployment Dashboard Laravel 12 di HestiaCP / Production

### Prasyarat Environment:
* PHP 8.3+ (Ext: `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `curl`, `json`)
* Web Server: Nginx / Apache
* Database: MySQL 8.0+ / MariaDB 10.5+

### Langkah Inisialisasi di HestiaCP / VPS Root:

```bash
cd /home/admin/web/dashboard.domainanda.com/

# 1. Hubungkan public_html ke folder public Laravel
rm -rf public_html
ln -s /home/admin/web/dashboard.domainanda.com/public public_html

# 2. Install PHP & Node Dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 3. Setup Environment Configuration
cp .env.example .env
php artisan key:generate

# Edit .env sesuaikan DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 4. Jalankan Database Migrations & Seeding
php artisan migrate --force
php artisan db:seed --force

# 5. Cache Konfigurasi & Rute untuk Performa Tinggi
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Fix Permission Storage HestiaCP
chown -R admin:admin /home/admin/web/dashboard.domainanda.com/
chmod -R 775 storage bootstrap/cache
```

### Konfigurasi Cron Scheduler di HestiaCP UI:

Jalankan Laravel Scheduler setiap menit di HestiaCP Panel (Menu CRON -> Add Cron Job `* * * * *`):

```text
cd /home/admin/web/dashboard.domainanda.com && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

---

## 4. Penguatan Keamanan (*Security Hardening*)

1. **Komunikasi Outbound HTTPS-Only**:
   * Agent **HANYA** mengirim data keluar (*outbound POST*) ke `/api/v1/agent/*`.
   * Agent **TIDAK PERNAH** membuka port listener inbound di internet.
2. **Strict Prohibition of Remote Execution**:
   * Dilarang keras membuat fitur remote PowerShell / CMD execution dari Dashboard ke Agent untuk mencegah celah *Remote Code Execution (RCE)*.
3. **Secret Token SHA-256 Hashing**:
   * Token autentikasi disimpan di database dalam bentuk hash SHA-256 (`hash('sha256', $token)`).
   * Fitur Rotasi Secret Token tersedia di UI untuk membatalkan token lama yang terkompromi.
4. **Rate Limiting & Input Validation**:
   * Endpoint API `/api/v1/agent/*` dilindungi middleware throttle rate limiter.

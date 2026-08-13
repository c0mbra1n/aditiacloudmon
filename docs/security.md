# PRINSIP & STANDAR KEAMANAN (SECURITY ARCHITECTURE) — WINDOWS VPS MONITORING PLATFORM

Keamanan adalah prioritas utama dalam perancangan Windows VPS Monitoring Platform. Dokumen ini menetapkan aturan keamanan wajib yang diterapkan pada **Dashboard Server** dan **Windows Monitoring Agent**.

---

## 1. DILARANG REMOTE COMMAND EXECUTION (STRICT RULE)

> [!CAUTION]
> **ATURAN UTAMA KEAMANAN PERANGKAT LUNAK**:
> Pada seluruh fase sistem ini, **DILARANG HARDIK** membuat fitur *Remote Command Execution*, eksekusi PowerShell interaktif, atau eksekusi perintah shell dari Dashboard ke Windows VPS Agent.
> Agent HANYA berfungsi sebagai komponen read-only pemantau (*Collector*).

---

## 2. STANDAR AUTENTIKASI AGENT & SECRET TOKENS

1. **Unique Agent Secret Token**:
   * Setiap Agent/Server memiliki Secret Token unik yang dihasilkan saat proses registrasi.
   * DILARANG menggunakan satu global token untuk seluruh Windows VPS.
2. **Hashed Token Storage**:
   * Token disimpan di database MySQL Dashboard dalam bentuk **Hashed Token** (`SHA-256` atau `Bcrypt`).
   * Plain-text secret token HANYA diberikan satu kali kepada Agent saat registrasi dan disimpan secara aman di file konfigurasi lokal Windows Agent (`config.json` terenkripsi / Windows Credential Manager).
3. **Token Management**:
   * Dashboard menyediakan antarmuka untuk:
     * Regenerasi / Rotasi secret token.
     * Revokasi (Revoke) token agent yang terkompromi.
     * Deaktivasi sementara akses agent.

---

## 3. KEAMANAN KOMUNIKASI & JARINGAN

1. **HTTPS Enforced Only**:
   * Seluruh komunikasi antara Agent dan Dashboard HARUS melalui enkripsi **HTTPS (TLS 1.2 / TLS 1.3)**.
   * Traffic HTTP biasa (Port 80) di-reject atau di-redirect otomatis ke HTTPS.
2. **Outbound Connection Only**:
   * Agent Windows VPS HANYA melakukan request *outbound* (port 443) ke Dashboard.
   * Agent **TIDAK MEMBUKA** port listener inbound dari internet, sehingga tidak memperluas *attack surface* dari Windows VPS yang dipantau.

---

## 4. PROTEKSI REQUEST & ANTIMANIPULASI

1. **Timestamp Validation & Anti-Replay Protection**:
   * Setiap request payload dari Agent menyertakan ISO 8601 UTC Timestamp (`timestamp`).
   * Dashboard memvalidasi bahwa selisih waktu request Agent tidak boleh lebih dari **± 5 menit** dari waktu server Dashboard untuk mencegah serangan *replay attack* atau isu sinkronisasi jam server.
2. **API Rate Limiting & Throttling**:
   * Endpoint API `/api/v1/agent/*` dilindungi dengan Laravel Rate Limiter (misal: maksimum 100 request/menit per agent token).
3. **Strict Request Validation**:
   * Semua payload masukan difilter dan divalidasi ketat menggunakan Laravel Form Request. Payload yang tidak valid langsung ditolak dengan HTTP 422 Unprocessable Entity.

---

## 5. KEAMANAN DASHBOARD WEB APPLICATION

1. **CSRF & Session Security**:
   * Seluruh formulir web dan komponen Livewire dilindungi CSRF Protection token.
2. **Password Security**:
   * Hashing password pengguna web menggunakan standar `Bcrypt` atau `Argon2id`.
   * Form autentikasi dilengkapi toggle ikon mata untuk mempermudah pengecekan input tanpa mengorbankan keamanan.
3. **Audit Logging**:
   * Seluruh tindakan sensitif admin di Dashboard (penambahan server, revokasi token agent, pengubahan alert rule, pembuatan maintenance window) dicatat secara terperinci di tabel `audit_logs`.

---

## 6. PRINSIP LEAST PRIVILEGE PADA WINDOWS AGENT

* Aplikasi `AditiaMonitorAgent` dijalankan sebagai Windows Service dengan akun bertipe **`NT AUTHORITY\LOCAL SERVICE`** atau **`NETWORK SERVICE`** bilamana memungkinkan, bukan `NT AUTHORITY\SYSTEM` atau akun Administrator domain penuh, untuk membatasi dampak apabila terjadi gangguan pada service.

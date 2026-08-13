# AGENTS.md — Agent & Developer Operating Instructions

Dokumen ini berisi panduan, aturan, dan standar pengkodean bagi AI Coding Agent dan pengembang yang bekerja pada repositori ini.

---

## 1. PROJECT OVERVIEW & ARCHITECTURE

Platform ini adalah **Windows VPS Monitoring Platform** monorepo yang terdiri dari 2 aplikasi independen:

1. `dashboard/`: Laravel 12 Web Application (PHP 8.3, MySQL, Redis, Livewire, Tailwind CSS, Alpine.js).
2. `agent/`: C# .NET Modern Worker Service / Windows Service (`AditiaMonitorAgent` / `AditiaMonitor.Agent.exe`).

### Prinsip Utama Arsitektur:
* **Komunikasi Satu Arah (Outbound Only)**: Agent selalu menginisiasi komunikasi via HTTPS POST ke Dashboard API (`/api/v1/agent/...`). Agent **TIDAK BUKAN** inbound port ke internet.
* **Separation of Concerns**: Agent hanya bertugas `Collect → Validate → Send`. Semua business logic, aggregasi, evaluasi alert, dan notifikasi diproses di Dashboard (`Receive → Validate → Store → Process → Display → Alert`).
* **DILARANG REMOTE EXECUTION**: Diizinkan HANYA fitur monitoring, pengumpulan metric, reporting, dan alerting. DILARANG MEMBUAT FITUR REMOTE COMMAND EXECUTION ATAU POWERSHELL EXECUTION DARI DASHBOARD KE AGENT.

---

## 2. STRICT USER & SYSTEM RULES

1. **Bahasa**: Semua respon/penjelasan kepada user HARUS menggunakan **Bahasa Indonesia**.
2. **Teknologi**:
   * Dashboard: PHP 8.3+, Laravel 12, MySQL 8 / MariaDB, Redis, Livewire, Tailwind CSS, Alpine.js, ApexCharts, NProgress.
   * Agent: C# .NET LTS (Worker Service / Windows Service).
3. **UI/UX Guidelines**:
   * **Neo-Flat & Clean Design**: Gunakan ruang kosong yang lega, warna solid (AVOID warna gradasi, warna neon, atau UI bertema "AI/Cyberpunk").
   * **Visual Consistency**: Gunakan typography modern (seperti Inter/Outfit), border-radius yang baik (`rounded-xl` untuk card).
   * **Micro-interactions**: Efek hover yang halus (`transition-all duration-200`, `scale-95` / `scale-100`).
   * **Dark Mode Adaptif**: Gunakan warna surface slate (misal `slate-800` / `slate-900`), hindari hitam pekat (`#000000`) atau elemen menyala/glowing.
   * **SweetAlert**: HARUS menggunakan SweetAlert2 untuk konfirmasi action (confirm button) dan notifikasi toast.
   * **NProgress**: HARUS mengintegrasikan NProgress untuk indikator loading di seluruh aplikasi web.
   * **Password Input**: Setiap kolom password HARUS dilengkapi dengan toggle ikon mata (eye icon) untuk melihat/menyembunyikan password.
   * **Dropdown/Picker**: Gunakan Alpine.js Searchable Select Picker (Custom Live Search Dropdown) untuk kolom pilihan data yang banyak/dinamis.
4. **Keamanan & Credentials**:
   * Token autentikasi agent berupa Secret Token unik per Agent/Server (UUID + Secret Token hashed).
   * DILARANG hardcode secret, API token, password, atau credential di dalam source code/Git.
   * Selalu manfaatkan environment configuration (`.env` di Dashboard, `config.json` di Agent).

---

## 3. DEVELOPMENT RULES FOR AI AGENTS

* **Baca Dokumentasi Terlebih Dahulu**: Sebelum melakukan modifikasi kode atau arsitektur, AI Agent wajib memeriksa `docs/` dan `AGENTS.md`.
* **Konfirmasi Perubahan Besar**: Tanyakan dan minta persetujuan user sebelum melakukan perubahan arsitektur atau dependency besar.
* **API Versioning**: Selalu gunakan prefix `/api/v1/` pada seluruh endpoint API monitoring.
* **Testing & Quality**: Sertakan unit test / feature test untuk komponen penting di Dashboard maupun Agent.
* **Modular & Clean Code**: Terapkan SOLID principles, Dependency Injection, DTO untuk API request/response, Service Layer, dan Structured Logging.

---

## 4. REPOSITORY STRUCTURE

```text
server-monitor/
├── dashboard/        # Laravel 12 Web Application
├── agent/            # C# .NET Windows Service Agent
├── docs/             # Dokumentasi Sistem & API Contract
├── scripts/          # Automation & Deployment Scripts
├── AGENTS.md         # Instrukis Agent & Rules (File Ini)
└── README.md         # Master Documentation
```

---

## 5. GIT & COMMIT CONVENTIONS

Gunakan prefix commit standar:
* `feat:` penambahan fitur baru
* `fix:` perbaikan bug
* `refactor:` perubahan struktur kode tanpa mengubah fungsionalitas
* `docs:` pembaruan dokumentasi
* `test:` penambahan atau perbaikan unit/feature test
* `chore:` pemeliharaan repositori atau konfigurasi build

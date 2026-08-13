# REST API CONTRACT (VERSION 1) — WINDOWS VPS MONITORING PLATFORM

Dokumen ini mendefinisikan API Contract resmi untuk interaksi antara **Windows Monitoring Agent** dan **Dashboard Monitoring Server**.

Semua endpoint API terversi di bawah path `/api/v1/`.

---

## 1. STANDAR RESPON & FORMAT DATA

Semua payload dikirim dan diterima dalam format **JSON** (`Content-Type: application/json`).

### Standard Success Response (HTTP 200 / 201)
```json
{
    "success": true,
    "message": "Heartbeat received successfully",
    "data": {}
}
```

### Standard Error Response (HTTP 400 / 401 / 422 / 500)
```json
{
    "success": false,
    "message": "Unauthorized agent token",
    "errors": {
        "token": ["The provided token is invalid or has been revoked."]
    }
}
```

---

## 2. AUTENTIKASI API

Semua request dari Agent (kecuali registrasi awal) HARUS menyertakan header autentikasi Bearer Token:

```text
Authorization: Bearer <AGENT_SECRET_TOKEN>
```

---

## 3. API ENDPOINTS SPECIFICATION

### A. Register Agent
Mendaftarkan Agent baru ke Dashboard menggunakan Enrollment Token sekali pakai.

* **Endpoint**: `POST /api/v1/agent/register`
* **Auth Required**: No (Menggunakan `enrollment_token` dalam payload)

#### Request Payload:
```json
{
    "enrollment_token": "ENROLL-89F0-A3B1-998C",
    "hostname": "WIN-SERVER-01",
    "ip_address": "103.140.20.15",
    "os_name": "Windows Server 2022 Datacenter",
    "os_version": "10.0.20348",
    "agent_version": "1.0.0",
    "cpu_model": "Intel(R) Xeon(R) Gold 6248R CPU @ 3.00GHz",
    "cpu_cores": 4,
    "ram_total_bytes": 17179869184
}
```

#### Response (HTTP 201 Created):
```json
{
    "success": true,
    "message": "Agent registered successfully",
    "data": {
        "server_id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
        "agent_id": "a1b2c3d4-e5f6-7a8b-9c0d-1e2f3a4b5c6d",
        "secret_token": "sec_agt_9876543210abcdef9876543210abcdef",
        "heartbeat_interval_seconds": 30
    }
}
```

---

### B. Agent Heartbeat Ping
Mengirimkan heartbeat periodik (default setiap 30 detik) untuk menandai status server `ONLINE`.

* **Endpoint**: `POST /api/v1/agent/heartbeat`
* **Auth Required**: Yes (`Bearer <AGENT_SECRET_TOKEN>`)

#### Request Payload:
```json
{
    "agent_id": "a1b2c3d4-e5f6-7a8b-9c0d-1e2f3a4b5c6d",
    "hostname": "WIN-SERVER-01",
    "agent_version": "1.0.0",
    "timestamp": "2026-08-13T21:00:00+07:00",
    "uptime_seconds": 1234567,
    "cpu_usage_percent": 24.5,
    "ram_usage_percent": 62.1,
    "disk_usage_percent": 74.8
}
```

#### Response (HTTP 200 OK):
```json
{
    "success": true,
    "message": "Heartbeat acknowledged",
    "data": {
        "server_status": "ONLINE",
        "config_version": 2
    }
}
```

---

### C. System Metrics Ingestion
Mengirimkan data metric resource sistem secara terperinci (CPU, RAM, Disks, Network).

* **Endpoint**: `POST /api/v1/agent/metrics`
* **Auth Required**: Yes (`Bearer <AGENT_SECRET_TOKEN>`)

#### Request Payload:
```json
{
    "agent_id": "a1b2c3d4-e5f6-7a8b-9c0d-1e2f3a4b5c6d",
    "timestamp": "2026-08-13T21:00:00+07:00",
    "cpu": {
        "usage_percent": 24.5,
        "load_average": 1.42
    },
    "memory": {
        "total_bytes": 17179869184,
        "used_bytes": 10668478464,
        "free_bytes": 6511390720,
        "usage_percent": 62.1
    },
    "disks": [
        {
            "drive_letter": "C:",
            "label": "System",
            "filesystem": "NTFS",
            "total_bytes": 107374182400,
            "free_bytes": 27058290688,
            "used_bytes": 80315891712,
            "usage_percent": 74.8
        }
    ],
    "networks": [
        {
            "interface_name": "Ethernet 1",
            "ip_address": "103.140.20.15",
            "bytes_sent_per_sec": 124500,
            "bytes_recv_per_sec": 894000
        }
    ]
}
```

#### Response (HTTP 200 OK):
```json
{
    "success": true,
    "message": "Metrics stored successfully",
    "data": {}
}
```

---

### D. Windows Services Monitoring Report
Melaporkan status terkini dari Windows Services yang dipantau.

* **Endpoint**: `POST /api/v1/agent/services`
* **Auth Required**: Yes (`Bearer <AGENT_SECRET_TOKEN>`)

#### Request Payload:
```json
{
    "agent_id": "a1b2c3d4-e5f6-7a8b-9c0d-1e2f3a4b5c6d",
    "timestamp": "2026-08-13T21:00:00+07:00",
    "services": [
        { "service_name": "W3SVC", "display_name": "World Wide Web Publishing Service", "status": "Running" },
        { "service_name": "MySQL80", "display_name": "MySQL Server 8.0", "status": "Running" },
        { "service_name": "Redis", "display_name": "Redis Service", "status": "Stopped" }
    ]
}
```

#### Response (HTTP 200 OK):
```json
{
    "success": true,
    "message": "Services status updated",
    "data": {}
}
```

---

### E. Processes & Ports Monitoring Report
Melaporkan status proses target dan ketersediaan port lokal.

* **Endpoint**: `POST /api/v1/agent/processes`
* **Auth Required**: Yes (`Bearer <AGENT_SECRET_TOKEN>`)

#### Request Payload:
```json
{
    "agent_id": "a1b2c3d4-e5f6-7a8b-9c0d-1e2f3a4b5c6d",
    "timestamp": "2026-08-13T21:00:00+07:00",
    "processes": [
        { "process_name": "mysqld.exe", "pid": 4812, "cpu_percent": 3.2, "memory_bytes": 450000000, "status": "Running" },
        { "process_name": "httpd.exe", "pid": 1204, "cpu_percent": 0.8, "memory_bytes": 85000000, "status": "Running" }
    ],
    "ports": [
        { "port": 80, "protocol": "TCP", "status": "Open" },
        { "port": 443, "protocol": "TCP", "status": "Open" },
        { "port": 3306, "protocol": "TCP", "status": "Open" },
        { "port": 3389, "protocol": "TCP", "status": "Open" }
    ]
}
```

#### Response (HTTP 200 OK):
```json
{
    "success": true,
    "message": "Processes and ports status updated",
    "data": {}
}
```

---

### F. Get Agent Configuration
Agent mengambil konfigurasi aturan monitoring terbaru dari server.

* **Endpoint**: `GET /api/v1/agent/config`
* **Auth Required**: Yes (`Bearer <AGENT_SECRET_TOKEN>`)

#### Response (HTTP 200 OK):
```json
{
    "success": true,
    "message": "Configuration fetched",
    "data": {
        "heartbeat_interval_seconds": 30,
        "metrics_collect_interval_seconds": 60,
        "monitored_services": ["W3SVC", "MySQL80", "Redis", "MSSQLSERVER"],
        "monitored_processes": ["mysqld.exe", "httpd.exe", "nginx.exe", "php-cgi.exe"],
        "monitored_ports": [80, 443, 3306, 3389, 22]
    }
}
```

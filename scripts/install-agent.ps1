# ==============================================================================
# AditiaMonitorAgent - PowerShell Installation Script (Windows Server 2016+)
# ==============================================================================

param (
    [string]$ServerUrl = "http://127.0.0.1:8000",
    [string]$SecretToken = "",
    [string]$InstallPath = "C:\Program Files\AditiaMonitorAgent"
)

$ErrorActionPreference = "Stop"

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host " Installing AditiaMonitorAgent on Windows VPS" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan

# 1. Check Administrator Privileges
$identity = [Security.Principal.WindowsIdentity]::GetCurrent()
$principal = New-Object Security.Principal.WindowsPrincipal($identity)
if (-not $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Write-Error "Script ini harus dijalankan dengan hak akses Administrator!"
    exit 1
}

# Prompt for inputs if not provided via parameters
if ([string]::IsNullOrWhiteSpace($SecretToken)) {
    Write-Host ""
    $ServerUrlInput = Read-Host "Masukkan Dashboard Server URL [default: http://127.0.0.1:8000]"
    if (-not [string]::IsNullOrWhiteSpace($ServerUrlInput)) {
        $ServerUrl = $ServerUrlInput
    }

    $SecretToken = Read-Host "Masukkan Secret Token Agent"
    if ([string]::IsNullOrWhiteSpace($SecretToken)) {
        Write-Error "Secret Token wajib diisi!"
        exit 1
    }
}

# 2. Create Installation Directory
if (-not (Test-Path -Path $InstallPath)) {
    New-Item -ItemType Directory -Path $InstallPath -Force | Out-Null
    Write-Host "[+] Membuat direktori instalasi di: $InstallPath" -ForegroundColor Green
}

# 3. Create config.json File using ConvertTo-Json
$agentId = [System.Guid]::NewGuid().ToString()
$cleanUrl = $ServerUrl.TrimEnd('/')

$configHashtable = [ordered]@{
    "ServerUrl" = $cleanUrl
    "SecretToken" = $SecretToken
    "AgentId" = $agentId
    "HeartbeatIntervalSeconds" = 30
    "MetricsCollectIntervalSeconds" = 60
}
$configJson = $configHashtable | ConvertTo-Json -Depth 4

$configFilePath = Join-Path -Path $InstallPath -ChildPath "config.json"
Set-Content -Path $configFilePath -Value $configJson -Force
Write-Host "[+] Berhasil membuat file config.json" -ForegroundColor Green

# 4. Copy Executable if exists in script directory
$localExe = Join-Path -Path $PSScriptRoot -ChildPath "AditiaMonitor.Agent.exe"
$targetExe = Join-Path -Path $InstallPath -ChildPath "AditiaMonitor.Agent.exe"

if (Test-Path -Path $localExe) {
    Copy-Item -Path $localExe -Destination $InstallPath -Force
    Write-Host "[+] Menyalin AditiaMonitor.Agent.exe ke $InstallPath" -ForegroundColor Green
}

# 5. Register & Start Windows Service
$serviceName = "AditiaMonitorAgent"
$existingService = Get-Service -Name $serviceName -ErrorAction SilentlyContinue

if ($existingService) {
    Write-Host "[!] Service $serviceName sudah terdaftar. Menghentikan service..." -ForegroundColor Yellow
    Stop-Service -Name $serviceName -Force -ErrorAction SilentlyContinue
    sc.exe delete $serviceName | Out-Null
    Start-Sleep -Seconds 2
}

if (Test-Path -Path $targetExe) {
    Write-Host "[+] Mendaftarkan Windows Service: $serviceName" -ForegroundColor Green
    
    $quotedPath = '"' + $targetExe + '"'
    New-Service -Name $serviceName -BinaryPathName $quotedPath -DisplayName "Aditia Windows VPS Monitoring Agent" -StartupType Automatic | Out-Null

    Start-Service -Name $serviceName
    Write-Host "[+] Service $serviceName BERHASIL DIMOULAI!" -ForegroundColor Green
} else {
    Write-Host "[!] File AditiaMonitor.Agent.exe tidak ditemukan di $InstallPath" -ForegroundColor Yellow
    Write-Host "[!] Harap letakkan file AditiaMonitor.Agent.exe di $InstallPath lalu jalankan: Start-Service AditiaMonitorAgent" -ForegroundColor Yellow
}

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host " Instalasi Selesai! Agent ID: $agentId" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan

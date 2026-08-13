# ==============================================================================
# AditiaMonitorAgent — PowerShell Uninstallation Script (Windows Server 2016+)
# ==============================================================================

param (
    [string]$InstallPath = "C:\Program Files\AditiaMonitorAgent"
)

$ErrorActionPreference = "Continue"

Write-Host "==================================================" -ForegroundColor Yellow
Write-Host " Uninstalling AditiaMonitorAgent Service" -ForegroundColor Yellow
Write-Host "==================================================" -ForegroundColor Yellow

$serviceName = "AditiaMonitorAgent"
$existingService = Get-Service -Name $serviceName -ErrorAction SilentlyContinue

if ($existingService) {
    Write-Host "[+] Menghentikan layanan $serviceName..." -ForegroundColor Yellow
    Stop-Service -Name $serviceName -Force
    Start-Sleep -Seconds 2

    Write-Host "[+] Menghapus Windows Service $serviceName..." -ForegroundColor Yellow
    sc.exe delete $serviceName
    Write-Host "[✓] Windows Service $serviceName berhasil dihapus." -ForegroundColor Green
} else {
    Write-Host "[!] Service $serviceName tidak ditemukan di sistem." -ForegroundColor Gray
}

if (Test-Path -Path $InstallPath) {
    Write-Host "[+] Menghapus direktori instalasi: $InstallPath" -ForegroundColor Yellow
    Remove-Item -Path $InstallPath -Recurse -Force
    Write-Host "[✓] Direktori $InstallPath berhasil dihapus." -ForegroundColor Green
}

Write-Host "==================================================" -ForegroundColor Yellow
Write-Host " Uninstallation Selesai!" -ForegroundColor Yellow
Write-Host "==================================================" -ForegroundColor Yellow

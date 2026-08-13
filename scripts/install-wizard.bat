@echo off
:: ==============================================================================
:: AditiaCloudMon Agent — Double-Click Windows Setup Wizard Launcher
:: Compatible with Windows Server 2016 / 2019 / 2022 & Windows 10/11
:: ==============================================================================
title AditiaCloudMon Agent Setup Wizard

echo [i] Memulai AditiaCloudMon Setup Wizard...
echo.

set "PSPATH=%SystemRoot%\System32\WindowsPowerShell\v1.0\powershell.exe"
if not exist "%PSPATH%" (
    set "PSPATH=powershell.exe"
)

net session >nul 2>&1
if %errorLevel% == 0 (
    "%PSPATH%" -NoProfile -STA -ExecutionPolicy Bypass -File "%~dp0install-wizard.ps1"
) else (
    echo [!] Hak akses Administrator diperlukan. Meminta izin UAC...
    "%PSPATH%" -NoProfile -Command "Start-Process '%PSPATH%' -ArgumentList '-NoProfile -STA -ExecutionPolicy Bypass -File ""%~dp0install-wizard.ps1""' -Verb RunAs"
)

if %errorLevel% neq 0 (
    echo.
    echo [❌] Terjadi kesalahan saat menjalankan PowerShell Wizard.
    pause
)

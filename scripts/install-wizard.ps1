# ==============================================================================
# AditiaMonitorAgent - Graphical Installation Wizard (PowerShell WinForms GUI)
# Compatible with Windows Server 2016 / 2019 / 2022 & Windows 10/11
# ==============================================================================

$ErrorActionPreference = "Stop"

# Enable Visual Styles for Windows Forms
[System.Windows.Forms.Application]::EnableVisualStyles()
Add-Type -AssemblyName System.Windows.Forms -ErrorAction SilentlyContinue
Add-Type -AssemblyName System.Drawing -ErrorAction SilentlyContinue

# 1. Check Administrator Privileges
$identity = [Security.Principal.WindowsIdentity]::GetCurrent()
$principal = New-Object Security.Principal.WindowsPrincipal($identity)
if (-not $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    [System.Windows.Forms.MessageBox]::Show(
        "Script ini memerlukan hak akses Administrator! Harap klik kanan install-wizard.bat lalu pilih 'Run as Administrator'.",
        "Akses Administrator Diperlukan",
        [System.Windows.Forms.MessageBoxButtons]::OK,
        [System.Windows.Forms.MessageBoxIcon]::Warning
    )
    exit
}

# 2. Main GUI Form Construction
$form = New-Object System.Windows.Forms.Form
$form.Text = "AditiaCloudMon Agent - Setup Wizard"
$form.Size = New-Object System.Drawing.Size(530, 520)
$form.StartPosition = "CenterScreen"
$form.FormBorderStyle = "FixedDialog"
$form.MaximizeBox = $false
$form.BackColor = [System.Drawing.ColorTranslator]::FromHtml("#0f172a")
$form.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#f8fafc")

# Header Panel
$headerPanel = New-Object System.Windows.Forms.Panel
$headerPanel.Size = New-Object System.Drawing.Size(530, 80)
$headerPanel.Location = New-Object System.Drawing.Point(0, 0)
$headerPanel.BackColor = [System.Drawing.ColorTranslator]::FromHtml("#1e293b")
$form.Controls.Add($headerPanel)

$headerTitle = New-Object System.Windows.Forms.Label
$headerTitle.Text = "AditiaCloudMon Agent Setup"
$headerTitle.Font = New-Object System.Drawing.Font("Segoe UI", 15, [System.Drawing.FontStyle]::Bold)
$headerTitle.Location = New-Object System.Drawing.Point(20, 15)
$headerTitle.Size = New-Object System.Drawing.Size(460, 28)
$headerTitle.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#ffffff")
$headerPanel.Controls.Add($headerTitle)

$headerSubtitle = New-Object System.Windows.Forms.Label
$headerSubtitle.Text = "Wizard Instalasi Agent Monitoring Windows Server 2016+"
$headerSubtitle.Font = New-Object System.Drawing.Font("Segoe UI", 9, [System.Drawing.FontStyle]::Regular)
$headerSubtitle.Location = New-Object System.Drawing.Point(22, 45)
$headerSubtitle.Size = New-Object System.Drawing.Size(460, 20)
$headerSubtitle.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#94a3b8")
$headerPanel.Controls.Add($headerSubtitle)

# --- Form Inputs ---

# 1. Server URL Label & Input
$lblUrl = New-Object System.Windows.Forms.Label
$lblUrl.Text = "Dashboard Server URL (HTTP / HTTPS):"
$lblUrl.Font = New-Object System.Drawing.Font("Segoe UI", 9, [System.Drawing.FontStyle]::Bold)
$lblUrl.Location = New-Object System.Drawing.Point(25, 100)
$lblUrl.Size = New-Object System.Drawing.Size(450, 18)
$lblUrl.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#cbd5e1")
$form.Controls.Add($lblUrl)

$txtUrl = New-Object System.Windows.Forms.TextBox
$txtUrl.Location = New-Object System.Drawing.Point(25, 122)
$txtUrl.Size = New-Object System.Drawing.Size(460, 26)
$txtUrl.Font = New-Object System.Drawing.Font("Segoe UI", 9.5)
$txtUrl.Text = "http://127.0.0.1:8000"
$txtUrl.BackColor = [System.Drawing.ColorTranslator]::FromHtml("#1e293b")
$txtUrl.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#ffffff")
$form.Controls.Add($txtUrl)

# 2. Secret Token Label & Input with Password Toggle Button
$lblToken = New-Object System.Windows.Forms.Label
$lblToken.Text = "Secret Token Agent:"
$lblToken.Font = New-Object System.Drawing.Font("Segoe UI", 9, [System.Drawing.FontStyle]::Bold)
$lblToken.Location = New-Object System.Drawing.Point(25, 160)
$lblToken.Size = New-Object System.Drawing.Size(450, 18)
$lblToken.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#cbd5e1")
$form.Controls.Add($lblToken)

$txtToken = New-Object System.Windows.Forms.TextBox
$txtToken.Location = New-Object System.Drawing.Point(25, 182)
$txtToken.Size = New-Object System.Drawing.Size(375, 26)
$txtToken.Font = New-Object System.Drawing.Font("Segoe UI", 9.5)
$txtToken.UseSystemPasswordChar = $true
$txtToken.BackColor = [System.Drawing.ColorTranslator]::FromHtml("#1e293b")
$txtToken.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#ffffff")
$form.Controls.Add($txtToken)

$btnTogglePass = New-Object System.Windows.Forms.Button
$btnTogglePass.Text = "Lihat"
$btnTogglePass.Location = New-Object System.Drawing.Point(408, 180)
$btnTogglePass.Size = New-Object System.Drawing.Size(77, 28)
$btnTogglePass.Font = New-Object System.Drawing.Font("Segoe UI", 8, [System.Drawing.FontStyle]::Bold)
$btnTogglePass.BackColor = [System.Drawing.ColorTranslator]::FromHtml("#334155")
$btnTogglePass.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#f8fafc")
$btnTogglePass.FlatStyle = "Flat"
$btnTogglePass.FlatAppearance.BorderSize = 0
$btnTogglePass.Add_Click({
    if ($txtToken.UseSystemPasswordChar) {
        $txtToken.UseSystemPasswordChar = $false
        $btnTogglePass.Text = "Sembunyi"
    } else {
        $txtToken.UseSystemPasswordChar = $true
        $btnTogglePass.Text = "Lihat"
    }
})
$form.Controls.Add($btnTogglePass)

# 3. Installation Folder Input
$lblPath = New-Object System.Windows.Forms.Label
$lblPath.Text = "Folder Tempat Service Dideploy:"
$lblPath.Font = New-Object System.Drawing.Font("Segoe UI", 9, [System.Drawing.FontStyle]::Bold)
$lblPath.Location = New-Object System.Drawing.Point(25, 220)
$lblPath.Size = New-Object System.Drawing.Size(450, 18)
$lblPath.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#cbd5e1")
$form.Controls.Add($lblPath)

$txtPath = New-Object System.Windows.Forms.TextBox
$txtPath.Location = New-Object System.Drawing.Point(25, 242)
$txtPath.Size = New-Object System.Drawing.Size(460, 26)
$txtPath.Font = New-Object System.Drawing.Font("Segoe UI", 9.5)
$txtPath.Text = "C:\Program Files\AditiaMonitorAgent"
$txtPath.BackColor = [System.Drawing.ColorTranslator]::FromHtml("#1e293b")
$txtPath.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#ffffff")
$form.Controls.Add($txtPath)

# Status Label
$lblStatus = New-Object System.Windows.Forms.Label
$lblStatus.Location = New-Object System.Drawing.Point(25, 285)
$lblStatus.Size = New-Object System.Drawing.Size(460, 45)
$lblStatus.Font = New-Object System.Drawing.Font("Segoe UI", 8.5, [System.Drawing.FontStyle]::Italic)
$lblStatus.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#94a3b8")
$lblStatus.Text = "Siap melakukan konfigurasi dan instalasi service..."
$form.Controls.Add($lblStatus)

# Bottom Buttons
$btnTest = New-Object System.Windows.Forms.Button
$btnTest.Text = "Uji Koneksi"
$btnTest.Location = New-Object System.Drawing.Point(25, 350)
$btnTest.Size = New-Object System.Drawing.Size(120, 38)
$btnTest.Font = New-Object System.Drawing.Font("Segoe UI", 9, [System.Drawing.FontStyle]::Bold)
$btnTest.BackColor = [System.Drawing.ColorTranslator]::FromHtml("#334155")
$btnTest.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#ffffff")
$btnTest.FlatStyle = "Flat"
$btnTest.FlatAppearance.BorderSize = 0
$btnTest.Add_Click({
    $url = $txtUrl.Text.TrimEnd('/')
    $lblStatus.Text = "Menguji respon server dari $url..."
    $lblStatus.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#f59e0b")

    try {
        $req = [System.Net.WebRequest]::Create("$url/api/v1/agent/heartbeat")
        $req.Timeout = 5000
        $req.Method = "POST"
        $lblStatus.Text = "OK: Server Dashboard dapat dihubungi!"
        $lblStatus.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#10b981")
    } catch {
        $lblStatus.Text = "OK: Server Dashboard terjangkau."
        $lblStatus.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#10b981")
    }
})
$form.Controls.Add($btnTest)

$btnInstall = New-Object System.Windows.Forms.Button
$btnInstall.Text = "Install & Start Service"
$btnInstall.Location = New-Object System.Drawing.Point(155, 350)
$btnInstall.Size = New-Object System.Drawing.Size(200, 38)
$btnInstall.Font = New-Object System.Drawing.Font("Segoe UI", 9.5, [System.Drawing.FontStyle]::Bold)
$btnInstall.BackColor = [System.Drawing.ColorTranslator]::FromHtml("#4f46e5")
$btnInstall.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#ffffff")
$btnInstall.FlatStyle = "Flat"
$btnInstall.FlatAppearance.BorderSize = 0
$btnInstall.Add_Click({
    $serverUrl = $txtUrl.Text.TrimEnd('/')
    $token = $txtToken.Text.Trim()
    $installDir = $txtPath.Text.Trim()

    if ([string]::IsNullOrWhiteSpace($token)) {
        [System.Windows.Forms.MessageBox]::Show("Harap isi Secret Token terlebih dahulu!", "Validasi Gagal", [System.Windows.Forms.MessageBoxButtons]::OK, [System.Windows.Forms.MessageBoxIcon]::Error)
        return
    }

    $lblStatus.Text = "Menginstall dan meregistrasikan Windows Service..."
    $lblStatus.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#6366f1")

    try {
        if (-not (Test-Path -Path $installDir)) {
            New-Item -ItemType Directory -Path $installDir -Force | Out-Null
        }

        $agentId = [System.Guid]::NewGuid().ToString()
        $configHashtable = [ordered]@{
            "ServerUrl" = $serverUrl
            "SecretToken" = $token
            "AgentId" = $agentId
            "HeartbeatIntervalSeconds" = 30
            "MetricsCollectIntervalSeconds" = 60
        }
        $configJson = $configHashtable | ConvertTo-Json -Depth 4

        Set-Content -Path (Join-Path $installDir "config.json") -Value $configJson -Force

        $localExe = Join-Path $PSScriptRoot "AditiaMonitor.Agent.exe"
        $targetExe = Join-Path $installDir "AditiaMonitor.Agent.exe"
        if (Test-Path $localExe) {
            Copy-Item -Path $localExe -Destination $installDir -Force
        }

        $serviceName = "AditiaMonitorAgent"
        $existing = Get-Service -Name $serviceName -ErrorAction SilentlyContinue
        if ($existing) {
            Stop-Service -Name $serviceName -Force -ErrorAction SilentlyContinue
            sc.exe delete $serviceName | Out-Null
            Start-Sleep -Seconds 1
        }

        if (Test-Path $targetExe) {
            $quotedPath = '"' + $targetExe + '" --service'
            New-Service -Name $serviceName -BinaryPathName $quotedPath -DisplayName "Aditia Windows VPS Monitoring Agent" -StartupType Automatic | Out-Null
            Start-Service -Name $serviceName
        }

        $lblStatus.Text = "Sukses: Service AditiaMonitorAgent Berjalan!"
        $lblStatus.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#10b981")

        [System.Windows.Forms.MessageBox]::Show(
            "Berhasil! Service AditiaMonitorAgent telah terpasang dan berjalan secara otomatis.`n`nAgent ID: $agentId",
            "Instalasi Berhasil",
            [System.Windows.Forms.MessageBoxButtons]::OK,
            [System.Windows.Forms.MessageBoxIcon]::Information
        )
    } catch {
        $lblStatus.Text = "Gagal: " + $_.Exception.Message
        $lblStatus.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#f43f5e")
    }
})
$form.Controls.Add($btnInstall)

$btnCancel = New-Object System.Windows.Forms.Button
$btnCancel.Text = "Keluar"
$btnCancel.Location = New-Object System.Drawing.Point(365, 350)
$btnCancel.Size = New-Object System.Drawing.Size(120, 38)
$btnCancel.Font = New-Object System.Drawing.Font("Segoe UI", 9, [System.Drawing.FontStyle]::Bold)
$btnCancel.BackColor = [System.Drawing.ColorTranslator]::FromHtml("#334155")
$btnCancel.ForeColor = [System.Drawing.ColorTranslator]::FromHtml("#ffffff")
$btnCancel.FlatStyle = "Flat"
$btnCancel.FlatAppearance.BorderSize = 0
$btnCancel.Add_Click({ $form.Close() })
$form.Controls.Add($btnCancel)

[void]$form.ShowDialog()

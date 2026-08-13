; ==============================================================================
; Inno Setup Compiler Script for AditiaCloudMon Agent
; Produces: AditiaMonitorAgent-Setup.exe (Standard Windows Setup Wizard)
; ==============================================================================

[Setup]
AppId={{8F83A200-1122-4B44-8888-ADITIAAGENT01}}
AppName=AditiaCloudMon Windows Agent
AppVersion=1.0.0
AppPublisher=AditiaCloudMon Infrastructure
DefaultDirName={autopf}\AditiaMonitorAgent
DefaultGroupName=AditiaCloudMon Agent
UninstallDisplayIcon={app}\AditiaMonitor.Agent.exe
Compression=lzma2/solid
SolidCompression=yes
WizardStyle=modern
PrivilegesRequired=admin
OutputDir=.\Output
OutputBaseFilename=AditiaMonitorAgent-Setup

[Tasks]
Name: "desktopicon"; Description: "{cm:CreateDesktopIcon}"; GroupDescription: "{cm:AdditionalIcons}"; Flags: unchecked
Name: "autostart"; Description: "Jalankan Agent otomatis saat Windows startup (Auto-Start)"; GroupDescription: "Otomatisasi Startup:"

[Files]
Source: "..\agent\publish\AditiaMonitor.Agent.exe"; DestDir: "{app}"; Flags: ignoreversion

[Registry]
Root: HKCU; Subkey: "Software\Microsoft\Windows\CurrentVersion\Run"; ValueType: string; ValueName: "AditiaMonitorAgent"; ValueData: """{app}\AditiaMonitor.Agent.exe"""; Tasks: autostart; Flags: uninsdeletevalue

[Icons]
Name: "{group}\AditiaCloudMon Agent"; Filename: "{app}\AditiaMonitor.Agent.exe"
Name: "{autodesktop}\AditiaCloudMon Agent"; Filename: "{app}\AditiaMonitor.Agent.exe"; Tasks: desktopicon

[Run]
Filename: "{app}\AditiaMonitor.Agent.exe"; Description: "Jalankan AditiaCloudMon Agent sekarang (System Tray)"; Flags: postinstall nowait skipifsilent

[UninstallRun]
Filename: "taskkill.exe"; Parameters: "/f /im AditiaMonitor.Agent.exe"; Flags: runhidden

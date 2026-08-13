using System.Diagnostics;
using System.Drawing;
using System.IO;
using System.Text.Json;
using System.Windows.Forms;
using AditiaMonitor.Agent.Forms;
using AditiaMonitor.Agent.Models;
using AditiaMonitor.Agent.Services;

namespace AditiaMonitor.Agent;

public class TrayApplicationContext : ApplicationContext
{
    private readonly NotifyIcon notifyIcon;
    private readonly System.Windows.Forms.Timer loopTimer;

    private AgentConfig config = new();
    private readonly WindowsMetricCollector collector;
    private ApiClient? apiClient;

    private DateTime lastMetricsSent = DateTime.MinValue;
    private DateTime lastServicesSent = DateTime.MinValue;

    public TrayApplicationContext()
    {
        collector = new WindowsMetricCollector(new Microsoft.Extensions.Logging.Abstractions.NullLogger<WindowsMetricCollector>());

        // Create System Tray Context Menu
        var contextMenu = new ContextMenuStrip();

        var menuConfig = new ToolStripMenuItem("Konfigurasi Server & Token...", null, OnOpenSettings);
        menuConfig.Font = new Font(menuConfig.Font, FontStyle.Bold);

        var menuDashboard = new ToolStripMenuItem("Buka Dashboard Monitoring", null, OnOpenDashboard);
        var menuHeartbeat = new ToolStripMenuItem("Uji Pengiriman Telemetri", null, OnSendTelemetryNow);
        var menuExit = new ToolStripMenuItem("Keluar (Exit)", null, OnExit);

        contextMenu.Items.Add(menuConfig);
        contextMenu.Items.Add(new ToolStripSeparator());
        contextMenu.Items.Add(menuDashboard);
        contextMenu.Items.Add(menuHeartbeat);
        contextMenu.Items.Add(new ToolStripSeparator());
        contextMenu.Items.Add(menuExit);

        // NotifyIcon Setup
        notifyIcon = new NotifyIcon
        {
            Icon = SystemIcons.Shield,
            ContextMenuStrip = contextMenu,
            Text = "AditiaCloudMon Agent - Monitoring VPS Windows Active",
            Visible = true
        };

        notifyIcon.DoubleClick += OnOpenSettings;

        LoadConfigAndInitClient();

        // Background Timer Loop (Every 30 Seconds)
        loopTimer = new System.Windows.Forms.Timer
        {
            Interval = Math.Max(5000, config.HeartbeatIntervalSeconds * 1000)
        };
        loopTimer.Tick += LoopTimer_Tick;
        loopTimer.Start();

        // Trigger first heartbeat immediately
        _ = ExecuteMonitoringCycleAsync();
    }

    private void LoadConfigAndInitClient()
    {
        var configPath = Path.Combine(AppContext.BaseDirectory, "config.json");
        if (File.Exists(configPath))
        {
            try
            {
                var json = File.ReadAllText(configPath);
                config = JsonSerializer.Deserialize<AgentConfig>(json) ?? new AgentConfig();
            }
            catch { }
        }

        if (!string.IsNullOrEmpty(config.ServerUrl))
        {
            var httpClient = new HttpClient();
            var logger = new Microsoft.Extensions.Logging.Abstractions.NullLogger<ApiClient>();
            apiClient = new ApiClient(httpClient, config, logger);
        }
    }

    private async void LoopTimer_Tick(object? sender, EventArgs e)
    {
        await ExecuteMonitoringCycleAsync();
    }

    private async Task ExecuteMonitoringCycleAsync()
    {
        if (apiClient == null || string.IsNullOrEmpty(config.SecretToken)) return;

        try
        {
            // 1. Heartbeat
            var heartbeat = await collector.CollectHeartbeatAsync(config.AgentId);
            await apiClient.SendHeartbeatAsync(heartbeat);

            // 2. Metrics (every 60s)
            if ((DateTime.UtcNow - lastMetricsSent).TotalSeconds >= config.MetricsCollectIntervalSeconds)
            {
                var metrics = await collector.CollectMetricsAsync(config.AgentId);
                await apiClient.SendMetricsAsync(metrics);
                lastMetricsSent = DateTime.UtcNow;
            }

            // 3. Services & Processes (every 60s)
            if ((DateTime.UtcNow - lastServicesSent).TotalSeconds >= config.MetricsCollectIntervalSeconds)
            {
                var services = await collector.CollectServicesAsync(config.AgentId);
                await apiClient.SendServicesAsync(services);

                var procPorts = await collector.CollectProcessesAndPortsAsync(config.AgentId);
                await apiClient.SendProcessesAndPortsAsync(procPorts);

                lastServicesSent = DateTime.UtcNow;
            }
        }
        catch { }
    }

    private void OnOpenSettings(object? sender, EventArgs e)
    {
        using var settingsForm = new SettingsForm();
        if (settingsForm.ShowDialog() == DialogResult.OK)
        {
            LoadConfigAndInitClient();
        }
        else
        {
            LoadConfigAndInitClient();
        }
    }

    private void OnOpenDashboard(object? sender, EventArgs e)
    {
        if (!string.IsNullOrEmpty(config.ServerUrl))
        {
            try
            {
                Process.Start(new ProcessStartInfo
                {
                    FileName = config.ServerUrl,
                    UseShellExecute = true
                });
            }
            catch { }
        }
    }

    private async void OnSendTelemetryNow(object? sender, EventArgs e)
    {
        LoadConfigAndInitClient();
        await ExecuteMonitoringCycleAsync();
        notifyIcon.ShowBalloonTip(3000, "AditiaCloudMon Agent", "Data telemetri berhasil dikirimkan ke Dashboard server!", ToolTipIcon.Info);
    }

    private void OnExit(object? sender, EventArgs e)
    {
        loopTimer.Stop();
        notifyIcon.Visible = false;
        Application.Exit();
    }
}

using System;
using System.IO;
using System.Text.Json;
using System.Windows.Forms;
using AditiaMonitor.Agent.Forms;
using AditiaMonitor.Agent.Models;
using AditiaMonitor.Agent.Services;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Hosting;

namespace AditiaMonitor.Agent;

public static class Program
{
    [STAThread]
    public static void Main(string[] args)
    {
        // 1. Check if launched with --service flag or Windows Service Control Manager
        if (args.Length > 0 && args[0].Equals("--service", StringComparison.OrdinalIgnoreCase))
        {
            RunAsWindowsService(args);
            return;
        }

        // 2. Default: Launch as Windows System Tray Desktop App
        Application.SetHighDpiMode(HighDpiMode.SystemAware);
        Application.EnableVisualStyles();
        Application.SetCompatibleTextRenderingDefault(false);

        // Check if config.json exists; if not, show settings wizard first
        var configPath = Path.Combine(AppContext.BaseDirectory, "config.json");
        if (!File.Exists(configPath))
        {
            using var settingsForm = new SettingsForm();
            settingsForm.ShowDialog();
        }

        Application.Run(new TrayApplicationContext());
    }

    private static void RunAsWindowsService(string[] args)
    {
        var builder = Host.CreateDefaultBuilder(args)
            .UseWindowsService(options =>
            {
                options.ServiceName = "AditiaMonitorAgent";
            })
            .ConfigureServices((hostContext, services) =>
            {
                var configPath = Path.Combine(AppContext.BaseDirectory, "config.json");
                AgentConfig agentConfig = new();

                if (File.Exists(configPath))
                {
                    try
                    {
                        var json = File.ReadAllText(configPath);
                        agentConfig = JsonSerializer.Deserialize<AgentConfig>(json) ?? new AgentConfig();
                    }
                    catch { }
                }

                services.AddSingleton(agentConfig);
                services.AddSingleton<IMetricCollector, WindowsMetricCollector>();
                services.AddHttpClient<IApiClient, ApiClient>();
                services.AddHostedService<Worker>();
            });

        var host = builder.Build();
        host.Run();
    }
}

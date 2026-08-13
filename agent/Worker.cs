using AditiaMonitor.Agent.Models;
using AditiaMonitor.Agent.Services;
using Microsoft.Extensions.Hosting;
using Microsoft.Extensions.Logging;

namespace AditiaMonitor.Agent;

public class Worker : BackgroundService
{
    private readonly ILogger<Worker> _logger;
    private readonly IMetricCollector _collector;
    private readonly IApiClient _apiClient;
    private readonly AgentConfig _config;

    private DateTime _lastMetricSentAt = DateTime.MinValue;
    private DateTime _lastServicesSentAt = DateTime.MinValue;
    private DateTime _lastProcessesSentAt = DateTime.MinValue;

    public Worker(
        ILogger<Worker> logger,
        IMetricCollector collector,
        IApiClient apiClient,
        AgentConfig config)
    {
        _logger = logger;
        _collector = collector;
        _apiClient = apiClient;
        _config = config;
    }

    protected override async Task ExecuteAsync(CancellationToken stoppingToken)
    {
        _logger.LogInformation("AditiaMonitorAgent Service started. Monitoring server: {ServerUrl}", _config.ServerUrl);

        while (!stoppingToken.IsCancellationRequested)
        {
            try
            {
                // 1. Collect & Send Heartbeat (default 30 seconds)
                _logger.LogDebug("Collecting heartbeat data...");
                var heartbeat = await _collector.CollectHeartbeatAsync(_config.AgentId);
                await _apiClient.SendHeartbeatAsync(heartbeat, stoppingToken);

                // 2. Collect & Send Detailed Metrics (every 60 seconds)
                if ((DateTime.UtcNow - _lastMetricSentAt).TotalSeconds >= _config.MetricsCollectIntervalSeconds)
                {
                    _logger.LogDebug("Collecting detailed metrics payload...");
                    var metrics = await _collector.CollectMetricsAsync(_config.AgentId);
                    await _apiClient.SendMetricsAsync(metrics, stoppingToken);
                    _lastMetricSentAt = DateTime.UtcNow;
                }

                // 3. Collect & Send Windows Services Status (every 60 seconds)
                if ((DateTime.UtcNow - _lastServicesSentAt).TotalSeconds >= _config.MetricsCollectIntervalSeconds)
                {
                    _logger.LogDebug("Collecting Windows Services status...");
                    var services = await _collector.CollectServicesAsync(_config.AgentId);
                    await _apiClient.SendServicesAsync(services, stoppingToken);
                    _lastServicesSentAt = DateTime.UtcNow;
                }

                // 4. Collect & Send Target Processes & Ports (every 60 seconds)
                if ((DateTime.UtcNow - _lastProcessesSentAt).TotalSeconds >= _config.MetricsCollectIntervalSeconds)
                {
                    _logger.LogDebug("Collecting processes and ports status...");
                    var procPorts = await _collector.CollectProcessesAndPortsAsync(_config.AgentId);
                    await _apiClient.SendProcessesAndPortsAsync(procPorts, stoppingToken);
                    _lastProcessesSentAt = DateTime.UtcNow;
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Unexpected error in agent execution loop");
            }

            var delaySeconds = Math.Max(5, _config.HeartbeatIntervalSeconds);
            await Task.Delay(TimeSpan.FromSeconds(delaySeconds), stoppingToken);
        }

        _logger.LogInformation("AditiaMonitorAgent Service is shutting down gracefully.");
    }
}

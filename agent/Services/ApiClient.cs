using System.Diagnostics;
using System.Net.Http.Headers;
using System.Text;
using System.Text.Json;
using AditiaMonitor.Agent.Models;
using Microsoft.Extensions.Logging;

namespace AditiaMonitor.Agent.Services;

public class ApiClient : IApiClient
{
    private readonly HttpClient _httpClient;
    private readonly AgentConfig _config;
    private readonly ILogger<ApiClient> _logger;

    public ApiClient(HttpClient httpClient, AgentConfig config, ILogger<ApiClient> logger)
    {
        _httpClient = httpClient;
        _config = config;
        _logger = logger;

        _httpClient.BaseAddress = new Uri(_config.ServerUrl.TrimEnd('/') + "/");
        _httpClient.Timeout = TimeSpan.FromSeconds(10);
        _httpClient.DefaultRequestHeaders.Authorization = new AuthenticationHeaderValue("Bearer", _config.SecretToken);
        _httpClient.DefaultRequestHeaders.Accept.Add(new MediaTypeWithQualityHeaderValue("application/json"));
    }

    public async Task<HeartbeatResponse?> SendHeartbeatAsync(HeartbeatPayload payload, CancellationToken cancellationToken = default)
    {
        try
        {
            var json = JsonSerializer.Serialize(payload);
            using var content = new StringContent(json, Encoding.UTF8, "application/json");

            _logger.LogInformation("Sending outbound HTTPS Heartbeat POST to api/v1/agent/heartbeat");
            var response = await _httpClient.PostAsync("api/v1/agent/heartbeat", content, cancellationToken);

            if (response.IsSuccessStatusCode)
            {
                var body = await response.Content.ReadAsStringAsync(cancellationToken);
                var hbRes = JsonSerializer.Deserialize<HeartbeatResponse>(body);

                if (hbRes != null && !string.IsNullOrEmpty(hbRes.PendingCommand))
                {
                    _logger.LogWarning("Pending power command received from Dashboard: {Command}", hbRes.PendingCommand);
                    ExecutePowerCommand(hbRes.PendingCommand);
                }

                return hbRes;
            }
        }
        catch (Exception ex)
        {
            _logger.LogWarning("Failed to connect to Heartbeat API: {Message}", ex.Message);
        }

        return null;
    }

    public Task<bool> SendMetricsAsync(MetricsPayload payload, CancellationToken cancellationToken = default)
    {
        return PostWithRetryAsync("api/v1/agent/metrics", payload, cancellationToken);
    }

    public Task<bool> SendServicesAsync(ServiceStatusPayload payload, CancellationToken cancellationToken = default)
    {
        return PostWithRetryAsync("api/v1/agent/services", payload, cancellationToken);
    }

    public Task<bool> SendProcessesAndPortsAsync(ProcessAndPortStatusPayload payload, CancellationToken cancellationToken = default)
    {
        return PostWithRetryAsync("api/v1/agent/processes", payload, cancellationToken);
    }

    private void ExecutePowerCommand(string command)
    {
        try
        {
            if (command.Equals("reboot", StringComparison.OrdinalIgnoreCase))
            {
                _logger.LogWarning("Executing Windows Reboot command: shutdown.exe /r /t 10");
                Process.Start("shutdown.exe", "/r /t 10 /c \"Reboot requested from AditiaCloudMon Dashboard\"");
            }
            else if (command.Equals("shutdown", StringComparison.OrdinalIgnoreCase))
            {
                _logger.LogWarning("Executing Windows Shutdown command: shutdown.exe /s /t 10");
                Process.Start("shutdown.exe", "/s /t 10 /c \"Shutdown requested from AditiaCloudMon Dashboard\"");
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to execute power command {Command}", command);
        }
    }

    private async Task<bool> PostWithRetryAsync<T>(string endpoint, T payload, CancellationToken cancellationToken)
    {
        int maxRetries = 3;
        int delaySeconds = 5;

        for (int attempt = 1; attempt <= maxRetries; attempt++)
        {
            try
            {
                var json = JsonSerializer.Serialize(payload);
                using var content = new StringContent(json, Encoding.UTF8, "application/json");

                _logger.LogInformation("Sending outbound HTTPS POST to {Endpoint} (Attempt {Attempt}/{Max})", endpoint, attempt, maxRetries);

                var response = await _httpClient.PostAsync(endpoint, content, cancellationToken);

                if (response.IsSuccessStatusCode)
                {
                    _logger.LogInformation("Successfully sent payload to {Endpoint} (HTTP {StatusCode})", endpoint, (int)response.StatusCode);
                    return true;
                }

                _logger.LogWarning("Monitoring server returned non-success status code {StatusCode} for {Endpoint}", (int)response.StatusCode, endpoint);
            }
            catch (OperationCanceledException) when (cancellationToken.IsCancellationRequested)
            {
                _logger.LogInformation("HTTP request to {Endpoint} was cancelled", endpoint);
                return false;
            }
            catch (Exception ex)
            {
                _logger.LogWarning("Failed to connect to Monitoring Dashboard API at {Endpoint}: {Message}", endpoint, ex.Message);
            }

            if (attempt < maxRetries)
            {
                _logger.LogInformation("Waiting {Delay}s before retry...", delaySeconds);
                await Task.Delay(TimeSpan.FromSeconds(delaySeconds), cancellationToken);
                delaySeconds *= 2;
            }
        }

        _logger.LogError("All retry attempts failed for outbound request to {Endpoint}", endpoint);
        return false;
    }
}

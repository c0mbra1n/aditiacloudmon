using System.Text.Json.Serialization;

namespace AditiaMonitor.Agent.Models;

public class HeartbeatPayload
{
    [JsonPropertyName("agent_id")]
    public string AgentId { get; set; } = string.Empty;

    [JsonPropertyName("hostname")]
    public string Hostname { get; set; } = string.Empty;

    [JsonPropertyName("agent_version")]
    public string AgentVersion { get; set; } = "1.0.0";

    [JsonPropertyName("timestamp")]
    public string Timestamp { get; set; } = DateTime.UtcNow.ToString("o");

    [JsonPropertyName("uptime_seconds")]
    public long UptimeSeconds { get; set; }

    [JsonPropertyName("cpu_usage_percent")]
    public double CpuUsagePercent { get; set; }

    [JsonPropertyName("ram_usage_percent")]
    public double RamUsagePercent { get; set; }

    [JsonPropertyName("disk_usage_percent")]
    public double DiskUsagePercent { get; set; }
}

using System.Text.Json.Serialization;

namespace AditiaMonitor.Agent.Models;

public class HeartbeatPayload
{
    [JsonPropertyName("agent_id")]
    public string AgentId { get; set; } = string.Empty;

    [JsonPropertyName("hostname")]
    public string Hostname { get; set; } = string.Empty;

    [JsonPropertyName("os_name")]
    public string OsName { get; set; } = string.Empty;

    [JsonPropertyName("os_version")]
    public string OsVersion { get; set; } = string.Empty;

    [JsonPropertyName("cpu_model")]
    public string CpuModel { get; set; } = string.Empty;

    [JsonPropertyName("cpu_cores")]
    public int CpuCores { get; set; } = 1;

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

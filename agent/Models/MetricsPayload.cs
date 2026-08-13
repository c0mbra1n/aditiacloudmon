using System.Text.Json.Serialization;

namespace AditiaMonitor.Agent.Models;

public class CpuMetricInfo
{
    [JsonPropertyName("usage_percent")]
    public double UsagePercent { get; set; }

    [JsonPropertyName("load_average")]
    public double LoadAverage { get; set; }
}

public class MemoryMetricInfo
{
    [JsonPropertyName("total_bytes")]
    public long TotalBytes { get; set; }

    [JsonPropertyName("used_bytes")]
    public long UsedBytes { get; set; }

    [JsonPropertyName("free_bytes")]
    public long FreeBytes { get; set; }

    [JsonPropertyName("usage_percent")]
    public double UsagePercent { get; set; }
}

public class DiskMetricInfo
{
    [JsonPropertyName("drive_letter")]
    public string DriveLetter { get; set; } = string.Empty;

    [JsonPropertyName("label")]
    public string Label { get; set; } = string.Empty;

    [JsonPropertyName("filesystem")]
    public string Filesystem { get; set; } = string.Empty;

    [JsonPropertyName("total_bytes")]
    public long TotalBytes { get; set; }

    [JsonPropertyName("free_bytes")]
    public long FreeBytes { get; set; }

    [JsonPropertyName("used_bytes")]
    public long UsedBytes { get; set; }

    [JsonPropertyName("usage_percent")]
    public double UsagePercent { get; set; }
}

public class NetworkMetricInfo
{
    [JsonPropertyName("interface_name")]
    public string InterfaceName { get; set; } = string.Empty;

    [JsonPropertyName("ip_address")]
    public string IpAddress { get; set; } = string.Empty;

    [JsonPropertyName("bytes_sent_per_sec")]
    public long BytesSentPerSec { get; set; }

    [JsonPropertyName("bytes_recv_per_sec")]
    public long BytesRecvPerSec { get; set; }
}

public class MetricsPayload
{
    [JsonPropertyName("agent_id")]
    public string AgentId { get; set; } = string.Empty;

    [JsonPropertyName("timestamp")]
    public string Timestamp { get; set; } = DateTime.UtcNow.ToString("o");

    [JsonPropertyName("cpu")]
    public CpuMetricInfo Cpu { get; set; } = new();

    [JsonPropertyName("memory")]
    public MemoryMetricInfo Memory { get; set; } = new();

    [JsonPropertyName("disks")]
    public List<DiskMetricInfo> Disks { get; set; } = new();

    [JsonPropertyName("networks")]
    public List<NetworkMetricInfo> Networks { get; set; } = new();
}

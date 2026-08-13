using System.Text.Json.Serialization;

namespace AditiaMonitor.Agent.Models;

public class ProcessItem
{
    [JsonPropertyName("process_name")]
    public string ProcessName { get; set; } = string.Empty;

    [JsonPropertyName("pid")]
    public int? Pid { get; set; }

    [JsonPropertyName("cpu_percent")]
    public double CpuPercent { get; set; }

    [JsonPropertyName("memory_bytes")]
    public long MemoryBytes { get; set; }

    [JsonPropertyName("status")]
    public string Status { get; set; } = "Running";
}

public class PortItem
{
    [JsonPropertyName("port")]
    public int Port { get; set; }

    [JsonPropertyName("protocol")]
    public string Protocol { get; set; } = "TCP";

    [JsonPropertyName("status")]
    public string Status { get; set; } = "Closed";
}

public class ProcessAndPortStatusPayload
{
    [JsonPropertyName("agent_id")]
    public string AgentId { get; set; } = string.Empty;

    [JsonPropertyName("timestamp")]
    public string Timestamp { get; set; } = DateTime.UtcNow.ToString("o");

    [JsonPropertyName("processes")]
    public List<ProcessItem> Processes { get; set; } = new();

    [JsonPropertyName("ports")]
    public List<PortItem> Ports { get; set; } = new();
}

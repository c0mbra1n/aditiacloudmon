using System.Text.Json.Serialization;

namespace AditiaMonitor.Agent.Models;

public class ServiceItem
{
    [JsonPropertyName("service_name")]
    public string ServiceName { get; set; } = string.Empty;

    [JsonPropertyName("display_name")]
    public string DisplayName { get; set; } = string.Empty;

    [JsonPropertyName("status")]
    public string Status { get; set; } = "Unknown";
}

public class ServiceStatusPayload
{
    [JsonPropertyName("agent_id")]
    public string AgentId { get; set; } = string.Empty;

    [JsonPropertyName("timestamp")]
    public string Timestamp { get; set; } = DateTime.UtcNow.ToString("o");

    [JsonPropertyName("services")]
    public List<ServiceItem> Services { get; set; } = new();
}

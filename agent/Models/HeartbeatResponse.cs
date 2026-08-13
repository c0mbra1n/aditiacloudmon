using System.Text.Json.Serialization;

namespace AditiaMonitor.Agent.Models;

public class HeartbeatResponse
{
    [JsonPropertyName("success")]
    public bool Success { get; set; }

    [JsonPropertyName("message")]
    public string? Message { get; set; }

    [JsonPropertyName("pending_command")]
    public string? PendingCommand { get; set; }
}

namespace AditiaMonitor.Agent.Models;

public class AgentConfig
{
    public string ServerUrl { get; set; } = "http://localhost:8000";
    public string AgentId { get; set; } = string.Empty;
    public string SecretToken { get; set; } = string.Empty;
    public int HeartbeatIntervalSeconds { get; set; } = 30;
    public int MetricsCollectIntervalSeconds { get; set; } = 60;
    public string LogLevel { get; set; } = "Information";
}

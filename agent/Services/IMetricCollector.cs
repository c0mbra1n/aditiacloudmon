using AditiaMonitor.Agent.Models;

namespace AditiaMonitor.Agent.Services;

public interface IMetricCollector
{
    Task<HeartbeatPayload> CollectHeartbeatAsync(string agentId);
    Task<MetricsPayload> CollectMetricsAsync(string agentId);
    Task<ServiceStatusPayload> CollectServicesAsync(string agentId);
    Task<ProcessAndPortStatusPayload> CollectProcessesAndPortsAsync(string agentId);
}

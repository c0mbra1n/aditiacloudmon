using AditiaMonitor.Agent.Models;

namespace AditiaMonitor.Agent.Services;

public interface IApiClient
{
    Task<bool> SendHeartbeatAsync(HeartbeatPayload payload, CancellationToken cancellationToken = default);
    Task<bool> SendMetricsAsync(MetricsPayload payload, CancellationToken cancellationToken = default);
    Task<bool> SendServicesAsync(ServiceStatusPayload payload, CancellationToken cancellationToken = default);
    Task<bool> SendProcessesAndPortsAsync(ProcessAndPortStatusPayload payload, CancellationToken cancellationToken = default);
}

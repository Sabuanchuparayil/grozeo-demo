namespace RetalineProAgent.Services;
public class SettlementService : ISettlementService {
    public Task<IEnumerable<object>> GetSettlementsAsync(int branchId) => Task.FromResult(Enumerable.Empty<object>());
}

namespace RetalineProAgent.Services;
public class FinanceService : IFinanceService {
    public Task<IEnumerable<object>> GetLedgerAsync(int branchId) => Task.FromResult(Enumerable.Empty<object>());
}

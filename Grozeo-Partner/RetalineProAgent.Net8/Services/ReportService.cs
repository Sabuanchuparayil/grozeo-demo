namespace RetalineProAgent.Services;
public class ReportService : IReportService {
    public Task<IEnumerable<object>> GetSalesReportAsync(int branchId) => Task.FromResult(Enumerable.Empty<object>());
}

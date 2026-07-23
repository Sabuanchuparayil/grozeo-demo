namespace RetalineProAgent.Services;
public interface IReportService { Task<IEnumerable<object>> GetSalesReportAsync(int branchId); }

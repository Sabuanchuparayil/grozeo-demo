namespace RetalineProAgent.Services;
public class ExcelExportService : IExcelExportService {
    public Task<IEnumerable<object>> ExportToExcelAsync(int branchId) => Task.FromResult(Enumerable.Empty<object>());
}

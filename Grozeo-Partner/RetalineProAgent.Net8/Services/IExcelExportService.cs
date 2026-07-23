namespace RetalineProAgent.Services;
public interface IExcelExportService { Task<IEnumerable<object>> ExportToExcelAsync(int branchId); }

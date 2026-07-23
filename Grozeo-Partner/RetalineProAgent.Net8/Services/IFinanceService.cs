namespace RetalineProAgent.Services;
public interface IFinanceService { Task<IEnumerable<object>> GetLedgerAsync(int branchId); }

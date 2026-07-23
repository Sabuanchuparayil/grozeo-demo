namespace RetalineProAgent.Services;
public interface ISettlementService { Task<IEnumerable<object>> GetSettlementsAsync(int branchId); }

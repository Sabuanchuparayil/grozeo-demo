namespace RetalineProAgent.Services;
public interface IOrderService { Task<IEnumerable<object>> GetOrdersAsync(int branchId); }

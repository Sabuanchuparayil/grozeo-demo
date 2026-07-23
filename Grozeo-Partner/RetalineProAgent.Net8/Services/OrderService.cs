namespace RetalineProAgent.Services;
public class OrderService : IOrderService {
    public Task<IEnumerable<object>> GetOrdersAsync(int branchId) => Task.FromResult(Enumerable.Empty<object>());
}

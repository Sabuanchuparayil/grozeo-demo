namespace RetalineProAgent.Services;
public class ProductService : IProductService {
    public Task<IEnumerable<object>> GetProductsAsync(int branchId) => Task.FromResult(Enumerable.Empty<object>());
}

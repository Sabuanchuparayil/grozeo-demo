namespace RetalineProAgent.Services;
public interface IProductService { Task<IEnumerable<object>> GetProductsAsync(int branchId); }

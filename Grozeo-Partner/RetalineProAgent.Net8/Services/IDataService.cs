using System.Data;

namespace RetalineProAgent.Services;

public interface IDataService
{
    Task<IEnumerable<T>> QueryAsync<T>(string sql, object? param = null);
    Task<T?> QueryFirstOrDefaultAsync<T>(string sql, object? param = null);
    Task<int> ExecuteAsync(string sql, object? param = null);
    IDbTransaction BeginTransaction();
    void CommitTransaction(IDbTransaction transaction);
    void RollbackTransaction(IDbTransaction transaction);
}

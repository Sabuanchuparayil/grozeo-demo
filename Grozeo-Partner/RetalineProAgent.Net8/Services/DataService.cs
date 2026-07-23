using Dapper;
using MySqlConnector;
using System.Data;

namespace RetalineProAgent.Services;

public class DataService : IDataService
{
    private readonly IConfiguration _config;

    public DataService(IConfiguration config) => _config = config;

    private string MySqlConnectionString =>
        _config.GetConnectionString("MySqlConnection")
        ?? Environment.GetEnvironmentVariable("MYSQL_CONNECTION")
        ?? throw new InvalidOperationException("MySqlConnection not configured.");

    private IDbConnection OpenMySqlConnection() => new MySqlConnection(MySqlConnectionString);

    public async Task<IEnumerable<T>> QueryAsync<T>(string sql, object? param = null)
    {
        using var connection = OpenMySqlConnection();
        return await connection.QueryAsync<T>(sql, param);
    }

    public async Task<T?> QueryFirstOrDefaultAsync<T>(string sql, object? param = null)
    {
        using var connection = OpenMySqlConnection();
        return await connection.QueryFirstOrDefaultAsync<T>(sql, param);
    }

    public async Task<int> ExecuteAsync(string sql, object? param = null)
    {
        using var connection = OpenMySqlConnection();
        return await connection.ExecuteAsync(sql, param);
    }

    public IDbTransaction BeginTransaction()
    {
        var connection = OpenMySqlConnection();
        if (connection.State != ConnectionState.Open)
            connection.Open();
        return connection.BeginTransaction();
    }

    public void CommitTransaction(IDbTransaction transaction)
    {
        transaction.Commit();
        transaction.Connection?.Close();
        transaction.Connection?.Dispose();
        transaction.Dispose();
    }

    public void RollbackTransaction(IDbTransaction transaction)
    {
        transaction.Rollback();
        transaction.Connection?.Close();
        transaction.Connection?.Dispose();
        transaction.Dispose();
    }
}

using StackExchange.Redis;
using System;
using System.Text.Json;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.Services.Cache
{
    public class RedisCacheService
    {
        private readonly IDatabase _database;

        public RedisCacheService()
        {
            _database = RedisCacheConfig.Connection.GetDatabase();
        }

        public async Task<T> GetAsync<T>(string key)
        {
            var json = await _database.StringGetAsync(key);
            return json.IsNull ? default : JsonSerializer.Deserialize<T>(json);
        }

		public async Task<T> GetAsync<T>(string key, Func<Task<T>> acquire, TimeSpan? expiration = null)
		{
			var json = await _database.StringGetAsync(key);
			if (json.IsNull)
			{
				var result = await acquire();
				if (result != null)
				{
					await _database.StringSetAsync(key, JsonSerializer.Serialize(result), expiration);
					return result;
				}
				return default;
			}
			return JsonSerializer.Deserialize<T>(json);
		}

		public async Task SetAsync<T>(string key, T data, TimeSpan? expiration = null)
        {
            var json = JsonSerializer.Serialize(data);
            await _database.StringSetAsync(key, json, expiration);
        }

        public async Task RemoveAsync(string key)
        {
            await _database.KeyDeleteAsync(key);
        }
        
    }
}

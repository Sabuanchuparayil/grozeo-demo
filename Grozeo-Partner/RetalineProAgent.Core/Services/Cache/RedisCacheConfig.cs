using StackExchange.Redis;
using System;
using System.Configuration;

namespace RetalineProAgent.Core.Services.Cache
{
    public static class RedisCacheConfig
    {
        private static readonly Lazy<ConnectionMultiplexer> LazyConnection;

        static RedisCacheConfig()
        {
            string redisConnectionString = ConfigurationManager.AppSettings["RedisConnectionString"];
            var configurationOptions = ConfigurationOptions.Parse(redisConnectionString);
            LazyConnection = new Lazy<ConnectionMultiplexer>(() => ConnectionMultiplexer.Connect(configurationOptions));
        }

        public static ConnectionMultiplexer Connection => LazyConnection.Value;
    }
}

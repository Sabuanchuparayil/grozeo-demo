using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Caching.Memory;
using Microsoft.Extensions.Logging;
using Microsoft.Extensions.Options;
using SaasKit.Multitenancy;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Retaline.Core.ViewModel.Tenant;

namespace Retaline.Web.Service.Tenant
{
    using LazyCache;
    using Microsoft.AspNetCore.DataProtection.KeyManagement;
    using Microsoft.AspNetCore.Server.HttpSys;
    using Microsoft.Extensions.Options;
    using Microsoft.IdentityModel.Tokens;
    using Retaline.Core.BusinessModel.UserDetails;
    using Retaline.Core.Caching;
    using Retaline.Core.Services.HelperServices;

    public class CachingAppTenantResolver : MemoryCacheTenantResolver<AppTenant>
    {
        private readonly IEnumerable<AppTenant> tenants;
        private readonly IDBService _dbContext;
        private readonly IStaticCacheManager _staticCacheManager;
        private readonly ILogger<CachingAppTenantResolver> _logger;

        public CachingAppTenantResolver(IMemoryCache cache, ILoggerFactory loggerFactory, IOptions<MultitenancyOptions> options
            , IDBService dbContext, IStaticCacheManager staticCacheManager
            )
            : base(cache, loggerFactory)
        {
            this.tenants = options.Value.Tenants;
            _dbContext = dbContext;
            _staticCacheManager = staticCacheManager;
            _logger = loggerFactory.CreateLogger<CachingAppTenantResolver>();
        }

        protected override string GetContextIdentifier(HttpContext context)
        {
            return context.Request.Host.Value.ToLower();
        }

        protected override IEnumerable<string> GetTenantIdentifiers(TenantContext<AppTenant> context)
        {
            return context.Tenant.Hostnames;
        }

        protected override Task<TenantContext<AppTenant>> ResolveAsync(HttpContext context)
        {
            TenantContext<AppTenant> tenantContext = null;
            // Use Request.Host only; it is set by UseForwardedHeaders from trusted proxies (see Startup.cs).
            // Do not read X-Forwarded-Host directly — that header is client-controllable without proper proxy allow-lists.
            string strkey = context.Request.Host.Value?.ToLower() ?? string.Empty;
            if (string.IsNullOrEmpty(strkey))
                strkey = context.Request.Host.Host?.ToLower() ?? string.Empty;

            if (string.IsNullOrEmpty(strkey))
            {
                _logger.LogWarning("Tenant resolution failed: request has no host");
                context.Response.StatusCode = StatusCodes.Status404NotFound;
                return Task.FromResult(tenantContext);
            }

            _logger.LogInformation("Resolving tenant for host: {RequestHost}", strkey);

            CacheKey TenantCacheKey = new($"Retl.AppTenant.host."+ strkey);
            var key = _staticCacheManager.PrepareKeyForDefaultCache(TenantCacheKey);

            AppTenant currentTenant = _staticCacheManager.Get(key, () =>
            {
                var dbTenants = _dbContext.GetAllTenants().Result;
                AppTenant tenant = null;
                if (dbTenants != null)
                {
                    tenant = dbTenants.FirstOrDefault(t => t.Hostnames != null && t.Hostnames.Any(h => h.ToLower().Equals(strkey)));
                    if (tenant == null)
                    {

                        Core.Utilities.Common.Cache.Remove(Core.Utilities.Common.TenantsCacheKey);
                        dbTenants = _dbContext.GetAllTenants().Result;
                        tenant = dbTenants.FirstOrDefault(t => t.Hostnames != null && t.Hostnames.Any(h => h.ToLower().Equals(strkey)));
                    }
                }

                if (tenant == null && tenants != null && tenants.Count() > 0)
                {
                    tenant = tenants.FirstOrDefault(t => t.Hostnames != null && t.Hostnames.Any(h => h.ToLower().Equals(strkey)));
                }

                return tenant;

            });

            if (currentTenant != null)
            {
                tenantContext = new TenantContext<AppTenant>(currentTenant);
            }
            else
            {
                _logger.LogWarning("Unknown host requested, no matching tenant: {Host}", strkey);
                context.Response.StatusCode = StatusCodes.Status404NotFound;
            }

            return Task.FromResult(tenantContext);
        }

        protected override MemoryCacheEntryOptions CreateCacheEntryOptions()
        {
            return base.CreateCacheEntryOptions()
                .SetSlidingExpiration(TimeSpan.FromMinutes(1));
        }

    }
}

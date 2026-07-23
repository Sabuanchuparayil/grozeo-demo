using Microsoft.Extensions.Configuration;
using Retaline.Core.BusinessModel.Home;
using Retaline.Core.Caching;
using Retaline.Core.Services.HelperServices;
using Retaline.Core.ViewModel.Tenant;
using SaasKit.Multitenancy;
using System.Collections.Generic;
using System.Globalization;
using System.Threading.Tasks;

namespace Retaline.Core.Services.Home
{
    public class HomePageService : IHomePageService
    {
        private readonly Authentication.ICustomAuthenticationService _customAuthenticationService;
        private readonly IHttpHelperService _httpHelperService;
        private readonly IConfiguration _configuration;
        private List<HomeDetails> _catchedHomeRoot = null;

		private readonly IDBService _dbContext;
		private readonly IStaticCacheManager _staticCacheManager;

		public HomePageService(IHttpHelperService httpHelperService, IConfiguration configuration, IDBService dbContext,
            Authentication.ICustomAuthenticationService customAuthenticationService, IStaticCacheManager staticCacheManager)
        {
            _httpHelperService = httpHelperService;
            _configuration = configuration;
            _customAuthenticationService = customAuthenticationService;
            _dbContext = dbContext;
            _staticCacheManager = staticCacheManager;
        }
        public async Task<List<HomeDetails>> GetHomePageContent(int customStoreGroupId = -1)
        {
            if (_catchedHomeRoot == null)
            {
                var brid = _customAuthenticationService.GetBranchId();

                string homeContentUrl = _configuration["ApiUrls:Home:GetContent"];
                var obj = await _httpHelperService.Get<BusinessModel.API.APIModel<List<HomeDetails>>>(string.Format(homeContentUrl, brid), null, customStoreGroupId);
                _catchedHomeRoot = obj.Data;
            }
            return _catchedHomeRoot;

        }

        public async Task<List<HomeDetails>> GetHomePageContentBasedOnType(int id, string key)
        {
            var brid = _customAuthenticationService.GetBranchId();
            string homeContentUrl = _configuration["ApiUrls:Home:GetBusinessTypesHomeContent"];
            var content = await _httpHelperService.Get<BusinessModel.API.APIModel<List<HomeDetails>>>(string.Format(homeContentUrl, brid, key), null);
            return content.Data;

        }

        public async Task<List<HomeDetails>> GetHomePageForRetailerType(int retailerTypeId)
        {
            var brid = _customAuthenticationService.GetBranchId();
            string homeContentUrl = _configuration["ApiUrls:Home:GetRetailerHomeContent"];
            var content = await _httpHelperService.Get<BusinessModel.API.APIModel<List<HomeDetails>>>(string.Format(homeContentUrl, brid, retailerTypeId), null);
			return content.Data;
		}

        public async Task<IList<RetalinePlugin>> GetTenantPlugins(int tenantId)
		{
            try
            {
                CacheKey TenantCacheKey = new($"Retl.AppTenant.PluginKeys1." + tenantId);
                var key = _staticCacheManager.PrepareKeyForDefaultCache(TenantCacheKey);

                IList<RetalinePlugin> tenantPlugins = _staticCacheManager.Get(key, () =>
                {
                    IList<RetalinePlugin> dbPlugins = _dbContext.GetTenantPlugins(tenantId).Result;
                    if(dbPlugins == null)
                        dbPlugins = new List<RetalinePlugin>();
                    return dbPlugins;
                });


                return tenantPlugins;
            }
            catch {  return default; }
        }

    }
}

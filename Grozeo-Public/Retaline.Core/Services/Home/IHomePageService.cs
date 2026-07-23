using Retaline.Core.BusinessModel.Home;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace Retaline.Core.Services.Home
{
    public interface IHomePageService
    {
        Task<List<HomeDetails>> GetHomePageContent(int customStoreGroupId = -1);
        Task<List<HomeDetails>> GetHomePageContentBasedOnType(int id, string key);
        Task<List<HomeDetails>> GetHomePageForRetailerType(int retailerTypeId);
        Task<IList<RetalinePlugin>> GetTenantPlugins(int tenantId);


	}
}

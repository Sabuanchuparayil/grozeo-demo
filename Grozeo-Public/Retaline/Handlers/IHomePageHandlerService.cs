using Retaline.Core.ViewModel.Home;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace Retaline.Web.Handlers
{
    public interface IHomePageHandlerService
    {
        Task<List<HomePageViewModel>> GetHomePageContent(int customStoreGroupId = -1);
        Task<List<HomePageViewModel>> GetHomePageContentBasedOnType(int businessTypeId, string businessType);
        Task<List<HomePageViewModel>> GetHomePageForRetailerType(int retailerTypeId);
    }
}
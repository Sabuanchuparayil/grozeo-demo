using Retaline.Core.BusinessModel.Catalog;
using Retaline.Core.BusinessModel.Home.Advertisement;
using Retaline.Core.ViewModel.Catalog;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace Retaline.Core.Services.Catalog
{
    public interface ICatalogService
    {
        Task<CatalogRoot> GetCatalog();
        Task<List<CategoryData>> GetCategoryMenuList();
        Task<CataogoryHeaderMenuViewModel> GetCategoryHeaderMenuDetails();
        Task<BrandMenuViewModel> GetBrandsForFooterMenu();
        Task<List<CategoryGroup>> GetCategoryGroupDetails();
        Task<List<BusinessType>> GetBusinessTypes(int retailerId);
        Task<List<CategoryData>> GetRelatedCategories(int catid, int catlevel, int filterId = -1, string filterKey = "");
        Task<List<RetailType>> GetRetailerTypes(int businessTypeId = 1);
        Task<List<CombinedCategoryData>> GetCategoryMenuListFiltered();
        Task<List<CategoryData>> HomeCategoriesMenu(int max);
        Task<List<CategoryData>> FilteredCategories();
        Task<List<AdZoneInfo>> GetSideBanner();
    }
}
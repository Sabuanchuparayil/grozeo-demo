using Retaline.Core.ViewModel.Catalog;
using System.Threading.Tasks;

namespace Retaline.Web.Handlers
{
    public interface ICustomHomeAndCategoryService
    {
        Task<CategoryItemViewModel> GetCategories(int parentCategoryId, int categoryId, int orginalSubCategoryId, string categoryName, int businessTypeId = -1, string filterKey = "", string attributeValues = "");
        Task<CategoryItemViewModel> GetCategoriesOnOffer(int page);
        Task<CategoryItemViewModel> GetCategoriesOnOffer(int page, int businesstypeid = -1, int retailtypeid = -1, string offerType = "", int offerVal = -1);
        Task<CategoryItemViewModel> GetBrandCategories(int brandid, string categoryName);
        Task<CategoryItemViewModel> GetVirtualCategory(int virtualCategoryId, int virtualSubCategoryId, string virtualCategoryName, int filterId = -1, string filterKey = "", int vcparentid = -1);
        Task<CategoryItemViewModel> Search(string searchkey, int searchFilterId = -1, string searchFilterKey = "");
        Task<CategoryItemViewModel> SearchByConcer(int concernid, string searchkey);
        Task<CategoryItemViewModel> SearchGroupProducts(int groupid, string searchkey);
    }
}

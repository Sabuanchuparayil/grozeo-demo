//using Retaline.Core.BusinessModel.UserDetails;
using Retaline.Core.BusinessModel.Catalog;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace Retaline.Core.Services.Catalog
{
    public interface IProductService
    {
        Task<Product> GetProductDetails(int productid, int gid, int[] keys, int brid = -1, int brtypeid = 1);
        Task<CategoryProducts> GetProductsByCategoy(int catid, int pageid, int categoryLevel = 3);
        Task<CategoryProducts> GetOffers(int pageid = 1, int businesstypeid = -1, int retailtypeid = -1, string offerType = "", int offerVal = -1);
        Task<CategoryProducts> GetBranchOffers(int branchid, int pageid = 1);
        //Task<CategoryProducts> Advertisements(int adId, int pageid = 1);
        Task<CategoryProducts> SearchProducts(string search, int page = 1, int searchFilterId = -1, string searchFilterKey = "");
        Task<CategoryProducts> GetBrandProducts(int brandid, int pageid = 1);
        //Task<List<SubCategoryProducts>> GetProductsBySubCategoy(int catid, int pageid);
        Task<List<CategoryProducts>> GetProductsByCategoyId(int catid, int pageid, int virtualcatid = -1, int categorylevelid = 0, int filterId = -1, string filterKey = "", string attributeValues = "");
        Task<CategoryProducts> SearchByConcer(int concernid, int page = 1);
        Task<CategoryProducts> SearchGroupProducts(int groupId, int page = 1);
        //Task<List<SubCategory>> GetVirtualSubcategories(int virtualcategoryid);
        //Task<CategoryProducts> GetProductsByBussinessType(int bussinessTypeId, int pageid);
        Task<List<Product>> GetSimilarProducts(int productid, int gid, int[] keys, int brid = -1, int brtypeid = 1);
        Task<List<Product>> GetLikeProducts(int productid, int gid, int[] keys, int brid = -1, int brtypeid = 1);
        Task<List<Product>> GetProductVarants(int productid, int gid, int brid = -1, int brtypeid = 1);
        Task<Product> SearchInOtherStore(int productid);

    }
}

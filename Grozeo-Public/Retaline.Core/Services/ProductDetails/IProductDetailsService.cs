using Retaline.Core.BusinessModel.Catalog;
using Retaline.Core.BusinessModel.ProductDetails;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace Retaline.Core.Services.ProductDetails
{
    public interface IProductDetailsService
    {
        Task<ProductDetailsModel> GetProductDetails(int productid, int brid, int brtypeid, string productname, int gid, string keys);
        Task<ProductDetailsModel> GetSimilarAndOtherProducts(int productid, int brid, int brtypeid, int catid, int gid, string keys);
        Task<List<Product>> GetProductVariants(int productid, int gid, int brid = -1, int brtypeid = 1);
    }
}

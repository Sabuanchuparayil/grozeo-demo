//using ODOCart.Core.BussinessModel.UserDetails;
using ODOCart.Core.BussinessModel.Catalog;
using System.Threading.Tasks;

namespace ODOCart.Core.Services.Catalog
{
    public interface IProductService
    {
        Task<Product> GetProductDetails(int productid, int gid, int[] keys);
        Task<CategoryProducts> GetProductsByCategoy(int catid, int pageid, int categoryLevel = 3);
        Task<CategoryProducts> GetOffers(int pageid = 1);
        Task<CategoryProducts> Advertisements(int adId, int pageid = 1);
        Task<CategoryProducts> SearchProducts(string search, int page = 1);
        Task<CategoryProducts> GetBrandProducts(int brandid, int pageid = 1);
    }
}

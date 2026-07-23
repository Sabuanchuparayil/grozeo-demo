using ODOCart.Core.BussinessModel.Catalog;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace ODOCart.Core.Services.Catalog
{
    public interface ICatalogService
    {
        Task<CatalogRoot> GetCatalog();
        //Task<List<CategoryData>> GetParentCategoies();

    }
}
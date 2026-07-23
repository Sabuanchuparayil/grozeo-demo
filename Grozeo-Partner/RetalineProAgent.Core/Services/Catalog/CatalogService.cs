using Microsoft.Extensions.Configuration;
using ODOCart.Core.BussinessModel.Catalog;
using ODOCart.Core.Services.HelperServices;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace ODOCart.Core.Services.Catalog
{
    public class CatalogService : ICatalogService
    {
        private static IHttpHelperService _httpHelperService;
        private readonly IConfiguration _configuration;
        private CatalogRoot _catchedCatalogRoot = null;
        public CatalogService(IHttpHelperService httpHelperService, IConfiguration configuration)
        {
            _httpHelperService = httpHelperService;
            _configuration = configuration;
        }

        public async Task<CatalogRoot> GetCatalog()
        {
            if (_catchedCatalogRoot == null)
            {
                string url = _configuration["ApiUrls:Catalog:GetCategories"];
                _catchedCatalogRoot = await _httpHelperService.Get<CatalogRoot>(url, null);
            }
            return _catchedCatalogRoot;
        }

        //public async Task<List<CategoryData>> GetParentCategoies()
        //{
        //    string url = "http://odocart.api.dev.velosit.in/api/category";
        //    return await _httpHelperService.Get<List<CategoryData>>(url);
        //}


    }
}

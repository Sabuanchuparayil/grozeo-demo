using System;
using System.Collections.Generic;
using System.Dynamic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Microsoft.Extensions.Configuration;
using ODOCart.Core.BussinessModel.Catalog;
using ODOCart.Core.Services.HelperServices;

namespace ODOCart.Core.Services.Catalog
{
    public class ProductService : IProductService
    {
        private readonly IHttpHelperService _httpHelperService;
        private readonly Authentication.ICustomAuthenticationService _customAuthenticationService;
        private readonly IConfiguration _configuration;
        public ProductService(IHttpHelperService httpHelperService, IConfiguration configuration, 
            Authentication.ICustomAuthenticationService customAuthenticationService)
        {
            _httpHelperService = httpHelperService;
            _customAuthenticationService = customAuthenticationService;
            _configuration = configuration;
        }
        public async Task<Product> GetProductDetails(int productid, int gid, int[] keys)
        {
            var requestParams = new Dictionary<string, object>
            {
                { "item_group", gid}, //818
                { "item", productid}, // 850
                { "possible_keys", keys }, // 850
            };
            string productUrl = _configuration["ApiUrls:Catalog:Product"].ToString();
            var aPIData = await _httpHelperService.Post<BussinessModel.API.APIModel<Product>>(productUrl, requestParams);

            
            return aPIData.Data;
            
        }

        /// <summary>
        /// Get products by category id (parent or category or subcategory)
        /// </summary>
        /// <param name="catid">category id</param>
        /// <param name="pageid">page id</param>
        /// <param name="categoryLevel">1 for Parent category, 2 for category and 3 for subcategory</param>
        /// <returns></returns>
        public async Task<CategoryProducts> GetProductsByCategoy(int catid, int pageid, int categoryLevel=3)
        {
            var requestParams = new Dictionary<string, object>
            {
                { "category_id", catid},
            };

            //string url = "http://odocart.api.dev.velosit.in/api/subcategory/products?page="+pageid;
            BussinessModel.API.APIModel<CategoryProducts> aPIData = null;
            if (categoryLevel == 1)
            {
                string url = String.Format(_configuration["ApiUrls:Catalog:ProductByParentCategoryId"], catid, pageid);
                aPIData = await _httpHelperService.Get<BussinessModel.API.APIModel<CategoryProducts>>(url);
            }
            else if(categoryLevel == 2)
            {
                string url = String.Format(_configuration["ApiUrls:Catalog:ProductByCategoryId"], catid, pageid);
                aPIData = await _httpHelperService.Get<BussinessModel.API.APIModel<CategoryProducts>>(url);
            }
            else
            {
                string url = $"{_configuration["ApiUrls:Catalog:ProductBySubCategory"]}{pageid}";
                aPIData = await _httpHelperService.Post<BussinessModel.API.APIModel<CategoryProducts>>(url, requestParams);
            }
            
            return aPIData.Data;
        }

        public async Task<CategoryProducts> SearchProducts(string search, int page=1)
        {
            var requestParams = new Dictionary<string, object>
            {
                { "product_name", search},
            };
            string url = String.Format(_configuration["ApiUrls:Catalog:Search"], page);//"http://odocart.api.dev.velosit.in/api/search/products-search";

            var aPIData = await _httpHelperService.Post<BussinessModel.API.APIModel<CategoryProducts>>(url, requestParams);
            if(aPIData != null && aPIData.Data != null)
                return aPIData.Data;

            return null;
        }

        public async Task<CategoryProducts> GetOffers(int pageid=1)
        {
            string url = $"{_configuration["ApiUrls:Catalog:Offer"]}{pageid}";//"http://odocart.api.dev.velosit.in/api/home/offers/sort?page=" + pageid;
            var aPIData = await _httpHelperService.Get<BussinessModel.API.APIModel<CategoryProducts>>(url);
            return aPIData.Data;
        }
        public async Task<CategoryProducts> Advertisements(int adId, int pageid = 1)
        {
            string url = $"{_configuration["ApiUrls:Catalog:Advertizements"]}{adId}?page={pageid}";
            var aPIData = await _httpHelperService.Get<BussinessModel.API.APIModel<CategoryProducts>>(url);
            return aPIData.Data;
        }

        public async Task<CategoryProducts> GetBrandProducts(int brandid, int pageid = 1)
        {
            var requestParams = new Dictionary<string, object>
            {
                { "brand_id", brandid},
            };
            string url = $"{_configuration["ApiUrls:Catalog:BrandProducts"]}{pageid}";
            var aPIData = await _httpHelperService.Post<BussinessModel.API.APIModel<CategoryProducts>>(url, requestParams);
            return aPIData.Data;
        }
    }

}

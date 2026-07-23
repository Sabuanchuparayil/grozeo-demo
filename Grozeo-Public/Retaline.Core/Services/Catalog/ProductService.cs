using Microsoft.Extensions.Configuration;
using Retaline.Core.BusinessModel.Catalog;
using Retaline.Core.Services.HelperServices;
using System;
using System.Collections.Generic;
using System.Security.Cryptography;
using System.Threading.Tasks;

namespace Retaline.Core.Services.Catalog
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
        public async Task<Product> GetProductDetails(int productid, int gid, int[] keys, int brid = -1, int brtypeid = 1)
        {
            if (brid <= 0)
                brid = 0;//_customAuthenticationService.GetBranchId();

            var requestParams = new Dictionary<string, object>
            {
                //{ "item_group", gid}, //818
                { "item", productid}, // 850
                //{ "possible_keys", keys }, // 850
                {"stit_ID", productid },
                {"branch_id", brid },
                {"branch_type_id", brtypeid }
            };
            string productUrl = _configuration["ApiUrls:Catalog:Product"].ToString();
            var aPIData = await _httpHelperService.Post<BusinessModel.API.APIModel<Product>>(productUrl, requestParams);


            return aPIData.Data;

        }

        /// <summary>
        /// Get products by category id (parent or category or subcategory)
        /// </summary>
        /// <param name="catid">category id</param>
        /// <param name="pageid">page id</param>
        /// <param name="categoryLevel">1 for Parent category, 2 for category and 3 for subcategory</param>
        /// <returns></returns>
        public async Task<CategoryProducts> GetProductsByCategoy(int catid, int pageid, int categoryLevel = 3)
        {
            var requestParams = new Dictionary<string, object>
            {
                { "category_id", catid}
            };

            //string url = "http://odocart.api.dev.velosit.in/api/subcategory/products?page="+pageid;
            BusinessModel.API.APIModel<CategoryProducts> aPIData = null;
            if (categoryLevel == 1)
            {
                string url = String.Format(_configuration["ApiUrls:Catalog:ProductByParentCategoryId"], catid, pageid);
                aPIData = await _httpHelperService.Get<BusinessModel.API.APIModel<CategoryProducts>>(url);
            }
            else if (categoryLevel == 2)
            {
                string url = String.Format(_configuration["ApiUrls:Catalog:ProductByCategoryId"], catid, pageid);
                aPIData = await _httpHelperService.Get<BusinessModel.API.APIModel<CategoryProducts>>(url);
            }
            else
            {
                string url = $"{_configuration["ApiUrls:Catalog:ProductBySubCategory"]}{pageid}";
                aPIData = await _httpHelperService.Post<BusinessModel.API.APIModel<CategoryProducts>>(url, requestParams);
            }
            if (aPIData != null)
                return aPIData.Data;

            return default;
        }

        /// <summary>
        /// Get Products by Category id - This is actually get product by sub category id to match it with the reatline pro api format.
        /// </summary>
        /// <param name="catid">sub category id</param>
        /// <param name="pageid">page id</param>
        /// <returns></returns>
        public async Task<List<CategoryProducts>> GetProductsByCategoyId(int catid, int pageid, int virtualcatid = -1, int categorylevelid = 0, int filterId = -1, string filterKey = "", string attributeValues="")
        {
            int branchId = _customAuthenticationService.GetBranchId();

            var requestParams = new Dictionary<string, object>
            {
                { "requested_id", catid},
                { "virtualcategoryid", virtualcatid},
                {"category_level",  categorylevelid},
                {"branch_id", branchId },
                {"order_method", 1 },
                {"sort", new Dictionary<string, object>{
                    {"price", "" }
                } },
                {"filter", new Dictionary<string, object>{
                    {"category", new object[]{ } },
                    {"brands", new object[]{ } },
                    {"price_range", new object[]{ } },
                    {"attributes", attributeValues }

                } }
            };

            if (filterId!=-1)
            {
                requestParams.Add(filterKey, filterId);
            }

            string url = $"{_configuration["ApiUrls:Catalog:ProductBySubCategory"]}{pageid}";
            var apiData = await _httpHelperService.Post<BusinessModel.API.APIModel<List<CategoryProducts>>>(url, requestParams);

            return apiData.Data;

        }
        //public async Task<List<SubCategoryProducts>> GetProductsBySubCategoy(int catid, int pageid)
        //{
        //    int branchId = _customAuthenticationService.GetBranchId();

        //    var requestParams = new Dictionary<string, object>
        //    {
        //        { "requested_id", catid},
        //        {"branch_id", branchId },
        //        {"order_method", 1 },
        //        {"sort", new Dictionary<string, object>{
        //            {"price", "" }
        //        } },
        //        {"filter", new Dictionary<string, object>{
        //            {"category", new object[]{ } },
        //            {"brands", new object[]{ } },
        //            {"price_range", new object[]{ } },

        //        } }
        //    };

        //    string url = $"{_configuration["ApiUrls:Catalog:ProductBySubCategory"]}{pageid}";
        //    var apiData = await _httpHelperService.Post<BusinessModel.API.APIModel<List<SubCategoryProducts>>>(url, requestParams);

        //    return apiData.Data;
        //}


        public async Task<CategoryProducts> SearchProducts(string search, int page = 1, int searchFilterId = -1, string searchFilterKey = "")
        {
            int branchId = _customAuthenticationService.GetBranchId();

            var requestParams = new Dictionary<string, object>
            {
                { "product_name", search},
                {"branch_id", branchId }
            };
            if (searchFilterId!=-1)
            {
                requestParams.Add(searchFilterKey, searchFilterId);
            }
            string url = string.Format(_configuration["ApiUrls:Catalog:Search"], page);//"http://odocart.api.dev.velosit.in/api/search/products-search";

            var aPIData = await _httpHelperService.Post<BusinessModel.API.APIModel<CategoryProducts>>(url, requestParams);
            if (aPIData != null && aPIData.Data != null)
                return aPIData.Data;

            return null;
        }
        public async Task<CategoryProducts> SearchGroupProducts(int groupId, int page = 1)
        {
            int branchId = _customAuthenticationService.GetBranchId();

            var requestParams = new Dictionary<string, object>
            {
                { "group_id", groupId},
                {"branch_id", branchId }
            };
            string url = String.Format(_configuration["ApiUrls:Catalog:gropuProducts"], page);//"http://odocart.api.dev.velosit.in/api/search/products-search";

            var aPIData = await _httpHelperService.Post<BusinessModel.API.APIModel<CategoryProducts>>(url, requestParams);
            if (aPIData != null && aPIData.Data != null)
                return aPIData.Data;

            return null;
        }

        public async Task<CategoryProducts> SearchByConcer(int concernid, int page = 1)
        {
            int branchId = _customAuthenticationService.GetBranchId();

            var requestParams = new Dictionary<string, object>
            {
                { "requested_id", concernid},
                {"branch_id", branchId },
                {"order_method", "1" },
                {"sort", null },
                {"filter", new Dictionary<string, object>{ { "category", new List<string> { } }, {"brands", new List<string> { } }, {"price_range", new List<string> { } } }
                }
            };
            // {"requested_id","1","branch_id","14","order_method":"1","sort":{"price":""},"filter":{"category":[],"brands":[],"price_range":[]}}
            string url = String.Format(_configuration["ApiUrls:Catalog:ShopByConcern"], page);

            var aPIData = await _httpHelperService.Post<BusinessModel.API.APIModel<List<DiseaseProduct>>>(url, requestParams);
            if (aPIData != null && aPIData.Data != null && aPIData.Data.Count > 0)
            {
                var diseaseProduct = aPIData.Data[0];
                return new CategoryProducts
                {
                    CurrentPage = diseaseProduct.Pagination.CurrentPage,
                    FirstPageUrl= diseaseProduct.Pagination.FirstPageUrl,
                    From= diseaseProduct.Pagination.From,
                    LastPage= diseaseProduct.Pagination.LastPage,
                    LastPageUrl= diseaseProduct.Pagination.LastPageUrl,
                    NextPageUrl= diseaseProduct.Pagination.NextPageUrl,
                    Path= diseaseProduct.Pagination.Path,
                    PerPage= diseaseProduct.Pagination.PerPage,
                    To= diseaseProduct.Pagination.To,
                    Total= diseaseProduct.Total,
                    Products= diseaseProduct.Products

                }; //aPIData.Data[0];
            }
            return null;

        }

        public async Task<CategoryProducts> GetOffers(int pageid = 1, int businesstypeid = -1, int retailtypeid = -1, string offerType="", int offerVal=-1)
        {
            string url = $"{_configuration["ApiUrls:Catalog:Offer"]}/{businesstypeid}/{retailtypeid}?page={pageid}";//"http://odocart.api.dev.velosit.in/api/home/offers/sort?page=" + pageid;
            if(!String.IsNullOrEmpty(offerType) && offerVal > 0)
                url= $"{_configuration["ApiUrls:Catalog:Offer"]}/{businesstypeid}/{retailtypeid}/null/{offerType}/{offerVal}?page={pageid}";
            var aPIData = await _httpHelperService.Get<BusinessModel.API.APIModel<CategoryProducts>>(url);
            return aPIData.Data;
        }
        public async Task<CategoryProducts> GetBranchOffers(int branchid, int pageid = 1)
        {
            string url = $"{_configuration["ApiUrls:Catalog:Offer"]}?page={pageid}";
            var aPIData = await _httpHelperService.Get<BusinessModel.API.APIModel<CategoryProducts>>(url, customBranchId: branchid);
            return aPIData.Data;
        }
        //public async Task<CategoryProducts> Advertisements(int adId, int pageid = 1)
        //{
        //    string url = $"{_configuration["ApiUrls:Catalog:Advertizements"]}{adId}?page={pageid}";
        //    var aPIData = await _httpHelperService.Get<BusinessModel.API.APIModel<CategoryProducts>>(url);
        //    return aPIData.Data;
        //}

        public async Task<CategoryProducts> GetBrandProducts(int brandid, int pageid = 1)
        {
            int branchId = _customAuthenticationService.GetBranchId();
            var requestParams = new Dictionary<string, object>
            {
                //{ "requested_id", concernid},
                {"branch_id", branchId },
                {"order_method", "1" },
                {"sort", null },
                {"filter", new Dictionary<string, object>{ { "category", new List<string> { } }, { "brands", new List<int> { brandid } }, {"price_range", new List<string> { } } }
                }
            };

            string url = $"{_configuration["ApiUrls:Catalog:BrandProducts"]}{pageid}";
            var aPIData = await _httpHelperService.Post<BusinessModel.API.APIModel<List<CategoryProducts>>>(url, requestParams);
            if (aPIData != null && aPIData.Data != null && aPIData.Data.Count > 0)
            {
                CategoryProducts categoryProducts = aPIData.Data[0];
                if (categoryProducts.Products == null && categoryProducts.Products2 != null)
                    categoryProducts.Products = categoryProducts.Products2;
                if (categoryProducts.Pagination != null)
                {
                    categoryProducts.CurrentPage = categoryProducts.Pagination.CurrentPage;
                    categoryProducts.FirstPageUrl = categoryProducts.Pagination.FirstPageUrl;
                    categoryProducts.From = categoryProducts.From;
                    categoryProducts.LastPage = categoryProducts.Pagination.LastPage;
                    categoryProducts.LastPageUrl = categoryProducts.Pagination.LastPageUrl;
                    categoryProducts.NextPageUrl = categoryProducts.Pagination.NextPageUrl;
                    categoryProducts.Path = categoryProducts.Pagination.Path;
                    categoryProducts.PerPage = categoryProducts.Pagination.PerPage;
                    categoryProducts.PerPageUrl = categoryProducts.Pagination.PreviousPageUrl;
                    categoryProducts.To = categoryProducts.Pagination.To;
                }

                return categoryProducts;
            }
            return default;
        }

        //public async Task<List<SubCategory>> GetVirtualSubcategories(int virtualcategoryid)
        //{
        //    // 
        //    string url = $"{_configuration["ApiUrls:Catalog:VirtualSubcategories"]}{virtualcategoryid}";
        //    var aPIData = await _httpHelperService.Get<BusinessModel.API.APIModel<List<SubCategory>>>(url);
        //    return aPIData.Data;
        //}

        //public async Task<CategoryProducts> GetProductsByBussinessType(int bussinessTypeId, int pageid)
        //{
        //    var categoryLevel = 2;
        //    var requestParams = new Dictionary<string, object>
        //    {
        //        { "businesstype_id", bussinessTypeId}
        //    };

        //    //string url = "http://odocart.api.dev.velosit.in/api/subcategory/products?page="+pageid;
        //    BusinessModel.API.APIModel<CategoryProducts> aPIData = null;
        //    if (categoryLevel == 1)
        //    {
        //        string url = String.Format(_configuration["ApiUrls:Catalog:ProductByParentCategoryId"], bussinessTypeId, pageid);
        //        aPIData = await _httpHelperService.Get<BusinessModel.API.APIModel<CategoryProducts>>(url);
        //    }
        //    else if (categoryLevel == 2)
        //    {
        //        string url = String.Format(_configuration["ApiUrls:Catalog:ProductByCategoryId"], bussinessTypeId, pageid);
        //        aPIData = await _httpHelperService.Get<BusinessModel.API.APIModel<CategoryProducts>>(url);
        //    }
        //    else
        //    {
        //        string url = $"{_configuration["ApiUrls:Catalog:ProductBySubCategory"]}{pageid}";
        //        aPIData = await _httpHelperService.Post<BusinessModel.API.APIModel<CategoryProducts>>(url, requestParams);
        //    }
        //    if (aPIData != null)
        //        return aPIData.Data;

        //    return default;
        //}

        /// <summary>
        /// Get similar products
        /// </summary>
        /// <param name="productid"></param>
        /// <param name="gid"></param>
        /// <param name="keys"></param>
        /// <param name="brid"></param>
        /// <param name="brtypeid"></param>
        /// <returns></returns>
        public async Task<List<Product>> GetSimilarProducts(int productid, int gid, int[] keys, int brid = -1, int brtypeid = 1)
        {
            if (brid <= 0)
                brid = _customAuthenticationService.GetBranchId();

            var requestParams = new Dictionary<string, object>
            {
                { "item_group", gid}, //818
                { "item", productid}, // 850
                { "possible_keys", keys }, // 850
                {"stit_ID", productid },
                {"branch_id", brid },
                {"branch_type_id", brtypeid }
            };
            string productUrl = _configuration["ApiUrls:Products:Similar"].ToString();
            var aPIData = await _httpHelperService.Post<BusinessModel.API.APIModel<List<Product>>>(productUrl, requestParams);

            if (aPIData != null)
                return aPIData.Data;

            return default;
        }

        /// <summary>
        /// Get similar products
        /// </summary>
        /// <param name="productid"></param>
        /// <param name="gid"></param>
        /// <param name="keys"></param>
        /// <param name="brid"></param>
        /// <param name="brtypeid"></param>
        /// <returns></returns>
        public async Task<List<Product>> GetLikeProducts(int productid, int gid, int[] keys, int brid = -1, int brtypeid = 1)
        {
            if (brid <= 0)
                brid = _customAuthenticationService.GetBranchId();

            var requestParams = new Dictionary<string, object>
            {
                { "item_group", gid}, //818
                { "item", productid}, // 850
                { "possible_keys", keys }, // 850
                {"stit_ID", -1 },
                {"branch_id", brid },
                {"branch_type_id", brtypeid }
            };
            string productUrl = _configuration["ApiUrls:Products:Like"].ToString();
            var aPIData = await _httpHelperService.Post<BusinessModel.API.APIModel<List<Product>>>(productUrl, requestParams);

            if (aPIData != null)
                return aPIData.Data;

            return default;
        }

        public async Task<List<Product>> GetProductVarants(int productid, int gid, int brid = -1, int brtypeid = 1)
        {
            var requestParams = new Dictionary<string, object>
            {
                { "group_id", gid},
                { "stit_ID", productid },
                {"branch_id", brid },
                {"branch_type_id", brtypeid }
            };
            string productUrl = _configuration["ApiUrls:Products:productVariants"].ToString();
            var aPIData = await _httpHelperService.Post<BusinessModel.API.APIModel<List<Product>>>(productUrl, requestParams);

            if (aPIData != null)
                return aPIData.Data;

            return default;
        }
        public async Task<Product> SearchInOtherStore(int productid)
        {
            var requestParams = new Dictionary<string, object>
            {

                  { "product_id", productid },
            };
            string productUrl = _configuration["ApiUrls:Products:searchInOtherStore"].ToString();
            var aPIData = await _httpHelperService.Post<BusinessModel.API.APIModel<Product>>(productUrl, requestParams);

            if (aPIData != null)
                return aPIData.Data;

            return default;
        }

    }

}

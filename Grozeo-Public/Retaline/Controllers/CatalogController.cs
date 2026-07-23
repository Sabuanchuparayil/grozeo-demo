using Microsoft.AspNetCore.Mvc;
using Microsoft.CodeAnalysis.Operations;
using Microsoft.IdentityModel.Tokens;
using Retaline.Core.BusinessModel.Brands;
using Retaline.Core.BusinessModel.Catalog;
using Retaline.Core.Services.Catalog;
using Retaline.Core.Utilities;
using Retaline.Core.ViewModel.Catalog;
using Retaline.Web.Handlers;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Retaline.Core.Services.Authentication;
using Retaline.Core.Services.ProfileManagement;
using Retaline.Core.BusinessModel.Store;
using Microsoft.Extensions.Configuration;

namespace Retaline.Web.Controllers
{

    public class CatalogController : Controller
    {
        private readonly IProductService _productService;
        private readonly ICatalogService _catalogService;
        private readonly ICustomHomeAndCategoryService _categoryBusinessService;
        private readonly IProfileService _profileService;
        private readonly ICustomAuthenticationService _authenticationService;
        private readonly IConfiguration _configuration;

        public CatalogController(IProductService productService, ICatalogService catalogService, ICustomHomeAndCategoryService categoryBusinessService, IProfileService profileService, IConfiguration configuration, ICustomAuthenticationService authenticationService)
        {
            _productService = productService;
            _catalogService = catalogService;
            _categoryBusinessService = categoryBusinessService;
            _configuration = configuration;
            _authenticationService = authenticationService;
            _profileService = profileService;

        }

        [Route("vc/{vcid}/{vcname}")]
        [Route("vc/{vcid}/{vcsubcatid}/{*vcname}")]
        [Route("vc/{vcparentid}/{vcid}/{vcsubcatid}/{*vcname}")]
        public async Task<IActionResult> VirtualCategory(int vcid, int vcparentid=-1, int vcsubcatid = -1, string vcname = "")
        {
            CategoryItemViewModel categoryItem = await _categoryBusinessService.GetVirtualCategory(vcid, vcsubcatid, vcname, vcparentid: vcparentid);
            if (categoryItem != null && String.IsNullOrEmpty(categoryItem.CategoryName))
                categoryItem.CategoryName = vcname;
            if (vcparentid > 0)
            {
                categoryItem.SubCatId = vcid;
                categoryItem.CatId = vcparentid;
                categoryItem.IsVirtualCategory = true;
                categoryItem.VirtualCategoryId = vcid;
                categoryItem.IsOffersView = false;
                //categoryItem.CategoryLevel
            }
            return View("CategoryListPage", categoryItem);
        }


        [Route("pc/{pcatid}/{catname}")]
        [Route("pc/{pcatid}/{ccatid}/{catname}")]
        [Route("pc/{pcatid}/{ccatid}/{scatid}/{catname}")]
        public async Task<IActionResult> GetCategories(int pcatid = -1, int ccatid = -1, int scatid = -1, string catname = "")
        {
            string attributeValues = "";
            if (Request.Query.Any(r => r.Key == "_atr"))
                attributeValues = Request.Query.Where(r => r.Key == "_atr").FirstOrDefault().Value.FirstOrDefault();
            if (attributeValues != null)
                attributeValues = attributeValues.Trim().Trim(new char[] { ',', '|'}).Replace("|", ",");

            CategoryItemViewModel categoryItem = await _categoryBusinessService.GetCategories(pcatid, ccatid, scatid, catname, attributeValues: attributeValues);
            return View("CategoryListPage", categoryItem);
        }

        [Route("offers/{page?}")]

        public async Task<IActionResult> GetCategoriesOnOffer(int page = 1)
        {
            CategoryItemViewModel categoryItem = await _categoryBusinessService.GetCategoriesOnOffer(page);
            return View("CategoryListPage", categoryItem);
        }

        [Route("offers/{businessTypeId}/{retailtypeid}/{offerType}/{offerTypeId}/{page?}")]
        public async Task<IActionResult> GetCustomOffer(int businessTypeId, int retailtypeid, string offerType, int offerTypeId, int page = 1)
        {
            CategoryItemViewModel categoryItem = await _categoryBusinessService.GetCategoriesOnOffer(page, businessTypeId, retailtypeid, offerType, offerTypeId);
            return View("CategoryListPage", categoryItem);
        }

        [Route("excloffers/{page?}")]

        public async Task<IActionResult> GetExclusiveOffers(int page = 1)
        {
            CategoryItemViewModel categoryItem = await _categoryBusinessService.GetCategoriesOnOffer(page);
            categoryItem.AutoLoadCount = 0;
            categoryItem.CategoryName = "Exclusive Offers";
            //return View("ExclOffers", categoryItem);
            return View("CategoryListPage", categoryItem);
        }

        [Route("brand/{brandid}/{catname}")]
        public async Task<IActionResult> GetBrandCategories(int brandid, string catname)
        {
            CategoryItemViewModel categoryItem = await _categoryBusinessService.GetBrandCategories(brandid, catname);
            return View("CategoryListPage", categoryItem);
        }

        [Route("categories")]
        public async Task<IActionResult> CategoryListMini()
        {
            CategoryItemViewModel categoryItem = new CategoryItemViewModel();
            var catalog = await _catalogService.GetCatalog();
            categoryItem.Categories = catalog.Data;
            return View(categoryItem);
        }

        [Route("/search")]
        public async Task<IActionResult> Search(string searchkey)
        {
            CategoryItemViewModel categoryItem = await _categoryBusinessService.Search(searchkey);
            return View("CategoryListPage", categoryItem);
        }
        [Route("/searchautocomplete")]
        public async Task<IActionResult> SearchAutoComplete(string searchkey)
        {
            CategoryItemViewModel categoryItem = await _categoryBusinessService.Search(searchkey);
            //categoryItem.Products.ForEach(p => { if (String.IsNullOrEmpty(p.SKU)) p.SKU = p.StitSku; });
            if (categoryItem != null && categoryItem.Products != null && categoryItem.Products.Count > 0)
            {
                var list = categoryItem.Products;//.SelectMany(p => p.Item.Select(m=> new { branch_id = m.BranchId, branch_type_id = m.BranchTypeId, stit_ID = m.StitId, 
                    //stit_fsiuid = m.StitFsiUId, stit_SKU = m.SKU, brand_name = p.BrandName, category_name = p.CategoryName, percentage = m.Percentage, selling_price = m.SellingPrice??m.SellingPrice2, mrp=m.MRP })).ToList();
                if (list != null && list.Count > 0)
                    return Json(list);
            }
            //return Json(categoryItem.Products.SelectMany(p => p.Item).ToList());
            return null;
        }

        [Route("/concern/{concernid}/{searchkey}")]
        public async Task<IActionResult> Search(string searchkey, int concernid = -1)
        {
            CategoryItemViewModel categoryItem = await _categoryBusinessService.SearchByConcer(concernid, searchkey);
            return View("CategoryListPage", categoryItem);
        }

        [Route("/groupitem/{groupid}/{*searchkey}")]
        public async Task<IActionResult> SearchGroupProducts(string searchkey, int groupid = -1)
        {
            CategoryItemViewModel categoryItem = await _categoryBusinessService.SearchGroupProducts(groupid, searchkey);
            return View("CategoryListPage", categoryItem);
        }


        [Route("/loadmore/{contenttype}/{subcatId}/{page}/{brandId?}/{categoryLevel?}")]
        [Route("/loadmore/{contenttype}/{page}/{key?}")]
        public async Task<IActionResult> LoadMore(int contenttype = 1, int page = 1, int subcatId = -1, int brandId = -1, int categoryLevel = 3, string key = "")
        {
            CategoryProducts categoryProducts = new CategoryProducts();
            CategoryItemViewModel categoryItem = new CategoryItemViewModel();
            categoryItem.ContentTypeId = contenttype;
            if (contenttype == 4) // search
            {
                categoryProducts = await _productService.SearchProducts(key, page);
                categoryItem.CategoryName = key;
            }
            else if (contenttype == 5) // Group id
            {
                try
                {
                    int groupid = Convert.ToInt32(key);
                    categoryProducts = await _productService.SearchGroupProducts(groupid, page);
                    categoryItem.CategoryName = key;
                }
                catch { categoryProducts = new CategoryProducts(); }
            }
            else if (contenttype == 6) // Virtual cat products
            {
                try
                {
                    int vcid = subcatId; 
                    if(!String.IsNullOrEmpty(key))
                       try { vcid = Convert.ToInt32(key); } catch { vcid = subcatId; }

                    var subcategoryProducts = await _productService.GetProductsByCategoyId(-1, page, vcid);
                    categoryItem.CategoryName = key;
                    if (subcategoryProducts.Count > 0)
                    {
                        var sc = subcategoryProducts.FirstOrDefault();
                        categoryProducts.Products = sc.Products2;
                        categoryProducts.CurrentPage = sc.Pagination.CurrentPage;
                        categoryProducts.FirstPageUrl = sc.Pagination.FirstPageUrl;
                        categoryProducts.From = sc.Pagination.From;
                        categoryProducts.LastPage = sc.Pagination.LastPage;
                        categoryProducts.LastPageUrl = sc.Pagination.LastPageUrl;
                        categoryProducts.NextPageUrl = sc.Pagination.NextPageUrl;
                        categoryProducts.Path = sc.Pagination.Path;
                        categoryProducts.PerPage = sc.Pagination.PerPage;
                        categoryProducts.Total = sc.Pagination.Total;
                        categoryProducts.To = sc.Pagination.To;
                    }
                }
                catch { categoryProducts = new CategoryProducts(); }
            }
            else if (subcatId > 0)
            {
                //categoryProducts = await _productService.GetProductsByCategoy(subcatId, page, categoryLevel);

                categoryItem.ContentTypeId = 1;
                //categoryItem.CategoryLevel = (subcatId > 0 ? 3 : (subcatId > 0 ? 2 : 1));

                //var subcategoryProducts = await _productService.GetProductsBySubCategoy(subcatId, page);
                //categoryProducts.Products = subcategoryProducts.Select(sp => new Product { Item = sp.Products, Name = sp.Title, Id = sp.Id, GroupId = sp.SubId }).ToList();
                var subcategoryProducts = await _productService.GetProductsByCategoyId(subcatId, page, -1, (categoryLevel == 3 ? 0 : categoryLevel));
                categoryItem.CurrentSubCatId = subcatId;

                if (subcategoryProducts.Count > 0)
                {
                    var sc = subcategoryProducts.FirstOrDefault();
                    categoryProducts.Products = sc.Products2;
                    categoryProducts.CurrentPage = sc.Pagination.CurrentPage;
                    categoryProducts.FirstPageUrl = sc.Pagination.FirstPageUrl;
                    categoryProducts.From = sc.Pagination.From;
                    categoryProducts.LastPage = sc.Pagination.LastPage;
                    categoryProducts.LastPageUrl = sc.Pagination.LastPageUrl;
                    categoryProducts.NextPageUrl = sc.Pagination.NextPageUrl;
                    categoryProducts.Path = sc.Pagination.Path;
                    categoryProducts.PerPage = sc.Pagination.PerPage;
                    categoryProducts.Total = sc.Pagination.Total;
                    categoryProducts.To = sc.Pagination.To;
                }




            }
            else if (contenttype == 3 && brandId > 0)
            {
                categoryProducts = await _productService.GetBrandProducts(brandId, page);
                categoryItem.CurrentSubCatId = -1;
                categoryItem.BrandId = brandId;
                categoryItem.IsBrandView = true;
            }
            else
            {
                categoryProducts = await _productService.GetOffers(page);
                categoryItem.IsOffersView = true;
                categoryItem.BrandId = brandId;
            }

            categoryItem.CurrentSubCatId = subcatId;
            categoryItem.Products = categoryProducts.Products;
            categoryItem.CurrentPage = categoryProducts.CurrentPage;
            categoryItem.LastPage = categoryProducts.LastPage;
            categoryItem.Total = categoryProducts.Total;

            //categoryItem.ParentCatId = pcatid;
            //categoryItem.CatId = ccatid;
            categoryItem.SubCatId = subcatId;
            categoryItem.IsOffersView = (subcatId <= 0);
            //categoryItem.Categories = catalog.Data;
            //categoryItem.CategoryName = catname;
            categoryItem.FirstPageUrl = categoryProducts.FirstPageUrl;
            categoryItem.From = categoryProducts.From;
            categoryItem.LastPageUrl = categoryProducts.LastPageUrl;
            categoryItem.NextPageUrl = categoryProducts.NextPageUrl;
            categoryItem.Path = categoryProducts.Path;
            categoryItem.PerPage = categoryProducts.PerPage;
            categoryItem.PerPageUrl = categoryProducts.PerPageUrl;
            categoryItem.To = categoryProducts.To;
            categoryItem.CategoryLevel = categoryLevel;
            return View(categoryItem);
        }

        [Route("/loadmoreoffers/{businesstypeid?}/{retailtypeid?}/{page}")]
        public async Task<IActionResult> OffersLoadMore(int page = 1, int businesstypeid=-1, int retailtypeid=-1)
        {
            CategoryProducts categoryProducts = new CategoryProducts();
            CategoryItemViewModel categoryItem = new CategoryItemViewModel();
            categoryItem.ContentTypeId = 2;
            categoryProducts = await _productService.GetOffers(page, businesstypeid, retailtypeid);
            categoryItem.IsOffersView = true;
            categoryItem.BrandId = -1;

            categoryItem.BusinessTypeId = businesstypeid;
            categoryItem.RetailTypeId= retailtypeid;
            categoryItem.CurrentSubCatId = -1;
            categoryItem.SubCatId = -1;
            if(categoryProducts != null)
            {
                try
                {
                    categoryItem.Products = categoryProducts.Products;
                    categoryItem.CurrentPage = categoryProducts.CurrentPage;
                    categoryItem.LastPage = categoryProducts.LastPage;
                    categoryItem.Total = categoryProducts.Total;

                    categoryItem.FirstPageUrl = categoryProducts.FirstPageUrl;
                    categoryItem.From = categoryProducts.From;
                    categoryItem.LastPageUrl = categoryProducts.LastPageUrl;
                    categoryItem.NextPageUrl = categoryProducts.NextPageUrl;
                    categoryItem.Path = categoryProducts.Path;
                    categoryItem.PerPage = categoryProducts.PerPage;
                    categoryItem.PerPageUrl = categoryProducts.PerPageUrl;
                    categoryItem.To = categoryProducts.To;
                }
                catch { }
            }
            categoryItem.CategoryLevel = -1;
            return View("LoadMore",categoryItem);

        }
        [Route("/br/loadmoreoffers/{brid}/{page}")]
        public async Task<IActionResult> BranchOffersLoadMore(int brid, int page = 1)
        {
            CategoryProducts categoryProducts = new CategoryProducts();
            CategoryItemViewModel categoryItem = new CategoryItemViewModel();
            categoryItem.ContentTypeId = -1;
            categoryItem.BranchId = brid;
            categoryProducts = await _productService.GetBranchOffers(brid, page);
            categoryItem.IsOffersView = true;
            categoryItem.BrandId = -1;

            categoryItem.CurrentSubCatId = -1;
            categoryItem.SubCatId = -1;
            if (categoryProducts != null)
            {
                try
                {
                    categoryItem.Products = categoryProducts.Products;
                    categoryItem.CurrentPage = categoryProducts.CurrentPage;
                    categoryItem.LastPage = categoryProducts.LastPage;
                    categoryItem.Total = categoryProducts.Total;

                    categoryItem.FirstPageUrl = categoryProducts.FirstPageUrl;
                    categoryItem.From = categoryProducts.From;
                    categoryItem.LastPageUrl = categoryProducts.LastPageUrl;
                    categoryItem.NextPageUrl = categoryProducts.NextPageUrl;
                    categoryItem.Path = categoryProducts.Path;
                    categoryItem.PerPage = categoryProducts.PerPage;
                    categoryItem.PerPageUrl = categoryProducts.PerPageUrl;
                    categoryItem.To = categoryProducts.To;
                }
                catch { }
            }
            categoryItem.CategoryLevel = -1;
            return View("LoadMore", categoryItem);

        }

        [HttpGet]
        [Route("retailerCategory/{businessTypeId}")]
        public async Task<IActionResult> GetRetailerTypes(int businessTypeId = 1)
        {
            var result = await _catalogService.GetRetailerTypes(businessTypeId);
            return Json(result);
        }

        [HttpGet]
        [Route("bt/more")]
        public async Task<IActionResult> GetAllcategories(int retailerId)
        {
            var result = await _catalogService.GetBusinessTypes(retailerId);
            IEnumerable<BusinessType> businessTypes = result.Skip(4);
            return View("AllCategories", businessTypes);
        }

        [Route("sidebannersmall")]
        public async Task<IActionResult> SideBanner()
        {
            try
            {

                Core.BusinessModel.Home.Advertisement.AdZoneInfo adZoneInfo = new Core.BusinessModel.Home.Advertisement.AdZoneInfo();
                var result = await _catalogService.GetSideBanner();
                if (result != null && result.Count > 0)
                {
                    adZoneInfo = result[0];
                    string url = Retaline.Web.Service.Common.OfferUrl(adZoneInfo);
                    return Json(new { adinfo = adZoneInfo, url = url });
                }
            }
            catch { }
            // return null; // Json(result);
            return new JsonResult(new { error = "No banners found", adinfo = new { }, url = "" });

        }

        [HttpGet]
        [Route("categorieslist")]
        public async Task<IActionResult> CategoryList()
        {
            CategoryItemViewModel categoryItem = new CategoryItemViewModel();
            var catalog = await _catalogService.GetCatalog();
            categoryItem.Categories = catalog.Data;
            return View("CategoryList", categoryItem);

            //return View("AllCategories", businessTypes);
        }
        [HttpPost]
        [Route("searchInOtherStore")]
        public async Task<IActionResult> SearchInOtherStore([FromBody] int productid)
        {
            var requestParams = new Dictionary<string, object>
            {
                { "stit_ID", productid },
            };

            Core.BusinessModel.Catalog.Product productFromotherStore = await _productService.SearchInOtherStore(productid);


            if (productFromotherStore != null)
                return Json(new { status = 1, data = productFromotherStore });
            else
				return Json(new { status = 0, message = "Sorry, No other stores are selling this product!" });
        }
        /// <summary>
        /// Get nearest stores by retail category id.
        /// </summary>
        /// <param name="rcId">retail category id</param>
        /// <param name="pageId">page number</param>
        /// <returns>view</returns>
        [Route("stores/{rcId}/{pageId?}")]
        public async Task<ActionResult> GetNearestStoresByRC(int rcId, int pageId = 0)
        {
            double lat = 0, lng = 0;
            try
            {
                string defLat = _configuration["DefLatitude"];
                string defLng = _configuration["DefLongitude"];
                if (!string.IsNullOrEmpty(defLat) && !string.IsNullOrEmpty(defLng))
                {
                    lat = Convert.ToDouble(defLat);
                    lng = Convert.ToDouble(defLng);
                }
            }
            catch { }

            if (User.Identity.IsAuthenticated)
            {
                try
                {
                    var user = _authenticationService.GetUserFromClaims();
                    if (user != null && user.PrimaryAddress != null)
                    {
                        lat = user.PrimaryAddress.Latitude;
                        lng = user.PrimaryAddress.Longitude;
                    }

                }
                catch { }
            }
            if (lat > 0 && lng > 0)
            {
                try
                {
                    var result = _profileService.GetNearestStores(lat, lng, rcId);
                    if (result != null && result.Result != null && result.Result.Data != null)
                        return PartialView("BusinessTypeItems", result.Result.Data.Data);

                }
                catch { }
            }

            return PartialView(new List<StoreGroup>());
        }
    }
}

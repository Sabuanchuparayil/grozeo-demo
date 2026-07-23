using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;
using Retaline.Core.BusinessModel.Logging;
using Retaline.Core.BusinessModel.UserDetails;
using Retaline.Core.Services.ProfileManagement;
using Retaline.Core.ViewModel.Catalog;
using Retaline.Core.ViewModel.Home;
using Retaline.Web.Handlers;
using SaasKit.Multitenancy;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Threading.Tasks;


namespace Retaline.Web.Controllers
{
    public class CustomHomeAndCategoryController : Controller
    {

        private readonly ICustomHomeAndCategoryService _categoryBusinessService;
        private readonly IHomePageHandlerService _homePageHandler;
        private readonly IHttpContextAccessor _httpContextAccessor;
        private readonly IProfileService _profileService;

        public CustomHomeAndCategoryController(ICustomHomeAndCategoryService categoryBusinessService, IHttpContextAccessor httpContextAccessor,
            IHomePageHandlerService homePageHandler, IProfileService profileService)
        {
            _categoryBusinessService=categoryBusinessService;
            _homePageHandler=homePageHandler;
            _httpContextAccessor = httpContextAccessor;
            _profileService = profileService;
        }

        [Route("bt/{businessTypeId}/{businessType}")]
        public async Task<IActionResult> BusinesTypeHome(int businessTypeId, string businessType)
        {
            ViewBag.UrlType = $"/bt";
            ViewBag.UrlTypeId=$"/{businessTypeId}";
            ViewBag.BTypeId = businessType;
            ViewBag.IsHomePage ="y";
            List<HomePageViewModel> details = await _homePageHandler.GetHomePageContentBasedOnType(businessTypeId, businessType);//"business_type_id");
            return View("../Home/index", details);
        }

        [Route("bt/search/{businessTypeId}")]
        public async Task<IActionResult> SearchBasedOnBusinessType(string searchKey, int businessTypeId)
        {
            ViewBag.UrlType = $"/bt";
            ViewBag.UrlTypeId=$"/{businessTypeId}";
            CategoryItemViewModel categoryItem = await _categoryBusinessService.Search(searchKey, businessTypeId, "business_type_id");
            return View("../Catalog/CategoryListPage", categoryItem);
        }



        [Route("bt/searchautocomplete/{businessTypeId}")]
        public async Task<IActionResult> SearchAutoCompleteBasedOnBusinessType(string searchKey, int businessTypeId)
        {
            CategoryItemViewModel categoryItem = await _categoryBusinessService.Search(searchKey, businessTypeId, "business_type_id");
            if (categoryItem != null && categoryItem.Products != null && categoryItem.Products.Count > 0)
            {
                var list = categoryItem.Products;//.SelectMany(p => p.Item.Select(m=> new { branch_id = m.BranchId, branch_type_id = m.BranchTypeId, stit_ID = m.StitId, 
                    //stit_fsiuid = m.StitFsiUId, stit_SKU = m.SKU, brand_name = p.BrandName, category_name = p.CategoryName, percentage = m.Percentage, selling_price = m.SellingPrice??m.SellingPrice2, mrp=m.MRP })).ToList();
                if (list != null && list.Count > 0)
                    return Json(list);
            }

            return null;// return Json(categoryItem.Products);
        }

        [Route("bt/pc/{pcatid}/{catname}/{businessTypeId}")]
        [Route("bt/pc/{pcatid}/{ccatid}/{catname}/{businessTypeId}")]
        [Route("bt/pc/{pcatid}/{ccatid}/{scatid}/{catname}/{businessTypeId}")]
        public async Task<IActionResult> GetCategories(int pcatid = -1, int ccatid = -1, int scatid = -1, string catname = "", int businessTypeId = -1)
        {
            ViewBag.UrlType = $"/bt";
            ViewBag.UrlTypeId=$"/{businessTypeId}";
            CategoryItemViewModel categoryItem = await _categoryBusinessService.GetCategories(pcatid, ccatid, scatid, catname, businessTypeId, "business_type_id");
            return View("../Catalog/CategoryListPage", categoryItem);
        }

        [Route("bt/vc/{vcid}/{vcname}/{businessTypeId}")]
        [Route("bt/vc/{vcid}/{vcsubcatid}/{businessTypeId}/{*vcname}")]
        public async Task<IActionResult> VirtualCategory(int vcid, int vcsubcatid = -1, string vcname = "", int businessTypeId = -1)
        {
            ViewBag.UrlType = $"/bt";
            ViewBag.UrlTypeId=$"/{businessTypeId}";
            CategoryItemViewModel categoryItem = await _categoryBusinessService.GetVirtualCategory(vcid, vcsubcatid, vcname, businessTypeId, "business_type_id");
            return View("../Catalog/CategoryListPage", categoryItem);
        }

        [Route("st/{storeId}/{storeName}")]
        public async Task<IActionResult> StoreHome(int storeId, string storeName)
        {
            ViewBag.UrlType = $"/st";
            ViewBag.UrlTypeId=$"/{storeId}";
            ViewBag.StoreName=storeName;
            List<HomePageViewModel> details = await _homePageHandler.GetHomePageContent(storeId); //.GetHomePageContentBasedOnType(storeId, "store_id");
            details.Add(new HomePageViewModel() { Content = null, Type = "Tenant Site" });
            return View("../Home/index", details);
        }

        [Route("st/search/{storeId}")]
        public async Task<IActionResult> SearchBasedOnStore(string searchKey, int storeId)
        {
            ViewBag.UrlType = $"/st";
            ViewBag.UrlTypeId=$"/{storeId}";
            CategoryItemViewModel categoryItem = await _categoryBusinessService.Search(searchKey, storeId, "store_id");
            return View("../Catalog/CategoryListPage", categoryItem);
        }

        [Route("st/searchautocomplete/{storeId}")]
        public async Task<IActionResult> SearchAutoCompleteBasedOnStore(string searchKey, int storeId)
        {
            CategoryItemViewModel categoryItem = await _categoryBusinessService.Search(searchKey, storeId, "store_id");
            if (categoryItem != null && categoryItem.Products != null && categoryItem.Products.Count > 0)
            {
                var list = categoryItem.Products;//.SelectMany(p => p.Item.Select(m=> new { branch_id = m.BranchId, branch_type_id = m.BranchTypeId, stit_ID = m.StitId, 
                //    stit_fsiuid = m.StitFsiUId, stit_SKU = m.SKU, brand_name = p.BrandName, category_name = p.CategoryName, percentage = m.Percentage, selling_price = m.SellingPrice??m.SellingPrice2, mrp=m.MRP })).ToList();
                if (list != null && list.Count > 0)
                    return Json(list);
            }

            return null;// return Json(categoryItem.Products);
        }

        [Route("st/pc/{pcatid}/{catname}/{storeId}")]
        [Route("st/pc/{pcatid}/{ccatid}/{catname}/{storeId}")]
        [Route("st/pc/{pcatid}/{ccatid}/{scatid}/{catname}/{storeId}")]
        public async Task<IActionResult> GetStoreCategories(int pcatid = -1, int ccatid = -1, int scatid = -1, string catname = "", int storeId = -1)
        {
            ViewBag.UrlType = $"/st";
            ViewBag.UrlTypeId=$"/{storeId}";
            CategoryItemViewModel categoryItem = await _categoryBusinessService.GetCategories(pcatid, ccatid, scatid, catname, storeId, "store_id");
            return View("../Catalog/CategoryListPage", categoryItem);
        }

        [Route("st/vc/{vcid}/{vcname}/{storeId}")]
        [Route("st/vc/{vcid}/{vcsubcatid}/{businessTypeId}/{*vcname}")]
        public async Task<IActionResult> VirtualStoreCategory(int vcid, int vcsubcatid = -1, string vcname = "", int storeId = -1)
        {
            ViewBag.UrlType = $"/st";
            ViewBag.UrlTypeId=$"/{storeId}";
            CategoryItemViewModel categoryItem = await _categoryBusinessService.GetVirtualCategory(vcid, vcsubcatid, vcname, storeId, "store_id");
            return View("../Catalog/CategoryListPage", categoryItem);
        }

        [Route("rc/{retailerTypeId}")]
        public async Task<IActionResult> RetailerTypeHome(int retailerTypeId)
        {
            ViewBag.UrlType = $"/rc";
            ViewBag.UrlTypeId=$"/{retailerTypeId}";
            ViewBag.IsHomePage ="y";
            List<HomePageViewModel> details = await _homePageHandler.GetHomePageForRetailerType(retailerTypeId);
            if(retailerTypeId == 0)
            {
                return View("../Catalog/BusinessTypesList", details);
            }
            return View("../Home/index", details);
        }

        [Route("rc/search/{retailerTypeId}")]
        public async Task<IActionResult> SearchBasedOnRetailerType(string searchKey, int retailerTypeId)
        {
            ViewBag.UrlType = $"/rc";
            ViewBag.UrlTypeId=$"/{retailerTypeId}";
            CategoryItemViewModel categoryItem = await _categoryBusinessService.Search(searchKey, retailerTypeId, "retailer_type_id");
            return View("../Catalog/CategoryListPage", categoryItem);
        }

        [Route("rc/searchautocomplete/{retailerTypeId}")]
        public async Task<IActionResult> SearchAutoCompleteBasedOnRetailerType(string searchKey, int retailerTypeId)
        {
            CategoryItemViewModel categoryItem = await _categoryBusinessService.Search(searchKey, retailerTypeId, "retailer_type_id");
            if (categoryItem != null && categoryItem.Products != null && categoryItem.Products.Count > 0)
            {
                var list = categoryItem.Products;//.SelectMany(p => p.Item.Select(m=> new { branch_id = m.BranchId, branch_type_id = m.BranchTypeId, stit_ID = m.StitId, 
                    //stit_fsiuid = m.StitFsiUId, stit_SKU = m.SKU, brand_name = p.BrandName, category_name = p.CategoryName, percentage = m.Percentage, selling_price = m.SellingPrice??m.SellingPrice2, mrp=m.MRP })).ToList();
                if (list != null && list.Count > 0)
                    return Json(list);
            }

            return null;// Json(categoryItem.Products);
        }

        [Route("rc/pc/{pcatid}/{catname}/{retailerTypeId}")]
        [Route("rc/pc/{pcatid}/{ccatid}/{catname}/{retailerTypeId}")]
        [Route("rc/pc/{pcatid}/{ccatid}/{scatid}/{catname}/{retailerTypeId}")]
        public async Task<IActionResult> GetCategoriesBasedRetailerType(int pcatid = -1, int ccatid = -1, int scatid = -1, string catname = "", int retailerTypeId = -1)
        {
            ViewBag.UrlType = $"/rc";
            ViewBag.UrlTypeId=$"/{retailerTypeId}";
            CategoryItemViewModel categoryItem = await _categoryBusinessService.GetCategories(pcatid, ccatid, scatid, catname, retailerTypeId, "retailer_type_id");
            return View("../Catalog/CategoryListPage", categoryItem);
        }

        [Route("rc/vc/{vcid}/{vcname}/{retailerTypeId}")]
        [Route("rc/vc/{vcid}/{vcsubcatid}/{retailerTypeId}/{*vcname}")]
        public async Task<IActionResult> VirtualCategoryBasedRetailerType(int vcid, int vcsubcatid = -1, string vcname = "", int retailerTypeId = -1)
        {
            ViewBag.UrlType = $"/rc";
            ViewBag.UrlTypeId=$"/{retailerTypeId}";
            CategoryItemViewModel categoryItem = await _categoryBusinessService.GetVirtualCategory(vcid, vcsubcatid, vcname, retailerTypeId, "retailer_type_id");
            return View("../Catalog/CategoryListPage", categoryItem);
        }

        [Route("br/{brid}/{*brname}")]
        public async Task<IActionResult> BranchList(int brid, string brname = "")
        {
            ViewBag.UrlType = $"/br";
            ViewBag.UrlTypeId = $"/{brid}";
            string strlat = _httpContextAccessor.HttpContext.Session.GetString("CURSEARCHLAT");
            string strlng = _httpContextAccessor.HttpContext.Session.GetString("CURSEARCHLNG");
            double lat = 0, lng = 0;
            if (!String.IsNullOrEmpty(strlat) && !String.IsNullOrEmpty(strlng)) {
                try
                {
                    lat = Convert.ToDouble(strlat);
                    lng = Convert.ToDouble(strlng);
                }
                catch { }
            }

            var result = await _profileService.GetNearestBranches(lat, lng, defaultBranchId: brid);
            Store store = new Store();
            if(result != null && result.Data != null && result.Data.Data != null && result.Data.Data.Count > 0)
            {
                store =  result.Data.Data.FirstOrDefault();
            }
            return View("~/Views/Home/Store.cshtml", store);
        }


    }
}

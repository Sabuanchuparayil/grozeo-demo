using Microsoft.AspNetCore.DataProtection.KeyManagement;
using Microsoft.Extensions.Configuration;
using Retaline.Core.BusinessModel.API;
using Retaline.Core.BusinessModel.Brands;
using Retaline.Core.BusinessModel.Catalog;
using Retaline.Core.BusinessModel.Home;
using Retaline.Core.BusinessModel.Home.Advertisement;
using Retaline.Core.Services.HelperServices;
using Retaline.Core.ViewModel.Catalog;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Core.Services.Catalog
{
    public class CatalogService : ICatalogService
    {
        private readonly IHttpHelperService _httpHelperService;
        private readonly IConfiguration _configuration;
        private CatalogRoot _catchedCatalogRoot = null;
        private List<CategoryData> categoryListMenu = null;
        private List<CombinedCategoryData> _combinedcategoryListMenu = null;

        private List<CategoryData> _splitedFilteredCategories = null;

        private List<BusinessType> _businessTypes = null;
        private List<RetailType> _retailerTypes = null;
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

        public async Task<List<CategoryData>> GetCategoryMenuList()
        {
            //if (categoryListMenu == null)
            //{
            //    string url = _configuration["ApiUrls:Catalog:GetCategoryMenu"];
            //    var data = await _httpHelperService.Get<APIModel<List<CategoryData>>>(url);
            //    categoryListMenu = data.Data;
            //}
            //return categoryListMenu;
            return await FilteredCategories();
        }

        public async Task<List<CombinedCategoryData>> GetCategoryMenuListFiltered()
        {
            if (_combinedcategoryListMenu == null)
            {
                string url = _configuration["ApiUrls:Catalog:GetCategoryMenuNew"];
                try
                {
                    var data = await _httpHelperService.Get<APIModel<List<CombinedCategoryData>>>(url);
                    _combinedcategoryListMenu = data.Data;
                }
                catch { }
            }

            return _combinedcategoryListMenu;
        }

        public async Task<List<CategoryData>> HomeCategoriesMenu(int max)
        {
            List<CategoryData> homeMenuCategories = new List<CategoryData>();
            //List<CombinedCategoryData> _combinedcategories = await GetCategoryMenuListFiltered();
            var splitedCategories = await FilteredCategories();

            homeMenuCategories = splitedCategories.Where(c => c.IsHome == 1).OrderByDescending(c=> c.Categorylevel).OrderBy(c=> c.CategoryName).Take(max).ToList();
            if (homeMenuCategories.Count < max)
                homeMenuCategories.AddRange(
                    splitedCategories.Where(c => c.IsHome != 1 && c.IsInCategory == 1).OrderByDescending(c => c.Categorylevel).OrderBy(c => c.CategoryName).Take(max - homeMenuCategories.Count).ToList()
                    );

            if (homeMenuCategories.Count < max)
                homeMenuCategories.AddRange(
                    splitedCategories.Where(c => c.IsHome != 1 && c.IsInCategory != 1).OrderByDescending(c => c.Categorylevel).OrderBy(c => c.CategoryName).Take(max - homeMenuCategories.Count).ToList()
                    );

            return homeMenuCategories;
        }

        public async Task<List<CategoryData>> FilteredCategories()
        {
            if (_splitedFilteredCategories != null)
                return _splitedFilteredCategories;

            List<CombinedCategoryData> _combinedcategories = await GetCategoryMenuListFiltered();
            if (_combinedcategories == null || _combinedcategories.Count <=0) return new List<CategoryData>();

            List<CategoryData> categories = new List<CategoryData>();
            // Select virtual categories and sub categories
            try
            {
                categories = _combinedcategories.Select(c =>
                    new CategoryData
                    {
                        Id = c.CatId,
                        Categorylevel = (c.isVirtualCategory == 1 ? 4 : 3),
                        CategoryName = c.Catname,
                        ImageUrl = c.CatImg,
                        ImageThumbUrl = (c.isVirtualCategory == 1 ? c.ParentThumbImg : ""),
						IsVirtualCategory = c.isVirtualCategory,
                        DisplayOrder = c.DisplayOrder,
                        ParentCategoryId = c.ParentId,
                        IsHome = c.isHome,
                        IsInCategory = c.isInCategory,
                        Attributes = c.Attributes
                    }).OrderBy(c => c.CategoryName).ToList();
            }
            catch { categories = new List<CategoryData>(); }
            try
            {
                // select parent categories
                categories.AddRange(_combinedcategories.Where(c => c.isVirtualCategory == 0).Select(c =>
                        new CategoryData
                        {
                            Id = c.ParentId,
                            Categorylevel = 2,
                            CategoryName = c.ParentName,
                            ImageUrl = c.ParentImg,
                            ImageThumbUrl = c.ParentThumbImg,
                            IsVirtualCategory = 0,
                            DisplayOrder = c.DisplayOrder,
                            ParentCategoryId = c.DepartmenId,
                            IsHome = c.ParentIsHome,
                            IsInCategory = c.ParentIsInCategory
                            ,Attributes = c.Attributes
                        }).GroupBy(c => c.Id).Select(c => c.First()).OrderBy(c => c.CategoryName).ToList());
            }
            catch { }
            try
            {
                // select departments
                categories.AddRange(_combinedcategories.Where(c => c.isVirtualCategory == 0).Select(c =>
                        new CategoryData
                        {
                            Id = c.DepartmenId,
                            Categorylevel = 1,
                            CategoryName = c.DepartmentName,
                            ImageUrl = c.DepartmentImg,
                            ImageThumbUrl = c.DepartmentThumbImg,
                            IsVirtualCategory = c.isVirtualCategory,
                            DisplayOrder = c.DisplayOrder,
                            ParentCategoryId = 0,
                            IsHome = c.DepartmentIsHome,
                            IsInCategory = c.DepartmentIsInCategory,
                            Attributes = c.Attributes
                        }).GroupBy(c => c.Id).Select(c => c.First()).OrderBy(c => c.CategoryName).ToList());
            }
            catch { }
            _splitedFilteredCategories = categories;

            return _splitedFilteredCategories;
        }

        public async Task<List<CategoryData>> GetRelatedCategories(int catid, int catlevel, int filterId = -1, string filterKey = "")
        {
            string url = "";
            if (filterId==-1)
            {
                url = string.Format(_configuration["ApiUrls:Catalog:GetRelatedCategories"], catid, catlevel);
            }
            else
            {
                url = string.Format(_configuration["ApiUrls:Catalog:GetRelatedCategoriesBasedOnType"], catid, catlevel, filterId, filterKey);
            }
            var data = await _httpHelperService.Get<APIModel<List<CategoryData>>>(url);
            return data.Data;
        }

        public async Task<CataogoryHeaderMenuViewModel> GetCategoryHeaderMenuDetails()
        {
            CataogoryHeaderMenuViewModel details = new()
            {
                Categories = await GetCategoryMenuList()
            };
            //details.Categories = details.Categories.Where(item => item.Categorylevel == 1).ToList();
            //details.MenuIterations = details.Categories.Count / 9;
            //details.MenuIterations = details.Categories.Count % 9 > 0 ? details.MenuIterations + 1 : details.MenuIterations;
            return details;
        }

        public async Task<List<CategoryGroup>> GetCategoryGroupDetails()
        {
            List<CategoryGroup> details = new();
            string url = _configuration["ApiUrls:Catalog:CategoryByGroup"];
            var data = await _httpHelperService.Get<APIModel<List<CategoryGroup>>>(url);
            details = data.Data;
            return details;
        }

        public async Task<BrandMenuViewModel> GetBrandsForFooterMenu()
        {
            string url = _configuration["ApiUrls:Catalog:Brands"];
            var parameter = new Dictionary<string, string>()
                {
                    { "id", "7" }
                };
            var data = await _httpHelperService.Post<APIModel<List<Brand>>>(url, parameter);
            BrandMenuViewModel details = new();
            if (data != null)
            {
                details.MenuIterations = 2;
                details.Brands = data.Data;
            }
            return details;
        }

        public async Task<List<BusinessType>> GetBusinessTypes(int retailerId)
        {
            if (_businessTypes == null)
            {
                string url = string.Format(_configuration["ApiUrls:Home:BusinessTypes"], retailerId);
                var data = await _httpHelperService.Get<APIModel<List<BusinessType>>>(url);
                if (data != null)
                    _businessTypes = data.Data;
            }
            return _businessTypes;
        }

        public async Task<List<RetailType>> GetRetailerTypes(int businessTypeId = 1)
        {
            if (_retailerTypes == null)
            {
                string url = string.Format(_configuration["ApiUrls:Home:RetailerTypes"], businessTypeId);
                var data = await _httpHelperService.Get<APIModel<List<RetailType>>>(url);
                if(data != null && data.Data != null)
                    _retailerTypes = data.Data == null ? new List<RetailType>() : data.Data;
            }
            return _retailerTypes;
        }

        public async Task<List<AdZoneInfo>> GetSideBanner()
        {
            string url = _configuration["ApiUrls:Catalog:SideBannerSmall"];
            var data = await _httpHelperService.Get<APIModel<List<HomeValue>>>(url);
            if (data != null && data.Data != null && data.Data.Count > 0)
                return data.Data[0].AdZoneDetails;

            return default;

        }

    }
}

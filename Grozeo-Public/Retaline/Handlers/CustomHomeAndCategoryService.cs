using Retaline.Core.BusinessModel.Catalog;
using Retaline.Core.Services.Catalog;
using Retaline.Core.ViewModel.Catalog;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Handlers
{
    public class CustomHomeAndCategoryService : ICustomHomeAndCategoryService
    {
        private readonly IProductService _productService;
        private readonly ICatalogService _catalogService;

        public CustomHomeAndCategoryService(IProductService productService, ICatalogService catalogService)
        {
            _productService = productService;
            _catalogService = catalogService;
        }

        public async Task<CategoryItemViewModel> GetCategories(int parentCategoryId, int categoryId, int orginalSubCategoryId, string categoryName, int filterId, string filterKey, string attributeValues = "")
        {
            int subcat = orginalSubCategoryId;
            var catalog = _catalogService.GetCatalog().Result;
            CategoryProducts categoryProducts = new();
            CategoryItemViewModel categoryItem = new()
            {
                Categories = (catalog == null ? new List<CategoryData>(): catalog.Data??new List<CategoryData>()),
                ContentTypeId = 1,
                CategoryLevel = (orginalSubCategoryId > 0 ? 3 : (categoryId > 0 ? 2 : 1)),
                CurrentSubCatId = subcat,
                ParentCatId = parentCategoryId,
                CatId = categoryId,
                SubCatId = orginalSubCategoryId,
                CategoryName = categoryName
            };
            if (catalog != null && catalog.Data != null && catalog.Data.Count > 0)
            {
                if (parentCategoryId < 0)
                    parentCategoryId = catalog.Data[0].ParentCategoryId;

                if (orginalSubCategoryId < 0)
                {
                    subcat = categoryId < 0 ? parentCategoryId : categoryId;
                }
            }

            categoryItem.RelatedCategories = _catalogService.GetRelatedCategories((categoryItem.CategoryLevel == 3 ? orginalSubCategoryId : (categoryItem.CategoryLevel == 2 ? categoryId : parentCategoryId)), categoryItem.CategoryLevel, filterId, filterKey).Result;
            List<CategoryProducts> subcategoryProducts = await _productService.GetProductsByCategoyId(subcat, 1, -1, (categoryItem.CategoryLevel == 3 ? 0 : categoryItem.CategoryLevel), filterId, filterKey, attributeValues);
            PrepareSubCategory(categoryProducts, subcategoryProducts);
            PrepareCategoryItemViewModel(categoryProducts, categoryItem);
            return categoryItem;
        }



        public async Task<CategoryItemViewModel> GetCategoriesOnOffer(int page)
        {

            var catalog = _catalogService.GetCatalog().Result;
            CategoryProducts categoryProducts = new();
            CategoryItemViewModel categoryItem = new()
            {
                ContentTypeId = 2,
                BrandId = -2,
                IsOffersView = true,
                CurrentSubCatId = -1,
                Categories = catalog.Data,
                CurrentPage=page
            };

            categoryProducts = await _productService.GetOffers(page);
            PrepareCategoryItemViewModel(categoryProducts, categoryItem);
            return categoryItem;
        }

        public async Task<CategoryItemViewModel> GetCategoriesOnOffer(int page, int businesstypeid = -1, int retailtypeid = -1, string offerType = "", int offerVal = -1)
        {

            var catalog = _catalogService.GetCatalog().Result;
            CategoryProducts categoryProducts = new();
            CategoryItemViewModel categoryItem = new()
            {
                ContentTypeId = 2,
                BrandId = -2,
                IsOffersView = true,
                CurrentSubCatId = -1,
                Categories = catalog.Data,
                CurrentPage = page, 
                BusinessTypeId=businesstypeid, RetailTypeId = retailtypeid
            };

            categoryProducts = await _productService.GetOffers(page, businesstypeid, retailtypeid, offerType, offerVal);
            PrepareCategoryItemViewModel(categoryProducts, categoryItem);
            return categoryItem;
        }

        public async Task<CategoryItemViewModel> GetBrandCategories(int brandid, string categoryName)
        {

            var catalog = _catalogService.GetCatalog().Result;
            CategoryItemViewModel categoryItem = new()
            {
                ContentTypeId = 3,
                CurrentSubCatId = -1,
                BrandId = brandid,
                Categories = catalog.Data,
                IsBrandView = true,
                CategoryName = categoryName
            };
            CategoryProducts categoryProducts = await _productService.GetBrandProducts(brandid, 1);
            if (categoryProducts != null && categoryProducts.Products2 != null && categoryProducts.Products2.Count > 0 && (categoryProducts.Products == null || categoryProducts.Products2.Count > 0))
                categoryProducts.Products = categoryProducts.Products2;

            categoryItem.BrandName = (categoryProducts != null && categoryProducts.Products != null && categoryProducts.Products.Count > 0) ? categoryProducts.Products[0].BrandName : categoryName;
            PrepareCategoryItemViewModel(categoryProducts, categoryItem);
            return categoryItem;
        }

        public async Task<CategoryItemViewModel> GetVirtualCategory(int virtualCategoryId, int virtualSubCategoryId, string virtualCategoryName, int filterId, string filterKey, int vcparentid=-1)
        {
            var catalog = _catalogService.GetCatalog().Result;
            CategoryProducts categoryProducts = new();
            CategoryItemViewModel categoryItem = new()
            {
                IsVirtualCategory = true,
                VirtualCategoryId = virtualCategoryId,
                ContentTypeId = 6,
                Categories = catalog.Data,
                CategoryLevel = 2,
                CurrentSubCatId = virtualSubCategoryId,
                GroupId = virtualCategoryId
            };
            if(vcparentid > 0)
                categoryItem.RelatedCategories = await _catalogService.GetRelatedCategories(vcparentid, 2, filterId, filterKey);
            else
                categoryItem.RelatedCategories = await _catalogService.GetRelatedCategories(virtualCategoryId, 4, filterId, filterKey);
            List<CategoryProducts> subcategoryProducts = await _productService.GetProductsByCategoyId(virtualSubCategoryId, 1, virtualCategoryId, filterId: filterId, filterKey: filterKey);
            PrepareSubCategory(categoryProducts, subcategoryProducts);
            PrepareCategoryItemViewModel(categoryProducts, categoryItem);
            return categoryItem;
        }


        public async Task<CategoryItemViewModel> Search(string searchkey, int searchFilterId = -1, string searchFilterKey = "")
        {
            var catalog = await _catalogService.GetCatalog();
            CategoryItemViewModel categoryItem = new()
            {
                ContentTypeId = 4,
                Categories = catalog.Data,
                CategoryName = searchkey
            };
            CategoryProducts categoryProducts = await _productService.SearchProducts(searchkey, 1, searchFilterId, searchFilterKey);
            PrepareCategoryItemViewModel(categoryProducts, categoryItem);
            return categoryItem;
        }

        public async Task<CategoryItemViewModel> SearchByConcer(int concernid, string searchkey)
        {
            var catalog = await _catalogService.GetCatalog();
            CategoryItemViewModel categoryItem = new()
            {
                ContentTypeId = 4,
                Categories = catalog.Data,
                CategoryName = $"Disease: {searchkey}"
            };
            CategoryProducts categoryProducts = await _productService.SearchByConcer(concernid, 1);
            PrepareCategoryItemViewModel(categoryProducts, categoryItem);
            return categoryItem;
        }

        public async Task<CategoryItemViewModel> SearchGroupProducts(int groupid, string searchkey)
        {
            var catalog = await _catalogService.GetCatalog();
            CategoryItemViewModel categoryItem = new()
            {
                ContentTypeId = 5,
                Categories = catalog.Data,
                CategoryName = searchkey
            };
            CategoryProducts categoryProducts = await _productService.SearchGroupProducts(groupid);
            PrepareCategoryItemViewModel(categoryProducts, categoryItem);
            return categoryItem;
        }


        private static void PrepareCategoryItemViewModel(CategoryProducts categoryProducts, CategoryItemViewModel categoryItem)
        {
            if (categoryProducts != null)
            {
                categoryItem.Products = categoryProducts.Products;
                categoryItem.CurrentPage = categoryProducts.CurrentPage;
                categoryItem.FirstPageUrl = categoryProducts.FirstPageUrl;
                categoryItem.From = categoryProducts.From;
                categoryItem.LastPage = categoryProducts.LastPage;
                categoryItem.LastPageUrl = categoryProducts.LastPageUrl;
                categoryItem.NextPageUrl = categoryProducts.NextPageUrl;
                categoryItem.Path = categoryProducts.Path;
                categoryItem.PerPage = categoryProducts.PerPage;
                categoryItem.PerPageUrl = categoryProducts.PerPageUrl;
                categoryItem.To = categoryProducts.To;
                categoryItem.Total = categoryProducts.Total;
            }
        }

        private static void PrepareSubCategory(CategoryProducts categoryProducts, List<CategoryProducts> subcategoryProducts)
        {
            if (subcategoryProducts != null && subcategoryProducts.Count > 0)
            {
                var subCategory = subcategoryProducts.FirstOrDefault();
                categoryProducts.Products = subCategory.Products2;
                categoryProducts.CurrentPage = subCategory.Pagination.CurrentPage;
                categoryProducts.FirstPageUrl = subCategory.Pagination.FirstPageUrl;
                categoryProducts.From = subCategory.Pagination.From;
                categoryProducts.LastPage = subCategory.Pagination.LastPage;
                categoryProducts.LastPageUrl = subCategory.Pagination.LastPageUrl;
                categoryProducts.NextPageUrl = subCategory.Pagination.NextPageUrl;
                categoryProducts.Path = subCategory.Pagination.Path;
                categoryProducts.PerPage = subCategory.Pagination.PerPage;
                categoryProducts.Total = subCategory.Pagination.Total;
                categoryProducts.To = subCategory.Pagination.To;
            }
        }
    }
}

using Retaline.Core.BusinessModel.Catalog;
using System.Collections.Generic;

namespace Retaline.Core.ViewModel.Catalog
{

    public class CategoryItemViewModel
    {
        //   int pcatid = -1, int ccatid = -1, int scatid = -1, string catname = "", int brandid = -1, int advtid = -1
        public bool IsOffersView { get; set; }
        public bool IsBrandView { get; set; }
        public string BrandName { get; set; }
        public int BrandId { get; set; }
        public int ParentCatId { get; set; } = -1;
        public int CatId { get; set; } = -1;
        public int SubCatId { get; set; } = -1; 
        public int CurrentSubCatId { get; set; }
        public int CategoryLevel { get; set; }
        public string CategoryName { get; set; }
        public int? CurrentPage { get; set; }
        public string FirstPageUrl { get; set; }
        public int? From { get; set; }
        public int? LastPage { get; set; }
        public string LastPageUrl { get; set; }
        public string NextPageUrl { get; set; }
        public string Path { get; set; }
        public int? PerPage { get; set; }
        public string PerPageUrl { get; set; }
        public int? To { get; set; }
        public int? Total { get; set; }
        public int ContentTypeId { get; set; }
        public List<Retaline.Core.BusinessModel.Catalog.Product> Products { get; set; }
        public List<CategoryData> Categories { get; set; }
        public List<CategoryData> RelatedCategories { get; set; }
        public List<SubcategoryMaster> Subcategories { get; set; }
        public int GroupId { get; set; }
        public bool IsVirtualCategory { get; set; }
        public int VirtualCategoryId { get; set; }
        public int BusinessTypeId { get; set; }
        public int RetailTypeId { get; set; }
        public int AutoLoadCount { get; set; } = 2;
        public string OfferType { get; set; }
        public int OfferTypeValue { get; set; }
        public int BranchId { get; set; } = -1;

    }
}

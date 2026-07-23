using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Catalog
{
    public class CategoryData
    {
        [JsonPropertyName("id")]
        public int Id { get; set; }
        [JsonPropertyName("parent_category_id")]
        public int ParentCategoryId { get; set; }

        [JsonPropertyName("category_id")]
        public int CategoryId { get; set; }

        //[JsonPropertyName("category_name")]
        [JsonPropertyName("parent_category")]
        public string CategoryName { get; set; }
        //[JsonPropertyName("parent_category")]
        //public string ParentCategoryname { get; set; }

        [JsonPropertyName("status")]
        public string Status { get; set; }

        [JsonPropertyName("image_url")]
        public string ImageUrl { get; set; }

		public string ImageThumbUrl { get; set; }
		[JsonPropertyName("subcategories")]
        public List<SubcategoryMaster> Subcategories { get; set; } = new List<SubcategoryMaster>();
        [JsonPropertyName("isVirtualCategory")]
        public int IsVirtualCategory { get; set; }
        [JsonPropertyName("displayOrder")]
        public int? DisplayOrder {  get; set; }
        [JsonPropertyName("cattype")]
        public int Categorylevel { get; set; }
        [JsonPropertyName("isHome")]
        public int? IsHome { get; set; }
        [JsonPropertyName("isInCategory")]
        public int? IsInCategory { get; set; }
        [JsonPropertyName("attributes")]
        public List<Attribute> Attributes { get; set; }
    }
}
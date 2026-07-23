using Retaline.Core.BusinessModel.Catalog;
using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Home
{
    public class HomeValue
    {
        [JsonPropertyName("fsi_uid")]
        public int FsiUid { get; set; }

        [JsonPropertyName("item_group_id")]
        public int ItemGroupId { get; set; }

        [JsonPropertyName("item_name")]
        public string ItemName { get; set; }

        [JsonPropertyName("brand_name")]
        public string BrandName { get; set; }

        [JsonPropertyName("category_id")]
        public int CategoryId { get; set; }

        [JsonPropertyName("category_name")]
        public string CategoryName { get; set; }

        [JsonPropertyName("variant")]
        public string Variant { get; set; }

        [JsonPropertyName("item_master")]
        public List<ItemMaster> ItemMaster { get; set; }

        [JsonPropertyName("image")]
        public string Image { get; set; }

		[JsonPropertyName("thumb_url")]
		public string ThumbImage { get; set; }

		[JsonPropertyName("description")]
        public string Description { get; set; }

        [JsonPropertyName("value")]
        public List<HomeValue> Value { get; set; }

        [JsonPropertyName("id")]
        public int? Id { get; set; }

        [JsonPropertyName("parent_category_id")]
        public int? ParentCategoryId { get; set; }

        [JsonPropertyName("parent_category")]
        public string ParentCategoryName { get; set; }

        [JsonPropertyName("image_url")]
        public string ImageUrl { get; set; }

        [JsonPropertyName("status")]
        public string Status { get; set; }

        [JsonPropertyName("subcategories")]
        public List<SubcategoryMaster> Subcategories { get; set; }

        [JsonPropertyName("brand_id")]
        public int? BrandId { get; set; }

        [JsonPropertyName("img_name")]
        public object ImgName { get; set; }
        [JsonPropertyName("img_url")]
        public string BrandImageUrl { get; set; }

        [JsonPropertyName("top_brand")]
        public int? TopBrand { get; set; }

        [JsonPropertyName("brand_status")]
        public int? BrandStatus { get; set; }

        [JsonPropertyName("min_count")]
        public int? MinCount { get; set; }

        [JsonPropertyName("total_count")]
        public int? TotalCount { get; set; }

        [JsonPropertyName("adzone_id")]
        public int? AdZoneId { get; set; }
        [JsonPropertyName("adzone_name")]
        public string AdZoneName { get; set; }
        [JsonPropertyName("adzone_type")]
        public string AdZoneType { get; set; }
        [JsonPropertyName("adzone_details")]
        public List<Advertisement.AdZoneInfo> AdZoneDetails { get; set; }

        [JsonPropertyName("disease_id")]
        public int DiseaseId { get; set; }
        [JsonPropertyName("disease_name")]
        public string DiseaseName { get; set; }
        [JsonPropertyName("disease_description")]
        public string DiseaseDesc { get; set; }
        [JsonPropertyName("disease_image")]
        public string DiseaseImage { get; set; }
        [JsonPropertyName("cattype")]
        public int? CategoryType { get; set; }
        [JsonPropertyName("pid")]
        public int? ParentId { get; set; }
        [JsonPropertyName("pcid")]
        public int? ParentCarId { get; set; }
        [JsonPropertyName("IsItemGroup")]
        public int? IsGroupItem { get; set; }
        [JsonPropertyName("groupDisplayName")]
        public string GroupDisplayName { get; set; }
        [JsonPropertyName("iteamGroupImage")]
        public string GroupImage { get; set; }
        [JsonPropertyName("groupId")]
        public int GroupId { get; set; }
        [JsonPropertyName("isHome")]
        public int IsHome { get; set; }
        [JsonPropertyName("isInCategory")]
        public int IsInCategory { get; set; }
        [JsonPropertyName("isPreferred")]
        public int? isPreferred { get; set; }
        [JsonPropertyName("displayOrder")]
        public int? DisplayOrder {  get; set; }
        public HomeValue()
        {
            Subcategories = new List<SubcategoryMaster>();
            Value = new List<HomeValue>();
        }

    }
}

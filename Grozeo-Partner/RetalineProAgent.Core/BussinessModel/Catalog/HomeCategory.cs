using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.Catalog
{
    public class HomeCategory
	{
		[JsonPropertyName("category_id")]
        public int CategoryId { get; set; }
		[JsonPropertyName("category_name")]
		public string CategoryName { get; set; }
		[JsonPropertyName("image_url")]
		public string ImageUrl { get; set; }
		[JsonPropertyName("banner_image_url")]
		public string BannerImageUrl { get; set; }
		[JsonPropertyName("parent_category")]
		public int ParentCategoryId { get; set; }
		[JsonPropertyName("status")]
		public string StatusId { get; set; }
		[JsonPropertyName("pdt_count")]
		public int PdtCount { get; set; }
		[JsonPropertyName("level")]
		public string Level { get; set; }
		[JsonPropertyName("isSubProduct")]
		public bool IsSubProduct { get; set; }
		//[JsonPropertyName("subcategory")]
		//public int CategoryId { get; set; }

	}
}

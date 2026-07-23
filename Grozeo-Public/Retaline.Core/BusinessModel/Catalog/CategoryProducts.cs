using System.Collections.Generic;
using System.Text.Json.Serialization;

namespace Retaline.Core.BusinessModel.Catalog
{
    public class CategoryProducts
    {
        [JsonPropertyName("current_page")]
        public int? CurrentPage { get; set; }
        [JsonPropertyName("first_page_url")]
        public string FirstPageUrl { get; set; }
        [JsonPropertyName("from")]
        public int? From { get; set; }
        [JsonPropertyName("last_page")]
        public int? LastPage { get; set; }
        [JsonPropertyName("last_page_url")]
        public string LastPageUrl { get; set; }
        [JsonPropertyName("next_page_url")]
        public string NextPageUrl { get; set; }
        [JsonPropertyName("path")]
        public string Path { get; set; }
        [JsonPropertyName("per_page")]
        public int? PerPage { get; set; }
        [JsonPropertyName("prev_page_url")]
        public string PerPageUrl { get; set; }
        [JsonPropertyName("to")]
        public int? To { get; set; }
        [JsonPropertyName("total")]
        public int? Total { get; set; }
        [JsonPropertyName("data")]
        public List<Product> Products { get; set; } = new List<Product>();

        /// <summary>
        /// API is not optimized so that some of the API is having products rendered as 'data' while others are with 'value'
        /// Products2 is for brands page since the data object is null here and actual data is under 'value' in API.
        /// </summary>
        [JsonPropertyName("value")]
        public List<Product> Products2 { get; set; } = new List<Product>();

        /// <summary>
        /// API is rendering Pagination in a seperate object, hense adding the pagination object to match with the API format.
        /// </summary>
        [JsonPropertyName("pagenate_details")]
        public PaginationInfo Pagination { get; set; } = new PaginationInfo();
    }
}

using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.Catalog
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
        public List<Product> Products { get; set; }
    }
}

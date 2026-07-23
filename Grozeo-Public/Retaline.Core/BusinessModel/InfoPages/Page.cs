using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.InfoPages
{
    public class Page
    {
        [JsonPropertyName("page_id")]
        public int Id { get; set; }
        [JsonPropertyName("page_name")]
        public string Name { get; set; }
        [JsonPropertyName("page_slug")]//: "privacy-policy",
        public string Slug { get; set; }
        [JsonPropertyName("page_content")]
        public string Content { get; set; }
        [JsonPropertyName("page_status")]
        public int Status { get; set; }
        [JsonPropertyName("page_type")]
        public int? TypeId { get; set; }

    }
}

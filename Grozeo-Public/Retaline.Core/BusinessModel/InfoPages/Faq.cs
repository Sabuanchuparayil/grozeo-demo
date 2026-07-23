using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.InfoPages
{
    public class Faq
    {
        [JsonPropertyName("faq")]
        public List<FaqContent> Content { get; set; }
    }
    public class FaqContent
    {
        [JsonPropertyName("faq_id")]
        public int Id { get; set; }
        [JsonPropertyName("faq_title")]
        public string Title { get; set; }
        [JsonPropertyName("faq_description")]
        public string Description { get; set; }
        [JsonPropertyName("faq_status")]
        public int StatusId { get; set; }

    }

}

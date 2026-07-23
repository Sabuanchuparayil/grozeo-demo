using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Order
{
    public class Style
    {
        [JsonPropertyName("label")]
        public string Label { get; set; }
        [JsonPropertyName("value")]
        public string Value { get; set; }
        [JsonPropertyName("color_code")]
        public string ColorCode { get; set; }
        [JsonPropertyName("is_bold")]
        public bool? IsBold { get; set; }
        [JsonPropertyName("is_italics")]
        public bool? IsItalics { get; set; }
        [JsonPropertyName("order")]
        public int? Order { get; set; }
    }
}

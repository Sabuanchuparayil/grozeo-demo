using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Catalog
{
    public class CombinedCategoryData
    {
        [JsonPropertyName("sub_category_id")]
        public int CatId { get; set;}
        [JsonPropertyName("sub_category")]
        public string Catname { get; set;}
        [JsonPropertyName("sub_category_image")]
        public string CatImg { get; set;}
        //[JsonPropertyName("STATUS")]
        //public int Status { get; set;}
        [JsonPropertyName("isHome")]
        public int isHome { get; set;}
        [JsonPropertyName("isInCategory")]
        public int isInCategory { get; set;}
        [JsonPropertyName("isVirtualCategory")]
        public int isVirtualCategory{ get; set;}
        [JsonPropertyName("displayOrder")]
        public int? DisplayOrder{ get; set;}
        [JsonPropertyName("parent_id")]
        public int ParentId{ get; set;}
        [JsonPropertyName("parent_name")]
        public string ParentName { get; set;}
        [JsonPropertyName("parent_image")]
        public string ParentImg { get; set;}
		[JsonPropertyName("parent_thumb_url")]
		public string ParentThumbImg { get; set; }

		[JsonPropertyName("parent_isHome")]
        public int ParentIsHome { get; set;}
        [JsonPropertyName("parent_isInCategory")]
        public int ParentIsInCategory { get; set;}
        [JsonPropertyName("dept_id")]
        public int DepartmenId{ get; set;}
        [JsonPropertyName("dept_name")]
        public string DepartmentName { get; set;}
        [JsonPropertyName("dept_img")]
        public string DepartmentImg{ get; set;}
		[JsonPropertyName("dept_thumb_url")]
		public string DepartmentThumbImg { get; set; }
		[JsonPropertyName("dept_isHome")]
        public int DepartmentIsHome{ get; set;}
        [JsonPropertyName("dept_isInCategory")]
        public int DepartmentIsInCategory{ get; set; }
        [JsonPropertyName("attributes")]
        [JsonConverter(typeof(AttributesListConverter))]
        public List<Attribute> Attributes { get; set;}

    }

    public class Attribute
    {
        [JsonPropertyName("id")]
        public int Id { get; set;}
        [JsonPropertyName("name")]
        public string Name { get; set;}
        [JsonPropertyName("values")]
        public List<AttributeValue> Values { get; set;}
    }
    public class AttributeValue
    {
        [JsonPropertyName("id")]
        public int Id { get; set;}
        [JsonPropertyName("value")]
        public string Value { get; set;}

    }


    public class AttributesListConverter : JsonConverter<List<Attribute>>
    {
        public override List<Attribute> Read(ref Utf8JsonReader reader, Type typeToConvert, JsonSerializerOptions options)
        {
            // Check if the value is an empty string
            if (reader.TokenType == JsonTokenType.String && reader.GetString() == "")
            {
                return new List<Attribute>(); //Array.Empty<principalPlaceAddress>();
            }

            // Use the default deserialization for arrays
            return JsonSerializer.Deserialize<List<Attribute>>(ref reader, options);
        }

        public override void Write(Utf8JsonWriter writer, List<Attribute> value, JsonSerializerOptions options)
        {
            // Use the default serialization for arrays
            JsonSerializer.Serialize(writer, value, options);
        }
    }


}

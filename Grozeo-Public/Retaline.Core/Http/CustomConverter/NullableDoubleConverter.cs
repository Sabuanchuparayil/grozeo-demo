using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.Http.CustomConverter
{
	public class NullableDoubleConverter : JsonConverter<double?>
	{
		public override double? Read(ref Utf8JsonReader reader, Type typeToConvert, JsonSerializerOptions options)
		{
			if (reader.TokenType == JsonTokenType.Null)
				return null;

			if (reader.TokenType == JsonTokenType.Number)
				return reader.GetDouble();

			if (reader.TokenType == JsonTokenType.String && reader.GetString() == "null")
				return null;

			throw new JsonException();
		}

		public override void Write(Utf8JsonWriter writer, double? value, JsonSerializerOptions options)
		{
			if (value.HasValue)
				writer.WriteNumberValue(value.Value);
			else
				writer.WriteNullValue();
		}
	}
}

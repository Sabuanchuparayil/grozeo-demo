using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.BusinessModel.Home.Advertisement
{
    public class AdZoneInfo
	{
		[JsonPropertyName("adv_id")]
		public int AdvId { get; set; }
		[JsonPropertyName("adv_title")]
		public string AdvTitle { get; set; }
		[JsonPropertyName("adv_imageurl")]
		public string AdvImageUrl { get; set; }
		[JsonPropertyName("adv_offer")]
		public string AdvOffer { get; set; }
		[JsonPropertyName("adv_offerValueId")]
		public double? AdvOfferValue { get; set; }
		[JsonPropertyName("adzone_id")]
		public int AdvZoneId { get; set; }
		[JsonPropertyName("details")]
		public AdvZoneDetails AdvAdditionalInfo { get; set; }
		[JsonPropertyName("storegroup_id")]
		public int StoreGroupId { get; set; }
		[JsonPropertyName("adv_offerType")]
		public string OfferType { get; set; }
        [JsonPropertyName("imageUrl_1")]
        public string AdvMobileImageUrl { get; set; }
        [JsonPropertyName("imageUrl_2")]
        public string AdvSmallImageUrl { get; set; }
    }

	public class AdvZoneDetails
	{
		[JsonPropertyName("item_group")]
		public int AdvZoneGroup { get; set; }
		[JsonPropertyName("possible_keys")]
		public int AdvZonePossibleKeys { get; set; }
		[JsonPropertyName("item")]
		public int AdvZoneItemId { get; set; }
		[JsonPropertyName("isMedicine")]
		public int AdvZoneIsMedicine { get; set; }
	}
}

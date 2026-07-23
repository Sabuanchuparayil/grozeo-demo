using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace Retaline.Core.ViewModel.Address
{
    public class AddressViewModel
    {
        [JsonPropertyName("deli_delivery_pin")]
        public string DeliDeliveryPin { get; set; }
        [JsonPropertyName("deli_house_no")]
        public string DeliHouseNumber { get; set; }

        [JsonPropertyName("deli_house_name")]
        public string DeliHouseName { get; set; }

        [JsonPropertyName("deli_land_mark")]
        public string DeliLandMark { get; set; }

        [JsonPropertyName("deli_district")]
        public string DeliDistrict { get; set; }
        [JsonPropertyName("deli_city")]
        public string DeliCity { get; set; }
        [JsonPropertyName("deli_state")]
        public string DeliState { get; set; }

        [JsonPropertyName("deli_contact_no")]
        public string DeliContactNo { get; set; }
        [JsonPropertyName("deli_name")]
        public string DeliName { get; set; }
        [JsonPropertyName("deli_post")]
        public string DeliPost { get; set; }

        [JsonPropertyName("deli_type")]
        public string DeliType { get; set; }

        [JsonPropertyName("deli_latitude")]
        public double DeliLatitude { get; set; }

        [JsonPropertyName("deli_longitude")]
        public double DeliLongitude { get; set; }

        [JsonPropertyName("deli_google_pin")]
        public string DeliGooglePin { get; set; }

        [JsonPropertyName("deli_address")]
        public string DeliAddress { get; set; }
        [JsonPropertyName("deli_address2")]
        public string DeliAddress2 { get; set; }

        [JsonPropertyName("deli_google_address")]
        public string DeliGoogleAddress { get; set; }
		//[JsonPropertyName("deli_is_primary")]
		//public int IsPrimary { get; set; }
		//[JsonPropertyName("branch_group")]
		//public int? BranchGroup { get; set; }
		[JsonPropertyName("deli_email")]
		public string DeliEmail { get; set; }
		public string signupEmail { get; set; }
        public string signupName { get; set; }
        public string password {  get; set; }
    }
}

using Newtonsoft.Json;
using Newtonsoft.Json.Linq;

using RetalineProAgent.Core.Services.HelperServices;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Linq;
using System.Net;


namespace RetalineProAgent.Core.Services.GST
{
	[Serializable]
	public class GSTValidationResult
	{
		public bool IsValid { get; set; }
		public string TaxPayerType { get; set; }
		public string LegalName { get; set; }
		public string TradeName { get; set; }
		public string ConstitutionOfBusiness { get; set; }
		public string Email { get; set; }
		public string Mobile { get; set; }
		public string Address { get; set; }
		public string GSTIN { get; set; }
		public string GSTINStatus { get; set; }
		public string RawResponse { get; set; }
		public string State { get; set; }
	}

	public class GSTValidatorService
	{
		public GSTValidationResult ValidateGST(string gstNumber)
		{
			try
			{
				var apiResponse = GetGSTDataFromAPI(gstNumber);
				return ParseGSTResponse(apiResponse);
			}
			catch (Exception ex)
			{
				return new GSTValidationResult { IsValid = false };
			}
		}

		private JObject GetGSTDataFromAPI(string gstNumber)
		{
			try
			{
				string emptraAPIClientID = ConfigurationSettings.AppSettings.Get("emptra.clientid");
				string emptraAPISecret = ConfigurationSettings.AppSettings.Get("emptra.secret");
				string APIUrl = "https://api.emptra.com/gstinDetailSearch";

				//List<KeyValuePair<string, string>> data = new List<KeyValuePair<string, string>>();
				//data.Add(new KeyValuePair<string, string>("gstin", gstNumber));
				var data = new { gstin = gstNumber };

				List<KeyValuePair<string, string>> headers = new List<KeyValuePair<string, string>>();
				headers.Add(new KeyValuePair<string, string>("clientId", emptraAPIClientID)); //"bdd9215673be7ff7740ae648a7cfdcb4:574ab7f003510e494abe4da72a66fef8"));
				headers.Add(new KeyValuePair<string, string>("secretKey", emptraAPISecret)); //"8STGIWr7xIXyeCuRJ09ez8yOtXj9LKuNsi5Kxu5ppf2poKjgDwyNX05ASBYRURmTh"));

				//var responseJson = HttpHelperService.Post<string>(APIUrl, data, 0, "", headers);

				// Make API call and return as JObject
				var responseJson = MakeHttpPostRequest(APIUrl, data, headers);
				return JObject.Parse(responseJson);
			}
			catch
			{
				return null;
			}
		}

		private string MakeHttpPostRequest(string url, object data, List<KeyValuePair<string, string>> headers)
		{
			using (var client = new WebClient())
			{
				client.Headers[HttpRequestHeader.ContentType] = "application/json";

				// Add custom headers
				foreach (var header in headers)
				{
					client.Headers[header.Key] = header.Value;
				}

				// Use Newtonsoft.Json for serialization
				var jsonData = JsonConvert.SerializeObject(data);
				var response = client.UploadString(url, "POST", jsonData);
				return response;
			}
		}

		private GSTValidationResult ParseGSTResponse(JObject apiResponse)
		{
			string strRawResponse = ""; try { strRawResponse = apiResponse.ToString(); } catch { }
			var result = new GSTValidationResult { RawResponse = strRawResponse };

			try
			{
				// Check if API call was successful
				if (apiResponse == null || apiResponse["code"]?.Value<int>() != 100)
				{
					result.IsValid = false;
					return result;
				}

				var resultNode = apiResponse["result"]?["result"];
				if (resultNode == null)
				{
					result.IsValid = false;
					return result;
				}

				var gstnDetailed = resultNode["gstnDetailed"];

				// Extract basic information
				result.GSTIN = GetSafeString(apiResponse["result"]?["essentials"]?["gstin"]);
				try{result.TaxPayerType = GetSafeString(gstnDetailed?["taxPayerType"]); } catch { }
				try{result.LegalName = GetSafeString(gstnDetailed?["legalNameOfBusiness"]); } catch { }
				try{
					result.TradeName = GetSafeString(gstnDetailed?["tradeNameOfBusiness"]);
					if(string.IsNullOrEmpty(result.TradeName) && !string.IsNullOrEmpty(result.LegalName))
						result.TradeName = result.LegalName;
				} catch { }

				try{result.ConstitutionOfBusiness = GetSafeString(gstnDetailed?["constitutionOfBusiness"]); } catch { }
				try{result.GSTINStatus = GetSafeString(gstnDetailed?["gstinStatus"]); } catch { }

				// Extract contact information
				try{result.Email = ExtractEmail(gstnDetailed); } catch { }
				try{result.Mobile = ExtractMobile(gstnDetailed); } catch { }
				try{result.Address = ExtractAddress(gstnDetailed); } catch { }
				try{result.State = ExtractState(gstnDetailed); } catch { }

				// Validation logic
				try
				{
					result.IsValid = !string.IsNullOrEmpty(result.TaxPayerType) &&
									!string.IsNullOrEmpty(result.LegalName) &&
									result.GSTINStatus?.ToUpper() == "ACTIVE";
				}
				catch { }

				return result;
			}
			catch (Exception ex)
			{
				result.IsValid = false;
				return result;
			}
		}

		private string GetSafeString(JToken token)
		{
			if (token == null) return string.Empty;

			try
			{
				switch (token.Type)
				{
					case JTokenType.String:
						return token.Value<string>()?.Trim() ?? string.Empty;
					case JTokenType.Array:
						return string.Join(", ", token.Select(t => GetSafeString(t)));
					case JTokenType.Boolean:
						return token.Value<bool>() ? "true" : "false";
					case JTokenType.Integer:
					case JTokenType.Float:
						return token.ToString();
					case JTokenType.Null:
					case JTokenType.Undefined:
						return string.Empty;
					default:
						return token.ToString()?.Trim() ?? string.Empty;
				}
			}
			catch
			{
				return string.Empty;
			}
		}

		private string ExtractEmail(JToken gstnDetailed)
		{
			try
			{
				// First try principal place address
				var principalEmail = GetSafeString(gstnDetailed?["principalPlaceAddress"]?["emailId"]);
				if (!string.IsNullOrEmpty(principalEmail)) return principalEmail;

				// Then try additional place addresses
				var additionalAddresses = gstnDetailed?["additionalPlaceAddress"];
				if (additionalAddresses != null && additionalAddresses.Type == JTokenType.Array)
				{
					foreach (var address in additionalAddresses)
					{
						var email = GetSafeString(address?["emailId"]);
						if (!string.IsNullOrEmpty(email)) return email;
					}
				}

				return string.Empty;
			}
			catch
			{
				return string.Empty;
			}
		}

		private string ExtractMobile(JToken gstnDetailed)
		{
			try
			{
				// First try principal place address
				var principalMobile = GetSafeString(gstnDetailed?["principalPlaceAddress"]?["mobile"]);
				if (!string.IsNullOrEmpty(principalMobile)) return principalMobile;

				// Then try additional place addresses
				var additionalAddresses = gstnDetailed?["additionalPlaceAddress"];
				if (additionalAddresses != null && additionalAddresses.Type == JTokenType.Array)
				{
					foreach (var address in additionalAddresses)
					{
						var mobile = GetSafeString(address?["mobile"]);
						if (!string.IsNullOrEmpty(mobile)) return mobile;
					}
				}

				return string.Empty;
			}
			catch
			{
				return string.Empty;
			}
		}

		private string ExtractAddress(JToken gstnDetailed)
		{
			try
			{
				// First try principal place address
				var principalAddress = GetSafeString(gstnDetailed?["principalPlaceAddress"]?["address"]);
				if (!string.IsNullOrEmpty(principalAddress)) return principalAddress;

				// If address is empty, try to construct from splitAddress
				var splitAddress = gstnDetailed?["principalPlaceAddress"]?["splitAddress"];
				if (splitAddress != null)
				{
					var addressLine = GetSafeString(splitAddress?["addressLine"]);
					var city = GetSafeString(splitAddress?["city"]);
					var state = GetSafeString(splitAddress?["state"]);
					var pincode = GetSafeString(splitAddress?["pincode"]);

					if (!string.IsNullOrEmpty(addressLine))
					{
						return $"{addressLine}, {city}, {state} - {pincode}".Trim();
					}
				}

				// Try additional place addresses
				var additionalAddresses = gstnDetailed?["additionalPlaceAddress"];
				if (additionalAddresses != null && additionalAddresses.Type == JTokenType.Array)
				{
					foreach (var address in additionalAddresses)
					{
						var addr = GetSafeString(address?["address"]);
						if (!string.IsNullOrEmpty(addr)) return addr;
					}
				}

				// Fallback: construct from individual components
				var street = GetSafeString(gstnDetailed?["principalPlaceStreet"]);
				var locality = GetSafeString(gstnDetailed?["principalPlaceLocality"]);
				var city2 = GetSafeString(gstnDetailed?["principalPlaceCity"]);
				var state2 = GetSafeString(gstnDetailed?["principalPlaceState"]);
				var pincode2 = GetSafeString(gstnDetailed?["principalPlacePincode"]);

				return $"{street} {locality}, {city2}, {state2} - {pincode2}".Trim();
			}
			catch
			{
				return string.Empty;
			}
		}

		private string ExtractState(JToken gstnDetailed)
		{
			try
			{
				try
				{
					// If address is empty, try to construct from splitAddress
					var splitAddress = gstnDetailed?["principalPlaceAddress"]?["splitAddress"];
					if (splitAddress != null)
					{
						var state = GetSafeString(splitAddress?["state"]);
						if (!string.IsNullOrEmpty(state))
							return state.Trim();
					}
				}
				catch { }

				// Try additional place addresses
				var additionalPlaceState = gstnDetailed?["additionalPlaceState"];
				try
				{
					if (additionalPlaceState != null)
					{
						var state = GetSafeString(additionalPlaceState);
						if (!string.IsNullOrEmpty(state))
							return state.Trim();
					}
				}
				catch { }

				// Fallback: construct from individual components
				var state2 = GetSafeString(gstnDetailed?["principalPlaceState"]);
				return state2.Trim();
			}
			catch
			{
				return string.Empty;
			}
		}


	}

}

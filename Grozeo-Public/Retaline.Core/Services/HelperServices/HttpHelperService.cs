using Microsoft.AspNetCore.Authentication;
using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Configuration;
using Retaline.Core.BusinessModel.UserDetails;
using Retaline.Core.Services.Authentication;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Net.Http;
using System.Net.Http.Headers;
using System.Net.Http.Json;
using System.Text;
using System.Text.Json;
using System.Threading.Tasks;

namespace Retaline.Core.Services.HelperServices
{
    /// <summary>
    /// Http Helper service
    /// Consolidate all api calls.
    /// </summary>
    public class HttpHelperService : IHttpHelperService
    {

        #region Private Variables

        private readonly IHttpContextAccessor _httpContextAccessor;
        private readonly IConfiguration _configuration;
        //private User cachedUser = null;
        // private GuestData _guestData;
        private readonly ViewModel.Tenant.AppTenant tenant;

        private const string GuestCookieName = "RET_guest_userNew";

        #endregion

        public HttpHelperService(IHttpContextAccessor httpContextAccessor, IConfiguration configuration, SaasKit.Multitenancy.ITenant<ViewModel.Tenant.AppTenant> tenant)
        {
            _httpContextAccessor = httpContextAccessor;
            _configuration = configuration;
            this.tenant = tenant?.Value;
        }

        /// <summary>
        /// Build partial url to fully qualified url with the left part taken from configuration file.
        /// Skip if the input url is fully qualified (start with http or https).
        /// </summary>
        /// <param name="url"></param>
        /// <returns></returns>
        private string GetAPIUrl(string url)
        {
            if (url.Contains("?"))
                url += "&unikey="+ Guid.NewGuid().ToString();
            else
                url += "?unikey=" + Guid.NewGuid().ToString();

            if (url.StartsWith("https://") || url.StartsWith("http://"))
                return url;

            // Get api url from configuration as left part.
            return (tenant?.APIUrl ?? _configuration["ApiUrls:ApiDomain"]) + url;
        }

        public GuestData GuestUser
        {
            get
            {                
                return GetGuestUser();
            }

        }

        /// <summary>
        /// Get guest token called from API server and store run time variable 
        /// to avoid multiple calls in the same request.
        /// </summary>
        private string GuestToken
        {
            get
            {

                return GuestUser?.Token; //_guestData.Token;
            }
        }

        /// <summary>
        /// Create GET method to call API
        /// </summary>
        /// <typeparam name="T"></typeparam>
        /// <param name="uri"></param>
        /// <param name="value"></param>
        /// <returns></returns>
        public async Task<T> Get<T>(string uri, object value = null, int customStoreGroupId = -1, int customBranchId = -1)
        {
            HttpRequestMessage request;

            if (value is List<KeyValuePair<string, string>>)
            {
                HttpContent content = new FormUrlEncodedContent(value as List<KeyValuePair<string, string>>);
                request = new HttpRequestMessage(HttpMethod.Get, GetAPIUrl(uri))
                {
                    Content = content
                };
            }
            else
            {
                var json = JsonSerializer.Serialize(value);

                request = new HttpRequestMessage(HttpMethod.Get, GetAPIUrl(uri))
                {
                    Content = new StringContent(json, Encoding.UTF8, "application/json")
                };
            }

            return await SendRequest<T>(request, customStoreGroupId, customBranchId);
        }
        /// <summary>
        /// Create DELETE method to call API
        /// </summary>
        /// <typeparam name="T"></typeparam>
        /// <param name="uri"></param>
        /// <param name="value"></param>
        /// <returns></returns>
        public async Task<T> Delete<T>(string uri, object value, int customStoreGroupId = -1, int customBranchId = -1)
        {
            if (value is List<KeyValuePair<string, string>>)
            {
                HttpContent content = new FormUrlEncodedContent(value as List<KeyValuePair<string, string>>);
                var request = new HttpRequestMessage(HttpMethod.Delete, GetAPIUrl(uri))
                {
                    Content = content
                };
                return await SendRequest<T>(request, customStoreGroupId, customBranchId);

            }
            else
            {
                var json = JsonSerializer.Serialize(value);

                var request = new HttpRequestMessage(HttpMethod.Delete, GetAPIUrl(uri))
                {
                    Content = new StringContent(json, Encoding.UTF8, "application/json")
                };
                return await SendRequest<T>(request, customStoreGroupId, customBranchId);
            }

        }

        public async Task<T> Put<T>(string uri, object value, int customStoreGroupId = -1, int customBranchId = -1)
        {
            if (value is List<KeyValuePair<string, string>>)
            {
                HttpContent content = new FormUrlEncodedContent(value as List<KeyValuePair<string, string>>);
                var request = new HttpRequestMessage(HttpMethod.Put, GetAPIUrl(uri))
                {
                    Content = content
                };
                return await SendRequest<T>(request, customStoreGroupId, customBranchId);

            }
            else
            {
                var json = JsonSerializer.Serialize(value);

                var request = new HttpRequestMessage(HttpMethod.Put, GetAPIUrl(uri))
                {
                    Content = new StringContent(json, Encoding.UTF8, "application/json")
                };
                return await SendRequest<T>(request, customStoreGroupId, customBranchId);
            }

        }

        /// <summary>
        /// Create POST method to call API
        /// </summary>
        /// <typeparam name="T"></typeparam>
        /// <param name="uri"></param>
        /// <param name="value"></param>
        /// <returns></returns>
        public async Task<T> Post<T>(string uri, object value, int customStoreGroupId = -1, int customBranchId = -1)
        {
            if (value is List<KeyValuePair<string, string>>)
            {
                HttpContent content = new FormUrlEncodedContent(value as List<KeyValuePair<string, string>>);
                var request = new HttpRequestMessage(HttpMethod.Post, GetAPIUrl(uri))
                {
                    Content = content
                };
                return await SendRequest<T>(request, customStoreGroupId, customBranchId);

            }
            else if (value !=null)
            {
                var json = JsonSerializer.Serialize(value);

                var request = new HttpRequestMessage(HttpMethod.Post, GetAPIUrl(uri))
                {
                    Content = new StringContent(json, Encoding.UTF8, "application/json")
                };
                return await SendRequest<T>(request, customStoreGroupId, customBranchId);
            }
            else
            {
                var request = new HttpRequestMessage(HttpMethod.Post, GetAPIUrl(uri));
                return await SendRequest<T>(request, customStoreGroupId, customBranchId);
            }


        }


        //public async Task<T> Post<T>(string uri, List<KeyValuePair<string, string>> cnt, int type=0)
        //{
        //    //HttpContent content = new FormUrlEncodedContent(cnt);
        //    var request = new HttpRequestMessage(HttpMethod.Post, uri)
        //    {
        //        Content = (cnt != null ? (new FormUrlEncodedContent(cnt)) : new StringContent(JsonSerializer.Serialize(""), Encoding.UTF8, "application/json"))
        //    };
        //    return await SendRequest<T>(request);
        //}
        /// <summary>
        /// Call API and build object from response data.
        /// </summary>
        /// <typeparam name="T"></typeparam>
        /// <param name="request"></param>
        /// <returns></returns>
        private async Task<T> SendRequest<T>(HttpRequestMessage request, int customStoreGroupId=-1, int customBranchId=-1)
        {
            try
            {
                var isApiUrl = !request.RequestUri.IsAbsoluteUri;
                //if (!request.RequestUri.AbsolutePath.EndsWith("/signup/verify"))
                if (!request.RequestUri.AbsolutePath.Contains("/api/signup/"))
                {
                    if (_httpContextAccessor.HttpContext.User.Identity.IsAuthenticated)
                    {
                        if (CurrentUser != null && !String.IsNullOrEmpty(CurrentUser.Token))//isApiUrl)
                            request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", CurrentUser.Token);
                    }
                    else
                    {
                        if (!String.IsNullOrEmpty(GuestToken))
                            request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", GuestToken);
                    }
                }
                if (!_httpContextAccessor.HttpContext.User.Identity.IsAuthenticated)
                {
                    request.Headers.Add("R-Guest-Data", JsonSerializer.Serialize(GuestUser));
                }

                //request.Headers.UserAgent.ParseAdd("Retaline-Web---LOG 1.3");
                string strStoreId = (customStoreGroupId > 0 ? customStoreGroupId.ToString() : tenant?.StoreId);

                if (!String.IsNullOrEmpty(strStoreId))
                    request.Headers.Add("defaultstoregroupid", strStoreId);
                if(customBranchId > 0)
                    request.Headers.Add("defaultbranchid", customBranchId.ToString());

                try {
                    request.Headers.CacheControl = new CacheControlHeaderValue
                    {
                        NoCache = true
                    };
                }
                catch { 
                    //
                }
                var httpClient = new HttpClient();
                using var response = await httpClient.SendAsync(request);

                httpClient.Dispose();

                if (response.StatusCode == HttpStatusCode.Unauthorized)
                {
                    if (_httpContextAccessor.HttpContext.User.Identity.IsAuthenticated)
                    {
                        string strabsurl = request.RequestUri.AbsoluteUri;
                        if (!(strabsurl.EndsWith("/api/customer/address") || strabsurl.EndsWith("/api/category") || strabsurl.EndsWith("/api/mywishlist") || strabsurl.Contains("/api/cart/")))
                            await _httpContextAccessor.HttpContext.SignOutAsync(Microsoft.AspNetCore.Authentication.JwtBearer.JwtBearerDefaults.AuthenticationScheme);
                    }
                    return default;
                }


                //if (!response.IsSuccessStatusCode)
                //{
                //    var error = await response.Content.ReadFromJsonAsync<Dictionary<string, string>>();
                //    throw new Exception(error["message"]);
                //}
                return await response.Content.ReadFromJsonAsync<T>(new System.Text.Json.JsonSerializerOptions() { Encoder = System.Text.Encodings.Web.JavaScriptEncoder.UnsafeRelaxedJsonEscaping });
            }
            catch (Exception ex)
            {
                return default;
            }
        }




        /// <summary>
        /// Get Cached current user.
        /// </summary>
        private User CurrentUser
        {
            get
            {
                try
                {
                    //if (cachedUser == null)
                    //{
                        if (_httpContextAccessor != null && _httpContextAccessor.HttpContext.User != null && _httpContextAccessor.HttpContext.User.Identity.IsAuthenticated)
                        {
                            var userData = _httpContextAccessor.HttpContext.User.Claims.Where(c => c.Type == System.Security.Claims.ClaimTypes.UserData).FirstOrDefault();
                            if (userData != null && !String.IsNullOrEmpty(userData.Value))
                                return Newtonsoft.Json.JsonConvert.DeserializeObject<User>(userData.Value); //cachedUser = Newtonsoft.Json.JsonConvert.DeserializeObject<User>(userData.Value);
                        }
                    //}

                }
                catch { }

                return null; //cachedUser;
            }
        }

        public GuestData GetGuestUser()
        {

            var cookie = _httpContextAccessor.HttpContext.Session.GetString(GuestCookieName); //_httpContextAccessor.HttpContext.Request.Cookies[GuestCookieName];

            if (!string.IsNullOrEmpty(cookie))
            {

                // Decrypt and deserialize guest user object from cookie
                return DecryptGuestUser(cookie);
            }

            // Generate a new guest user if none exists
            var guestUser = CreateNewGuestUser();
            SaveGuestUser(guestUser);
            return guestUser;
        }
        // Save Guest User (store in encrypted cookie)
        private void SaveGuestUser(GuestData guestUser)
        {
            var serializedData = Newtonsoft.Json.JsonConvert.SerializeObject(guestUser);
            var encodedData = EncodeBase64String(serializedData);

            _httpContextAccessor.HttpContext.Session.SetString(GuestCookieName, encodedData);
            //_httpContextAccessor.HttpContext.Response.Cookies.Append(GuestCookieName, encodedData, new CookieOptions
            //{
            //    IsEssential = true,
            //    HttpOnly = true,
            //    Secure = true,
            //    SameSite = SameSiteMode.Strict,
            //    Expires = null
            //});
        }

        public void SetGuestLocation(double lat, double lng, string locality)
        {
            var cookie = _httpContextAccessor.HttpContext.Session.GetString(GuestCookieName); // _httpContextAccessor.HttpContext.Request.Cookies[GuestCookieName];
            GuestData guest = null;
            if (!string.IsNullOrEmpty(cookie))
            {

                // Decrypt and deserialize guest user object from cookie
                guest= DecryptGuestUser(cookie);
            }

            // Generate a new guest user if none exists
            if (guest == null)
                guest = CreateNewGuestUser();

            guest.GuestLatitude = lat.ToString();
            guest.GuestLongitude = lng.ToString();
            guest.GuestLocality = locality;
            var serializedData = Newtonsoft.Json.JsonConvert.SerializeObject(guest);
            var encodedData = EncodeBase64String(serializedData);

            _httpContextAccessor.HttpContext.Session.SetString(GuestCookieName, encodedData);
            //_httpContextAccessor.HttpContext.Response.Cookies.Append(GuestCookieName, encodedData, new CookieOptions
            //{
            //    IsEssential = true,
            //    HttpOnly = true,
            //    Secure = true,
            //    SameSite = SameSiteMode.Strict,
            //    Expires = null
            //});
        }


        // Decode guest user object from Base64 session value
        private GuestData DecryptGuestUser(string encodedData)
        {
            var serializedData = DecodeBase64String(encodedData);
            return Newtonsoft.Json.JsonConvert.DeserializeObject<GuestData>(serializedData);
        }

        // Create a New Guest User Object
        private GuestData CreateNewGuestUser()
        {
            GuestData guestUser = new GuestData();
            try
            {
                string url = _configuration["ApiUrls:Authentication:GetGuest"];
                HttpRequestMessage request = new HttpRequestMessage(HttpMethod.Get, GetAPIUrl(url));
                var httpClient = new HttpClient();
                using (var response = httpClient.SendAsync(request).Result)
                {
                    httpClient.Dispose();
                    var data = response.Content.ReadFromJsonAsync<GuestRoot>(new JsonSerializerOptions() { Encoder = System.Text.Encodings.Web.JavaScriptEncoder.UnsafeRelaxedJsonEscaping }).Result;
                    if (data != null && data.Data != null)
                        guestUser = data.Data;
                }
                if (guestUser == null)
                    guestUser = new GuestData();
            }
            catch
            {
                guestUser = new GuestData();
            }
            if (!string.IsNullOrEmpty(_httpContextAccessor.HttpContext.Session.GetString("FTKEY")))
            {
                guestUser.FrontEndToken = _httpContextAccessor.HttpContext.Session.GetString("FTKEY");
            }
            else
            {
                guestUser.FrontEndToken = Guid.NewGuid().ToString();
                _httpContextAccessor.HttpContext.Session.SetString("FTKEY", guestUser.FrontEndToken);
            }

            return guestUser;
        }

        // TODO: This is NOT real encryption — it is Base64 encoding only. Replace with actual AES encryption for guest session data.
        private string EncodeBase64String(string plainText)
        {
            return Convert.ToBase64String(System.Text.Encoding.UTF8.GetBytes(plainText));
        }

        // TODO: This is NOT real decryption — it is Base64 decoding only. Replace with actual AES decryption for guest session data.
        private string DecodeBase64String(string encodedText)
        {
            return System.Text.Encoding.UTF8.GetString(Convert.FromBase64String(encodedText));
        }



    }
}

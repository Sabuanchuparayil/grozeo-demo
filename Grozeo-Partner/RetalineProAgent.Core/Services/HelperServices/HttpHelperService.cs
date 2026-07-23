//using Microsoft.AspNetCore.Authentication;
//using Microsoft.AspNetCore.Http;
//using Microsoft.Extensions.Configuration;
//using RetalineProAgent.Core.BussinessModel.UserDetails;
//using RetalineProAgent.Core.Services.Authentication;
using RetalineProAgent.Core.BussinessModel.UserDetails;
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

namespace RetalineProAgent.Core.Services.HelperServices
{
    public static class HttpHelperService
    {
        static string apiUrl = System.Configuration.ConfigurationSettings.AppSettings.Get("api.url");
        //private readonly IHttpContextAccessor _httpContextAccessor;
        //private readonly IConfiguration _configuration;

        //private User cachedUser = null;
        private static GuestData _guestData;
        private static int _storeId = -1;
        //private readonly ViewModel.Tenant.AppTenant tenant;

        //public HttpHelperService(IHttpContextAccessor httpContextAccessor, IConfiguration configuration, SaasKit.Multitenancy.ITenant<ViewModel.Tenant.AppTenant> tenant)
        //{
        //    _httpContextAccessor = httpContextAccessor;
        //    _configuration = configuration;
        //    //this.tenant = tenant?.Value;
        //}
        public static void ClearToken()
        {
            _guestData = null;
        }
        private static string GetAPIUrl(string url)
        {
            if (url.StartsWith("https://") || url.StartsWith("http://"))
                return url;

                return apiUrl + url;
        }
        private static string GuestToken
        {
            get
            {
                if (_guestData == null)
                {
                    try
                    {
                        string url = "/api/initial"; //_configuration["ApiUrls:Authentication:GetGuest"];
                        //string strStoreId = tenant?.StoreId;
                        if(_storeId > 0)
                            url += $"/{_storeId}";
                        HttpRequestMessage request = new HttpRequestMessage(HttpMethod.Get, GetAPIUrl(url));
                        request.Headers.UserAgent.ParseAdd("Retaline-Web---LOG 1.3");
                        var httpClient = new HttpClient();
                        using (var response = httpClient.SendAsync(request).Result)
                        {
                            httpClient.Dispose();
                            var data = response.Content.ReadFromJsonAsync<GuestRoot>(new JsonSerializerOptions() { Encoder = System.Text.Encodings.Web.JavaScriptEncoder.UnsafeRelaxedJsonEscaping }).Result;
                            if (data != null && data.Data != null)
                                _guestData = data.Data;
                        }
                        if (_guestData == null)
                            _guestData = new GuestData();
                    }
                    catch
                    {
                        _guestData = new GuestData();
                    }
                }
                return _guestData.Token;
            }
        }

        public static async Task<T> Get<T>(string uri, int storeid, object value = null)
        {
            _storeId = storeid;
            var uriBuilder = new UriBuilder(GetAPIUrl(uri));
            //if (!_httpContextAccessor.HttpContext.User.Identity.IsAuthenticated)
            //{
            //    var query = System.Web.HttpUtility.ParseQueryString(uriBuilder.Query);
            //    if (!String.IsNullOrEmpty(guestToken))
            //        query["token"] = guestToken;

            //    uriBuilder.Query = query.ToString();
            //}
            HttpRequestMessage request;
            request = new HttpRequestMessage(HttpMethod.Get, uriBuilder.Uri.ToString());
           
            return SendRequest<T>(request, storeid);
        }

        public static T Post<T>(string uri, object value, int storeid, string authKey = "", List<KeyValuePair<string, string>> headers = null)
        {
            HttpRequestMessage request = new HttpRequestMessage();
            if (value is List<KeyValuePair<string, string>>)
            {
                HttpContent content = new FormUrlEncodedContent(value as List<KeyValuePair<string, string>>);
                request = new HttpRequestMessage(HttpMethod.Post, GetAPIUrl(uri))
                {
                    Content = content
                };
            }
            else
            {
                var json = JsonSerializer.Serialize(value);
                request = new HttpRequestMessage(HttpMethod.Post, GetAPIUrl(uri))
                {
                    Content = new StringContent(json, Encoding.UTF8, "application/json")
                };
                //return SendRequest<T>(request, storeid);
            }

            if (!String.IsNullOrEmpty(authKey))
                request.Headers.Authorization = new AuthenticationHeaderValue(authKey);

            if (headers != null && headers.Count > 0)
                foreach (var header in headers)
                {
                    if (header.Key == "application/json")
                    {
                        request.Headers.Accept.Add(new MediaTypeWithQualityHeaderValue("application/json"));//ACCEPT header
                    }
                    else //if (!request.Headers.Contains(header.Key))
                    {
                        request.Headers.Add(header.Key, header.Value);
                    }
                }
            return SendRequest<T>(request, storeid);
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

        private static T SendRequest<T>(HttpRequestMessage request, int storeid)
        {
            try
            {
                var isApiUrl = !request.RequestUri.IsAbsoluteUri;
                //if (!request.RequestUri.AbsolutePath.EndsWith("/signup/verify"))
                if (!request.RequestUri.AbsolutePath.Contains("/api/signup/"))
                {
                    //if (_httpContextAccessor.HttpContext.User.Identity.IsAuthenticated)
                    //{
                    //    if (CurrentUser != null && !String.IsNullOrEmpty(CurrentUser.Token))//isApiUrl)
                    //        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", CurrentUser.Token);
                    //}
                    //else
                    //{
                    if (request.Headers.Authorization == null && !String.IsNullOrEmpty(GuestToken))
                        request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", GuestToken);
                    //}

                }
                if (storeid > 0)
                    request.Headers.Add("defaultstoregroupid", storeid.ToString());

                request.Headers.UserAgent.ParseAdd("Retaline-Web---LOG 1.3");

                var httpClient = new HttpClient();
                using (var response = httpClient.SendAsync(request).Result)
                {

                    httpClient.Dispose();

                    if (response.StatusCode == HttpStatusCode.Unauthorized)
                    {
                        //if (_httpContextAccessor.HttpContext.User.Identity.IsAuthenticated)
                        //    await _httpContextAccessor.HttpContext.SignOutAsync(Microsoft.AspNetCore.Authentication.JwtBearer.JwtBearerDefaults.AuthenticationScheme);

                        //return default;
                    }


                    //if (!response.IsSuccessStatusCode)
                    //{
                    //    var error = await response.Content.ReadFromJsonAsync<Dictionary<string, string>>();
                    //    throw new Exception(error["message"]);
                    //}
                    return response.Content.ReadFromJsonAsync<T>(new JsonSerializerOptions() { Encoder = System.Text.Encodings.Web.JavaScriptEncoder.UnsafeRelaxedJsonEscaping }).Result;
                }
            }
            catch (Exception ex)
            {
                return default;
            }
        }

    }
}

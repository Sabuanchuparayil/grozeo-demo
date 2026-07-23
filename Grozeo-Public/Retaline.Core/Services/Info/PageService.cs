using Microsoft.Extensions.Configuration;
using Retaline.Core.BusinessModel.API;
using Retaline.Core.BusinessModel.InfoPages;
using Retaline.Core.Caching;
using Retaline.Core.Services.HelperServices;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text.RegularExpressions;
using System.Threading.Tasks;

namespace Retaline.Core.Services.Info
{
    public class PageService : IPageService
    {
        private readonly IHttpHelperService _httpHelperService;
        private readonly IConfiguration _configuration;
        private List<Page> catchedPages;
        private readonly ViewModel.Tenant.AppTenant tenant;
        private readonly IStaticCacheManager _staticCacheManager;

        public PageService(IHttpHelperService httpHelperService, IConfiguration configuration, 
            SaasKit.Multitenancy.ITenant<ViewModel.Tenant.AppTenant> tenant, IStaticCacheManager staticCacheManager)
        {
            _httpHelperService = httpHelperService;
            _configuration = configuration;
            this.tenant = tenant?.Value;
            _staticCacheManager = staticCacheManager;
        }
        public async Task<List<Page>> GetPage() {
            if (catchedPages == null)
            {
                string pageUrl = _configuration["ApiUrls:Info:Pages"].ToString();
                var pages = await _httpHelperService.Get<APIModel<List<Page>>>(pageUrl, null);
                catchedPages = pages.Data;
            }
            return catchedPages;
        }

        /// <summary>
        /// Get content pages.
        /// </summary>
        /// <param name="pageId">1:About,2:Privacy,3:Terms,4:HowItWorks,5:FAQ</param>
        /// <returns></returns>
        public async Task<Page> GetPage(int pageTypeId) {

            var pages = await GetPage();
            if (pages != null && pages.Count > 0)
                return pages.Where(p => p.TypeId == pageTypeId).FirstOrDefault();

            return null;
        }
        public async Task<string> GetAboutUsMiniContent(int maxSize)
        {
            string strAboutUsMini = "";
            try
            {
                string strkey = "";
                if (tenant?.Hostnames.Length > 0)
                    strkey = tenant?.Hostnames[0];
                if (String.IsNullOrEmpty(strkey) && !String.IsNullOrEmpty(tenant?.Name))
                    strkey = tenant?.Name.Trim().ToLower().Replace(" ", "");

                if (!String.IsNullOrEmpty(strkey))
                {
                    Retaline.Core.Caching.CacheKey TenantFooterCacheKey = new($"Retl.AppTenant.footercontent.host." + strkey);
                    var key = _staticCacheManager.PrepareKeyForDefaultCache(TenantFooterCacheKey);
                    string strAboutUsFooterCached = _staticCacheManager.Get<string>(key, () =>
                    {
                        string strContent="";
                        var pages = GetPage().Result;
                        if (pages != null && pages.Count > 0)
                        {
                            var page = pages.Where(p => p.TypeId == 1).FirstOrDefault();
                            strContent = page.Content.Replace("[[Store Name]]", tenant?.Name);
                            // Remove html tags
                            strContent = Regex.Replace(strContent, "<.*?>", String.Empty);
                            // Shrink text
                            if (!String.IsNullOrEmpty(strContent) && strContent.Length > maxSize)
                                strContent = strContent.Substring(0, maxSize) + "..";
                        }
                        return strContent;
                    });
                    strAboutUsMini = strAboutUsFooterCached;
                }                
            }catch (Exception ex) { }

            return strAboutUsMini;
        }
        public async Task<string> ContactSubmit(string email, string phone, string message)
        {
            List<KeyValuePair<string, string>> requestParams = new List<KeyValuePair<string, string>>();
            requestParams.Add(new KeyValuePair<string, string>("email", email));
            requestParams.Add(new KeyValuePair<string, string>("phone", phone));
            requestParams.Add(new KeyValuePair<string, string>("message", message));
            requestParams.Add(new KeyValuePair<string, string>("tenantid", tenant?.StoreId));
            requestParams.Add(new KeyValuePair<string, string>("store", tenant?.Name));

            //var val=new KeyValuePair<string, string>()
            //var requestParams = new Dictionary<string, object>
            //{
            //    { "email", email},
            //    { "phone", phone},
            //    { "message", message }
            //};
            string crmUrl = _configuration["ApiUrls:Info:CrmContactUrl"].ToString();
            //string url = "http://leads.bitszol.com/api/entries.aspx?frm=2392";
            var aPIData = await _httpHelperService.Post<APIModel<string>>(crmUrl, requestParams);
            return aPIData.Data;
        }

        public async Task<string> OrderHelpSubmit(string email, string phone, string message, string orderId, string orderNum, string branch, string orderdate)
        {
            List<KeyValuePair<string, string>> requestParams = new List<KeyValuePair<string, string>>();
            requestParams.Add(new KeyValuePair<string, string>("email", email));
            requestParams.Add(new KeyValuePair<string, string>("phone", phone));
            requestParams.Add(new KeyValuePair<string, string>("message", message));
            requestParams.Add(new KeyValuePair<string, string>("orderId", orderId));
            requestParams.Add(new KeyValuePair<string, string>("orderNum", orderNum));

            requestParams.Add(new KeyValuePair<string, string>("branch", branch));
            requestParams.Add(new KeyValuePair<string, string>("orderdate", orderdate));
            requestParams.Add(new KeyValuePair<string, string>("tenantid", tenant?.StoreId));
            requestParams.Add(new KeyValuePair<string, string>("store", tenant?.Name));

            string crmUrl = _configuration["ApiUrls:Info:CrmOrderUrl"].ToString();
            var aPIData = await _httpHelperService.Post<APIModel<string>>(crmUrl, requestParams);
            return aPIData.Data;
        }

        public async Task<object> SubmitFeedback(string phone, string email, string msg)
        {
            List<KeyValuePair<string, string>> requestParams = new List<KeyValuePair<string, string>>();
            requestParams.Add(new KeyValuePair<string, string>("fb_mobile", phone));
            requestParams.Add(new KeyValuePair<string, string>("fb_email", email));
            requestParams.Add(new KeyValuePair<string, string>("fb_comments", msg));


            string crmUrl = _configuration["ApiUrls:Info:Feedback"].ToString();
            var aPIData = await _httpHelperService.Post<APISuccessModel>(crmUrl, requestParams);
            return aPIData.Message;
        }
        /// <summary>
        /// Get all FAQ content
        /// </summary>
        /// <returns></returns>
        public async Task<List<FaqContent>> GetFAQ()
        {
            string pageUrl = _configuration["ApiUrls:Info:Faq"].ToString();
            var result = await _httpHelperService.Get<APIModel<Faq>>(pageUrl, null);
            if (result != null && result.Data != null)
                return result.Data.Content;

            return default;
        }

    }
}

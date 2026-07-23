using Microsoft.IdentityModel.Abstractions;
using Retaline.Core.BusinessModel.Home;
using Retaline.Core.BusinessModel.Logging;
using Retaline.Core.Services.Home;
using Retaline.Core.ViewModel.Home;
using Retaline.Core.ViewModel.Tenant;
using SaasKit.Multitenancy;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Handlers
{
    public class HomePageHandlerService : IHomePageHandlerService
    {
        private readonly IHomePageService _homePageService;
        private readonly AppTenant _tenant;


        public HomePageHandlerService(IHomePageService homePageService,
            ITenant<AppTenant> tenant)
        {
            _homePageService = homePageService;
            _tenant =tenant?.Value;
        }
        public async Task<List<HomePageViewModel>> GetHomePageContent(int customStoreGroupId = -1)
        {
            var dataFromAPI = await _homePageService.GetHomePageContent(customStoreGroupId);
            List<HomePageViewModel> homepageContents = new();
            GetBannerContent(dataFromAPI, homepageContents);
            var data = _tenant?.OwnBannerOnly;
            return homepageContents;
        }

        public async Task<List<HomePageViewModel>> GetHomePageContentBasedOnType(int id, string key)
        {
            var dataFromAPI = await _homePageService.GetHomePageContentBasedOnType(id, key);
            List<HomePageViewModel> homepageContents = new();
            GetBannerContent(dataFromAPI, homepageContents);
            return homepageContents;
        }

        public async Task<List<HomePageViewModel>> GetHomePageForRetailerType(int retailerTypeId)
        {
            var dataFromAPI = await _homePageService.GetHomePageForRetailerType(retailerTypeId);
            List<HomePageViewModel> homepageContents = new();
            GetBannerContent(dataFromAPI, homepageContents);
            return homepageContents;
        }

        private static void GetBannerContent(List<HomeDetails> dataFromAPI, List<HomePageViewModel> homepageContents)
        {
            FormatData(dataFromAPI, homepageContents, "advertisement");
            FormatData(dataFromAPI, homepageContents, "category");
            FormatData(dataFromAPI, homepageContents, "Featured Products");
            FormatData(dataFromAPI, homepageContents, "Brand");
            FormatData(dataFromAPI, homepageContents, "Popular products");
            FormatData(dataFromAPI, homepageContents, "shop by concern");
            FormatData(dataFromAPI, homepageContents, "subcategory");
            FormatData(dataFromAPI, homepageContents, "combinedcategory");
        }

        private static void FormatData(List<HomeDetails> dataFromAPI, List<HomePageViewModel> homepageContents, string type)
        {
            HomePageViewModel content = new()
            {
                Type = type
            };
            if (dataFromAPI != null)
            {
                try
                {
                    HomeDetails data = dataFromAPI.Where(item => item.Type.ToLower() == type.ToLower()).FirstOrDefault();
                    if (data != null)
                    {
                        string strData = data.DynamicValue.GetRawText();
                        if (!string.IsNullOrEmpty(strData))
                        {
                            if (type == "Featured Products" || type == "Popular products")
                            {
                                content.Products = System.Text.Json.JsonSerializer.Deserialize<List<Retaline.Core.BusinessModel.Catalog.Product>>(strData);
                            }
                            else
                            {
                                content.Content = System.Text.Json.JsonSerializer.Deserialize<List<HomeValue>>(strData);
                            }
                        }
                    }
                }
                catch(Exception ex)
                {
                    
                }
                //content.Content = dataFromAPI.Where(item => item.Type.ToLower() == type.ToLower()).SelectMany(item => item.Value).ToList();
                homepageContents.Add(content);
            }
        }
    }
}

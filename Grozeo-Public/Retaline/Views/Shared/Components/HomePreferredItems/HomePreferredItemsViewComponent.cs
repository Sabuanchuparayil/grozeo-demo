using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Retaline.Core.BusinessModel.Home;
using Retaline.Core.BusinessModel.Store;
using Retaline.Core.Services.Authentication;
using Retaline.Core.Services.ProfileManagement;
using Retaline.Core.ViewModel.Home;
using System;
using System.Collections.Generic;
using System.Globalization;
using System.Linq;
using System.Reflection;

namespace Retaline.Web.Views.Shared.Components.HomePreferredStores
{
    public class HomePreferredItemsViewComponent : ViewComponent
    {
        private readonly ICustomAuthenticationService _authenticationService;
        private readonly IProfileService _profileService;
        private readonly IConfiguration _configuration;

        public HomePreferredItemsViewComponent(ICustomAuthenticationService authenticationService, IProfileService profileService, IConfiguration configuration)
        {
            _authenticationService = authenticationService;
            _profileService = profileService;
            _configuration = configuration;
        }

        public IViewComponentResult Invoke(List<HomePageViewModel> model)
        {
            if (model != null && model.Any(m => m.Type == "combinedcategory" && m.Content.Count > 0))
            {
                var prefVCategories = model.Where(m => m.Type == "combinedcategory").FirstOrDefault().Content.Where(c => c.CategoryType == 4 && c.isPreferred == 1).OrderBy(c=> c.DisplayOrder).ToList();
                return View(prefVCategories);
            }

            return View(new List<HomeValue>());
        }

        //private void CalculateDistance(List<Store> stores, User user)
        //{
        //    for (int i = 0; i < stores.Count; i++)
        //    {
        //        double latitude = Convert.ToDouble(stores[i].Lat);
        //        double longitude = Convert.ToDouble(stores[i].Lng);
        //        var R = 6371e3; // metres
        //        var φ1 = user.PrimaryAddress.Latitude * Math.PI / 180; // φ, λ in radians
        //        var φ2 = latitude * Math.PI / 180;
        //        var Δφ = (latitude - user.PrimaryAddress.Latitude) * Math.PI / 180;
        //        var Δλ = (longitude - user.PrimaryAddress.Longitude) * Math.PI / 180;

        //        var a = Math.Sin(Δφ / 2) * Math.Sin(Δφ / 2) +
        //                  Math.Cos(φ1) * Math.Cos(φ2) *
        //                  Math.Sin(Δλ / 2) * Math.Sin(Δλ / 2);
        //        var c = 2 * Math.Atan2(Math.Sqrt(a), Math.Sqrt(1 - a));

        //        var d = (R * c) / 1000; // in kilo metres
        //        stores[i].Distance = Math.Round(d, 2);
        //    }
        //    stores = stores.OrderBy(item => item.Distance).ToList();
        //}

    }
}

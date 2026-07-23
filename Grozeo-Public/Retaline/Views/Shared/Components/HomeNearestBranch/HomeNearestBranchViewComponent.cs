using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Retaline.Core.BusinessModel.Store;
using Retaline.Core.Services.Authentication;
using Retaline.Core.Services.ProfileManagement;
using System;
using System.Collections.Generic;
using System.Globalization;
using System.Linq;

namespace Retaline.Web.Views.Shared.Components.HomeNearestBranch
{
    public class HomeNearestBranchViewComponent : ViewComponent
    {
        private readonly ICustomAuthenticationService _authenticationService;
        private readonly IProfileService _profileService;
        private readonly IConfiguration _configuration;

        public HomeNearestBranchViewComponent(ICustomAuthenticationService authenticationService, IProfileService profileService, IConfiguration configuration)
        {
            _authenticationService = authenticationService;
            _profileService = profileService;
            _configuration = configuration;
        }

        public IViewComponentResult Invoke(bool isGuest = true)
        {
            double lat = 0, lng = 0;
            try
            {
                lat = Convert.ToDouble(_configuration["DefLatitude"]);
                lng = Convert.ToDouble(_configuration["DefLongitude"]);
            }
            catch { }

            if (!isGuest && User.Identity.IsAuthenticated)
            {
                try
                {
                    var user = _authenticationService.GetUserFromClaims();
                    if (user != null && user.PrimaryAddress != null)
                    {
                        lat = user.PrimaryAddress.Latitude;
                        lng = user.PrimaryAddress.Longitude;
                    }

                }
                catch { }
            }
            if (lat > 0 && lng > 0)
            {
                try
                {
                    var result = _profileService.GetNearestStores(lat, lng);
                    //CalculateDistance(result.Result.Data, user);
                    if (result != null && result.Result != null && result.Result.Data != null)
                        return View(result.Result.Data.Data);

                }
                catch { }
            }

            return View(new List<StoreGroup>());
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

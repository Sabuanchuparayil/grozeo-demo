using Microsoft.AspNetCore.Mvc;
using Retaline.Core.BusinessModel.Home;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Views.Shared.Components.HomeBanner
{
    public class BonitosCollectionBannerViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke(List<HomeValue> homeValues)
        {
            return View("BonitosCollectionBanner", homeValues);
        }
    }
}

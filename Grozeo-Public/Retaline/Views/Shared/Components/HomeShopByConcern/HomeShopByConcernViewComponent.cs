using Retaline.Core.BusinessModel.Home;
using Microsoft.AspNetCore.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Views.Shared.Components.HomeContent
{
    public class HomeShopByConcernViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke(List<HomeValue> homeValues)
        {
            return View(homeValues);
        }
    }
}

using Microsoft.AspNetCore.Mvc;
using Retaline.Core.BusinessModel.Home;
using System.Collections.Generic;

namespace Retaline.Web.Views.Shared.Components.HomeBrands
{
    public class HomeItemViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke(List<HomeValue> item)
        {
            return View(item);
        }
    }
}

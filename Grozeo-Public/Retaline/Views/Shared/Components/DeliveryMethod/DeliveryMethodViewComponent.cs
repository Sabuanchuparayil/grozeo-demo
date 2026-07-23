using Microsoft.AspNetCore.Mvc;
using Retaline.Core.BusinessModel.Home;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Views.Shared.Components.HomeBrands
{
    public class DeliveryMethodViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke()
        {
            return View();
        }
    }
}

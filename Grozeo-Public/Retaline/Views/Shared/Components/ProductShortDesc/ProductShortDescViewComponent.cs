using Microsoft.AspNetCore.Mvc;
using Retaline.Core.BusinessModel.Home;
using System.Collections.Generic;

namespace Retaline.Web.Views.Shared.Components.ProductShortDesc
{
    public class ProductShortDescViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke(Core.BusinessModel.ProductDetails.ProductDetailsModel item)
        {
            return View(item);
        }
    }
}

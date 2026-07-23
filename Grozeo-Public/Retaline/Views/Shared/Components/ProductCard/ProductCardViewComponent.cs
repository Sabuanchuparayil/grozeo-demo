using Microsoft.AspNetCore.Mvc;
using Retaline.Core.BusinessModel.Catalog;
using System.Collections.Generic;

namespace Retaline.Web.Views.Shared.Components.HomeBrands
{
    public class ProductCardViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke(List<Product> item)
        {
            return View(item);
        }
    }
}

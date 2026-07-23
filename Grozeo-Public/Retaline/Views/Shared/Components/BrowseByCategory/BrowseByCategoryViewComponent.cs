using Microsoft.AspNetCore.Mvc;
using Retaline.Core.BusinessModel.Catalog;
using System.Collections.Generic;

namespace Retaline.Web.Views.Shared.Components.HomeBrands
{
    public class BrowseByCategoryViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke(List<CategoryData> categories)
        {
            return View(categories);
        }
    }
}
